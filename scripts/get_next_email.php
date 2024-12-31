<?php
require_once '../gobrikconn_env.php';

header('Content-Type: application/json');
$response = [];

try {
    $sql_fetch_next = "
        SELECT email_addr
        FROM tb_ecobrickers
        WHERE test_email_status = 'unsent'
          AND buwana_activated = 1
        ORDER BY ecobricker_id ASC
        LIMIT 1";
    $stmt_fetch_next = $gobrik_conn->prepare($sql_fetch_next);
    if (!$stmt_fetch_next) {
        throw new Exception('Error preparing statement: ' . $gobrik_conn->error);
    }
    $stmt_fetch_next->execute();
    $stmt_fetch_next->bind_result($email_addr);
    $stmt_fetch_next->fetch();
    $stmt_fetch_next->close();

    if (empty($email_addr)) {
        throw new Exception('No email pending.');
    }

    $response = [
        'success' => true,
        'email_addr' => $email_addr,
    ];
} catch (Exception $e) {
    $response = [
        'success' => false,
        'error' => $e->getMessage(),
    ];
}

echo json_encode($response);
exit();
?>
