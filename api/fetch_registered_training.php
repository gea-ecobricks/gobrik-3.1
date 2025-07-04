<?php
require_once '../gobrikconn_env.php';

header('Content-Type: application/json');

$training_id = isset($_GET['training_id']) ? intval($_GET['training_id']) : 0;

if ($training_id <= 0) {
    echo json_encode(["error" => "Invalid training ID."]);
    exit();
}

// Fetch training details
$sql = "SELECT training_title, lead_trainer, training_date, training_time_txt, training_type, training_summary, training_location,
               zoom_link, zoom_link_full, moodle_url, feature_photo1_tmb, trainer_contact_email
        FROM tb_trainings
        WHERE training_id = ?";

$stmt = $gobrik_conn->prepare($sql);
$stmt->bind_param("i", $training_id);
$stmt->execute();
$result = $stmt->get_result();

$data = $result->fetch_assoc() ?: ["error" => "Training not found."];

$stmt->close();
$gobrik_conn->close();

echo json_encode($data);
?>
