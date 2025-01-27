<?php
require_once '../earthenAuth_helper.php';
require_once '../buwanaconn_env.php';
require_once '../calconn_env.php'; // Include EarthCal database connection

error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING); // Suppress warnings and notices
ini_set('display_errors', '0'); // Disable error display for production

$allowed_origins = [
    'https://cal.earthen.io',
    'https://cycles.earthen.io',
    'https://ecobricks.org',
    'https://gobrik.com',
    'http://localhost',
    'file://' // Allow local Snap apps or filesystem-based origins
];

// Normalize the HTTP_ORIGIN (remove trailing slashes or fragments)
$origin = isset($_SERVER['HTTP_ORIGIN']) ? rtrim($_SERVER['HTTP_ORIGIN'], '/') : '';

// Log the detected origin
error_log('Incoming HTTP_ORIGIN: ' . $origin);

if (empty($origin)) {
    // Allow requests with no origin for local development
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
    header('Access-Control-Allow-Credentials: true');
} elseif (in_array($origin, $allowed_origins)) {
    header('Access-Control-Allow-Origin: ' . $origin);
    header('Access-Control-Allow-Methods: POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
    header('Access-Control-Allow-Credentials: true');
} else {
    error_log('CORS error: Invalid or missing HTTP_ORIGIN - ' . $origin);
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['success' => false, 'message' => 'CORS error: Invalid origin']);
    exit();
}

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Methods: POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
    header('Access-Control-Allow-Credentials: true');
    exit(0);
}

$response = ['success' => false];

// Check the request method
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    $response['message'] = 'Invalid request method. Use POST.';
    echo json_encode($response);
    exit();
}

// Get the JSON input from the request
$input = json_decode(file_get_contents('php://input'), true);
$buwana_id = $input['buwana_id'] ?? null;
$calendar_name = $input['calendar_name'] ?? null;

// Validate inputs
if (empty($calendar_name) && (empty($buwana_id) || !is_numeric($buwana_id))) {
    $response['message'] = 'Invalid or missing input. Either buwana_id or calendar_name must be provided.';
    echo json_encode($response);
    exit();
}

try {
    // Ensure the request method is POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405); // Method Not Allowed
        echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
        exit;
    }

    // Get the raw input data
    $inputData = file_get_contents('php://input');
    $dateCycle = json_decode($inputData, true);

    // Validate required fields
    $requiredFields = ['user_id', 'calendar_id', 'event_name', 'date'];
    foreach ($requiredFields as $field) {
        if (empty($dateCycle[$field])) {
            http_response_code(400); // Bad Request
            echo json_encode(['success' => false, 'message' => "Missing required field: $field"]);
            exit;
        }
    }

    // Extract data from the request
    $userId = intval($dateCycle['user_id']);
    $calendarId = intval($dateCycle['calendar_id']);
    $eventName = htmlspecialchars($dateCycle['event_name']);
    $date = $dateCycle['date']; // Assume date is already in YYYY-MM-DD format
    $frequency = $dateCycle['Frequency'] ?? 'One-time';
    $completed = $dateCycle['Completed'] ?? 'No';
    $pinned = $dateCycle['Pinned'] ?? 'No';
    $public = $dateCycle['public'] ?? 'No';
    $comment = htmlspecialchars($dateCycle['comment'] ?? '');
    $color = htmlspecialchars($dateCycle['calendar_color'] ?? '');
    $toDelete = $dateCycle['Delete'] ?? 'No';
    $lastEdited = date('Y-m-d H:i:s'); // Current timestamp
    $synked = 'Yes'; // API always marks records as synced

    // Prepare SQL to insert a new record
    $sql = "INSERT INTO datecycles_tb (
                user_id, calendar_id, event_name, date, frequency, completed, pinned, public,
                comment, color, delete, last_edited, synked
            ) VALUES (
                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
            )";

    // Prepare and execute the statement
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $userId, $calendarId, $eventName, $date, $frequency, $completed, $pinned, $public,
        $comment, $color, $toDelete, $lastEdited, $synked
    ]);

    // Get the last inserted ID
    $lastInsertId = $pdo->lastInsertId();

    // Respond with success and the new ID
    http_response_code(201); // Created
    echo json_encode([
        'success' => true,
        'message' => 'DateCycle added successfully.',
        'id' => $lastInsertId
    ]);
} catch (PDOException $e) {
    // Database error
    http_response_code(500); // Internal Server Error
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    // General error
    http_response_code(500); // Internal Server Error
    echo json_encode(['success' => false, 'message' => 'An unexpected error occurred: ' . $e->getMessage()]);
}
?>
