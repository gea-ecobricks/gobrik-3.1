<?php
session_start();
require_once '../gobrikconn_env.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $training_id = $_POST['training_id'] ?? null;
    if ($training_id) {
        $sql = "DELETE FROM tb_trainings WHERE training_id = ?";
        $stmt = $gobrik_conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param('i', $training_id);
            if ($stmt->execute()) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Failed to delete training: ' . $stmt->error]);
            }
            $stmt->close();
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to prepare statement: ' . $gobrik_conn->error]);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid training ID provided']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}

$gobrik_conn->close();
?>
