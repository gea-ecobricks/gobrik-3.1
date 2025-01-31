<?php
require_once '../earthenAuth_helper.php';
require_once '../buwanaconn_env.php';
require_once '../calconn_env.php'; // Include EarthCal database connection

// Set headers for JSON response
header('Content-Type: application/json');

// CORS configuration
$allowed_origins = [
    'https://cal.earthen.io',
    'https://cycles.earthen.io',
    'https://ecobricks.org',
    'https://gobrik.com',
    'http://localhost',
    'file://'
];

$origin = isset($_SERVER['HTTP_ORIGIN']) ? rtrim($_SERVER['HTTP_ORIGIN'], '/') : '';

if (empty($origin)) {
    header('Access-Control-Allow-Origin: *');
} elseif (in_array($origin, $allowed_origins)) {
    header('Access-Control-Allow-Origin: ' . $origin);
} else {
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

// Validate required fields
$required_fields = [
    'buwana_id', 'cal_id', 'title', 'date', 'time', 'time_zone',
    'day', 'month', 'year', 'frequency', 'last_edited'
];

foreach ($required_fields as $field) {
    if (!isset($data[$field])) {
        echo json_encode(['success' => false, 'message' => "Missing required field: $field"]);
        exit();
    }
}

// Extract and sanitize inputs
$buwana_id = (int) $data['buwana_id'];
$cal_id = (int) $data['cal_id'];
$title = $cal_conn->real_escape_string($data['title']);
$date = $cal_conn->real_escape_string($data['date']);
$time = $cal_conn->real_escape_string($data['time']);
$time_zone = $cal_conn->real_escape_string($data['time_zone']);
$day = (int) $data['day'];
$month = (int) $data['month'];
$year = (int) $data['year'];
$comment = isset($data['comment']) ? $cal_conn->real_escape_string($data['comment']) : '0';
$comments = isset($data['comments']) ? $cal_conn->real_escape_string($data['comments']) : '';
$datecycle_color = isset($data['datecycle_color']) ? $cal_conn->real_escape_string($data['datecycle_color']) : 'green';
$cal_name = isset($data['cal_name']) ? $cal_conn->real_escape_string($data['cal_name']) : 'Unknown Calendar';
$cal_color = isset($data['cal_color']) ? $cal_conn->real_escape_string($data['cal_color']) : 'gray';
$frequency = isset($data['frequency']) ? $cal_conn->real_escape_string($data['frequency']) : 'One-time';
$pinned = isset($data['pinned']) ? $cal_conn->real_escape_string($data['pinned']) : 'No';
$completed = isset($data['completed']) ? $cal_conn->real_escape_string($data['completed']) : 'No';
$public = isset($data['public']) ? $cal_conn->real_escape_string($data['public']) : 'No';
$delete_it = isset($data['delete_it']) ? $cal_conn->real_escape_string($data['delete_it']) : 'No';
$synced = 1; // Always set to 1 (tinyint equivalent of "Yes")
$conflict = isset($data['conflict']) ? $cal_conn->real_escape_string($data['conflict']) : 'No';
$last_edited = date('Y-m-d H:i:s');
$raw_json = $cal_conn->real_escape_string(json_encode($data));

try {
    // Insert query with `raw_json`
    $query = "
        INSERT INTO datecycles_tb
        (buwana_id, cal_id, title, date, time, time_zone, day, month, year, comment, comments, datecycle_color, cal_name, cal_color, frequency, pinned, completed, public, delete_it, synced, conflict, last_edited, raw_json)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ";

    $stmt = $cal_conn->prepare($query);
    if (!$stmt) {
        throw new Exception('Failed to prepare the statement: ' . $cal_conn->error);
    }

    // Bind parameters
    $stmt->bind_param(
        'iissssiiissssssssssssss',
        $buwana_id,
        $cal_id,
        $title,
        $date,
        $time,
        $time_zone,
        $day,
        $month,
        $year,
        $comment,
        $comments,
        $datecycle_color,
        $cal_name,
        $cal_color,
        $frequency,
        $pinned,
        $completed,
        $public,
        $delete_it, // Updated parameter
        $synced, // Always set to "1"
        $conflict,
        $last_edited,
        $raw_json
    );

    // Execute the query
    $stmt->execute();
    $new_id = $stmt->insert_id;
    $stmt->close();

    // Return success response with the new ID
    echo json_encode(['success' => true, 'id' => $new_id]);
} catch (Exception $e) {
    error_log('Error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
}

?>
