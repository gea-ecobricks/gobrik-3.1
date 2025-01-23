<?php
require_once '../gobrikconn_env.php'; // Include database connection

// Get the request parameters sent by DataTables
$draw = isset($_POST['draw']) ? intval($_POST['draw']) : 0;
$start = isset($_POST['start']) ? intval($_POST['start']) : 0;
$length = isset($_POST['length']) ? intval($_POST['length']) : 10;
$searchValue = isset($_POST['search']['value']) ? $_POST['search']['value'] : '';

// Base SQL query to fetch unactivated ecobrickers
$sql = "SELECT
            ecobricker_id,
            buwana_id,
            email_addr,
            account_notes,
            first_name,
            full_name,
            user_roles,
            ecobricks_made,
            login_count,
            emailing_status,
            location_full
        FROM tb_ecobrickers
        WHERE ecobricker_id > 3
          AND (buwana_id IS NULL OR buwana_id = 150)
          AND emailing_status IN ('unsent', 'resend')
          AND buwana_activated = 0";

// Add search filter if any
if (!empty($searchValue)) {
    $sql .= " AND (full_name LIKE ? OR email_addr LIKE ? OR user_roles LIKE ? OR emailing_status LIKE ?)";
    $bindTypes = "ssss";
    $bindValues = array_fill(0, 4, "%$searchValue%");
} else {
    $bindTypes = "";
    $bindValues = [];
}

// Add ORDER BY and LIMIT for pagination
$sql .= " ORDER BY ecobricker_id ASC LIMIT ?, ?";
$bindTypes .= "ii";
$bindValues[] = $start;
$bindValues[] = $length;

// Count total records before filtering
$totalRecordsQuery = "SELECT COUNT(*) as total FROM tb_ecobrickers WHERE ecobricker_id > 3 AND (buwana_id IS NULL OR buwana_id = 150) AND emailing_status IN ('unsent', 'resend') AND buwana_activated = 0";
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

// Bind parameters dynamically if search is applied
if (!empty($searchValue)) {
    $stmt->bind_param($bindTypes, ...$bindValues);
} else {
    $stmt->bind_param("ii", $start, $length);
}

$stmt->execute();

// Bind the results
$stmt->bind_result(
    $ecobricker_id,
    $buwana_id,
    $email_addr,
    $account_notes,
    $first_name,
    $full_name,
    $user_roles,
    $ecobricks_made,
    $login_count,
    $emailing_status,
    $location_full
);

$data = [];
while ($stmt->fetch()) {
    $data[] = [
        'ecobricker_id' => htmlspecialchars($ecobricker_id ?? '', ENT_QUOTES, 'UTF-8'),
        'email_addr' => htmlspecialchars($email_addr ?? '', ENT_QUOTES, 'UTF-8'),
        'account_notes' => htmlspecialchars($account_notes ?? '', ENT_QUOTES, 'UTF-8'),
        'first_name' => htmlspecialchars($first_name ?? '', ENT_QUOTES, 'UTF-8'),
        'full_name' => htmlspecialchars($full_name ?? '', ENT_QUOTES, 'UTF-8'),
        'user_roles' => htmlspecialchars($user_roles ?? '', ENT_QUOTES, 'UTF-8'),
        'ecobricks_made' => intval($ecobricks_made ?? 0),
        'login_count' => intval($login_count ?? 0),
        'emailing_status' => htmlspecialchars($emailing_status ?? '', ENT_QUOTES, 'UTF-8'),
        'location_full' => htmlspecialchars($location_full ?? '', ENT_QUOTES, 'UTF-8')
    ];
}

// Get total filtered records
$filteredSql = "SELECT COUNT(*) as total FROM tb_ecobrickers WHERE ecobricker_id > 3 AND (buwana_id IS NULL OR buwana_id = 150) AND emailing_status IN ('unsent', 'resend') AND buwana_activated = 0";
if (!empty($searchValue)) {
    $filteredSql .= " AND (full_name LIKE '%$searchValue%' OR email_addr LIKE '%$searchValue%' OR user_roles LIKE '%$searchValue%' OR emailing_status LIKE '%$searchValue%')";
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
