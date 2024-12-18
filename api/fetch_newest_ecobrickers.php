<?php
require_once '../gobrikconn_env.php'; // Include database connection

// Get the request parameters sent by DataTables
$draw = isset($_POST['draw']) ? intval($_POST['draw']) : 0;
$start = isset($_POST['start']) ? intval($_POST['start']) : 0;
$length = isset($_POST['length']) ? intval($_POST['length']) : 10;
$searchValue = isset($_POST['search']['value']) ? $_POST['search']['value'] : '';

// Base SQL query to fetch activated ecobrickers
$sql = "SELECT buwana_id, first_name, email, account_status, ecobricks_made, login_count, account_notes, location_full
        FROM tb_ecobrickers
        WHERE buwana_activated = 1";

// Add search filter if any
if (!empty($searchValue)) {
    $sql .= " AND (first_name LIKE ? OR email LIKE ? OR account_status LIKE ?)";
    $bindTypes = "sss";
    $bindValues = array_fill(0, 3, "%$searchValue%");
} else {
    $bindTypes = "";
    $bindValues = [];
}

// Order and limit for pagination
$sql .= " ORDER BY buwana_id DESC LIMIT ?, ?";
$bindTypes .= "ii";
$bindValues[] = $start;
$bindValues[] = $length;

// Count total records before filtering
$totalRecordsQuery = "SELECT COUNT(*) as total FROM tb_ecobrickers WHERE buwana_activated = 1";
$totalRecordsResult = $gobrik_conn->query($totalRecordsQuery);
$totalRecords = $totalRecordsResult->fetch_assoc()['total'] ?? 0;

// Prepare the statement for the main query
$stmt = $gobrik_conn->prepare($sql);

// Error handling for SQL preparation
if (!$stmt) {
    error_log("SQL Error: " . $gobrik_conn->error);
    echo json_encode([
        "draw" => $draw,
        "recordsTotal" => 0,
        "recordsFiltered" => 0,
        "data" => [],
        "error" => "Failed to prepare SQL statement: " . $gobrik_conn->error
    ]);
    exit;
}

// Bind parameters dynamically if any
if (!empty($searchValue)) {
    $stmt->bind_param($bindTypes, ...$bindValues);
} else {
    $stmt->bind_param("ii", $start, $length);
}

$stmt->execute();
$stmt->bind_result($buwana_id, $first_name, $email, $account_status, $ecobricks_made, $login_count, $account_notes, $location_full);

$data = [];
while ($stmt->fetch()) {
    $data[] = [
        'buwana_id' => htmlspecialchars($buwana_id),
        'first_name' => htmlspecialchars($first_name),
        'email' => htmlspecialchars($email),
        'account_status' => htmlspecialchars($account_status),
        'ecobricks_made' => intval($ecobricks_made),
        'login_count' => intval($login_count),
        'account_notes' => htmlspecialchars($account_notes),
        'location_full' => htmlspecialchars($location_full)
    ];
}

// Get total filtered records
$filteredSql = "SELECT COUNT(*) as total FROM tb_ecobrickers WHERE buwana_activated = 1";
if (!empty($searchValue)) {
    $filteredSql .= " AND (first_name LIKE '%$searchValue%' OR email LIKE '%$searchValue%' OR account_status LIKE '%$searchValue%')";
}
$filteredResult = $gobrik_conn->query($filteredSql);
$totalFilteredRecords = $filteredResult->fetch_assoc()['total'] ?? 0;

// Prepare JSON response
$response = [
    "draw" => $draw,
    "recordsTotal" => $totalRecords,
    "recordsFiltered" => $totalFilteredRecords,
    "data" => $data
];

// Close database connection
$gobrik_conn->close();

// Send response in JSON format
echo json_encode($response);
?>
