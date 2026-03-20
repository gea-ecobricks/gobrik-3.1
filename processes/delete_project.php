<?php
session_start();
require_once '../gobrikconn_env.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $project_id = isset($_POST['project_id']) ? (int)$_POST['project_id'] : 0;
    if ($project_id > 0) {
        $sql = "DELETE FROM tb_projects WHERE project_id = ?";
        $stmt = $gobrik_conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param('i', $project_id);
            if ($stmt->execute()) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Failed to delete project: ' . $stmt->error]);
            }
            $stmt->close();
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to prepare statement: ' . $gobrik_conn->error]);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid project ID provided']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}

$gobrik_conn->close();
?>
