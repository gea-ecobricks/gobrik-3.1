<?php
session_start();
require_once '../gobrikconn_env.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $training_id = $_POST['training_id'] ?? null;
    if ($training_id) {
        // Delete child rows first to satisfy FK constraints.
        // Order: registrations first (FK → pledges ON SET NULL), then pledges, then training.
        $gobrik_conn->begin_transaction();
        try {
            $stmt1 = $gobrik_conn->prepare("DELETE FROM training_registrations_tb WHERE training_id = ?");
            $stmt1->bind_param('i', $training_id);
            $stmt1->execute();
            $stmt1->close();

            $stmt2 = $gobrik_conn->prepare("DELETE FROM training_pledges_tb WHERE training_id = ?");
            $stmt2->bind_param('i', $training_id);
            $stmt2->execute();
            $stmt2->close();

            $stmt3 = $gobrik_conn->prepare("DELETE FROM tb_trainings WHERE training_id = ?");
            $stmt3->bind_param('i', $training_id);
            $stmt3->execute();
            $stmt3->close();

            $gobrik_conn->commit();
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            $gobrik_conn->rollback();
            echo json_encode(['success' => false, 'error' => 'Failed to delete training: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid training ID provided']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}

$gobrik_conn->close();
?>
