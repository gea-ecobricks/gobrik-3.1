<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include the database connection
require_once '../buwanaconn_env.php';

// Retrieve inputs from the POST request
$message_id = isset($_POST['message_id']) ? intval($_POST['message_id']) : 0;
$user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
$status = isset($_POST['status']) ? trim($_POST['status']) : '';
$field = isset($_POST['field']) ? $_POST['field'] : '';

$response = [];

// Validate input data
if ($message_id > 0 && $user_id > 0 && in_array($status, ['sent', 'read', 'received'])) {
    try {
        // Retrieve the current list of readers
        $stmt = $buwana_conn->prepare("SELECT sender_id, recorded_readers FROM messages_tb WHERE message_id = ?");
        $stmt->bind_param("i", $message_id);
        $stmt->execute();
        $stmt->bind_result($sender_id, $recorded_readers);
        $stmt->fetch();
        $stmt->close();

        // Convert recorded_readers to an array
        $readersArray = $recorded_readers ? explode(',', $recorded_readers) : [];

        // If user is not already a reader and is not the sender, add them
        if (!in_array($user_id, $readersArray) && $user_id !== $sender_id) {
            $readersArray[] = $user_id;
            $newRecordedReaders = implode(',', $readersArray);

            // Update recorded_readers along with the specified status field
            $updateQuery = "UPDATE messages_tb SET $field = ?, recorded_readers = ?, updated_at = NOW() WHERE message_id = ?";
            $updateStmt = $buwana_conn->prepare($updateQuery);

            if (!$updateStmt) {
                error_log("Database prepare error: " . $buwana_conn->error);
                throw new Exception("Failed to prepare the update statement.");
            }

            $updateStmt->bind_param("ssi", $status, $newRecordedReaders, $message_id);
            $updateStmt->execute();

            if ($updateStmt->affected_rows > 0) {
                $response = [
                    "status" => "success",
                    "message" => "Message status updated successfully and reader recorded."
                ];
            } else {
                $response = [
                    "status" => "error",
                    "message" => "No status updated. The message may already have this status."
                ];
            }

            $updateStmt->close();
        } else {
            $response = [
                "status" => "success",
                "message" => "User is already recorded as a reader, no update needed."
            ];
        }
    } catch (Exception $e) {
        $response = [
            "status" => "error",
            "message" => "An error occurred while updating the message status: " . $e->getMessage()
        ];
    }
} else {
    $response = [
        "status" => "error",
        "message" => "Invalid input data. 'message_id', 'user_id', and a valid 'status' are required."
    ];
}

// Output the response as JSON
header('Content-Type: application/json');
echo json_encode($response);

$buwana_conn->close();
?>
