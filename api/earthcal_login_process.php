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

// Initialize the response
$response = ['success' => false];

// Check the request method
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    $response['message'] = 'Invalid request method. Use POST.';
    echo json_encode($response);
    exit();
}

// Decode JSON input
$input = json_decode(file_get_contents('php://input'), true);
$buwana_id = $input['buwana_id'] ?? null;

// Validate input
if (empty($buwana_id) || !is_numeric($buwana_id)) {
    $response['message'] = 'Invalid or missing Buwana ID.';
    echo json_encode($response);
    exit();
}

try {
    // Step 1: Fetch user data from the Buwana database
    $sql = "SELECT buwana_id, first_name, continent_code, location_full, connected_app_ids AS connected_apps
            FROM users_tb
            WHERE buwana_id = ?";
    $stmt = $buwana_conn->prepare($sql);

    if (!$stmt) {
        throw new Exception("Database error: " . $buwana_conn->error);
    }

    $stmt->bind_param("i", $buwana_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows !== 1) {
        throw new Exception("User not found in the Buwana database.");
    }

    $user_data = $result->fetch_assoc();
    $stmt->close();

    // Step 2: Prepare the response
    $response['success'] = true;
    $response['buwana_id'] = $user_data['buwana_id'];
    $response['first_name'] = $user_data['first_name'];
    $response['continent_code'] = $user_data['continent_code'];
    $response['location_full'] = $user_data['location_full'];
    $response['connected_apps'] = $user_data['connected_apps'];
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
} finally {
    // Close the database connection
    $buwana_conn->close();
}

// Output the JSON response
echo json_encode($response);
exit();
