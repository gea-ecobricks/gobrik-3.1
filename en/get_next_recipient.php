<?php
require_once '../buwanaconn_env.php';
header('Content-Type: application/json');
session_start(); // ✅ Required if using session



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

        // Optional: Log lock for debugging
        error_log("[EARTHEN] LOCKED: {$subscriber['email']} via get-next-recipient");

        $_SESSION['locked_subscriber_id'] = $subscriber['id']; // optional session storage

        // ✅ COMMIT the lock immediately so other sessions can proceed
        $buwana_conn->commit();

        echo json_encode([
            'success' => true,
            'subscriber' => $subscriber
        ]);
    } else {
        $buwana_conn->commit(); // Cleanly end transaction
        echo json_encode([
            'success' => false,
            'message' => 'No more recipients'
        ]);
    }

} catch (Exception $e) {
    $buwana_conn->rollback();
    error_log("[EARTHEN] ❌ ERROR in get-next-recipient: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching recipient'
    ]);
}
