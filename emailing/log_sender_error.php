<?php
require_once '../buwanaconn_env.php';
header('Content-Type: application/json');

$msg = $_POST['message'] ?? 'Unknown error';
error_log('[EARTHEN] ' . $msg);

echo json_encode(['success' => true]);
