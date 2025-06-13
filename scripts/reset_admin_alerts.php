<?php
require_once '../buwanaconn_env.php';
header('Content-Type: application/json');

$response = ['success' => false];

try {
    $sql = "UPDATE admin_alerts SET addressed = 1 WHERE addressed = 0";
    if ($buwana_conn->query($sql) === TRUE) {
        $response['success'] = true;
    } else {
        $response['error'] = $buwana_conn->error;
    }
} catch (Exception $e) {
    $response['error'] = $e->getMessage();
}

echo json_encode($response);

