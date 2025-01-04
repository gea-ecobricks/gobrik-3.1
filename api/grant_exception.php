<?php
require_once '../gobrikconn_env.php';

$user_id = intval($_GET['user_id'] ?? 0);

if ($user_id > 0) {
    $query = "UPDATE tb_ecobrickers SET emailing_status = 'exception granted' WHERE id = ?";
    $stmt = $gobrik_conn->prepare($query);
    $stmt->bind_param("i", $user_id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['error' => $stmt->error]);
    }

    $stmt->close();
} else {
    echo json_encode(['error' => 'Invalid user ID']);
}

$gobrik_conn->close();
?>
