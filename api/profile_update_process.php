<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../buwanaconn_env.php'; // Buwana database credentials

// Ensure the response is JSON
header('Content-Type: application/json');

// Check if the request is an AJAX request
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || $_SERVER['HTTP_X_REQUESTED_WITH'] !== 'XMLHttpRequest') {
    echo json_encode(['status' => 'failed', 'message' => 'Unauthorized access.']);
    exit();
}

// Check if the user is logged in
if (!isset($_SESSION['buwana_id'])) {
    echo json_encode(['status' => 'failed', 'message' => 'User is not logged in.']);
    exit();
}

$buwana_id = $_SESSION['buwana_id'];

// Define required fields
$required_fields = ['first_name', 'last_name', 'country_id', 'language_id', 'birth_date', 'continent_code', 'community_id', 'location_full', 'latitude', 'longitude', 'location_watershed'];

// Check if all required fields are present and not empty
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

// Debugging: Log the sanitized data
error_log("Updating user: buwana_id=$buwana_id, first_name=$first_name, last_name=$last_name, country_id=$country_id, language_id=$language_id, birth_date=$birth_date, continent_code=$continent_code, community_id=$community_id, location_full=$location_full, latitude=$latitude, longitude=$longitude, location_watershed=$location_watershed");

// Update the user's profile in the Buwana database
$sql_update = "UPDATE users_tb
               SET first_name = ?, last_name = ?, country_id = ?, language_id = ?, birth_date = ?,
                   continent_code = ?, community_id = ?, location_full = ?,
                   location_lat = ?, location_long = ?, location_watershed = ?
               WHERE buwana_id = ?";

$stmt_update = $buwana_conn->prepare($sql_update);

if ($stmt_update) {
    // Bind parameters with correct data types
    $stmt_update->bind_param('ssisssisddsi',
        $first_name,      // string
        $last_name,       // string
        $country_id,      // integer
        $language_id,     // string
        $birth_date,      // string
        $continent_code,  // string
        $community_id,    // integer
        $location_full,   // string
        $latitude,        // decimal (float)
        $longitude,       // decimal (float)
        $location_watershed,  // string
        $buwana_id        // integer
    );

    // Execute the statement
    if ($stmt_update->execute()) {
        echo json_encode(['status' => 'succeeded']);
    } else {
        // Log any error that occurred during execution
        error_log("Error executing query: " . $stmt_update->error);
        echo json_encode(['status' => 'failed', 'message' => 'Failed to execute update query: ' . $stmt_update->error]);
    }
    $stmt_update->close();
} else {
    // Log any error that occurred during preparation
    error_log("Error preparing statement: " . $buwana_conn->error);
    echo json_encode(['status' => 'failed', 'message' => 'Failed to prepare update statement: ' . $buwana_conn->error]);
}

// Close the database connection
$buwana_conn->close();
exit();
?>
