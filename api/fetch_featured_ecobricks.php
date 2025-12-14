<?php
require_once '../gobrikconn_env.php';

header('Content-Type: application/json');

$limit = isset($_GET['limit']) ? max(1, min(50, (int) $_GET['limit'])) : 9;
$offset = isset($_GET['offset']) ? max(0, (int) $_GET['offset']) : 0;

function formatLocationTail(?string $location_full): string {
    $location_parts = array_filter(array_map('trim', explode(',', $location_full ?? '')));
    return implode(', ', array_slice($location_parts, -2));
}

$sql = "SELECT ecobrick_full_photo_url, ecobrick_thumb_photo_url, serial_no, photo_version, weight_g, ecobricker_maker, location_full, vision, date_logged_ts, status
        FROM tb_ecobricks
        WHERE ecobrick_full_photo_url IS NOT NULL
          AND ecobrick_full_photo_url != ''
          AND feature = 1
          AND status != 'not ready'
        ORDER BY date_logged_ts DESC
        LIMIT ? OFFSET ?";

$stmt = $gobrik_conn->prepare($sql);

if (!$stmt) {
    echo json_encode([
        'success' => false,
        'error' => 'Failed to prepare statement: ' . $gobrik_conn->error,
    ]);
    exit;
}

$stmt->bind_param('ii', $limit, $offset);
$stmt->execute();
$result = $stmt->get_result();

$ecobricks = [];
while ($row = $result->fetch_assoc()) {
    $ecobricks[] = [
        'ecobrick_full_photo_url' => $row['ecobrick_full_photo_url'] ?? '',
        'ecobrick_thumb_photo_url' => $row['ecobrick_thumb_photo_url'] ?? '',
        'serial_no' => $row['serial_no'] ?? '',
        'photo_version' => $row['photo_version'] ?? '',
        'weight_g' => $row['weight_g'] ?? '',
        'ecobricker_maker' => $row['ecobricker_maker'] ?? '',
        'location_display' => formatLocationTail($row['location_full'] ?? ''),
        'vision' => $row['vision'] ?? '',
        'date_logged_ts' => $row['date_logged_ts'] ?? '',
        'status' => $row['status'] ?? '',
    ];
}

$stmt->close();
$gobrik_conn->close();

echo json_encode([
    'success' => true,
    'data' => $ecobricks,
]);
