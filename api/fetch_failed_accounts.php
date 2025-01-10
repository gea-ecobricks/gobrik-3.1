<?php
require_once '../gobrikconn_env.php';

$limit = intval($_GET['limit'] ?? 50); // Default limit to 50 if not provided

$query = "SELECT full_name, email_addr, emailing_status, ecobricks_made
          FROM tb_ecobrickers
          WHERE emailing_status = 'failed'
          LIMIT ?";

$stmt = $gobrik_conn->prepare($query);
$stmt->bind_param("i", $limit);
$stmt->execute();
$result = $stmt->get_result();

$accounts = [];
while ($row = $result->fetch_assoc()) {
    $accounts[] = $row;
}

echo json_encode($accounts);
$stmt->close();
$gobrik_conn->close();
?>
