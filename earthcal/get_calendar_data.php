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

// Check if required fields are present
if (!isset($data['buwana_id']) || !isset($data['cal_id'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields: buwana_id or cal_id.']);
    exit();
}

$buwana_id = (int) $data['buwana_id'];
$cal_id = (int) $data['cal_id'];

// Debugging: Log the received values
error_log("🔹 get_calendar_data.php called with buwana_id: $buwana_id, cal_id: $cal_id");

try {
    // Fetch all dateCycles for the given calendar.
    $query = "
    SELECT ID, buwana_id, cal_id, title, date, time, time_zone, day, month, year,
           frequency, completed, pinned, public, comment, comments, datecycle_color,
           cal_name, cal_color, cal_emoji, date_emoji, synced, conflict, delete_it, last_edited, created_at, unique_key
    FROM datecycles_tb
    WHERE cal_id = ? AND (buwana_id = ? OR public = 1) AND delete_it = 0
";


    $stmt = $cal_conn->prepare($query);
    if (!$stmt) {
        throw new Exception('Failed to prepare the statement: ' . $cal_conn->error);
    }

    error_log("🔹 Executing query for cal_id: $cal_id and buwana_id: $buwana_id");

    // Bind parameters.
    $stmt->bind_param('ii', $cal_id, $buwana_id);

    $stmt->execute();

    $result = $stmt->get_result();
    $dateCycles = [];
    while ($row = $result->fetch_assoc()) {
        $dateCycles[] = $row;
    }

    $stmt->close();

    error_log("🔹 Retrieved " . count($dateCycles) . " dateCycles for cal_id: $cal_id");

    echo json_encode([
        "success" => true,
        "dateCycles" => $dateCycles
    ]);
} catch (Exception $e) {
    error_log('❌ Error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
}
?>
