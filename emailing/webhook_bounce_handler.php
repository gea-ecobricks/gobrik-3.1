<?php
header('Content-Type: application/json');

// Include your database connection setup
require_once '../gobrikconn_env.php';

try {
    // Read the POST data sent by Mailgun
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    // Check if the event is a bounce
    if (isset($data['event-data']['event']) && $data['event-data']['event'] === 'bounced') {
        $email = $data['event-data']['recipient']; // The email address that bounced
        $error_message = $data['event-data']['delivery-status']['message']; // Bounce reason
        $timestamp = $data['event-data']['timestamp']; // Event timestamp

        // Log the bounce for debugging purposes
        error_log("Bounce detected for $email at $timestamp: $error_message");

        // Prepare SQL to update the test_email_status field for the corresponding record
        $sql_update_bounce = "
            UPDATE tb_ecobrickers
            SET test_email_status = 'Bounced'
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
            error_log("Successfully updated test_email_status to 'Bounced' for $email.");
        } else {
            error_log("No record found for $email. No update was made.");
        }

        $stmt_update_bounce->close();
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