<?php
require_once '../earthenAuth_helper.php';
require_once '../buwanaconn_env.php';
require_once '../calconn_env.php'; // Include EarthCal database connection

error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', '/path/to/secure/error.log'); // Adjust this path

// Check if $cal_conn is initialized
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

if (!isset($data['buwana_id'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required field: buwana_id.']);
    exit();
}

$buwana_id = (int) $data['buwana_id'];

try {
    // Prepare the query
    $query = "
        SELECT calendar_id, calendar_name, calendar_color, calendar_public,
               last_updated, synked, deleted, conflict_flag
        FROM calendars_tb
        WHERE (buwana_id = ? OR calendar_public = 1) AND deleted = 0
    ";

    $stmt = $cal_conn->prepare($query);
    if (!$stmt) {
        throw new Exception('Failed to prepare the statement: ' . $cal_conn->error);
    }

    // Bind parameters
    $stmt->bind_param('i', $buwana_id);

    // Execute the query
    $stmt->execute();

    // Fetch results
    $result = $stmt->get_result();
    $calendars = [];
    while ($row = $result->fetch_assoc()) {
        $calendars[] = $row;
    }

    // Close the statement
    $stmt->close();

    // Check if calendars are found
    if (count($calendars) === 0) {
        echo json_encode([
            "success" => true,
            "calendars" => []
        ]);
        exit();
    }

    // Return the calendars data
    echo json_encode([
        "success" => true,
        "calendars" => $calendars
    ]);
} catch (Exception $e) {
    error_log('Error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
}
?>
