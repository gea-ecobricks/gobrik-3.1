<?php
require_once '../earthenAuth_helper.php';
require_once '../buwanaconn_env.php';
require_once '../calconn_env.php'; // Include EarthCal database connection

error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', '/path/to/secure/error.log'); // Adjust this path as needed

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
error_log('Incoming HTTP_ORIGIN: ' . $origin);

if (empty($origin)) {
    header('Access-Control-Allow-Origin: *');
} elseif (in_array($origin, $allowed_origins)) {
    header('Access-Control-Allow-Origin: ' . $origin);
} else {
    error_log('CORS error: Invalid or missing HTTP_ORIGIN - ' . $origin);
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['success' => false, 'message' => 'CORS error: Invalid origin']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Methods: POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
    exit(0);
}

$response = ['success' => false];

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    $response['message'] = 'Invalid request method. Use POST.';
    echo json_encode($response);
    exit();
}

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!isset($data['buwana_id']) || !isset($data['calendar_id'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields: buwana_id and/or calendar_id.']);
    exit();
}

$buwana_id = $data['buwana_id'];
$calendar_id = $data['calendar_id'];

if (strlen($buwana_id) > 50 || strlen($calendar_id) > 50) {
    echo json_encode(['success' => false, 'message' => 'Invalid input length.']);
    exit();
}

try {
    // Prepare the query
    $query = "
        SELECT e.id AS ID, e.event_name, e.frequency, e.day, e.month, e.year, e.date,
               e.comment, e.comments, e.completed, e.pinned, e.last_edited,
               e.datecycle_color, e.calendar_color, e.synced
        FROM events e
        INNER JOIN calendars c ON e.calendar_id = c.id
        WHERE c.id = ? AND c.buwana_id = ? AND e.deleted = 0
    ";

    // Prepare the statement
    $stmt = $cal_conn->prepare($query);
    if (!$stmt) {
        throw new Exception('Failed to prepare the statement: ' . $cal_conn->error);
    }

    // Bind parameters
    $stmt->bind_param('ss', $calendar_id, $buwana_id);

    // Execute the query
    $stmt->execute();

    // Fetch results
    $result = $stmt->get_result();
    $events = [];
    while ($row = $result->fetch_assoc()) {
        $events[] = $row;
    }

    // Close the statement
    $stmt->close();

    // Check if events are found
    if (count($events) === 0) {
        echo json_encode([
            "success" => true,
            "data" => [
                "calendar_id" => $calendar_id,
                "events_json_blob" => []
            ]
        ]);
        exit();
    }

    // Return the events data
    echo json_encode([
        "success" => true,
        "data" => [
            "calendar_id" => $calendar_id,
            "events_json_blob" => $events
        ]
    ]);
} catch (Exception $e) {
    error_log('Error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
}
?>
