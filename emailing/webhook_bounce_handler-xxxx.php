<?php
header('Content-Type: application/json');

// Include your database connection setup
require_once '../gobrikconn_env.php';

try {
    // Log when the file is accessed
    error_log("webhook_bounce_handler.php accessed at " . date('Y-m-d H:i:s'));

    // Read the POST data sent by Mailgun
    $input = file_get_contents('php://input');

    // Log the raw POST input for debugging
    if (!empty($input)) {
        error_log("Raw JSON payload received: $input");
    } else {
        error_log("No JSON payload received.");
        http_response_code(400); // Respond with HTTP 400 for bad request
        exit();
    }

    // Decode the JSON input
    $data = json_decode($input, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("Failed to decode JSON payload: " . json_last_error_msg());
        http_response_code(400); // Respond with HTTP 400 for bad request
        exit();
    }

    // Log the decoded JSON for further debugging
    error_log("Decoded JSON payload: " . print_r($data, true));

    // Check if the event is a bounce
    if (isset($data['event-data']['event']) && $data['event-data']['event'] === 'bounced') {
        $email = $data['event-data']['recipient']; // The email address that bounced
        $error_message = $data['event-data']['delivery-status']['message']; // Bounce reason
        $timestamp = $data['event-data']['timestamp']; // Event timestamp

        // Log the bounce details
        error_log("Bounce detected for $email at $timestamp: $error_message");

        // Prepare SQL to update the emailing_status field for the corresponding record
        $sql_update_bounce = "
            UPDATE tb_ecobrickers
            SET emailing_status = 'Bounced'
            WHERE email_addr = ?
        ";

        $stmt_update_bounce = $gobrik_conn->prepare($sql_update_bounce);
        if (!$stmt_update_bounce) {
            throw new Exception('Error preparing update statement: ' . $gobrik_conn->error);
        }

        // Bind parameters and execute the statement
        $stmt_update_bounce->bind_param('s', $email);
        $stmt_update_bounce->execute();

        if ($stmt_update_bounce->affected_rows > 0) {
            error_log("Successfully updated emailing_status to 'Bounced' for $email.");
        } else {
            error_log("No record found for $email. No update was made.");
        }

        $stmt_update_bounce->close();
    } else {
        error_log("Event data is not a bounce or is missing. Event type: " . ($data['event-data']['event'] ?? 'Unknown'));
    }

    // Respond with HTTP 200 to acknowledge the webhook
    http_response_code(200);
} catch (Exception $e) {
    // Handle exceptions and log errors
    error_log("Error in webhook_bounce_handler: " . $e->getMessage());
    http_response_code(500); // Respond with HTTP 500 for server errors
}

exit();
?>