<?php
require_once '../earthenAuth_helper.php';
require_once '../buwanaconn_env.php';
require_once '../calconn_env.php'; // Include EarthCal database connection

// Set headers for JSON response
header('Content-Type: application/json');

// CORS configuration
$allowed_origins = [
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

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Methods: POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
    exit(0);
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['success' => false, 'message' => 'Invalid request method. Use POST.']);
    exit();
}

try {
    // Parse input
    $inputData = json_decode(file_get_contents('php://input'), true);

    // Validate input
    $requiredFields = ['local_id', 'name', 'color', 'public'];
    foreach ($requiredFields as $field) {
        if (empty($inputData[$field])) {
            http_response_code(400); // Bad Request
            echo json_encode(['success' => false, 'message' => "Missing required field: $field"]);
            exit();
        }
    }

    // Extract input data
    $localId = htmlspecialchars($inputData['local_id']);
    $name = htmlspecialchars($inputData['name']);
    $color = htmlspecialchars($inputData['color']);
    $public = intval($inputData['public']);
    $buwanaId = isset($inputData['buwana_id']) && is_numeric($inputData['buwana_id']) ? intval($inputData['buwana_id']) : null;

    // Prepare SQL to insert the calendar
    $sql = "INSERT INTO calendars_tb (
                buwana_id, calendar_name, calendar_color, calendar_public
            ) VALUES (?, ?, ?, ?)";

    $stmt = $cal_conn->prepare($sql);
    $stmt->bind_param("issi", $buwanaId, $name, $color, $public);

    // Execute the query
    if ($stmt->execute()) {
        // Retrieve the newly inserted calendar ID
        $calendarId = $stmt->insert_id;

        // Return success response
        echo json_encode([
            'success' => true,
            'message' => 'Calendar successfully created.',
            'calendar_id' => $calendarId
        ]);
    } else {
        throw new Exception("Failed to insert calendar into the database.");
    }
} catch (Exception $e) {
    http_response_code(500); // Internal Server Error
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} finally {
    $stmt->close();
    $cal_conn->close();
}
?>