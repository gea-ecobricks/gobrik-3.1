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
    echo json_encode(['success' => false, 'message' => 'CORS error: Invalid origin v2']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Methods: POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
    exit(0);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit();
}

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!isset($data['buwana_id'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required field: buwana_id.']);
    exit();
}

$buwana_id = (int) $data['buwana_id'];

try {
    // 🔹 **Fetch Unique Calendars from `datecycles_tb` Instead of `calendars_tb`**
    $query = "
        SELECT DISTINCT cal_id, cal_name, cal_color, public AS calendar_public, last_edited AS last_updated
        FROM datecycles_tb
        WHERE (buwana_id = ? OR public = 1) AND delete_it = 0
    ";

    $stmt = $cal_conn->prepare($query);
    if (!$stmt) {
        throw new Exception('Failed to prepare the statement: ' . $cal_conn->error);
    }

    // Bind parameters
    $stmt->bind_param('i', $buwana_id);

    // Execute the query
    $stmt->execute();

    // Fetch results
    $result = $stmt->get_result();
    $calendars = [];
    while ($row = $result->fetch_assoc()) {
        $calendars[] = $row;
    }

    // Close the statement
    $stmt->close();

    // Check if calendars are found
    if (count($calendars) === 0) {
        echo json_encode([
            "success" => true,
            "calendars" => []
        ]);
        exit();
    }

    // Return the calendars data
    echo json_encode([
        "success" => true,
        "calendars" => $calendars
    ]);
} catch (Exception $e) {
    error_log('Error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
}
?>
