<?php
require_once '../earthenAuth_helper.php';
require_once '../gobrikconn_env.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'not_logged_in']);
    exit();
}

$training_id = isset($_POST['training_id']) ? intval($_POST['training_id']) : 0;
$show_report = isset($_POST['show_report']) ? intval($_POST['show_report']) : null;

if ($training_id <= 0 || !in_array($show_report, [0,1], true)) {
    echo json_encode(['success' => false, 'error' => 'invalid_params']);
    exit();
}

$stmt = $gobrik_conn->prepare("UPDATE tb_trainings SET show_report = ? WHERE training_id = ?");
if (!$stmt) {
    echo json_encode(['success' => false, 'error' => 'db']);
    exit();
}
$stmt->bind_param('ii', $show_report, $training_id);
$success = $stmt->execute();
$stmt->close();
$gobrik_conn->close();

if ($success) {
    echo json_encode(['success' => true, 'show_report' => $show_report]);
} else {
    echo json_encode(['success' => false, 'error' => 'query']);
}
?>
