<?php
require_once '../buwanaconn_env.php';
header('Content-Type: application/json');
session_start();

$response = [
    'success' => false,
    'has_alerts' => false,
    'alerts' => [],
];

try {
    // 🚨 Check for unaddressed alerts first
    $alert_query = "SELECT alert_title, alert_message FROM admin_alerts WHERE addressed = 0 ORDER BY date_posted DESC LIMIT 3";
    $result = $buwana_conn->query($alert_query);

    if ($result && $result->num_rows > 0) {
        $alerts = [];
        while ($row = $result->fetch_assoc()) {
            $alerts[] = $row;
        }

        $response['has_alerts'] = true;
        $response['alerts'] = $alerts;
        echo json_encode($response);
        exit();
    }

    // 🔒 Begin transaction for safe locking
    $buwana_conn->begin_transaction(MYSQLI_TRANS_START_READ_WRITE);

    // ✅ Clean single query to fetch next eligible recipient
    $sql = "
        SELECT buwana_id, email, full_name
        FROM users_tb
        WHERE
            test_sent = 0
            AND (processing IS NULL OR processing = 0)
            AND email IS NOT NULL
            AND email <> ''
        ORDER BY created_at DESC
        LIMIT 1
        FOR UPDATE
    ";

    $result = $buwana_conn->query($sql);

    if ($result && $result->num_rows > 0) {
        $subscriber = $result->fetch_assoc();
        $_SESSION['locked_subscriber_id'] = $subscriber['buwana_id'];

        $buwana_conn->commit();

        // 🔢 Fetch email stats for real-time updates
        $stats_result = $buwana_conn->query("
            SELECT COUNT(*) AS total,
                   SUM(CASE WHEN test_sent = 1 THEN 1 ELSE 0 END) AS sent
            FROM users_tb
        ");
        $stats = $stats_result->fetch_assoc();
        $percentage = ($stats['total'] > 0) ? round(($stats['sent'] / $stats['total']) * 100, 2) : 0;

        error_log("[BUWANA] ✅ LOCKED: {$subscriber['email']} via get-next-recipient");

        echo json_encode([
            'success' => true,
            'subscriber' => $subscriber,
            'stats' => [
                'total' => (int)$stats['total'],
                'sent' => (int)$stats['sent'],
                'percentage' => $percentage
            ],
            'has_alerts' => false,
        ]);
    } else {
        // No recipient found
        $buwana_conn->commit();
        echo json_encode([
            'success' => false,
            'message' => 'No more recipients',
            'has_alerts' => false
        ]);
    }

} catch (Exception $e) {
    $buwana_conn->rollback();
    error_log("[BUWANA] ❌ ERROR in get-next-recipient: " . $e->getMessage());

    echo json_encode([
        'success' => false,
        'message' => 'Error fetching recipient',
        'has_alerts' => false
    ]);
}
?>
