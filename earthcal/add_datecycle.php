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

// Normalize the HTTP_ORIGIN (remove trailing slashes or fragments)
$origin = isset($_SERVER['HTTP_ORIGIN']) ? rtrim($_SERVER['HTTP_ORIGIN'], '/') : '';

if (empty($origin)) {
    header('Access-Control-Allow-Origin: *');
} elseif (in_array($origin, $allowed_origins)) {
    header('Access-Control-Allow-Origin: ' . $origin);
} else {
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['success' => false, 'message' => 'CORS error: Invalid origin v2']);
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
    'user_id', 'cal_id', 'title', 'date', 'time', 'time_zone',
    'day', 'month', 'year', 'frequency', 'last_edited'
];

foreach ($required_fields as $field) {
    if (!isset($data[$field])) {
        echo json_encode(['success' => false, 'message' => "Missing required field: $field"]);
        exit();
    }
}

// Extract and sanitize inputs
$user_id = (int) $data['user_id'];
$cal_id = (int) $data['cal_id'];
$title = $cal_conn->real_escape_string($data['title']);
$date = $cal_conn->real_escape_string($data['date']);
$time = $cal_conn->real_escape_string($data['time']);
$time_zone = $cal_conn->real_escape_string($data['time_zone']);
$day = (int) $data['day'];
$month = (int) $data['month'];
$year = (int) $data['year'];
$comment = $cal_conn->real_escape_string($data['comment'] ?? '0');
$comments = $cal_conn->real_escape_string($data['comments'] ?? null);
$datecycle_color = $cal_conn->real_escape_string($data['datecycle_color'] ?? 'green');
$cal_name = $cal_conn->real_escape_string($data['cal_name'] ?? null);
$cal_color = $cal_conn->real_escape_string($data['cal_color'] ?? 'under development');
$frequency = $cal_conn->real_escape_string($data['frequency'] ?? 'One-time');
$pinned = $cal_conn->real_escape_string($data['pinned'] ?? 'No');
$completed = $cal_conn->real_escape_string($data['completed'] ?? 'No');
$public = $cal_conn->real_escape_string($data['public'] ?? 'No');
$delete = $cal_conn->real_escape_string($data['delete'] ?? 'No');
$synced = $cal_conn->real_escape_string($data['synced'] ?? 'No');
$conflict = $cal_conn->real_escape_string($data['conflict'] ?? 'No');
$last_edited = date('Y-m-d H:i:s');
$raw_json = $cal_conn->real_escape_string(json_encode($data)); // Store raw JSON for debugging

try {
    // Insert query
    $query = "
        INSERT INTO datecycles_tb
        (buwana_id, cal_id, title, date, time, time_zone, day, month, year, comment, comments, datecycle_color, cal_name, cal_color, frequency, pinned, completed, public, delete, synced, conflict, last_edited, raw_json)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ";

    $stmt = $cal_conn->prepare($query);
    if (!$stmt) {
        throw new Exception('Failed to prepare the statement: ' . $cal_conn->error);
    }

    // Bind parameters
    $stmt->bind_param(
        'iissssiissssssssssssss',
        $user_id,
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
        $delete,
        $synced,
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
