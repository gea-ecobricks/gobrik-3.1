<?php
require_once '../earthenAuth_helper.php';
require_once '../buwanaconn_env.php';
require_once '../calconn_env.php';

header('Content-Type: application/json');
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
ini_set('display_errors', '0');

$allowed_origins = [
    'https://cal.earthen.io',
    'https://cycles.earthen.io',
    'https://ecobricks.org',
    'https://gobrik.com',
    'http://localhost',
    'file://',
    'file:///home/russs/PycharmProjects/earthcalendar/'
];

$origin = isset($_SERVER['HTTP_ORIGIN']) ? rtrim($_SERVER['HTTP_ORIGIN'], '/') : '';
if (empty($origin)) {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Credentials: true');
} elseif (in_array($origin, $allowed_origins, true)) {
    header('Access-Control-Allow-Origin: ' . $origin);
    header('Access-Control-Allow-Credentials: true');
} else {
    error_log('CORS error: Invalid origin - ' . $origin);
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'CORS error: Invalid origin']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Methods: POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
    header('Access-Control-Allow-Credentials: true');
    exit(0);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Invalid request method. Use POST.']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true) ?? [];
$buwana_id   = $input['buwana_id'] ?? null;
$calendar_id = $input['calendar_id'] ?? null;
$active      = $input['active'] ?? $input['is_active'] ?? $input['calendar_active'] ?? null;

$response = ['success' => false];

try {
    if (empty($buwana_id) || !is_numeric($buwana_id)) {
        throw new Exception('Invalid or missing Buwana ID.');
    }

    if (empty($calendar_id) || !is_numeric($calendar_id)) {
        throw new Exception('Invalid or missing calendar ID.');
    }

    if ($active === null || !in_array((string) $active, ['0', '1'], true)) {
        throw new Exception('Invalid active state. Expected "0" or "1".');
    }

    $activeInt = (int) $active;
    $buwanaInt = (int) $buwana_id;
    $calendarInt = (int) $calendar_id;

    $stmt = $cal_conn->prepare('UPDATE calendars_tb SET calendar_active = ?, last_updated = NOW() WHERE calendar_id = ? AND buwana_id = ?');
    if (!$stmt) {
        throw new Exception('SQL preparation failed: ' . $cal_conn->error);
    }

    $stmt->bind_param('iii', $activeInt, $calendarInt, $buwanaInt);
    $stmt->execute();

    if ($stmt->affected_rows === 0) {
        $checkStmt = $cal_conn->prepare('SELECT calendar_active FROM calendars_tb WHERE calendar_id = ? AND buwana_id = ?');
        if (!$checkStmt) {
            throw new Exception('SQL preparation failed: ' . $cal_conn->error);
        }

        $checkStmt->bind_param('ii', $calendarInt, $buwanaInt);
        $checkStmt->execute();
        $result = $checkStmt->get_result();
        $current = $result->fetch_assoc();
        $checkStmt->close();

        if (!$current) {
            throw new Exception('Calendar not found for this user.');
        }

        $response['success'] = true;
        $response['message'] = 'Calendar state already matches requested value.';
        $response['calendar_active'] = (int) $current['calendar_active'];
    } else {
        $response['success'] = true;
        $response['message'] = $activeInt === 1 ? 'Calendar activated successfully.' : 'Calendar deactivated successfully.';
        $response['calendar_active'] = $activeInt;
    }

    $response['calendar_id'] = $calendarInt;
    $response['buwana_id'] = $buwanaInt;

    error_log(sprintf('cal_active_toggle.php: buwana_id=%d calendar_id=%d active=%d result=%s', $buwanaInt, $calendarInt, $activeInt, $response['message']));

    echo json_encode($response);
} catch (Exception $e) {
    http_response_code(400);
    $response['message'] = $e->getMessage();
    error_log('cal_active_toggle.php error: ' . $e->getMessage());
    echo json_encode($response);
} finally {
    if (isset($stmt) && $stmt instanceof mysqli_stmt) {
        $stmt->close();
    }
    $cal_conn->close();
}

?>
