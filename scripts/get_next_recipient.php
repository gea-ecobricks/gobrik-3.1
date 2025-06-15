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
    $alerts = [];
    $has_alerts = false;

    $alert_query = "SELECT alert_title, alert_message FROM admin_alerts WHERE addressed = 0 ORDER BY date_posted DESC LIMIT 3";
    $result = $buwana_conn->query($alert_query);

    if ($result && $result->num_rows > 0) {
        $has_alerts = true;
        while ($row = $result->fetch_assoc()) {
            $alerts[] = $row;
        }

        // Return alerts and exit early — don’t allow fetching if there are alerts
        $response['has_alerts'] = true;
        $response['alerts'] = $alerts;
        echo json_encode($response);
        exit();
    }

    // Begin transaction to lock recipient
    $buwana_conn->begin_transaction(MYSQLI_TRANS_START_READ_WRITE);

    $query = "
        SELECT id, email, name
        FROM earthen_members_tb
        WHERE test_sent = 0 AND processing = 0
        ORDER BY id ASC
        LIMIT 1
        FOR UPDATE
    ";

    $result = $buwana_conn->query($query);

    if ($result && $result->num_rows > 0) {
        $subscriber = $result->fetch_assoc();
        $_SESSION['locked_subscriber_id'] = $subscriber['id'];

        // Mark this subscriber as processing to avoid duplicates
        $update_sql = "UPDATE earthen_members_tb SET processing = 1 WHERE id = ?";
        $stmt_update = $buwana_conn->prepare($update_sql);
        if ($stmt_update) {
            $stmt_update->bind_param('i', $subscriber['id']);
            $stmt_update->execute();
            $stmt_update->close();
        }

        // ✅ Commit lock early
        $buwana_conn->commit();

        // ✅ Optional: fetch email stats for real-time updates
        $stats = $buwana_conn->query("SELECT COUNT(*) AS total, SUM(CASE WHEN test_sent = 1 THEN 1 ELSE 0 END) AS sent FROM earthen_members_tb")->fetch_assoc();
        $percentage = ($stats['total'] > 0) ? round(($stats['sent'] / $stats['total']) * 100, 2) : 0;

        error_log("[EARTHEN] LOCKED: {$subscriber['email']} via get-next-recipient");

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
        $buwana_conn->commit();
        echo json_encode([
            'success' => false,
            'message' => 'No more recipients',
            'has_alerts' => false
        ]);
    }

} catch (Exception $e) {
    $buwana_conn->rollback();
    error_log("[EARTHEN] ❌ ERROR in get-next-recipient: " . $e->getMessage());

    echo json_encode([
        'success' => false,
        'message' => 'Error fetching recipient',
        'has_alerts' => false
    ]);
}
