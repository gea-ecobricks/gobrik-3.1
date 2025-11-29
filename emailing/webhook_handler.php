<?php
header('Content-Type: application/json');
require_once '../scripts/earthen_subscribe_functions.php';

// Include database connection setups
require_once '../gobrikconn_env.php';  // Gobrik DB (for tb_ecobrickers, mailgun logging)
require_once '../buwanaconn_env.php';  // Buwana DB (for admin_alerts)

function resolveMemberIdByEmail(string $email): ?int
{
    global $gobrik_conn;

    if (!isset($gobrik_conn)) {
        error_log('[MAILGUN] Gobrik database connection not available for member lookup.');
        return null;
    }


    if (!$stmt) {
        error_log('[MAILGUN] Failed to prepare member lookup: ' . $gobrik_conn->error);
        return null;
    }

    $stmt->bind_param('s', $email);
    $stmt->execute();
    $stmt->bind_result($member_id);
    $found = $stmt->fetch();
    $stmt->close();

    return $found ? (int) $member_id : null;
}

function logMailgunEvent(?int $member_id, string $recipient_email, array $eventData): void
{
    global $gobrik_conn;

    if (!isset($gobrik_conn)) {
        error_log('[MAILGUN] Gobrik database connection not available for logging.');
        return;
    }

    $campaign_name = null;
    if (!empty($eventData['tags'][0])) {
        $campaign_name = $eventData['tags'][0];
    } elseif (!empty($eventData['message']['headers']['campaign-name'])) {
        $campaign_name = $eventData['message']['headers']['campaign-name'];
    }

    $user_variables = $eventData['user-variables'] ?? [];
    $newsletter_slug = $user_variables['newsletter_slug'] ?? ($user_variables['newsletter'] ?? null);

    $event_timestamp = isset($eventData['timestamp'])
        ? date('Y-m-d H:i:s', (int) $eventData['timestamp'])
        : date('Y-m-d H:i:s');

    $mailgun_message_id = $eventData['message']['headers']['message-id'] ?? null;
    $mailgun_event_id = $eventData['id'] ?? null;
    $event_type = $eventData['event'] ?? 'unknown';
    $severity = $eventData['severity'] ?? null;
    $reason = $eventData['reason'] ?? ($eventData['delivery-status']['description'] ?? null);
    $error_code = $eventData['delivery-status']['code'] ?? null;
    $error_message = $eventData['delivery-status']['message'] ?? null;

    $client_info = $eventData['client-info'] ?? [];
    $client_ip = $client_info['ip'] ?? ($eventData['ip'] ?? null);
    $client_country = $client_info['country'] ?? null;
    $client_region = $client_info['region'] ?? null;
    $client_city = $client_info['city'] ?? null;
    $client_device = null;

    if (!empty($client_info)) {
        $client_device = trim(($client_info['client-name'] ?? '') . ' ' . ($client_info['client-os'] ?? ''));
        $client_device = $client_device !== '' ? $client_device : null;
    }

    $clicked_url = $eventData['url'] ?? null;
    $tags_json = !empty($eventData['tags']) ? json_encode($eventData['tags']) : null;
    $user_variables_json = !empty($user_variables) ? json_encode($user_variables) : null;
    $raw_payload = !empty($eventData) ? json_encode($eventData) : null;

    $stmt = $gobrik_conn->prepare(
        "INSERT INTO earthen_mailgun_events_tb (
            member_id,
            recipient_email,
            campaign_name,
            newsletter_slug,
            mailgun_message_id,
            mailgun_event_id,
            event_type,
            event_timestamp,
            severity,
            reason,
            error_code,
            error_message,
            client_ip,
            client_country,
            client_region,
            client_city,
            client_device,
            clicked_url,
            tags,
            user_variables,
            raw_payload
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );

    if (!$stmt) {
        error_log('[MAILGUN] Failed to prepare mailgun event insert: ' . $gobrik_conn->error);
        return;
    }

    $stmt->bind_param(
        'isssssssssssssssssss',
        $member_id,
        $recipient_email,
        $campaign_name,
        $newsletter_slug,
        $mailgun_message_id,
        $mailgun_event_id,
        $event_type,
        $event_timestamp,
        $severity,
        $reason,
        $error_code,
        $error_message,
        $client_ip,
        $client_country,
        $client_region,
        $client_city,
        $client_device,
        $clicked_url,
        $tags_json,
        $user_variables_json,
        $raw_payload
    );

    if (!$stmt->execute()) {
        error_log('[MAILGUN] Failed to log event: ' . $stmt->error);
    }

    $stmt->close();
}

// Retrieve Mailgun webhook signing key from the environment
$mailgun_signing_key = getenv('MAILGUN_WEBHOOK');

if (!$mailgun_signing_key) {
    error_log("❌ Mailgun webhook signing key not set in the environment.");
    http_response_code(500); // Internal Server Error
    exit();
}

try {
    // Log when the file is accessed
    error_log("");
    error_log("➡️ webhook_handler.php accessed at " . date('Y-m-d H:i:s'));

    // Read the POST data sent by Mailgun
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if (json_last_error() !== JSON_ERROR_NONE || empty($data)) {
        error_log("❌ Invalid or empty JSON payload received.");
        http_response_code(400); // Bad Request
        exit();
    }

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
    $member_id = resolveMemberIdByEmail($email_addr);

    // Log a concise summary of the event
    $log_message = "📬 Mailgun Event: '$email_subject' to $email_addr was sent on $timestamp and returned: \"$response_message\"";
    error_log($log_message);

    logMailgunEvent($member_id, $email_addr, $data['event-data'] ?? []);

    // 🚨 Detect and log rate limiting issues 🚨
if (stripos($response_message, "rate limited") !== false || stripos($response_message, "throttled") !== false) {
    error_log("🚨 Rate Limiting detected! Logging to admin_alerts.");

    $alert_title = "Rate Limited!";
    $alert_description = "A critical Mailgun log has reported that: \"$log_message\"";
    $alert_unaddressed = 0;

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


    // Member-specific updates have been removed to keep webhook focused on event logging.

    // Respond with HTTP 200 to acknowledge the webhook
    http_response_code(200);
} catch (Exception $e) {
    // Handle exceptions and log errors
    error_log("❌ Error in webhook_handler: " . $e->getMessage());
    http_response_code(500); // Internal Server Error
}

exit();
?>
