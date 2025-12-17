<?php
require_once '../gobrikconn_env.php';

header('Content-Type: application/json');

$limit = isset($_GET['limit']) ? max(1, min(50, (int) $_GET['limit'])) : 9;
$offset = isset($_GET['offset']) ? max(0, (int) $_GET['offset']) : 0;

$sql = "SELECT project_id, project_name, description_short, briks_used, photo1_main, photo1_tmb
        FROM tb_projects
        WHERE ready_to_show = 1
        ORDER BY project_id DESC
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

$projects = [];
while ($row = $result->fetch_assoc()) {
    $projects[] = [
        'project_id' => (int) ($row['project_id'] ?? 0),
        'project_name' => $row['project_name'] ?? '',
        'description_short' => $row['description_short'] ?? '',
        'briks_used' => (int) ($row['briks_used'] ?? 0),
        'photo1_main' => $row['photo1_main'] ?? '',
        'photo1_tmb' => $row['photo1_tmb'] ?? '',
    ];
}

$stmt->close();
$gobrik_conn->close();

echo json_encode([
    'success' => true,
    'data' => $projects,
]);
