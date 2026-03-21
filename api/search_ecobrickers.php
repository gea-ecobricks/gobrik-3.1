<?php
require_once '../gobrikconn_env.php';
header('Content-Type: application/json');
$ecobrickers = [];
if (isset($_GET['query'])) {
    $query = '%' . trim($_GET['query']) . '%';
    $sql = "SELECT ecobricker_id, full_name FROM tb_ecobrickers WHERE full_name LIKE ? ORDER BY full_name ASC LIMIT 10";
    if ($stmt = $gobrik_conn->prepare($sql)) {
        $stmt->bind_param('s', $query);
        $stmt->execute();
        $stmt->bind_result($id, $name);
        while ($stmt->fetch()) {
            $ecobrickers[] = ['ecobricker_id' => $id, 'full_name' => $name];
        }
        $stmt->close();
    }
}
echo json_encode($ecobrickers);
$gobrik_conn->close();
?>
