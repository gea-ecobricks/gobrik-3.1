<?php
require_once '../gobrikconn_env.php'; // Include database connection

$buwana_id = $_GET['buwana_id'] ?? null;

if (!$buwana_id || !is_numeric($buwana_id)) {
    echo json_encode(['error' => 'Invalid Buwana ID']);
    exit();
}

$sql = "SELECT gea_status, user_roles, capabilities FROM tb_ecobrickers WHERE buwana_id = ?";
$stmt = $gobrik_conn->prepare($sql);

if (!$stmt) {
    echo json_encode(['error' => 'Database error']);
    exit();
}

$stmt->bind_param("i", $buwana_id);
$stmt->execute();
$stmt->bind_result($gea_status, $user_roles, $capabilities);
$stmt->fetch();

echo json_encode([
    'gea_status' => $gea_status,
    'user_roles' => $user_roles,
    'capabilities' => $capabilities,
]);

$stmt->close();
$gobrik_conn->close();
?>
