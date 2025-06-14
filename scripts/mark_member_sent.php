<?php
require_once '../buwanaconn_env.php';
header('Content-Type: application/json');

$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$response = ['success' => false];

if ($id > 0) {
    $stmt = $buwana_conn->prepare("UPDATE earthen_members_tb SET test_sent = 1, processing = 1, test_sent_date_time = NOW() WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param('i', $id);
        if ($stmt->execute()) {
            $response['success'] = true;
        } else {
            $response['error'] = $stmt->error;
        }
        $stmt->close();
    } else {
        $response['error'] = $buwana_conn->error;
    }
    error_log("[EARTHEN] Marked member $id as processed after repeated failures.");
} else {
    $response['error'] = 'Invalid ID';
}

echo json_encode($response);
