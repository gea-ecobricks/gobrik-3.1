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
    'stats' => [
        'total' => 0,
        'sent' => 0,
        'pending' => 0,
        'percentage' => 0,
    ],
];

try {
    $batchSize = isset($_GET['limit']) ? max(1, min((int) $_GET['limit'], 50)) : 20;
    $fetchLimit = max($batchSize * 5, 100);
    $maxFetchLimit = 10000;

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

    // Pull top-line stats directly to understand the full pending pool
    $ghostConn = loadGhostStatsConnection();

    if ($ghostConn instanceof mysqli) {
        $totalResult = $ghostConn->query("SELECT COUNT(*) AS total FROM members");
        if ($totalResult && ($row = $totalResult->fetch_assoc())) {
            $response['stats']['total'] = (int) ($row['total'] ?? 0);
        }

        $sentQuery = "SELECT COUNT(*) AS sent
                      FROM members_labels ml
                      INNER JOIN labels l ON ml.label_id = l.id
                      WHERE l.name = 'sent-001'";

        $sentResult = $ghostConn->query($sentQuery);
        if ($sentResult && ($row = $sentResult->fetch_assoc())) {
            $response['stats']['sent'] = (int) ($row['sent'] ?? 0);
        }
    }

    $response['stats']['pending'] = max(0, $response['stats']['total'] - $response['stats']['sent']);
    $response['stats']['percentage'] = $response['stats']['total'] > 0
        ? round(($response['stats']['sent'] / $response['stats']['total']) * 100, 2)
        : 0;

    $summary = [
        'pending' => [],
        'sent_percentage' => 0,
    ];

    // Expand the fetch window if we know there are more records than the current limit
    while ($fetchLimit <= $maxFetchLimit) {
        $members = fetchGhostMembers([
            'limit' => $fetchLimit,
        ]);

        $summary = summarizeGhostMembers($members, 'sent-001');

        if (!empty($summary['pending'])) {
            break;
        }

        if ($response['stats']['total'] > $fetchLimit) {
            $fetchLimit = min($maxFetchLimit, $fetchLimit * 2);
            continue;
        }

        break;
    }

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

    $response['success'] = true;
    echo json_encode($response);
    exit();
} catch (Exception $e) {
    error_log('[EARTHEN] ❌ ERROR in get_recipient_batch: ' . $e->getMessage());
}

echo json_encode($response);
