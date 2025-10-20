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
        location_full, location_watershed, ecobricker_maker, community_name, serial_no, status, feature, photo_version
        FROM tb_ecobricks
        WHERE status != 'not ready'
          AND LOWER(TRIM(status)) NOT IN ('rejected', 'authenticated')";

$bindTypes = "";
$bindValues = [];

// If ecobricker_id is provided, use it to filter by maker_id in the SQL query
if (!empty($ecobricker_id)) {
    $sql .= " AND maker_id = ?";
    $bindTypes .= "s";
    $bindValues[] = $ecobricker_id;
}

// Add search filter if there is a search term
if (!empty($searchValue)) {
    $sql .= " AND (serial_no LIKE ? OR location_full LIKE ? OR ecobricker_maker LIKE ? OR community_name LIKE ?)";
    $bindTypes .= "ssss";
    $searchTerm = "%" . $searchValue . "%";
    $bindValues = array_merge($bindValues, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
}

// Count total records before filtering
$totalRecordsQuery = "SELECT COUNT(*) as total FROM tb_ecobricks WHERE status != 'not ready' AND LOWER(TRIM(status)) NOT IN ('rejected', 'authenticated')";
if (!empty($ecobricker_id)) {
    $totalRecordsQuery .= " AND maker_id = ?";
    $stmtTotal = $gobrik_conn->prepare($totalRecordsQuery);
    $stmtTotal->bind_param("s", $ecobricker_id);
} else {
    $stmtTotal = $gobrik_conn->prepare($totalRecordsQuery);
}

$stmtTotal->execute();
$totalRecordsResult = $stmtTotal->get_result();
$totalRecords = $totalRecordsResult->fetch_assoc()['total'] ?? 0;

// Determine ordering (default newest first)
$orderColumn = 'date_logged_ts';
$orderDirection = 'DESC';
$columnMap = [
    1 => 'ecobricker_maker',
    2 => 'status',
    3 => 'weight_g',
    4 => 'volume_ml',
    5 => 'density',
    6 => 'location_full',
    7 => 'serial_no'
];

if (isset($_POST['order'][0]['column'])) {
    $orderColumnIndex = intval($_POST['order'][0]['column']);
    if (isset($columnMap[$orderColumnIndex])) {
        $orderColumn = $columnMap[$orderColumnIndex];
        $requestedDir = strtolower($_POST['order'][0]['dir'] ?? 'desc');
        $orderDirection = $requestedDir === 'asc' ? 'ASC' : 'DESC';
    }
}

// Prepare the statement for the main query
if ($orderColumn === 'status') {
    $statusCase = "CASE\n        WHEN LOWER(TRIM(status)) = 'awaiting validation' THEN 0\n        WHEN LOWER(TRIM(status)) LIKE 'step 2%' THEN 1\n        ELSE 2\n    END";
    $serialOrder = "CASE WHEN serial_no REGEXP '^[0-9]+$' THEN CAST(serial_no AS UNSIGNED) ELSE 999999999 END";
    $sql .= " ORDER BY {$statusCase}, {$serialOrder} ASC, status ASC LIMIT ?, ?";
} else {
    $sql .= " ORDER BY {$orderColumn} {$orderDirection} LIMIT ?, ?";
}
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
    $location_parts = array_filter(array_map('trim', explode(',', $row['location_full'] ?? '')));
    $location_tail = array_slice($location_parts, -2);
    $location_brik = implode(', ', $location_tail);

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
        'serial_no' => safe_html($row['serial_no']), // Pass only the raw serial number
        'feature' => isset($row['feature']) ? (int)$row['feature'] : 0
    ];
}

// Get total filtered records
$totalFilteredQuery = "SELECT COUNT(*) as total FROM tb_ecobricks WHERE status != 'not ready' AND LOWER(TRIM(status)) NOT IN ('rejected', 'authenticated')";
if (!empty($ecobricker_id)) {
    $totalFilteredQuery .= " AND maker_id = ?";
}
if (!empty($searchValue)) {
    $totalFilteredQuery .= " AND (serial_no LIKE ? OR location_full LIKE ? OR ecobricker_maker LIKE ? OR community_name LIKE ?)";
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
