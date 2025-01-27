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


// Read input JSON payload
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Validate input
if (!isset($data['buwana_id']) || !isset($data['calendar_id'])) {
    sendErrorResponse('Missing required fields: buwana_id and/or calendar_id.');
}

$buwana_id = $data['buwana_id'];
$calendar_id = $data['calendar_id'];

try {
    // Create a prepared statement to fetch calendar events
    $query = "
        SELECT e.id AS ID, e.event_name, e.frequency, e.day, e.month, e.year, e.date,
               e.comment, e.comments, e.completed, e.pinned, e.last_edited,
               e.datecycle_color, e.calendar_color, e.synced
        FROM events e
        INNER JOIN calendars c ON e.calendar_id = c.id
        WHERE c.id = :calendar_id AND c.buwana_id = :buwana_id AND e.deleted = 0
    ";

    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':calendar_id', $calendar_id, PDO::PARAM_STR);
    $stmt->bindParam(':buwana_id', $buwana_id, PDO::PARAM_STR);
    $stmt->execute();

    // Fetch results
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($events === false || count($events) === 0) {
        sendErrorResponse('No events found for the specified calendar.');
    }

    // Return successful response with event data
    echo json_encode([
        "success" => true,
        "data" => [
            "calendar_id" => $calendar_id,
            "events_json_blob" => $events
        ]
    ]);
} catch (PDOException $e) {
    // Handle database errors
    sendErrorResponse('Database error: ' . $e->getMessage());
} catch (Exception $e) {
    // Handle other errors
    sendErrorResponse('An unexpected error occurred: ' . $e->getMessage());
}
?>
