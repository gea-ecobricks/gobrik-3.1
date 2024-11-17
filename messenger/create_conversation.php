<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include the database connection
require_once '../buwanaconn_env.php';

// Retrieve the inputs from the POST request
$created_by = isset($_POST['created_by']) ? intval($_POST['created_by']) : 0;
$participant_ids = isset($_POST['participant_ids']) ? json_decode($_POST['participant_ids'], true) : [];

$response = [];

// Check if the required data is present
if ($created_by > 0 && !empty($participant_ids) && is_array($participant_ids)) {
    // Begin a transaction to ensure data integrity
    $buwana_conn->begin_transaction();
    try {
        // Create a new conversation
        $stmt = $buwana_conn->prepare("INSERT INTO conversations_tb (created_by) VALUES (?)");
        $stmt->bind_param("i", $created_by);
        $stmt->execute();
        $conversation_id = $buwana_conn->insert_id;
        $stmt->close();

        // Add participants to the conversation, including the creator
        $stmt = $buwana_conn->prepare("INSERT INTO participants_tb (conversation_id, buwana_id) VALUES (?, ?)");
        foreach ($participant_ids as $buwana_id) {
            $stmt->bind_param("ii", $conversation_id, $buwana_id);
            $stmt->execute();
        }
        // Add the creator as a participant if not already included
        if (!in_array($created_by, $participant_ids)) {
            $stmt->bind_param("ii", $conversation_id, $created_by);
            $stmt->execute();
        }
        $stmt->close();

        // Commit the transaction
        $buwana_conn->commit();

        // Success response
        $response = [
            "status" => "success",
            "conversation_id" => $conversation_id
        ];
    } catch (Exception $e) {
        // Rollback the transaction in case of an error
        $buwana_conn->rollback();

        // Error response
        $response = [
            "status" => "error",
            "message" => "An error occurred while creating the conversation: " . $e->getMessage()
        ];
    }
} else {
    // Invalid input data
    $response = [
        "status" => "error",
        "message" => "Invalid input data. 'created_by' must be a valid user ID and 'participant_ids' must be a valid array."
    ];
}

// Output the response as JSON
header('Content-Type: application/json');
echo json_encode($response);

// Close the database connection
$buwana_conn->close();
?>
