<?php
require_once '../gobrikconn_env.php'; // Include database connection

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json'); // Ensure JSON response

// Initialize variables
$draw = intval($_POST['draw'] ?? 0);
$start = intval($_POST['start'] ?? 0);
$length = intval($_POST['length'] ?? 10);
$searchValue = $_POST['search']['value'] ?? '';

// Base SQL query
$sql = "SELECT ecobrick_thumb_photo_url, ecobricker_maker, location_full, weight_g, volume_ml, density, status, serial_no
        FROM tb_ecobricks
        WHERE status != 'not ready'";

// Search filter
$searchTerm = $searchValue ? "%$searchValue%" : null;
if (!empty($searchValue)) {
    $sql .= " AND (serial_no LIKE ? OR location_full LIKE ? OR ecobricker_maker LIKE ?)";
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

// Bind parameters dynamically
if (!empty($searchValue)) {
    $bindTypes = "sssii";
    $stmt->bind_param($bindTypes, $searchTerm, $searchTerm, $searchTerm, $start, $length);
} else {
    $bindTypes = "ii";
    $stmt->bind_param($bindTypes, $start, $length);
}

$stmt->execute();
$result = $stmt->get_result();

// Define a safe HTML helper
function safe_html($string) {
    return $string !== null ? htmlspecialchars($string, ENT_QUOTES, 'UTF-8') : '';
}

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = [
        'ecobrick_thumb_photo_url' => '<img src="' . safe_html($row['ecobrick_thumb_photo_url']) . '" alt="Thumbnail">',
        'ecobricker_maker' => safe_html($row['ecobricker_maker']),
        'location_brik' => safe_html($row['location_full']),
        'weight_g' => number_format($row['weight_g']) . ' g',
        'volume_ml' => number_format($row['volume_ml']) . ' ml',
        'density' => number_format($row['density'], 2) . ' g/ml',
        'status' => safe_html($row['status']),
        'serial_no' => safe_html($row['serial_no'])
    ];
}

// Get total records
$totalRecordsQuery = "SELECT COUNT(*) AS total FROM tb_ecobricks WHERE status != 'not ready'";
$totalRecordsResult = $gobrik_conn->query($totalRecordsQuery);
$totalRecords = $totalRecordsResult->fetch_assoc()['total'] ?? 0;

// Prepare the JSON response
$response = [
    "draw" => intval($draw),
    "recordsTotal" => intval($totalRecords),
    "recordsFiltered" => count($data), // Use filtered row count
    "data" => $data
];

echo json_encode($response, JSON_PRETTY_PRINT);
?>
