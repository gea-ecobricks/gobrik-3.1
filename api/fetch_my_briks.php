<?php
require_once '../gobrikconn_env.php'; // Include database connection

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Get the request parameters sent by DataTables
$draw = isset($_POST['draw']) ? intval($_POST['draw']) : 0;
$start = isset($_POST['start']) ? intval($_POST['start']) : 0;
$length = isset($_POST['length']) ? intval($_POST['length']) : 10;
$ecobricker_id = isset($_POST['ecobricker_id']) ? $_POST['ecobricker_id'] : ''; // Get the ecobricker_id from the request

// Search term (if any)
$searchValue = $_POST['search']['value'] ?? '';

// Define a safe HTML helper function
function safe_html($string) {
    return $string !== null ? htmlspecialchars($string, ENT_QUOTES, 'UTF-8') : '';
}

// Prepare the base SQL query, including the community_name field
$sql = "SELECT ecobrick_thumb_photo_url, ecobrick_full_photo_url, weight_g, volume_ml, density, date_logged_ts,
        location_full, location_watershed, ecobricker_maker, community_name, serial_no, status, photo_version
        FROM tb_ecobricks";

$bindTypes = "";
$bindValues = [];

// If ecobricker_id is provided, use it to filter by maker_id in the SQL query
if (!empty($ecobricker_id)) {
    $sql .= " WHERE maker_id = ?";
    $bindTypes .= "s";
    $bindValues[] = $ecobricker_id;
}

// Add search filter if there is a search term
if (!empty($searchValue)) {
    $sql .= (!empty($ecobricker_id) ? " AND " : " WHERE ") .
            "(serial_no LIKE ? OR location_full LIKE ? OR ecobricker_maker LIKE ? OR community_name LIKE ?)";
    $bindTypes .= "ssss";
    $searchTerm = "%" . $searchValue . "%";
    $bindValues = array_merge($bindValues, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
}

// Count total records before filtering
$totalRecordsQuery = "SELECT COUNT(*) as total FROM tb_ecobricks";
if (!empty($ecobricker_id)) {
    $totalRecordsQuery .= " WHERE maker_id = ?";
    $stmtTotal = $gobrik_conn->prepare($totalRecordsQuery);
    $stmtTotal->bind_param("s", $ecobricker_id);
} else {
    $stmtTotal = $gobrik_conn->prepare($totalRecordsQuery);
}

$stmtTotal->execute();
$totalRecordsResult = $stmtTotal->get_result();
$totalRecords = $totalRecordsResult->fetch_assoc()['total'] ?? 0;

// Prepare the statement for the main query
$sql .= " ORDER BY date_logged_ts DESC LIMIT ?, ?";
$bindTypes .= "ii";
$bindValues[] = $start;
$bindValues[] = $length;

$stmt = $gobrik_conn->prepare($sql);

if (!$stmt) {
    echo json_encode([
        "draw" => $draw,
        "recordsTotal" => 0,
        "recordsFiltered" => 0,
        "data" => [],
        "error" => "Failed to prepare SQL statement: " . $gobrik_conn->error
    ]);
    exit;
}

// Bind parameters dynamically
$stmt->bind_param($bindTypes, ...$bindValues);
$stmt->execute();

$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    // Process the location into $location_brik
    $location_parts = explode(',', $row['location_full']);
    $location_parts = array_map('trim', $location_parts);

    $location_last = $location_parts[count($location_parts) - 1] ?? '';
    $location_third_last = $location_parts[count($location_parts) - 3] ?? '';
    $location_brik = $location_third_last . ', ' . $location_last;

    if (!empty($row['location_watershed'])) {
        $location_brik = $row['location_watershed'] . ', ' . $location_brik;
    }

    $serial_url = "brik.php?serial_no=" . urlencode($row['serial_no']);

    $data[] = [
        'ecobrick_thumb_photo_url' => '<img src="' . safe_html($row['ecobrick_thumb_photo_url']) . '?v=' . safe_html($row['photo_version']) . '"
            alt="' . safe_html($row['serial_no']) . '"
            title="Ecobrick ' . safe_html($row['serial_no']) . '"
            class="table-thumbnail"
            onclick="ecobrickPreview(\'' . safe_html($row['ecobrick_full_photo_url']) . '?v=' . safe_html($row['photo_version']) . '\', \'' . safe_html($row['serial_no']) . '\', \'' . safe_html($row['weight_g']) . ' g\', \'' . safe_html($row['ecobricker_maker']) . '\', \'' . safe_html($location_brik) . '\')">',
        'weight_g' => number_format($row['weight_g']) . ' g',
        'volume_ml' => number_format($row['volume_ml']) . ' ml',
        'density' => number_format($row['density'], 2) . ' g/ml',
        'date_logged_ts' => date("Y-m-d", strtotime($row['date_logged_ts'])),
        'location_brik' => safe_html($location_brik),
        'ecobricker_maker' => safe_html($row['ecobricker_maker']),
        'community_name' => safe_html($row['community_name']),
        'status' => safe_html($row['status']),
        'serial_no' => safe_html($row['serial_no']) // Pass only the raw serial number
    ];
}

// Get total filtered records
$totalFilteredQuery = "SELECT COUNT(*) as total FROM tb_ecobricks";
if (!empty($ecobricker_id)) {
    $totalFilteredQuery .= " WHERE maker_id = ?";
}
if (!empty($searchValue)) {
    $totalFilteredQuery .= (!empty($ecobricker_id) ? " AND " : " WHERE ") .
                           "(serial_no LIKE ? OR location_full LIKE ? OR ecobricker_maker LIKE ? OR community_name LIKE ?)";
}
$stmtFiltered = $gobrik_conn->prepare($totalFilteredQuery);

if (!empty($ecobricker_id) && !empty($searchValue)) {
    $stmtFiltered->bind_param("ssss", $ecobricker_id, $searchTerm, $searchTerm, $searchTerm);
} elseif (!empty($ecobricker_id)) {
    $stmtFiltered->bind_param("s", $ecobricker_id);
} elseif (!empty($searchValue)) {
    $stmtFiltered->bind_param("ssss", $searchTerm, $searchTerm, $searchTerm, $searchTerm);
}

$stmtFiltered->execute();
$totalFilteredResult = $stmtFiltered->get_result();
$totalFilteredRecords = $totalFilteredResult->fetch_assoc()['total'] ?? 0;

// Prepare the JSON response
$response = [
    "draw" => $draw,
    "recordsTotal" => $totalRecords,
    "recordsFiltered" => $totalFilteredRecords,
    "data" => $data
];

// Close database connection
$gobrik_conn->close();

// Send the response in JSON format
header('Content-Type: application/json');
echo json_encode($response, JSON_PRETTY_PRINT);
?>
