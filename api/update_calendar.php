<?php
require_once '../earthenAuth_helper.php';
require_once '../buwanaconn_env.php';
require_once '../calconn_env.php'; // Include EarthCal database connection

$allowed_origins = [
    'https://cycles.earthen.io',
    'https://ecobricks.org',
    'https://gobrik.com',
    'http://localhost', // Allow localhost
    'http://127.0.0.1', // Allow loopback address
    'http://localhost:8000', // Allow specific localhost ports (adjust as needed)
    'http://127.0.0.1:8000'
];

// Normalize the HTTP_ORIGIN (remove trailing slashes or fragments)
$origin = isset($_SERVER['HTTP_ORIGIN']) ? rtrim($_SERVER['HTTP_ORIGIN'], '/') : '';

if ($origin && in_array($origin, $allowed_origins)) {
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
$datecycles = $input['datecycles'] ?? null;

// Validate inputs
if (empty($buwana_id) || !is_numeric($buwana_id)) {
    $response['message'] = 'Invalid or missing Buwana ID.';
    echo json_encode($response);
    exit();
}

if (empty($calendar_name)) {
    $response['message'] = 'Invalid or missing calendar name.';
    echo json_encode($response);
    exit();
}

if (!is_array($datecycles)) {
    $response['message'] = 'Invalid or missing datecycles data.';
    echo json_encode($response);
    exit();
}

try {
    // Step 1: Convert datecycles array to JSON
    $datecycles_json = json_encode($datecycles);

    if ($datecycles_json === false) {
        throw new Exception('Failed to encode datecycles data to JSON.');
    }

    // Step 2: Update the calendar data in the database
    $sql = "UPDATE calendars_tb
            SET events_json_blob = ?, last_updated = NOW()
            WHERE buwana_id = ? AND calendar_name = ?";
    $stmt = $cal_conn->prepare($sql);

    if (!$stmt) {
        throw new Exception("Database error: " . $cal_conn->error);
    }

    $stmt->bind_param("sis", $datecycles_json, $buwana_id, $calendar_name);
    $stmt->execute();

    if ($stmt->affected_rows === 0) {
        throw new Exception("No calendar found to update, or no changes were made.");
    }

    $stmt->close();

    // Step 3: Return success response
    $response['success'] = true;
    $response['message'] = 'Calendar updated successfully.';
    $response['last_updated'] = date('Y-m-d H:i:s');
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
} finally {
    // Close the database connection
    $cal_conn->close();
}

// Output the JSON response
echo json_encode($response);
exit();
?>
