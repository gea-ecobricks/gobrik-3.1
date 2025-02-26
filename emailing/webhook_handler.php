<?php
header('Content-Type: application/json');
require_once '../scripts/earthen_subscribe_functions.php';

// Include database connection setups
require_once '../gobrikconn_env.php';  // Gobrik DB (for tb_ecobrickers)
require_once '../buwanaconn_env.php';  // Buwana DB (for failed_emails_tb, admin_alerts)

// Retrieve Mailgun webhook signing key from the environment
$mailgun_signing_key = getenv('MAILGUN_WEBHOOK');

if (!$mailgun_signing_key) {
    error_log("❌ Mailgun webhook signing key not set in the environment.");
    http_response_code(500); // Internal Server Error
    exit();
}

try {
    // Log when the file is accessed
    error_log("🟢 webhook_handler.php accessed at " . date('Y-m-d H:i:s'));

    // Read the POST data sent by Mailgun
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if (json_last_error() !== JSON_ERROR_NONE || empty($data)) {
        error_log("❌ Invalid or empty JSON payload received.");
        http_response_code(400); // Bad Request
        exit();
    }

    // Define failure events
    $failure_events = ['failed', 'bounced', 'complained'];

    // Validate the webhook signature
    $signature = $data['signature'] ?? null;
    if (!isset($signature['timestamp'], $signature['token'], $signature['signature'])) {
        error_log("❌ Webhook signature is missing or incomplete.");
        http_response_code(400); // Bad Request
        exit();
    }

    // Verify the signature
    $expected_signature = hash_hmac(
        'sha256',
        $signature['timestamp'] . $signature['token'],
        $mailgun_signing_key
    );

    if (!hash_equals($expected_signature, $signature['signature'])) {
        error_log("❌ Invalid Mailgun webhook signature.");
        http_response_code(403); // Forbidden
        exit();
    }

    // Extract relevant data
    $email_addr = trim(strtolower($data['event-data']['recipient'] ?? 'Unknown')); // Normalize email
    $timestamp = isset($data['event-data']['timestamp'])
        ? date('Y-m-d H:i:s', (int) $data['event-data']['timestamp'])
        : 'Unknown';
    $basic_mailgun_status = $data['event-data']['event'] ?? 'unknown';
    $email_subject = $data['event-data']['message']['headers']['subject'] ?? 'No Subject';
    $response_message = $data['event-data']['delivery-status']['message'] ?? 'No response message';

    // Log a concise summary of the event
    $log_message = "📬 Mailgun Event: '$email_subject' to $email_addr was sent on $timestamp and returned: \"$response_message\"";
    error_log($log_message);

    // 🚨 Detect and log rate limiting issues 🚨
    if (stripos($response_message, "rate limited") !== false) {
        error_log("🚨 Rate Limiting detected! Logging to admin_alerts.");

        $alert_title = "Rate Limited!";
        $alert_description = "A critical Mailgun log has reported that: \"$log_message\"";
        $alert_unaddressed = 1;

        // Insert alert into `admin_alerts` (avoid duplicates)
        $sql_insert_alert = "
            INSERT INTO admin_alerts (alert_title, alert_message, alert_server_log, addressed, date_posted)
            VALUES (?, ?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE
                alert_message = VALUES(alert_message),
                alert_server_log = VALUES(alert_server_log),
                addressed = VALUES(addressed),
                date_posted = NOW()
        ";

        $stmt_insert_alert = $buwana_conn->prepare($sql_insert_alert);
        if ($stmt_insert_alert) {
            $stmt_insert_alert->bind_param('sssi', $alert_title, $alert_description, $log_message, $alert_unaddressed);
            $stmt_insert_alert->execute();
            $stmt_insert_alert->close();
            error_log("✅ Rate limiting issue logged to admin_alerts.");
        } else {
            error_log("❌ Failed to insert rate limiting alert: " . $buwana_conn->error);
        }
    }

    // 🚨 Fetch current emailing_status before updating 🚨
    $sql_check_status = "SELECT emailing_status FROM tb_ecobrickers WHERE email_addr = ?";
    $stmt_check_status = $gobrik_conn->prepare($sql_check_status);
    $stmt_check_status->bind_param('s', $email_addr);
    $stmt_check_status->execute();
    $stmt_check_status->bind_result($current_status);
    $stmt_check_status->fetch();
    $stmt_check_status->close();

    // ✅ Log what we found in the database
    if ($current_status !== null) {
        error_log("🔎 Found $email_addr in tb_ecobrickers. Current emailing_status: '$current_status'");
    } else {
        error_log("❌ No record found for $email_addr in tb_ecobrickers!");
    }

    // 🚨 Update the database with the new emailing status 🚨
    $sql_update_status = "
        UPDATE tb_ecobrickers
        SET emailing_status = ?
        WHERE email_addr = ?
    ";

    $stmt_update_status = $gobrik_conn->prepare($sql_update_status);
    if (!$stmt_update_status) {
        throw new Exception('❌ Error preparing update statement: ' . $gobrik_conn->error);
    }

    // Bind parameters and execute the statement
    $stmt_update_status->bind_param('ss', $basic_mailgun_status, $email_addr);
    $stmt_update_status->execute();

    // 🚨 Check if any row was actually updated 🚨
    if ($stmt_update_status->affected_rows > 0) {
        error_log("✅ Delivered! Emailing_status set to '$basic_mailgun_status' for $email_addr.");
    } else {
        if ($current_status === $basic_mailgun_status) {
            error_log("👌️ No update needed for $email_addr. Emailing_status was already '$basic_mailgun_status'.");
        } else {
            error_log("⚠️ No record found for $email_addr in tb_ecobrickers. No update was made.");
        }
    }

    $stmt_update_status->close();

    // Respond with HTTP 200 to acknowledge the webhook
    http_response_code(200);
} catch (Exception $e) {
    // Handle exceptions and log errors
    error_log("❌ Error in webhook_handler: " . $e->getMessage());
    http_response_code(500); // Internal Server Error
}

// 🚨 Handle failed emails by logging them to Buwana DB 🚨
if (!empty($email_addr) && in_array($basic_mailgun_status, $failure_events)) {
    error_log("🚫 Adding $email_addr to Buwana failed_emails queue.");

    // Insert failed email into the queue table (Buwana database)
    $sql_insert_failed = "
        INSERT INTO failed_emails_tb (email_addr, fail_reason)
        VALUES (?, ?)
        ON DUPLICATE KEY UPDATE fail_reason = VALUES(fail_reason), created_at = CURRENT_TIMESTAMP
    ";

    $stmt_insert_failed = $buwana_conn->prepare($sql_insert_failed);
    if ($stmt_insert_failed) {
        $stmt_insert_failed->bind_param('ss', $email_addr, $response_message);
        $stmt_insert_failed->execute();
        $stmt_insert_failed->close();
        error_log("✅ Inserted $email_addr into failed_emails_tb successfully.");
    } else {
        error_log("❌ Failed to insert $email_addr into unsubscribe queue: " . $buwana_conn->error);
    }
}

exit();
?>
