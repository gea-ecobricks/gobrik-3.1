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

    // 🔒 Begin transaction for safe locking
    $buwana_conn->begin_transaction(MYSQLI_TRANS_START_READ_WRITE);

    $sql = "
        SELECT id, email, name
        FROM earthen_members_tb
        WHERE test_sent = 0
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

        // Mark the record as being processed to avoid duplicate fetches
        $update = $buwana_conn->prepare(
            "UPDATE earthen_members_tb SET processing = 1 WHERE id = ?"
        );
        if ($update) {
            $update->bind_param('i', $subscriber['id']);
            $update->execute();
            $update->close();
        }

        $buwana_conn->commit();

        // Fetch updated stats
        $stats_result = $buwana_conn->query(
            "SELECT COUNT(*) AS total, SUM(CASE WHEN test_sent = 1 THEN 1 ELSE 0 END) AS sent FROM earthen_members_tb"
        );
        $stats = $stats_result->fetch_assoc();
        $percentage = ($stats['total'] > 0) ? round(($stats['sent'] / $stats['total']) * 100, 2) : 0;

        error_log("[EARTHEN] ✅ LOCKED: {$subscriber['email']}");

        echo json_encode([
            'success' => true,
            'subscriber' => $subscriber,
            'stats' => [
                'total' => (int)$stats['total'],
                'sent' => (int)$stats['sent'],
                'percentage' => $percentage
            ]
        ]);
    } else {
        $buwana_conn->commit();
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
        'message' => 'Error fetching recipient',
        'has_alerts' => false
    ]);
}
