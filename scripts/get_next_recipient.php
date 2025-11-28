<?php
require_once '../buwanaconn_env.php';
require_once '../ghostconn_env.php';
header('Content-Type: application/json');
session_start();

$response = [
    'success' => false,
    'has_alerts' => false,
    'alerts' => [],
];

try {
    $sent_label_slug = 'sent-001';
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
    $ghost_conn->begin_transaction(MYSQLI_TRANS_START_READ_WRITE);

    $sql = "
        SELECT m.id, m.email, m.name
        FROM members m
        INNER JOIN members_newsletters mn ON mn.member_id = m.id AND mn.subscribed = 1
        WHERE NOT EXISTS (
            SELECT 1 FROM members_labels ml
            INNER JOIN labels l ON l.id = ml.label_id
            WHERE ml.member_id = m.id AND (l.slug = ? OR l.name = ?)
        )
        ORDER BY m.created_at ASC
        LIMIT 1
        FOR UPDATE
    ";

    $stmt = $ghost_conn->prepare($sql);
    $stmt->bind_param('ss', $sent_label_slug, $sent_label_slug);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $subscriber = $result->fetch_assoc();
        $stmt->close();
        $ghost_conn->commit();

        $stats_sql = "SELECT COUNT(DISTINCT m.id) AS total, COUNT(DISTINCT sent.member_id) AS sent
                      FROM members m
                      INNER JOIN members_newsletters mn ON mn.member_id = m.id AND mn.subscribed = 1
                      LEFT JOIN (
                          SELECT ml.member_id
                          FROM members_labels ml
                          INNER JOIN labels l ON l.id = ml.label_id
                          WHERE l.slug = ? OR l.name = ?
                      ) AS sent ON sent.member_id = m.id";
        $stats_stmt = $ghost_conn->prepare($stats_sql);
        $stats_stmt->bind_param('ss', $sent_label_slug, $sent_label_slug);
        $stats_stmt->execute();
        $stats_result = $stats_stmt->get_result();
        $stats = $stats_result ? $stats_result->fetch_assoc() : ['total' => 0, 'sent' => 0];
        $stats_stmt->close();

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
        $ghost_conn->commit();
        $stmt->close();
        echo json_encode([
            'success' => false,
            'message' => 'No more recipients'
        ]);
    }

} catch (Exception $e) {
    $ghost_conn->rollback();
    error_log("[EARTHEN] ❌ ERROR in get-next-recipient: " . $e->getMessage());

    echo json_encode([
        'success' => false,
        'message' => 'Error fetching recipient',
        'has_alerts' => false
    ]);
}
