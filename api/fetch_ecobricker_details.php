<?php
require_once '../gobrikconn_env.php';

$buwana_id = isset($_GET['buwana_id']) ? intval($_GET['buwana_id']) : 0;

if ($buwana_id <= 0) {
    echo json_encode(["error" => "Invalid Buwana ID"]);
    exit;
}

$sql = "SELECT * FROM tb_ecobrickers WHERE buwana_id = ?";
$stmt = $gobrik_conn->prepare($sql);
$stmt->bind_param("i", $buwana_id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode($result->fetch_assoc());
} else {
    echo json_encode(["error" => "No record found"]);
}

$stmt->close();
$gobrik_conn->close();
?>
