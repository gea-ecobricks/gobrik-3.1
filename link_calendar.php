<?php
require_once '../earthenAuth_helper.php';
require_once '../buwanaconn_env.php';
require_once '../calconn_env.php'; // Include EarthCal database connection

error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING); // Suppress warnings and notices
ini_set('display_errors', '0'); // Disable error display for production

$allowed_origins = [
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
    // Check if the calendar already exists for the user
    $sql = "SELECT calendar_id FROM calendars_tb WHERE buwana_id = ? AND calendar_name = ?";
    $stmt = $cal_conn->prepare($sql);

    if (!$stmt) {
        throw new Exception("Database error: " . $cal_conn->error);
    }

    $stmt->bind_param("is", $buwana_id, $calendar_name);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Calendar already exists; return its ID
        $row = $result->fetch_assoc();
        echo json_encode(['success' => true, 'calendar_id' => $row['calendar_id']]);
    } else {
        // Calendar doesn't exist; create a new one
        $insertSql = "INSERT INTO calendars_tb (buwana_id, calendar_name, calendar_public) VALUES (?, ?, 0)";
        $insertStmt = $cal_conn->prepare($insertSql);

        if (!$insertStmt) {
            throw new Exception("Database error: " . $cal_conn->error);
        }

        $insertStmt->bind_param("is", $buwana_id, $calendar_name);
        $insertStmt->execute();

        if ($insertStmt->affected_rows > 0) {
            $newCalendarId = $insertStmt->insert_id;
            echo json_encode(['success' => true, 'calendar_id' => $newCalendarId]);
        } else {
            throw new Exception("Failed to create new calendar.");
        }
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} finally {
    $cal_conn->close();
}
