<?php
require_once '../gobrikconn_env.php';

$email_addr = $_GET['email_addr'] ?? '';

if (!empty($email_addr)) {
    $query = "UPDATE tb_ecobrickers SET emailing_status = 'exception granted' WHERE email_addr = ?";
    $stmt = $gobrik_conn->prepare($query);
    $stmt->bind_param("s", $email_addr);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['error' => 'No account found with the provided email address.']);
        }
    } else {
        echo json_encode(['error' => $stmt->error]);
    }

    $stmt->close();
} else {
    echo json_encode(['error' => 'Invalid or missing email address.']);
}

$gobrik_conn->close();
?>
