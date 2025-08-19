<?php
session_start();
require_once '../gobrikconn_env.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cash_tran_id = $_POST['cash_tran_id'] ?? null;
    if ($cash_tran_id) {
        $sql = "DELETE FROM tb_cash_transaction WHERE cash_tran_id = ?";
        $stmt = $gobrik_conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param('i', $cash_tran_id);
            if ($stmt->execute()) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Failed to delete transaction: ' . $stmt->error]);
            }
            $stmt->close();
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to prepare statement: ' . $gobrik_conn->error]);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid transaction ID provided']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}

$gobrik_conn->close();
?>
