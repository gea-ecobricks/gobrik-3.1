<?php
require_once '../earthenAuth_helper.php';
require_once '../buwanaconn_env.php';
require_once '../calconn_env.php'; // Include EarthCal database connection

error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', '/path/to/secure/error.log'); // Adjust this path

if (!$cal_conn || $cal_conn->connect_error) {
    error_log('Database connection error: ' . $cal_conn->connect_error);
    die(json_encode(['success' => false, 'message' => 'Database connection error.']));
}

$allowed_origins = [
    'https://cal.earthen.io',
    'https://cycles.earthen.io',
    'https://ecobricks.org',
    'https://gobrik.com',
    'http://localhost',
    'file://'
];

$origin = isset($_SERVER['HTTP_ORIGIN']) ? strtolower(rtrim($_SERVER['HTTP_ORIGIN'], '/')) : '';
if (!in_array($origin, $allowed_origins)) {
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['success' => false, 'message' => 'CORS error: Invalid origin']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Methods: POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
    exit(0);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit();
}

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!isset($data['buwana_id'], $data['calendar_id'], $data['event_name'], $data['day'], $data['month'], $data['year'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields.']);
    exit();
}

$buwana_id = $data['buwana_id'];
$calendar_id = $data['calendar_id'];
$event_name = $data['event_name'];
$day = $data['day'];
$month = $data['month'];
$year = $data['year'];
$frequency = $data['frequency'] ?? 'One-time';
$comment = $data['comment'] ?? '';
$comments = $data['comments'] ?? '';
$pinned = $data['pinned'] ?? 'no';
$last_edited = date('Y-m-d H:i:s');
$datecycle_color = $data['datecycle_color'] ?? '#FFFFFF';

try {
    $query = "
        INSERT INTO events (calendar_id, event_name, frequency, day, month, year, comment, comments, pinned, last_edited, datecycle_color)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ";

    $stmt = $cal_conn->prepare($query);
    if (!$stmt) {
        throw new Exception('Failed to prepare the statement: ' . $cal_conn->error);
    }

    $stmt->bind_param('sssssssssss', $calendar_id, $event_name, $frequency, $day, $month, $year, $comment, $comments, $pinned, $last_edited, $datecycle_color);

    $stmt->execute();
    $new_id = $stmt->insert_id;
    $stmt->close();

    echo json_encode(['success' => true, 'id' => $new_id]);
} catch (Exception $e) {
    error_log('Error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
}
?>
