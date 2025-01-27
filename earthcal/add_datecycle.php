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
if (!isset($data['user_id'], $data['calendar_id'], $data['event_name'], $data['date'], $data['cal_color'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields: user_id, calendar_id, event_name, date, or cal_color.']);
    exit();
}

// Extract and sanitize inputs
$user_id = (int) $data['user_id'];
$calendar_id = (int) $data['calendar_id'];
$event_name = $cal_conn->real_escape_string($data['event_name']);
$date = $cal_conn->real_escape_string($data['date']);
$frequency = $cal_conn->real_escape_string($data['frequency'] ?? 'One-time');
$completed = $cal_conn->real_escape_string($data['completed'] ?? 'No');
$pinned = $cal_conn->real_escape_string($data['pinned'] ?? 'No');
$public = $cal_conn->real_escape_string($data['public'] ?? 'No');
$comment = $cal_conn->real_escape_string($data['comment'] ?? '');
$color = $cal_conn->real_escape_string($data['color'] ?? null);
$cal_color = $cal_conn->real_escape_string($data['cal_color']);
$raw_json = $cal_conn->real_escape_string(json_encode($data)); // Optional raw JSON for debugging
$synced = $cal_conn->real_escape_string($data['synced'] ?? 'No');
$last_edited = date('Y-m-d H:i:s');

try {
    // Insert query
    $query = "
        INSERT INTO datecycles_tb
        (user_id, calendar_id, event_name, date, frequency, completed, pinned, public, comment, color, cal_color, raw_json, synced, last_edited)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ";

    $stmt = $cal_conn->prepare($query);
    if (!$stmt) {
        throw new Exception('Failed to prepare the statement: ' . $cal_conn->error);
    }

    // Bind parameters
    $stmt->bind_param(
        'iissssssssssss',
        $user_id,
        $calendar_id,
        $event_name,
        $date,
        $frequency,
        $completed,
        $pinned,
        $public,
        $comment,
        $color,
        $cal_color,
        $raw_json,
        $synced,
        $last_edited
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
