<?php
require_once '../earthenAuth_helper.php';
require_once '../buwanaconn_env.php';
require_once '../calconn_env.php'; // Include EarthCal database connection

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

// Validate required fields. (You may add more if needed.)
$required_fields = [
    'unique_key', 'buwana_id', 'cal_id', 'cal_name', 'cal_color', 'title', 'date',
    'time', 'time_zone', 'day', 'month', 'year', 'frequency', 'last_edited', 'created_at'
];
foreach ($required_fields as $field) {
    if (!isset($data[$field])) {
        echo json_encode(['success' => false, 'message' => "Missing required field: $field"]);
        exit();
    }
}

// Sanitize and extract input.
$unique_key  = $cal_conn->real_escape_string($data['unique_key']);
$buwana_id   = (int)$data['buwana_id'];
$cal_id      = (int)$data['cal_id'];
$cal_name    = $cal_conn->real_escape_string($data['cal_name']);
$cal_color   = $cal_conn->real_escape_string($data['cal_color']);
$title       = $cal_conn->real_escape_string($data['title']);
$date        = $cal_conn->real_escape_string($data['date']);  // e.g., "2025-2-1"
$time        = $cal_conn->real_escape_string($data['time']);
$time_zone   = $cal_conn->real_escape_string($data['time_zone']);
$day         = (int)$data['day'];
$month       = (int)$data['month'];
$year        = (int)$data['year'];
$frequency   = $cal_conn->real_escape_string($data['frequency']);
$last_edited = date('Y-m-d H:i:s', strtotime($data['last_edited']));
$created_at  = $cal_conn->real_escape_string($data['created_at']);

// Optional fields.
$completed       = isset($data['completed']) ? $cal_conn->real_escape_string($data['completed']) : "0";
$pinned          = isset($data['pinned']) ? $cal_conn->real_escape_string($data['pinned']) : "0";
$public          = isset($data['public']) ? $cal_conn->real_escape_string($data['public']) : "0";
$comment         = isset($data['comment']) ? $cal_conn->real_escape_string($data['comment']) : "";
$comments        = isset($data['comments']) ? $cal_conn->real_escape_string($data['comments']) : "";
$datecycle_color = isset($data['datecycle_color']) ? $cal_conn->real_escape_string($data['datecycle_color']) : "#000";
$synced          = (int)$data['synced'];
$conflict        = isset($data['conflict']) ? $cal_conn->real_escape_string($data['conflict']) : "0";
$delete_it       = isset($data['delete_it']) ? $cal_conn->real_escape_string($data['delete_it']) : "0";

try {
    // Prepare the update query.
    $query = "
        UPDATE datecycles_tb
        SET buwana_id = ?, cal_id = ?, cal_name = ?, cal_color = ?, title = ?, date = ?, time = ?, time_zone = ?, day = ?, month = ?, year = ?, frequency = ?, last_edited = ?,
            created_at = ?, completed = ?, pinned = ?, public = ?, comment = ?, comments = ?, datecycle_color = ?, synced = ?, conflict = ?, delete_it = ?
        WHERE unique_key = ?
    ";

    $stmt = $cal_conn->prepare($query);
    if (!$stmt) {
        throw new Exception('Failed to prepare statement: ' . $cal_conn->error);
    }

    // Bind parameters. (Adjust the type string as necessary.)
    $stmt->bind_param(
        'iissssssiiisssiisssis',
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
        $last_edited,
        $created_at,
        $completed,
        $pinned,
        $public,
        $comment,
        $comments,
        $datecycle_color,
        $synced,
        $conflict,
        $delete_it,
        $unique_key
    );

    $stmt->execute();
    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No record updated.']);
    }
    $stmt->close();
} catch (Exception $e) {
    error_log('Error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
}
?>
