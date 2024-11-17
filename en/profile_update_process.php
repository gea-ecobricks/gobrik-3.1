<?php

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../buwanaconn_env.php'; // Buwana database credentials

header('Content-Type: application/json');

// Check if the user is logged in
if (!isset($_SESSION['buwana_id'])) {
    echo json_encode(['status' => 'failed', 'message' => 'User is not logged in.']);
    exit();
}

$buwana_id = $_SESSION['buwana_id'];

// Check if all required fields are present
$required_fields = ['first_name', 'last_name', 'country_id', 'language_id', 'birth_date', 'continent_code', 'community_id', 'location_full', 'latitude', 'longitude', 'location_watershed'];
foreach ($required_fields as $field) {
    if (empty($_POST[$field])) {
        echo json_encode(['status' => 'failed', 'message' => 'Missing required field: ' . $field]);
        exit();
    }
}

// Sanitize and validate input fields
$first_name = trim($_POST['first_name']);
$last_name = trim($_POST['last_name']);
$country_id = (int)$_POST['country_id'];
$language_id = trim($_POST['language_id']);
$birth_date = $_POST['birth_date'];
$continent_code = trim($_POST['continent_code']);
$community_id = (int)$_POST['community_id'];
$location_full = trim($_POST['location_full']);
$latitude = (float)$_POST['latitude'];
$longitude = (float)$_POST['longitude'];
$location_watershed = trim($_POST['location_watershed']);

// Debugging: Log received data
error_log("Received community_id from POST: " . $_POST['community_id']);
error_log("Sanitized values: community_id=$community_id, buwana_id=$buwana_id");

// Prevent saving zero as community_id if it’s invalid
if ($community_id <= 0) {
    echo json_encode(['status' => 'failed', 'message' => 'Invalid community ID.']);
    exit();
}

// Additional check: Verify if community_id exists in communities_tb
$sql_check_community = "SELECT com_id FROM communities_tb WHERE com_id = ?";
$stmt_check = $buwana_conn->prepare($sql_check_community);
$stmt_check->bind_param("i", $community_id);
$stmt_check->execute();
$stmt_check->store_result();

if ($stmt_check->num_rows === 0) {
    echo json_encode(['status' => 'failed', 'message' => 'Invalid community ID: Not found in communities_tb.']);
    $stmt_check->close();
    exit();
}
$stmt_check->close();

// Update the user's profile in the Buwana database
$sql_update = "UPDATE users_tb
               SET first_name = ?, last_name = ?, country_id = ?, language_id = ?, birth_date = ?,
                   continent_code = ?, community_id = ?, location_full = ?,
                   location_lat = ?, location_long = ?, location_watershed = ?
               WHERE buwana_id = ?";

$stmt_update = $buwana_conn->prepare($sql_update);

if ($stmt_update) {
    $stmt_update->bind_param('ssisssisddsi',
        $first_name,
        $last_name,
        $country_id,
        $language_id,
        $birth_date,
        $continent_code,
        $community_id,
        $location_full,
        $latitude,
        $longitude,
        $location_watershed,
        $buwana_id
    );

    if ($stmt_update->execute()) {
        echo json_encode(['status' => 'succeeded']);
    } else {
        error_log("Error executing query: " . $stmt_update->error);
        echo json_encode(['status' => 'failed', 'message' => 'Failed to execute update query: ' . $stmt_update->error]);
    }
    $stmt_update->close();
} else {
    error_log("Error preparing statement: " . $buwana_conn->error);
    echo json_encode(['status' => 'failed', 'message' => 'Failed to prepare update statement: ' . $buwana_conn->error]);
}

$buwana_conn->close();
exit();

?>
