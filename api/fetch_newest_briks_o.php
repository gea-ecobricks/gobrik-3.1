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
if (!empty($searchValue)) {
    $sql .= " AND (serial_no LIKE ? OR location_full LIKE ? OR ecobricker_maker LIKE ?)";
    $searchTerm = "%$searchValue%";
}

// Add ordering and pagination
$sql .= " ORDER BY date_logged_ts DESC LIMIT ?, ?";
$stmt = $gobrik_conn->prepare($sql);

if (!$stmt) {
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

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = [
        'ecobrick_thumb_photo_url' => '<img src="' . htmlspecialchars($row['ecobrick_thumb_photo_url']) . '" alt="Thumbnail">',
        'ecobricker_maker' => htmlspecialchars($row['ecobricker_maker']),
        'location_brik' => htmlspecialchars($row['location_full']),
        'weight_g' => number_format($row['weight_g']) . ' g',
        'volume_ml' => number_format($row['volume_ml']) . ' ml',
        'density' => number_format($row['density'], 2) . ' g/ml',
        'status' => htmlspecialchars($row['status']),
        'serial_no' => htmlspecialchars($row['serial_no'])
    ];
}

// Get total records
$totalRecordsQuery = "SELECT COUNT(*) AS total FROM tb_ecobricks WHERE status != 'not ready'";
$totalRecordsResult = $gobrik_conn->query($totalRecordsQuery);
$totalRecords = $totalRecordsResult->fetch_assoc()['total'] ?? 0;

// Get filtered records
$totalFilteredRecords = count($data); // Filtered rows are equal to fetched rows here

$response = [
    "draw" => $draw,
    "recordsTotal" => $totalRecords,
    "recordsFiltered" => $totalFilteredRecords,
    "data" => $data
];

echo json_encode($response, JSON_PRETTY_PRINT);
?>
