<?php
require_once '../buwanaconn_env.php';
header('Content-Type: application/json');

try {
    $buwana_conn->begin_transaction(MYSQLI_TRANS_START_READ_WRITE);

    $query = "
        SELECT id, email, name
        FROM earthen_members_tb
        WHERE test_sent = 0
        AND email NOT LIKE '%@hotmail.%'
        AND email NOT LIKE '%@comcast%'
        ORDER BY id ASC
        LIMIT 1
        FOR UPDATE
    ";

    $result = $buwana_conn->query($query);
    if ($result && $result->num_rows > 0) {
        $subscriber = $result->fetch_assoc();

        // Store lock in session or pass back to client
        $_SESSION['locked_subscriber_id'] = $subscriber['id'];

        echo json_encode([
            'success' => true,
            'subscriber' => $subscriber
        ]);
    } else {
        $buwana_conn->commit(); // No rows, release lock
        echo json_encode(['success' => false, 'message' => 'No more recipients']);
    }

} catch (Exception $e) {
    $buwana_conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Error fetching recipient']);
}
