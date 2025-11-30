<?php
require_once '../buwanaconn_env.php';
require_once __DIR__ . '/earthen_helpers.php';

header('Content-Type: application/json');
session_start();

$response = [
    'success' => false,
    'has_alerts' => false,
    'alerts' => [],
    'batch' => [],
];

try {
    $batchSize = isset($_GET['limit']) ? max(1, min((int) $_GET['limit'], 100)) : 100;
    $offset = isset($_GET['offset']) ? max(0, (int) $_GET['offset']) : 0;

    $alert_query = "SELECT alert_title, alert_message FROM admin_alerts WHERE addressed = 0 ORDER BY date_posted DESC LIMIT 3";
    $result = $buwana_conn->query($alert_query);

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $response['alerts'][] = $row;
        }

        $response['has_alerts'] = true;
        echo json_encode($response);
        exit();
    }

    $members = fetchGhostMembers([
        'limit' => $batchSize,
        'offset' => $offset,
    ]);

    $summary = summarizeGhostMembers($members, 'sent-001');

    usort($summary['pending'], function ($a, $b) {
        return strcmp($a['created_at'] ?? '', $b['created_at'] ?? '');
    });

    $batchMembers = array_slice($summary['pending'], 0, $batchSize);

    $response['batch'] = array_map(function ($member) {
        return [
            'id' => $member['id'] ?? null,
            'email' => $member['email'] ?? '',
            'name' => $member['name'] ?? '',
            'uuid' => $member['uuid'] ?? '',
            'email_open_rate' => calculateOpenRate($member),
            'status' => 'pending',
        ];
    }, $batchMembers);

    $totalMembers = defined('EARTHEN_TOTAL_MEMBERS') ? EARTHEN_TOTAL_MEMBERS : ($summary['total'] ?? 0);
    $sentCount = $summary['sent_count'];

    $response['stats'] = [
        'total' => $totalMembers,
        'sent' => $sentCount,
        'percentage' => $totalMembers > 0 ? round(($sentCount / $totalMembers) * 100, 3) : 0,
    ];

    $response['success'] = true;
    echo json_encode($response);
    exit();
} catch (Exception $e) {
    error_log('[EARTHEN] ❌ ERROR in get_recipient_batch: ' . $e->getMessage());
}

echo json_encode($response);
