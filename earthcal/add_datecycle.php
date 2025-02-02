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

// Validate required fields, including unique_key.
$required_fields = [
    'buwana_id', 'cal_id', 'cal_name', 'cal_color', 'title', 'date', 'time', 'time_zone',
    'day', 'month', 'year', 'frequency', 'last_edited', 'created_at', 'unique_key'
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

// Since created_at is now human-readable, we leave it as a string.
$created_at = $cal_conn->real_escape_string($data['created_at']);

// For last_edited, we continue to format it as before.
$last_edited = date('Y-m-d H:i:s', strtotime($data['last_edited'] ?? 'now'));

// Extract and sanitize the unique_key.
$unique_key = $cal_conn->real_escape_string($data['unique_key']);

// Set synced flag to integer 1 (meaning synced)
$synced = 1;

try {
    // Prepare the insert query – note the addition of the unique_key field.
    $query = "
        INSERT INTO datecycles_tb
        (buwana_id, cal_id, cal_name, cal_color, title, date, time, time_zone, day, month, year, frequency, created_at, last_edited, synced, unique_key)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ";

    $stmt = $cal_conn->prepare($query);
    if (!$stmt) {
        throw new Exception('Failed to prepare the statement: ' . $cal_conn->error);
    }

    // Bind the parameters.
    // Parameter types: i = integer, s = string.
    // Order: buwana_id (i), cal_id (i), cal_name (s), cal_color (s), title (s),
    // date (s), time (s), time_zone (s), day (i), month (i), year (i),
    // frequency (s), created_at (s), last_edited (s), synced (i), unique_key (s)
    $stmt->bind_param(
        'iissssssiiisssis',
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
        $unique_key
    );

    // Execute the query.
    $stmt->execute();
    $new_id = $stmt->insert_id;
    $stmt->close();

    // Return success response with the new ID.
    echo json_encode(['success' => true, 'id' => $new_id]);
} catch (Exception $e) {
    error_log('Error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
}
?>
