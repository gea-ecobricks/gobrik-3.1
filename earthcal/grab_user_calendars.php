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
    // ✅ Fetch calendars including `created_at`
    $query = "
        SELECT calendar_id, calendar_name, calendar_color, calendar_public, last_updated, created_at
        FROM calendars_tb
        WHERE (buwana_id = ? OR calendar_public = 1) AND deleted = 0
    ";

    $stmt = $cal_conn->prepare($query);
    if (!$stmt) {
        throw new Exception('Failed to prepare the statement: ' . $cal_conn->error);
    }

    $stmt->bind_param('i', $buwana_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $calendars = [];

    while ($row = $result->fetch_assoc()) {
        $calendars[] = $row;
    }

    $stmt->close();

    echo json_encode([
        "success" => true,
        "buwana_id" => $buwana_id,
        "calendars" => $calendars
    ]);

} catch (Exception $e) {
    error_log('Error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
}
?>
