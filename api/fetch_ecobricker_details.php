<?php
require_once '../gobrikconn_env.php';

$ecobricker_id = isset($_GET['ecobricker_id']) ? intval($_GET['ecobricker_id']) : 0;

if ($ecobricker_id <= 0) {
    echo json_encode(["error" => "Invalid Ecobricker ID"]);
    exit;
}

$sql = "SELECT * FROM tb_ecobrickers WHERE ecobricker_id = ?";
$stmt = $gobrik_conn->prepare($sql);

if (!$stmt) {
    echo json_encode(["error" => "Database error: Unable to prepare statement"]);
    exit;
}

$stmt->bind_param("i", $ecobricker_id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode($result->fetch_assoc());
} else {
    echo json_encode(["error" => "No record found for Ecobricker ID"]);
}

$stmt->close();
$gobrik_conn->close();
?>
