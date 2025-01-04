<?php
require_once '../gobrikconn_env.php';

$query = "DELETE FROM tb_ecobrickers WHERE emailing_status = 'failed' LIMIT 5";

if ($gobrik_conn->query($query)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['error' => $gobrik_conn->error]);
}

$gobrik_conn->close();
?>
