<?php
require_once '../buwanaconn_env.php';
header('Content-Type: application/json');

$query = "SELECT COUNT(*) AS total_members, SUM(CASE WHEN test_sent = 1 THEN 1 ELSE 0 END) AS sent_count FROM earthen_members_tb";
$result = $buwana_conn->query($query);
$row = $result->fetch_assoc();

$total = intval($row['total_members'] ?? 0);
$sent = intval($row['sent_count'] ?? 0);
$percentage = ($total > 0) ? round(($sent / $total) * 100, 2) : 0;

echo json_encode([
    'success' => true,
    'total_members' => $total,
    'sent_count' => $sent,
    'sent_percentage' => $percentage
]);
?>
