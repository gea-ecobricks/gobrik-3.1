<?php
header('Content-Type: application/json');

// Include your database connection setup
require_once '../gobrikconn_env.php';

// Retrieve Mailgun webhook signing key from the environment
$mailgun_signing_key = getenv('MAILGUN_WEBHOOK');

if (!$mailgun_signing_key) {
    error_log("Mailgun webhook signing key not set in the environment.");
    http_response_code(500); // Internal Server Error
    exit();
}

try {
    // Log when the file is accessed
    error_log("webhook_handler.php accessed at " . date('Y-m-d H:i:s'));

    // Read the POST data sent by Mailgun
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if (json_last_error() !== JSON_ERROR_NONE || empty($data)) {
        error_log("Invalid or empty JSON payload received.");
        http_response_code(400); // Bad Request
        exit();
    }

    // Validate the webhook signature
    $signature = $data['signature'] ?? null;
    if (!isset($signature['timestamp'], $signature['token'], $signature['signature'])) {
        error_log("Webhook signature is missing or incomplete.");
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
        error_log("Invalid Mailgun webhook signature.");
        http_response_code(403); // Forbidden
        exit();
    }

    // Extract relevant data
    $email_addr = $data['event-data']['recipient'] ?? 'Unknown';
    $timestamp = isset($data['event-data']['timestamp'])
        ? date('Y-m-d H:i:s', (int) $data['event-data']['timestamp'])
        : 'Unknown';
    $basic_mailgun_status = $data['event-data']['event'] ?? 'unknown';
    $email_subject = $data['event-data']['message']['headers']['subject'] ?? 'No Subject';
    $response_message = $data['event-data']['delivery-status']['message'] ?? 'No response message';

    // Log a concise summary of the event
    error_log("Mailgun Event: $email_subject to $email_addr was sent on $timestamp and returned: \"$response_message\"");

    // Update the database with the new emailing status
    $sql_update_status = "
        UPDATE tb_ecobrickers
        SET emailing_status = ?
        WHERE email_addr = ?
    ";

    $stmt_update_status = $gobrik_conn->prepare($sql_update_status);
    if (!$stmt_update_status) {
        throw new Exception('Error preparing update statement: ' . $gobrik_conn->error);
    }

    // Bind parameters and execute the statement
    $stmt_update_status->bind_param('ss', $basic_mailgun_status, $email_addr);
    $stmt_update_status->execute();

    if ($stmt_update_status->affected_rows > 0) {
        error_log("Successfully updated emailing_status to '$basic_mailgun_status' for $email_addr.");
    } else {
        error_log("No record found for $email_addr. No update was made.");
    }

    $stmt_update_status->close();

    // Respond with HTTP 200 to acknowledge the webhook
    http_response_code(200);
} catch (Exception $e) {
    // Handle exceptions and log errors
    error_log("Error in webhook_handler: " . $e->getMessage());
    http_response_code(500); // Internal Server Error
}

exit();
?>