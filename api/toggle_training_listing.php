<?php
require_once '../earthenAuth_helper.php';
require_once '../gobrikconn_env.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'not_logged_in']);
    exit();
}


$training_id = isset($_POST['training_id']) ? intval($_POST['training_id']) : 0;
$ready_to_show = isset($_POST['ready_to_show']) ? intval($_POST['ready_to_show']) : null;

if ($training_id <= 0 || !in_array($ready_to_show, [0,1], true)) {
    echo json_encode(['success' => false, 'error' => 'invalid_params']);
    exit();
}

$stmt = $gobrik_conn->prepare("UPDATE tb_trainings SET ready_to_show = ? WHERE training_id = ?");
if (!$stmt) {
    echo json_encode(['success' => false, 'error' => 'db']);
    exit();
}
$stmt->bind_param('ii', $ready_to_show, $training_id);
$success = $stmt->execute();
$stmt->close();
$gobrik_conn->close();

if ($success) {
    echo json_encode(['success' => true, 'ready_to_show' => $ready_to_show]);
} else {
    echo json_encode(['success' => false, 'error' => 'query']);
}
?>
