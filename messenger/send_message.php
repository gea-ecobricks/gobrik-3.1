<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
error_log("Starting message send process");

// Include the database connection
require_once '../buwanaconn_env.php';

// Retrieve the inputs from the POST request
$conversation_id = isset($_POST['conversation_id']) ? intval($_POST['conversation_id']) : 0;
$sender_id = isset($_POST['sender_id']) ? intval($_POST['sender_id']) : 0;
$content = isset($_POST['content']) ? trim($_POST['content']) : '';

$response = [];

// Check if the required data is present
if ($conversation_id > 0 && $sender_id > 0 && (!empty($content) || isset($_FILES['image']))) {
    $buwana_conn->begin_transaction();
    try {
        // Calculate text size in bytes
        $txt_size_bytes = strlen($content);

        // Insert the new message with initial statuses and calculated txt_size_bytes into messages_tb
        $stmt = $buwana_conn->prepare("
            INSERT INTO messages_tb (conversation_id, sender_id, content, status_sender, status_reader, txt_size_bytes)
            VALUES (?, ?, ?, 'Sending...', 'Unread', ?)
        ");
        $stmt->bind_param("iisi", $conversation_id, $sender_id, $content, $txt_size_bytes);
        $stmt->execute();
        $message_id = $buwana_conn->insert_id;
        $stmt->close();
        error_log("Message ID: $message_id created for Conversation ID: $conversation_id");

        // Initialize image and thumbnail sizes
        $image_size_bytes = 0;
        $thumbnail_size_bytes = 0;

        // Handle file upload if an image is included
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            error_log("Processing image upload for message ID: $message_id");

            // Prepare data for the upload function
            $_POST['user_id'] = $sender_id;
            $_POST['message_id'] = $message_id;
            $_POST['conversation_id'] = $conversation_id;
            $_FILES['image'] = $_FILES['image'];

            // Capture the output of the upload_image_attachment.php script
            ob_start();
            include '../messenger/upload_image_attachment.php';
            $upload_response = json_decode(ob_get_clean(), true);

            // Check the response from the upload script and get file sizes
            if (!isset($upload_response['status']) || $upload_response['status'] !== 'success') {
                throw new Exception($upload_response['message'] ?? 'Unknown error during image upload.');
            }

            // Set image and thumbnail sizes if they exist in the response
            $image_size_bytes = isset($upload_response['image_size_bytes']) ? intval($upload_response['image_size_bytes']) : 0;
            $thumbnail_size_bytes = isset($upload_response['thumbnail_size_bytes']) ? intval($upload_response['thumbnail_size_bytes']) : 0;
        }

        // Calculate total message size in bytes
        $total_msg_size = $txt_size_bytes + $image_size_bytes + $thumbnail_size_bytes;

        // Update message entry with calculated image and thumbnail sizes and total message size
        $update_msg_stmt = $buwana_conn->prepare("
            UPDATE messages_tb
            SET image_size_bytes = ?, thumbnail_size_bytes = ?, total_msg_size = ?
            WHERE message_id = ?
        ");
        $update_msg_stmt->bind_param("iiii", $image_size_bytes, $thumbnail_size_bytes, $total_msg_size, $message_id);
        $update_msg_stmt->execute();
        $update_msg_stmt->close();

        // Update the last message ID, timestamp, and size, and set all_msgs_posted to 0 in the conversation
        $stmt = $buwana_conn->prepare("
            UPDATE conversations_tb
            SET last_message_id = ?,
                updated_at = NOW(),
                size_in_bytes = size_in_bytes + ?,
                all_msgs_posted = 0
            WHERE conversation_id = ?
        ");
        $stmt->bind_param("iii", $message_id, $total_msg_size, $conversation_id);
        $stmt->execute();
        $stmt->close();

        // Commit the transaction
        $buwana_conn->commit();

        // Success response
        $response = [
            "status" => "success",
            "message_id" => $message_id
        ];
    } catch (Exception $e) {
        $buwana_conn->rollback();
        $response = [
            "status" => "error",
            "message" => "An error occurred while sending the message: " . $e->getMessage()
        ];
    }
} else {
    $response = [
        "status" => "error",
        "message" => "Invalid input data. 'conversation_id', 'sender_id', and 'content' or an image are required."
    ];
}

// Output the response as JSON
header('Content-Type: application/json');
echo json_encode($response);

$buwana_conn->close();
?>
