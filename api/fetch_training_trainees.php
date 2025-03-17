<?php

//HELLO WORL

require_once '../gobrikconn_env.php';

header('Content-Type: application/json'); // Ensure correct JSON response header

$training_id = isset($_GET['training_id']) ? intval($_GET['training_id']) : 0;

if ($training_id <= 0) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid training ID."]);
    exit();
}

// Prepare the SQL query to fetch trainees
$sql = "SELECT e.first_name, e.email_addr, e.gea_status, t.rsvp_status, t.date_registered
        FROM tb_training_trainees t
        INNER JOIN tb_ecobrickers e ON t.ecobricker_id = e.ecobricker_id
        WHERE t.training_id = ?";

$stmt = $gobrik_conn->prepare($sql);

if (!$stmt) {
    http_response_code(500);
    echo json_encode(["error" => "Database error: " . $gobrik_conn->error]);
    exit();
}

$stmt->bind_param("i", $training_id);
$stmt->execute();
$result = $stmt->get_result();

$trainees = [];
while ($row = $result->fetch_assoc()) {
    $trainees[] = $row;
}

$stmt->close();

// Close connection only if it's open
if ($gobrik_conn) {
    $gobrik_conn->close();
}

// Return trainees as JSON
echo json_encode($trainees);
?>
