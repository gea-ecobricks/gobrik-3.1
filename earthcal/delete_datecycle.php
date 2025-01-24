<?php
require_once '../earthenAuth_helper.php';
require_once '../buwanaconn_env.php';
require_once '../calconn_env.php'; // Include EarthCal database connection

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

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Methods: POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
    exit(0);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['success' => false, 'message' => 'Invalid request method. Use POST.']);
    exit();
}

try {
    $inputData = json_decode(file_get_contents('php://input'), true);

    // Validate input
    if (empty($inputData['buwana_id']) || !is_numeric($inputData['buwana_id'])) {
        http_response_code(400); // Bad Request
        echo json_encode(['success' => false, 'message' => 'Missing or invalid Buwana ID.']);
        exit();
    }

    if (empty($inputData['datecycle_id'])) {
        http_response_code(400); // Bad Request
        echo json_encode(['success' => false, 'message' => 'Missing DateCycle ID.']);
        exit();
    }

    $buwanaId = intval($inputData['buwana_id']);
    $dateCycleId = htmlspecialchars($inputData['datecycle_id']);

    // Delete the dateCycle from the database
    $sql = "DELETE FROM datecycles_tb WHERE id = ? AND user_id = ?";
    $stmt = $cal_conn->prepare($sql);
    $stmt->bind_param("si", $dateCycleId, $buwanaId);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(['success' => true, 'message' => 'DateCycle deleted successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'No matching DateCycle found to delete.']);
        }
    } else {
        throw new Exception("Failed to delete DateCycle from the database.");
    }
} catch (Exception $e) {
    http_response_code(500); // Internal Server Error
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} finally {
    $stmt->close();
    $cal_conn->close();
}
?>
