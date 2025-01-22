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
    echo json_encode(['success' => false, 'message' => 'CORS error: Invalid origin']);
    exit();
}

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Methods: POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
    exit(0);
}

// Ensure the request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['success' => false, 'message' => 'Invalid request method. Use POST.']);
    exit();
}

// Initialize response structure
$response = ['success' => false];

try {
    // Parse JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    error_log('Incoming request: ' . print_r($input, true)); // Log input for debugging

    $buwana_id = $input['buwana_id'] ?? null;

    // Validate Buwana ID
    if (empty($buwana_id) || !is_numeric($buwana_id)) {
        http_response_code(400); // Bad Request
        echo json_encode(['success' => false, 'message' => 'Invalid or missing Buwana ID.']);
        exit();
    }

    // Fetch all calendars associated with the user
    $sqlAllCalendars = "SELECT calendar_id AS id, calendar_name AS name, calendar_color AS color
                        FROM calendars_tb
                        WHERE buwana_id = ?";
    $stmtAll = $cal_conn->prepare($sqlAllCalendars);
    $stmtAll->bind_param("i", $buwana_id);
    $stmtAll->execute();
    $allCalendars = $stmtAll->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmtAll->close();

    // Log fetched calendars
    error_log('Fetched calendars: ' . print_r($allCalendars, true));

    // Prepare the response
    $response['success'] = true;
    $response['calendars'] = $allCalendars;

    if (empty($allCalendars)) {
        $response['message'] = 'No calendars found for this user.';
    }
} catch (Exception $e) {
    error_log('Error in grab_user_calendars.php: ' . $e->getMessage());
    $response['message'] = 'An error occurred while fetching calendars.';
} finally {
    $cal_conn->close();
}

// Output response as JSON
echo json_encode($response);
exit();
