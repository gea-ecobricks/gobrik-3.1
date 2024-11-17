<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include the database connection
require_once '../buwanaconn_env.php';

// Retrieve the conversation ID from the POST request
$conversation_id = isset($_POST['conversation_id']) ? intval($_POST['conversation_id']) : 0;

$response = [];

// Validate the conversation ID
if ($conversation_id > 0) {
    // Begin a transaction to ensure all deletions are processed
    $buwana_conn->begin_transaction();
    try {
        // Delete all messages associated with the conversation
        $stmt = $buwana_conn->prepare("DELETE FROM messages_tb WHERE conversation_id = ?");
        $stmt->bind_param("i", $conversation_id);
        $stmt->execute();
        $stmt->close();

        // Delete all participants of the conversation
        $stmt = $buwana_conn->prepare("DELETE FROM participants_tb WHERE conversation_id = ?");
        $stmt->bind_param("i", $conversation_id);
        $stmt->execute();
        $stmt->close();

        // Delete the conversation itself
        $stmt = $buwana_conn->prepare("DELETE FROM conversations_tb WHERE conversation_id = ?");
        $stmt->bind_param("i", $conversation_id);
        $stmt->execute();
        $stmt->close();

        // Commit the transaction
        $buwana_conn->commit();

        // Return success response
        $response = [
            "status" => "success",
            "message" => "Conversation deleted successfully."
        ];
    } catch (Exception $e) {
        $buwana_conn->rollback();
        $response = [
            "status" => "error",
            "message" => "Failed to delete the conversation: " . $e->getMessage()
        ];
    }
} else {
    $response = [
        "status" => "error",
        "message" => "Invalid conversation ID."
    ];
}

// Output the response as JSON
header('Content-Type: application/json');
echo json_encode($response);

// Close the database connection
$buwana_conn->close();
?>
