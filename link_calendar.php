<?php
require_once '../earthenAuth_helper.php';
require_once '../buwanaconn_env.php'; // Buwana database connection
require_once '../calconn_env.php';   // EarthCal database connection

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

$origin = isset($_SERVER['HTTP_ORIGIN']) ? rtrim($_SERVER['HTTP_ORIGIN'], '/') : '';
if (empty($origin)) {
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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method. Use POST.']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
$buwana_id = $input['buwana_id'] ?? null;
$calendar_name = $input['calendar_name'] ?? null;

if (empty($buwana_id) || !is_numeric($buwana_id) || empty($calendar_name)) {
    echo json_encode(['success' => false, 'message' => 'Invalid or missing parameters.']);
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
