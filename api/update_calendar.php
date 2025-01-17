<?php
require_once '../earthenAuth_helper.php';
require_once '../buwanaconn_env.php';
require_once '../calconn_env.php';

header('Content-Type: application/json');
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING); // Suppress warnings
ini_set('display_errors', '0'); // Disable error display

$allowed_origins = [
    'https://cycles.earthen.io',
    'https://ecobricks.org',
    'https://gobrik.com',
    'http://localhost',
    'file://'
];

$origin = isset($_SERVER['HTTP_ORIGIN']) ? rtrim($_SERVER['HTTP_ORIGIN'], '/') : '';
error_log('Incoming update_calendar HTTP_ORIGIN: ' . $origin);

if (empty($origin)) {
    header('Access-Control-Allow-Origin: *');
} elseif (in_array($origin, $allowed_origins)) {
    header('Access-Control-Allow-Origin: ' . $origin);
} else {
    error_log('CORS error: Invalid origin - ' . $origin);
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['success' => false, 'message' => 'CORS error']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Methods: POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
    exit();
}

$response = ['success' => false];
$input = json_decode(file_get_contents('php://input'), true);
$buwana_id = $input['buwana_id'] ?? null;
$calendar_name = $input['calendar_name'] ?? null;
$datecycles = $input['datecycles'] ?? null;

if (empty($buwana_id) || !is_numeric($buwana_id) || empty($calendar_name) || !is_array($datecycles)) {
    $response['message'] = 'Invalid input';
    echo json_encode($response);
    exit();
}

try {
    $datecycles_json = json_encode($datecycles);
    if ($datecycles_json === false) {
        throw new Exception('Failed to encode datecycles.');
    }

    $sql = "UPDATE calendars_tb SET events_json_blob = ?, last_updated = NOW() WHERE buwana_id = ? AND calendar_name = ?";
    $stmt = $cal_conn->prepare($sql);
    if (!$stmt) throw new Exception("SQL Preparation Failed: " . $cal_conn->error);

    $stmt->bind_param("sis", $datecycles_json, $buwana_id, $calendar_name);
    $stmt->execute();

    if ($stmt->affected_rows === 0) {
        error_log("No changes made for calendar: $calendar_name");
        $response['message'] = "No changes made.";
    }

    $stmt_user = $cal_conn->prepare("UPDATE users_tb SET last_sync_ts = NOW() WHERE buwana_id = ?");
    if (!$stmt_user) throw new Exception("SQL Preparation Failed: " . $cal_conn->error);

    $stmt_user->bind_param("i", $buwana_id);
    $stmt_user->execute();

    $response['success'] = true;
    $response['last_updated'] = date('Y-m-d H:i:s');
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
} finally {
    if (isset($stmt)) $stmt->close();
    if (isset($stmt_user)) $stmt_user->close();
    $cal_conn->close();
}

echo json_encode($response);
exit();
?>
