<?php
require_once '../gobrikconn_env.php';

header('Content-Type: application/json');

$training_id = isset($_GET['training_id']) ? intval($_GET['training_id']) : 0;

if ($training_id <= 0) {
    echo json_encode(["error" => "Invalid training ID."]);
    exit();
}

// Fetch training zoom links
$sql = "SELECT zoom_link, zoom_link_full FROM tb_trainings WHERE training_id = ?";
$stmt = $gobrik_conn->prepare($sql);
$stmt->bind_param("i", $training_id);
$stmt->execute();
$result = $stmt->get_result();

$data = $result->fetch_assoc() ?: ["error" => "Training not found."];

$stmt->close();
$gobrik_conn->close();

echo json_encode($data);
?>
