<?php
require_once '../gobrikconn_env.php';
header('Content-Type: application/json');
$trainers = [];
if (isset($_GET['query'])) {
    $query = '%' . trim($_GET['query']) . '%';
    $sql = "SELECT ecobricker_id, full_name FROM tb_ecobrickers WHERE gea_status LIKE '%trainer%' AND full_name LIKE ? ORDER BY full_name ASC LIMIT 10";
    if ($stmt = $gobrik_conn->prepare($sql)) {
        $stmt->bind_param('s', $query);
        $stmt->execute();
        $stmt->bind_result($id, $name);
        while ($stmt->fetch()) {
            $trainers[] = ['ecobricker_id' => $id, 'full_name' => $name];
        }
        $stmt->close();
    }
}
echo json_encode($trainers);
$gobrik_conn->close();
?>
