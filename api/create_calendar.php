<?php
header('Content-Type: application/json');

// Include the database connection for the EarthCal database
require_once '../calconn_env.php';


$allowed_origins = [
    'https://cycles.earthen.io',
    'https://ecobricks.org',
    'https://gobrik.com',
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
$calendar_color = $input['calendar_color'] ?? 'blue';
$calendar_public = $input['calendar_public'] ?? 0;

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

try {
    // Insert the new calendar into the database
    $sql = "INSERT INTO calendars_tb (buwana_id, calendar_name, calendar_created, last_updated, calendar_color, calendar_public)
            VALUES (?, ?, NOW(), NOW(), ?, ?)";
    $stmt = $cal_conn->prepare($sql);

    if (!$stmt) {
        throw new Exception("Database error: " . $cal_conn->error);
    }

    $stmt->bind_param("issi", $buwana_id, $calendar_name, $calendar_color, $calendar_public);
    $stmt->execute();

    if ($stmt->affected_rows === 0) {
        throw new Exception("Failed to create the calendar.");
    }

    $stmt->close();

    // Success response
    $response['success'] = true;
    $response['message'] = 'Calendar created successfully.';
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
} finally {
    // Close the database connection
    $cal_conn->close();
}

echo json_encode($response);
exit();
?>
