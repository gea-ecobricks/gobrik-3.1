<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../buwanaconn_env.php';

// Retrieve the user ID from the GET request
$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;

$response = [];

if ($user_id > 0) {
    try {
        // Calculate the number of conversations and total size for all messages in those conversations
        $convo_stmt = $buwana_conn->prepare("
            SELECT COUNT(DISTINCT c.conversation_id) AS number_conversations,
                   SUM(c.size_in_bytes) AS total_size_in_bytes
            FROM conversations_tb c
            JOIN participants_tb p ON c.conversation_id = p.conversation_id
            WHERE p.buwana_id = ?
        ");
        $convo_stmt->bind_param("i", $user_id);
        $convo_stmt->execute();
        $convo_stmt->bind_result($number_conversations, $total_size_in_bytes);
        $convo_stmt->fetch();
        $convo_stmt->close();

        // Calculate the number of unread messages in all conversations connected to the user
        $unread_stmt = $buwana_conn->prepare("
            SELECT COUNT(m.message_id) AS unread_messages
            FROM messages_tb m
            JOIN participants_tb p ON m.conversation_id = p.conversation_id
            WHERE p.buwana_id = ? AND m.sender_id != ? AND m.status_reader = 'Unread'
        ");
        $unread_stmt->bind_param("ii", $user_id, $user_id);
        $unread_stmt->execute();
        $unread_stmt->bind_result($unread_messages);
        $unread_stmt->fetch();
        $unread_stmt->close();

        // Convert total size from bytes to MB, rounding to two decimal places
        $total_mbs_on_server = round($total_size_in_bytes / (1024 * 1024), 2);

        // Prepare the response
        $response = [
            "status" => "success",
            "number_conversations" => $number_conversations,
            "unread_messages" => $unread_messages,
            "total_mbs_on_server" => $total_mbs_on_server
        ];
    } catch (Exception $e) {
        $response = [
            "status" => "error",
            "message" => "An error occurred while calculating message stats: " . $e->getMessage()
        ];
    }
} else {
    // Handle invalid user ID
    $response = [
        "status" => "error",
        "message" => "Invalid user ID."
    ];
}

// Output the response as JSON
header('Content-Type: application/json');
echo json_encode($response);

// Close the database connection
$buwana_conn->close();
?>
