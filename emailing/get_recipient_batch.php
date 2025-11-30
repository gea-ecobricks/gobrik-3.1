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
    $batchSize = isset($_GET['limit']) ? max(1, min((int) $_GET['limit'], 50)) : 20;

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
        'limit' => max($batchSize * 5, 100),
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

    $response['stats'] = [
        'total' => $summary['total'],
        'sent' => $summary['sent_count'],
        'percentage' => $summary['sent_percentage'],
    ];

    $response['success'] = true;
    echo json_encode($response);
    exit();
} catch (Exception $e) {
    error_log('[EARTHEN] ❌ ERROR in get_recipient_batch: ' . $e->getMessage());
}

echo json_encode($response);
