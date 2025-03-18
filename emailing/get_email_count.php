<?php
require_once '../buwanaconn_env.php'; // Load database credentials

// Ensure database connection exists
if (!isset($buwana_conn)) {
    echo json_encode(["error" => "Database connection not established"]);
    exit;
}

// Get count of emails still pending validation
$sql = "SELECT COUNT(*) AS total FROM ghost_test_email_tb WHERE validated = 0";
$result = $buwana_conn->query($sql);

if (!$result) {
    echo json_encode(["error" => "Query failed: " . $buwana_conn->error]);
    exit;
}

$row = $result->fetch_assoc();
echo json_encode(["total" => $row['total'] ?? 0]);
?>
