<?php
require_once '../earthenAuth_helper.php';
require_once '../gobrikconn_env.php';

header('Content-Type: application/json');
session_start();

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'not_logged_in']);
    exit();
}

$serial_no = $_POST['serial_no'] ?? '';
$feature = isset($_POST['feature']) ? intval($_POST['feature']) : null;

if ($serial_no === '' || $feature === null || ($feature !== 0 && $feature !== 1)) {
    echo json_encode(['success' => false, 'error' => 'invalid_params']);
    exit();
}

$stmt = $gobrik_conn->prepare("UPDATE tb_ecobricks SET feature = ? WHERE serial_no = ?");
if (!$stmt) {
    echo json_encode(['success' => false, 'error' => 'db']);
    exit();
}
$stmt->bind_param('is', $feature, $serial_no);
$success = $stmt->execute();
$stmt->close();
$gobrik_conn->close();

if ($success) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'query']);
}
?>
