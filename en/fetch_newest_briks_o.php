<?php
require_once '../gobrikconn_env.php'; // Include database connection

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Get DataTables parameters
$draw = $_POST['draw'] ?? 0;
$start = $_POST['start'] ?? 0;
$length = $_POST['length'] ?? 10;
$searchValue = $_POST['search']['value'] ?? '';

// Build the SQL query
$sql = "SELECT ecobrick_thumb_photo_url, ecobricker_maker, location_full, weight_g, volume_ml, density, status, serial_no
        FROM tb_ecobricks
        WHERE status != 'not ready'";

// Add search filter
if (!empty($searchValue)) {
    $sql .= " AND (serial_no LIKE ? OR location_full LIKE ? OR ecobricker_maker LIKE ?)";
    $searchTerm = "%" . $searchValue . "%";
}

// Add ordering and pagination
$sql .= " ORDER BY date_logged_ts DESC LIMIT ?, ?";
$stmt = $gobrik_conn->prepare($sql);

if (!$stmt) {
    error_log("SQL prepare failed: " . $gobrik_conn->error);
    echo json_encode([
        "draw" => $draw,
        "recordsTotal" => 0,
        "recordsFiltered" => 0,
        "data" => [],
        "error" => "SQL prepare failed: " . $gobrik_conn->error
    ]);
    exit;
}

// Bind parameters
$bindTypes = "ssii";
$stmt->bind_param($bindTypes, $searchTerm, $searchTerm, $searchTerm, $start, $length);

// Execute and fetch data
$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

// Get total records
$totalRecordsResult = $gobrik_conn->query("SELECT COUNT(*) AS total FROM tb_ecobricks WHERE status != 'not ready'");
$totalRecords = $totalRecordsResult->fetch_assoc()['total'] ?? 0;

// Prepare the JSON response
$response = [
    "draw" => intval($draw),
    "recordsTotal" => intval($totalRecords),
    "recordsFiltered" => intval($totalRecords),
    "data" => $data
];

// Output response
echo json_encode($response);
?>
