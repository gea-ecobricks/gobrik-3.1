<?php
require_once '../buwanaconn_env.php';
require_once '../en/ghost_admin_helpers.php';
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

    $members = fetchGhostMembers();
    $summary = summarizeGhostMembers($members, 'sent-001');

    usort($summary['pending'], function ($a, $b) {
        return strcmp($a['created_at'] ?? '', $b['created_at'] ?? '');
    });

    if (!empty($summary['pending'])) {
        $subscriber = $summary['pending'][0];

        error_log("[EARTHEN] ✅ SELECTED: {$subscriber['email']}");

        echo json_encode([
            'success' => true,
            'subscriber' => [
                'id' => $subscriber['id'] ?? null,
                'email' => $subscriber['email'] ?? '',
                'name' => $subscriber['name'] ?? '',
            ],
            'stats' => [
                'total' => $summary['total'],
                'sent' => $summary['sent_count'],
                'percentage' => $summary['sent_percentage']
            ]
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'No more recipients'
        ]);
    }

} catch (Exception $e) {
    error_log("[EARTHEN] ❌ ERROR in get-next-recipient: " . $e->getMessage());

    echo json_encode([
        'success' => false,
        'message' => 'Error fetching recipient',
        'has_alerts' => false
    ]);
}
