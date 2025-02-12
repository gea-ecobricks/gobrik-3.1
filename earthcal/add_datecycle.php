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

// ✅ **Add `datecycle_color` to required fields**
$required_fields = [
    'buwana_id', 'cal_id', 'cal_name', 'cal_color', 'title', 'date', 'time', 'time_zone',
    'day', 'month', 'year', 'frequency', 'last_edited', 'created_at', 'unique_key', 'datecycle_color'
];

foreach ($required_fields as $field) {
    if (!isset($data[$field])) {
        echo json_encode(['success' => false, 'message' => "Missing required field: $field"]);
        exit();
    }
}

// Extract and sanitize inputs.
$buwana_id  = (int) $data['buwana_id'];
$cal_id     = (int) $data['cal_id'];
$cal_name   = $cal_conn->real_escape_string($data['cal_name']);
$cal_color  = $cal_conn->real_escape_string($data['cal_color']);
$title      = $cal_conn->real_escape_string($data['title']);
$date       = $cal_conn->real_escape_string($data['date']); // e.g., "2025-2-1"
$time       = $cal_conn->real_escape_string($data['time']);
$time_zone  = $cal_conn->real_escape_string($data['time_zone']);
$day        = (int) $data['day'];
$month      = (int) $data['month'];
$year       = (int) $data['year'];
$frequency  = $cal_conn->real_escape_string($data['frequency']);
$created_at = $cal_conn->real_escape_string($data['created_at']);
$last_edited = date('Y-m-d H:i:s', strtotime($data['last_edited'] ?? 'now'));
$unique_key = $cal_conn->real_escape_string($data['unique_key']);

// ✅ **Extract and sanitize `datecycle_color`**
$datecycle_color = $cal_conn->real_escape_string($data['datecycle_color']);

// Set synced flag to integer 1 (meaning synced)
$synced = 1;

try {
    // ✅ **Modify SQL query to include `datecycle_color`**
    $query = "
        INSERT INTO datecycles_tb
        (buwana_id, cal_id, cal_name, cal_color, title, date, time, time_zone, day, month, year, frequency, created_at, last_edited, synced, unique_key, datecycle_color)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ";

    $stmt = $cal_conn->prepare($query);
    if (!$stmt) {
        throw new Exception('Failed to prepare the statement: ' . $cal_conn->error);
    }

    // ✅ **Bind `datecycle_color` in the parameters**
    $stmt->bind_param(
        'iissssssiiisssiss',
        $buwana_id,
        $cal_id,
        $cal_name,
        $cal_color,
        $title,
        $date,
        $time,
        $time_zone,
        $day,
        $month,
        $year,
        $frequency,
        $created_at,
        $last_edited,
        $synced,
        $unique_key,
        $datecycle_color // Bind this newly added field
    );

    // Execute the query.
    $stmt->execute();
    $new_id = $stmt->insert_id;
    $stmt->close();

    // ✅ **Return success response with stored `datecycle_color`**
    echo json_encode(['success' => true, 'id' => $new_id, 'stored_color' => $datecycle_color]);
} catch (Exception $e) {
    error_log('Error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
}
?>
