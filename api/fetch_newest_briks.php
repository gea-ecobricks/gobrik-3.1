<?php
require_once '../gobrikconn_env.php'; // Include database connection

// Get the request parameters sent by DataTables
$draw = isset($_POST['draw']) ? intval($_POST['draw']) : 0;
$start = isset($_POST['start']) ? intval($_POST['start']) : 0;
$length = isset($_POST['length']) ? intval($_POST['length']) : 5;
$ecobricker_id = isset($_POST['ecobricker_id']) ? $_POST['ecobricker_id'] : ''; // Get the ecobricker_id from the request

// Search term (if any)
$searchValue = isset($_POST['searchValue']) ? $_POST['searchValue'] : '';

// Prepare the base SQL query, including the community_name field
$sql = "SELECT ecobrick_thumb_photo_url, ecobrick_full_photo_url, weight_g, volume_ml, density, date_logged_ts,
        location_full, location_watershed, ecobricker_maker, community_name, serial_no, status, photo_version
        FROM tb_ecobricks
        WHERE status != 'not ready'";

// If ecobricker_id is provided, use it to filter by maker_id in the SQL query
$bindTypes = "";
$bindValues = [];

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
$totalRecordsQuery = "SELECT COUNT(*) as total FROM tb_ecobricks WHERE status != 'not ready'";
if (!empty($ecobricker_id)) {
    $totalRecordsQuery .= " AND maker_id = '$ecobricker_id'";
}
$totalRecordsResult = $gobrik_conn->query($totalRecordsQuery);
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

// Bind the results to variables
$stmt->bind_result(
    $ecobrick_thumb_photo_url,
    $ecobrick_full_photo_url,
    $weight_g,
    $volume_ml,
    $density,
    $date_logged_ts,
    $location_full,
    $location_watershed,
    $ecobricker_maker,
    $community_name,
    $serial_no,
    $status,
    $photo_version
);

$data = [];
while ($stmt->fetch()) {
    // Process the location into $location_brik
    $location_parts = explode(',', $location_full);
    $location_parts = array_map('trim', $location_parts);

    $location_last = $location_parts[count($location_parts) - 1] ?? '';
    $location_third_last = $location_parts[count($location_parts) - 3] ?? '';
    $location_brik = $location_third_last . ', ' . $location_last;

    if (!empty($location_watershed)) {
        $location_brik = $location_watershed . ', ' . $location_brik;
    }

    $serial_url = "brik.php?serial_no=" . urlencode($serial_no);

    $data[] = [
        'ecobrick_thumb_photo_url' => '<img src="' . htmlspecialchars($ecobrick_thumb_photo_url) . '?v=' . htmlspecialchars($photo_version) . '"
            alt="' . htmlspecialchars($serial_no) . '"
            title="Ecobrick ' . htmlspecialchars($serial_no) . ' Image version ' . htmlspecialchars($photo_version) . '"
            class="table-thumbnail"
            onclick="ecobrickPreview(\'' . htmlspecialchars($ecobrick_full_photo_url) . '?v=' . htmlspecialchars($photo_version) . '\', \'' . htmlspecialchars($serial_no) . '\', \'' . htmlspecialchars($weight_g) . ' g\', \'' . htmlspecialchars($ecobricker_maker) . '\', \'' . htmlspecialchars($location_brik) . '\')">',
        'weight_g' => number_format($weight_g) . ' g',
        'volume_ml' => number_format($volume_ml) . ' ml',
        'density' => number_format($density, 2) . ' g/ml',
        'date_logged_ts' => date("Y-m-d", strtotime($date_logged_ts)),
        'location_brik' => htmlspecialchars($location_brik),
        'ecobricker_maker' => htmlspecialchars($ecobricker_maker),
        'community_name' => htmlspecialchars($community_name),
        'status' => htmlspecialchars($status),
        'serial_no' => htmlspecialchars($serial_no) // Pass only the raw serial number
    ];
}

// Get total filtered records
$filteredSql = "SELECT COUNT(*) as total FROM tb_ecobricks WHERE status != 'not ready'";
if (!empty($ecobricker_id)) {
    $filteredSql .= " AND maker_id = '$ecobricker_id'";
}
if (!empty($searchValue)) {
    $filteredSql .= " AND (serial_no LIKE '%$searchValue%' OR location_full LIKE '%$searchValue%' OR ecobricker_maker LIKE '%$searchValue%' OR community_name LIKE '%$searchValue%')";
}
$filteredResult = $gobrik_conn->query($filteredSql);
$totalFilteredRecords = $filteredResult->fetch_assoc()['total'] ?? 0;

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
echo json_encode($response);
?>
