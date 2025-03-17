<?php
require_once '../gobrikconn_env.php';

$training_id = isset($_GET['training_id']) ? intval($_GET['training_id']) : 0;

if ($training_id <= 0) {
    echo json_encode(["error" => "Invalid training ID."]);
    exit();
}

// Fetch trainees for the given training
$sql = "SELECT e.first_name, e.email_addr, e.gea_status, t.rsvp_status, t.date_registered
        FROM tb_training_trainees t
        INNER JOIN tb_ecobrickers e ON t.ecobricker_id = e.ecobricker_id
        WHERE t.training_id = ?";
$stmt = $gobrik_conn->prepare($sql);
$stmt->bind_param("i", $training_id);
$stmt->execute();
$result = $stmt->get_result();

$trainees = [];
while ($row = $result->fetch_assoc()) {
    $trainees[] = $row;
}

// Count total trainees
$total_trainees = count($trainees);

$stmt->close();
$gobrik_conn->close();

// Return trainees and total count
echo json_encode([
    "total_trainees" => $total_trainees,
    "trainees" => $trainees
]);
?>
