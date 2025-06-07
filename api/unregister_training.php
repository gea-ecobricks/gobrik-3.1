<?php
require_once '../earthenAuth_helper.php';
require_once '../gobrikconn_env.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'not_logged_in']);
    exit();
}

$training_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$ecobricker_id = isset($_GET['ecobricker_id']) ? intval($_GET['ecobricker_id']) : 0;

if ($training_id <= 0 || $ecobricker_id <= 0) {
    echo json_encode(['success' => false, 'error' => 'invalid_params']);
    exit();
}

$stmt = $gobrik_conn->prepare("DELETE FROM tb_training_trainees WHERE training_id = ? AND ecobricker_id = ?");
if (!$stmt) {
    echo json_encode(['success' => false, 'error' => 'db']);
    exit();
}
$stmt->bind_param('ii', $training_id, $ecobricker_id);
$success = $stmt->execute();
$stmt->close();
$gobrik_conn->close();

if ($success) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'query']);
}
?>
