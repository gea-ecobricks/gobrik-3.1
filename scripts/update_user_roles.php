<?php
require_once '../gobrikconn_env.php'; // Include database connection

$data = json_decode(file_get_contents('php://input'), true);

$buwana_id = $data['buwana_id'] ?? null;
$gea_status = $data['gea_status'] ?? null;
$user_roles = $data['user_roles'] ?? null;
$capabilities = $data['capabilities'] ?? null;

if (!$buwana_id || !is_numeric($buwana_id)) {
    echo json_encode(['success' => false, 'error' => 'Invalid Buwana ID']);
    exit();
}

$sql = "UPDATE tb_ecobrickers SET gea_status = ?, user_roles = ?, capabilities = ? WHERE buwana_id = ?";
$stmt = $gobrik_conn->prepare($sql);

if (!$stmt) {
    echo json_encode(['success' => false, 'error' => 'Database error']);
    exit();
}

$stmt->bind_param("sssi", $gea_status, $user_roles, $capabilities, $buwana_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Error updating user roles']);
}

$stmt->close();
$gobrik_conn->close();
?>
