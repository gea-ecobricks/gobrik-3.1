<?php
require_once '../buwanaconn_env.php';
header('Content-Type: application/json');
session_start();

try {
    $buwana_conn->begin_transaction(MYSQLI_TRANS_START_READ_WRITE);

    // Get the next recipient
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

    $subscriber = null;

    if ($result && $result->num_rows > 0) {
        $subscriber = $result->fetch_assoc();
        $_SESSION['locked_subscriber_id'] = $subscriber['id'];
        error_log("[EARTHEN] LOCKED: {$subscriber['email']} via get-next-recipient");
    }

    $buwana_conn->commit();

    // Get updated stats
    $statsQuery = "SELECT COUNT(*) AS total_members, SUM(CASE WHEN test_sent = 1 THEN 1 ELSE 0 END) AS sent_count FROM earthen_members_tb";
    $statsResult = $buwana_conn->query($statsQuery);
    $stats = $statsResult->fetch_assoc();

    $total_members = intval($stats['total_members'] ?? 0);
    $sent_count = intval($stats['sent_count'] ?? 0);
    $sent_percentage = $total_members > 0 ? round(($sent_count / $total_members) * 100, 2) : 0;

    echo json_encode([
        'success' => true,
        'subscriber' => $subscriber,
        'stats' => [
            'total' => $total_members,
            'sent' => $sent_count,
            'percentage' => $sent_percentage
        ]
    ]);

} catch (Exception $e) {
    $buwana_conn->rollback();
    error_log("[EARTHEN] ❌ ERROR in get-next-recipient: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Server error'
    ]);
}
