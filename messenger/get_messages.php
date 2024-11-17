<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../buwanaconn_env.php';

$conversation_id = isset($_GET['conversation_id']) ? intval($_GET['conversation_id']) : 0;
$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;

$response = [];

if ($conversation_id > 0 && $user_id > 0) {
    try {
        // Fetch the server's current time
        $serverTime = date("Y-m-d H:i:s");

        // Fetch the current user's name
        $user_stmt = $buwana_conn->prepare("SELECT first_name FROM users_tb WHERE buwana_id = ?");
        $user_stmt->bind_param("i", $user_id);
        $user_stmt->execute();
        $user_stmt->bind_result($first_name);
        $user_stmt->fetch();
        $user_stmt->close();

        // Fetch other participants' names
        $participants_stmt = $buwana_conn->prepare("
            SELECT GROUP_CONCAT(first_name SEPARATOR ', ') AS other_participants
            FROM users_tb u
            JOIN participants_tb p ON u.buwana_id = p.buwana_id
            WHERE p.conversation_id = ? AND p.buwana_id != ?
        ");
        $participants_stmt->bind_param("ii", $conversation_id, $user_id);
        $participants_stmt->execute();
        $participants_stmt->bind_result($other_participants);
        $participants_stmt->fetch();
        $participants_stmt->close();

        // Retrieve size_in_bytes and all_msgs_posted for the conversation
        $conv_stmt = $buwana_conn->prepare("
            SELECT size_in_bytes, all_msgs_posted
            FROM conversations_tb
            WHERE conversation_id = ?
        ");
        $conv_stmt->bind_param("i", $conversation_id);
        $conv_stmt->execute();
        $conv_stmt->bind_result($size_in_bytes, $all_msgs_posted);
        $conv_stmt->fetch();
        $conv_stmt->close();

        // Retrieve messages and gather updates in arrays
        $stmt = $buwana_conn->prepare("
            SELECT m.message_id,
                   m.sender_id,
                   u.first_name AS sender_name,
                   m.content,
                   m.created_at,
                   m.status_sender,
                   m.status_reader,
                   m.image_url,
                   m.thumbnail_url,
                   m.recorded_readers
            FROM messages_tb m
            LEFT JOIN users_tb u ON m.sender_id = u.buwana_id
            WHERE m.conversation_id = ?
            ORDER BY m.created_at ASC
        ");
        $stmt->bind_param("i", $conversation_id);
        $stmt->execute();
        $stmt->bind_result($message_id, $sender_id, $sender_name, $content, $created_at, $status_sender, $status_reader, $image_url, $thumbnail_url, $recorded_readers);

        $messages = [];
        $messageIdsToUpdateReader = [];
        $messageIdsToUpdateSenderSent = [];
        $messageIdsToUpdateSenderRead = [];
        $lastMessageSenderId = null;
        $updatedRecordedReaders = []; // Track messages that need recorded_readers updates

        while ($stmt->fetch()) {
            // Prepare status updates based on the message's current status
            if ($sender_id != $user_id && $status_reader == 'Unread') {
                $messageIdsToUpdateReader[] = $message_id;
                $messageIdsToUpdateSenderRead[] = $message_id;
                $status_reader = 'Received';
                $status_sender = 'Read';
            }

            if ($sender_id == $user_id && $status_sender == 'Sending...') {
                $messageIdsToUpdateSenderSent[] = $message_id;
                $status_sender = 'Sent';
            }

            // Check and prepare updates for recorded_readers
            if ($sender_id != $user_id && (!str_contains($recorded_readers, (string)$user_id) || $recorded_readers === null)) {
                $updatedRecordedReaders[] = ['message_id' => $message_id, 'new_recorded_readers' => $recorded_readers ? $recorded_readers . ', ' . $user_id : (string)$user_id];
                $recorded_readers = $recorded_readers ? $recorded_readers . ', ' . $user_id : (string)$user_id; // Update local for response
            }

            $messages[] = [
                "message_id" => $message_id,
                "sender_id" => $sender_id,
                "sender_name" => $sender_name,
                "content" => $content,
                "created_at" => $created_at,
                "status_sender" => $status_sender,
                "status_reader" => $status_reader,
                "image_url" => $image_url,
                "thumbnail_url" => $thumbnail_url,
                "recorded_readers" => $recorded_readers
            ];

            $lastMessageSenderId = $sender_id;
        }
        $stmt->close();

        // Batch updates for status_reader and status_sender
        if (!empty($messageIdsToUpdateReader)) {
            $placeholders = implode(',', array_fill(0, count($messageIdsToUpdateReader), '?'));
            $types = str_repeat('i', count($messageIdsToUpdateReader));
            $update_reader_stmt = $buwana_conn->prepare("UPDATE messages_tb SET status_reader = 'Received' WHERE message_id IN ($placeholders)");
            $update_reader_stmt->bind_param($types, ...$messageIdsToUpdateReader);
            $update_reader_stmt->execute();
            $update_reader_stmt->close();
        }

        if (!empty($messageIdsToUpdateSenderRead)) {
            $placeholders = implode(',', array_fill(0, count($messageIdsToUpdateSenderRead), '?'));
            $types = str_repeat('i', count($messageIdsToUpdateSenderRead));
            $update_sender_read_stmt = $buwana_conn->prepare("UPDATE messages_tb SET status_sender = 'Read' WHERE message_id IN ($placeholders)");
            $update_sender_read_stmt->bind_param($types, ...$messageIdsToUpdateSenderRead);
            $update_sender_read_stmt->execute();
            $update_sender_read_stmt->close();
        }

        if (!empty($messageIdsToUpdateSenderSent)) {
            $placeholders = implode(',', array_fill(0, count($messageIdsToUpdateSenderSent), '?'));
            $types = str_repeat('i', count($messageIdsToUpdateSenderSent));
            $update_sender_sent_stmt = $buwana_conn->prepare("UPDATE messages_tb SET status_sender = 'Sent' WHERE message_id IN ($placeholders)");
            $update_sender_sent_stmt->bind_param($types, ...$messageIdsToUpdateSenderSent);
            $update_sender_sent_stmt->execute();
            $update_sender_sent_stmt->close();
        }

        // Update recorded_readers field individually
        foreach ($updatedRecordedReaders as $readerUpdate) {
            $update_reader_stmt = $buwana_conn->prepare("UPDATE messages_tb SET recorded_readers = ? WHERE message_id = ?");
            if ($update_reader_stmt === false) {
                throw new Exception("Failed to prepare statement for updating recorded_readers: " . $buwana_conn->error);
            }
            $update_reader_stmt->bind_param("si", $readerUpdate['new_recorded_readers'], $readerUpdate['message_id']);
            $update_reader_stmt->execute();
            $update_reader_stmt->close();
        }

        // Update all_msgs_posted if last message wasn't sent by the user
        if ($lastMessageSenderId !== null && $lastMessageSenderId != $user_id && $all_msgs_posted == 0) {
            $update_stmt = $buwana_conn->prepare("UPDATE conversations_tb SET all_msgs_posted = 1 WHERE conversation_id = ?");
            $update_stmt->bind_param("i", $conversation_id);
            $update_stmt->execute();
            $update_stmt->close();
        }

        $response = [
            "status" => "success",
            "messages" => $messages,
            "first_name" => $first_name,
            "other_participants" => $other_participants,
            "server_time" => $serverTime,
            "size_in_bytes" => $size_in_bytes,
            "all_msgs_posted" => $all_msgs_posted
        ];
    } catch (Exception $e) {
        $response = [
            "status" => "error",
            "message" => "An error occurred while retrieving messages: " . $e->getMessage()
        ];
    }
} else {
    $response = [
        "status" => "error",
        "message" => "Invalid conversation ID or user ID."
    ];
}

// Output the response as JSON
header('Content-Type: application/json');
echo json_encode($response);

$buwana_conn->close();
?>
