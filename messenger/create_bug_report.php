<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include the database connection
require_once '../buwanaconn_env.php';

// Retrieve the inputs from the POST request
$created_by = isset($_POST['created_by']) ? intval($_POST['created_by']) : 0;
$message = isset($_POST['message']) ? trim($_POST['message']) : '';

$response = [];

// Check if the required data is present
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
        $dev_team_ids = [1, 150, 145, 144];
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

        // Handle file upload if an image is included
if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    // Log the start of the file upload
    error_log("Image file received: " . $_FILES['image']['name']);

            // Prepare data for the upload function
            $_POST['user_id'] = $created_by;
            $_POST['message_id'] = $message_id;
            $_POST['conversation_id'] = $conversation_id;
            $_FILES['image'] = $_FILES['image']; // Include the image file

            // Log the prepared data before calling the upload script
            error_log("Prepared upload data: User ID: " . $_POST['user_id'] . ", Message ID: " . $_POST['message_id'] . ", Conversation ID: " . $_POST['conversation_id']);

            // Call the upload script and handle the response
            ob_start(); // Start output buffering to capture the JSON response
            include '../messenger/upload_image_attachment.php';
            $upload_response = json_decode(ob_get_clean(), true);

            // Check if the upload response is valid
            if (isset($upload_response['status']) && $upload_response['status'] === 'success') {
                error_log("Image uploaded successfully.");
            } else {
                $error_message = $upload_response['message'] ?? 'Unknown error during upload.';
                error_log("Image upload failed: " . $error_message);
                throw new Exception($error_message);
            }
        } else {
            error_log("No image file received or there was an error in the upload process.");
        }

        $buwana_conn->commit();
        $response = ["status" => "success", "message" => "Bug report created successfully.", "conversation_id" => $conversation_id];
    } catch (Exception $e) {
        $buwana_conn->rollback();
        $response = ["status" => "error", "message" => "An error occurred: " . $e->getMessage()];
    }
} else {
    $response = ["status" => "error", "message" => "Invalid input data."];
}

// Output JSON response
header('Content-Type: application/json');
echo json_encode($response);
$buwana_conn->close();
?>
