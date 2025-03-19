<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../buwanaconn_env.php'; // Database connection
require '../vendor/autoload.php'; // Composer autoload for Mailgun API

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

$created_by = isset($_POST['created_by']) ? intval($_POST['created_by']) : 0;
$message = isset($_POST['message']) ? trim($_POST['message']) : '';

$response = [];

if ($created_by > 0 && !empty($message)) {
    $buwana_conn->begin_transaction();
    try {
        // Create a new conversation
        $stmt = $buwana_conn->prepare("INSERT INTO conversations_tb (created_by) VALUES (?)");
        $stmt->bind_param("i", $created_by);
        $stmt->execute();
        $conversation_id = $buwana_conn->insert_id;
        $stmt->close();

        // Add participants to the conversation
        $dev_team_ids = [1, 150,];
        $stmt = $buwana_conn->prepare("INSERT INTO participants_tb (conversation_id, buwana_id) VALUES (?, ?)");
        foreach ($dev_team_ids as $dev_id) {
            $stmt->bind_param("ii", $conversation_id, $dev_id);
            $stmt->execute();
        }
        $stmt->bind_param("ii", $conversation_id, $created_by);
        $stmt->execute();
        $stmt->close();

        // Insert the user's message
        $stmt = $buwana_conn->prepare("INSERT INTO messages_tb (conversation_id, sender_id, content) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $conversation_id, $created_by, $message);
        $stmt->execute();
        $message_id = $buwana_conn->insert_id;
        $stmt->close();

        // Fetch the created_at timestamp
        $stmt = $buwana_conn->prepare("SELECT created_at FROM messages_tb WHERE message_id = ?");
        $stmt->bind_param("i", $message_id);
        $stmt->execute();
        $stmt->bind_result($date_sent);
        $stmt->fetch();
        $stmt->close();

        // Fetch the sender's email
        $stmt = $buwana_conn->prepare("SELECT email FROM users_tb WHERE buwana_id = ?");
        $stmt->bind_param("i", $created_by);
        $stmt->execute();
        $stmt->bind_result($sender_email);
        $stmt->fetch();
        $stmt->close();

        // Commit transaction
        $buwana_conn->commit();

        // Send bug report email notification
        sendBugReportEmail($message_id, $created_by, $date_sent, $message, $sender_email);

        $response = ["status" => "success", "message" => "Bug report created successfully.", "conversation_id" => $conversation_id];
    } catch (Exception $e) {
        $buwana_conn->rollback();
        error_log("Database transaction failed: " . $e->getMessage()); // Log database failure
        $response = ["status" => "error", "message" => "An error occurred: " . $e->getMessage()];
    }
} else {
    $response = ["status" => "error", "message" => "Invalid input data."];
}

// Output JSON response
header('Content-Type: application/json');
echo json_encode($response);
$buwana_conn->close();

/**
 * Send Bug Report Email with Error Logging
 */
function sendBugReportEmail($message_id, $created_by, $date_sent, $message, $sender_email) {
    $client = new Client(['base_uri' => 'https://api.eu.mailgun.net/v3/']);
    $mailgunApiKey = getenv('MAILGUN_API_KEY');
    $mailgunDomain = 'mail.gobrik.com';

    if (!$mailgunApiKey) {
        error_log("Mailgun API key is missing. Check environment variables.");
        return false;
    }

    // Email subject
    $subject = "Bug Report #$message_id";

    // Email body
    $html_body = "
        <div style='font-family: Arial, sans-serif;'>
            <h2>Bug Report Submitted</h2>
            <p>A bug was reported on GoBrik.</p>
            <p><strong>Submitted By:</strong> $created_by</p>
            <p><strong>Date:</strong> $date_sent</p>
            <p><strong>Bug Description:</strong></p>
            <blockquote style='border-left: 4px solid #0073e6; padding-left: 10px; margin-left: 0;'>$message</blockquote>
            <p>You can reply to the user at: <a href='mailto:$sender_email'>$sender_email</a></p>
            <br>
            <p>— GoBrik Bug Tracker</p>
        </div>
    ";

    // Plain text fallback
    $text_body = "Bug Report Submitted\n\nA bug was reported on GoBrik.\n\nSubmitted By: $created_by\nDate: $date_sent\n\nBug Description:\n\"$message\"\n\nYou can reply to the user at: $sender_email\n\n— GoBrik Bug Tracker";

    try {
        error_log("Sending bug report email: Bug #$message_id from $sender_email...");

        $response = $client->post("https://api.eu.mailgun.net/v3/$mailgunDomain/messages", [
            'auth' => ['api', $mailgunApiKey],
            'form_params' => [
                'from' => 'GoBrik Support <support@ecobricks.org>',
                'to' => 'support@ecobricks.org',
                'bcc' => 'russ@ecobricks.org, franoflanagan@ecobricks.org, mikejpof@gmail.com',
                'subject' => $subject,
                'html' => $html_body,
                'text' => $text_body,
                'h:Reply-To' => $sender_email,
            ]
        ]);

        $statusCode = $response->getStatusCode();
        $body = $response->getBody()->getContents();
        error_log("Mailgun Response Code: $statusCode");
        error_log("Mailgun Response Body: $body");

        if ($statusCode == 200) {
            error_log("Bug report email sent successfully.");
            return true;
        } else {
            error_log("Mailgun email failed with status code: $statusCode");
            return false;
        }
    } catch (RequestException $e) {
        error_log("Mailgun API Exception: " . $e->getMessage());
        if ($e->hasResponse()) {
            $errorBody = $e->getResponse()->getBody()->getContents();
            error_log("Mailgun API Response Error: $errorBody");
        }
        return false;
    }
}
?>
