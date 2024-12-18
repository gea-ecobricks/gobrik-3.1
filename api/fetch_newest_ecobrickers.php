<?php
require_once '../gobrikconn_env.php'; // Include database connection

// Get the request parameters sent by DataTables
$draw = isset($_POST['draw']) ? intval($_POST['draw']) : 0;
$start = isset($_POST['start']) ? intval($_POST['start']) : 0;
$length = isset($_POST['length']) ? intval($_POST['length']) : 10;
$searchValue = isset($_POST['search']['value']) ? $_POST['search']['value'] : '';

// Base SQL query to fetch activated ecobrickers
$baseSql = "SELECT buwana_id, first_name, email, account_status, ecobricks_made, login_count, account_notes, location_full
            FROM tb_ecobrickers
            WHERE buwana_activated = 1";

// Count total records before filtering
$totalRecordsQuery = "SELECT COUNT(*) as total FROM tb_ecobrickers WHERE buwana_activated = 1";
$totalRecordsResult = $gobrik_conn->query($totalRecordsQuery);
$totalRecords = $totalRecordsResult->fetch_assoc()['total'] ?? 0;

// Build filtering logic
$filterSql = $baseSql;
$filterConditions = [];
$bindTypes = "";
$bindValues = [];

if (!empty($searchValue)) {
    $filterConditions[] = "(first_name LIKE ? OR email LIKE ? OR account_status LIKE ?)";
    $bindTypes .= "sss";
    $bindValues[] = "%$searchValue%";
    $bindValues[] = "%$searchValue%";
    $bindValues[] = "%$searchValue%";
}

// Append filtering conditions
if ($filterConditions) {
    $filterSql .= " AND " . implode(" AND ", $filterConditions);
}

// Add ordering and pagination
$filterSql .= " ORDER BY buwana_id DESC LIMIT ?, ?";
$bindTypes .= "ii";
$bindValues[] = $start;
$bindValues[] = $length;

// Prepare the filtered query
$stmt = $gobrik_conn->prepare($filterSql);

// Error handling for SQL preparation
if (!$stmt) {
    error_log("SQL Error: " . $gobrik_conn->error);
    echo json_encode([
        "draw" => $draw,
        "recordsTotal" => $totalRecords,
        "recordsFiltered" => 0,
        "data" => [],
        "error" => "Failed to prepare SQL statement: " . $gobrik_conn->error
    ]);
    exit;
}

// Bind parameters dynamically
if (!empty($bindValues)) {
    $stmt->bind_param($bindTypes, ...$bindValues);
}

$stmt->execute();
$stmt->bind_result($buwana_id, $first_name, $email, $account_status, $ecobricks_made, $login_count, $account_notes, $location_full);

// Fetch filtered data
$data = [];
while ($stmt->fetch()) {
    $data[] = [
        'buwana_id' => $buwana_id,
        'first_name' => htmlspecialchars($first_name), // Escape potential HTML in strings
        'email' => htmlspecialchars($email),
        'account_status' => htmlspecialchars($account_status),
        'ecobricks_made' => intval($ecobricks_made),
        'login_count' => intval($login_count),
        'account_notes' => htmlspecialchars($account_notes),
        'location_full' => htmlspecialchars($location_full)
    ];
}

// Count total filtered records
$filteredSql = "SELECT COUNT(*) as total FROM tb_ecobrickers WHERE buwana_activated = 1";
if (!empty($filterConditions)) {
    $filteredSql .= " AND " . implode(" AND ", $filterConditions);
}

$filteredStmt = $gobrik_conn->prepare($filteredSql);
if ($bindTypes && $filteredStmt) {
    $filteredStmt->bind_param(substr($bindTypes, 0, -2), ...array_slice($bindValues, 0, -2)); // Exclude pagination params
    $filteredStmt->execute();
    $filteredResult = $filteredStmt->get_result();
    $totalFilteredRecords = $filteredResult->fetch_assoc()['total'] ?? 0;
    $filteredStmt->close();
} else {
    $totalFilteredRecords = $totalRecords; // Default to total records if no filtering
}

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
