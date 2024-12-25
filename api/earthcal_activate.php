<?php
header('Content-Type: application/json');

// Load database connection files
require_once '../buwanaconn_env.php'; // Buwana database connection
require_once '../calconn_env.php';   // EarthCal database connection


$allowed_origins = [
    'https://cycles.earthen.io',
    'https://ecobricks.org',
    'https://gobrik.com',
];

// PArt 0: Normalize the HTTP_ORIGIN (remove trailing slashes or fragments)
$origin = isset($_SERVER['HTTP_ORIGIN']) ? rtrim($_SERVER['HTTP_ORIGIN'], '/') : '';

// Check if the origin is in the allowed list
if ($origin && in_array($origin, $allowed_origins)) {
    header('Access-Control-Allow-Origin: ' . $origin);
    header('Access-Control-Allow-Methods: POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
    header('Access-Control-Allow-Credentials: true');
} else {
    error_log('CORS error: Invalid or missing HTTP_ORIGIN - ' . $origin);
    // Do not send the headers for invalid origins
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['success' => false, 'message' => 'CORS error: Invalid origin']);
    exit();
}

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Methods: POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
    header('Access-Control-Allow-Credentials: true');
    exit(0); // Stop execution for preflight
}

$response = ['success' => false];

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    $response['message'] = 'Invalid request method.';
    echo json_encode($response);
    exit();
}

// Get the Buwana ID from the request
$input = json_decode(file_get_contents('php://input'), true);
$buwana_id = $input['buwana_id'] ?? null;

if (!$buwana_id || !is_numeric($buwana_id)) {
    $response['message'] = 'Invalid Buwana ID.';
    echo json_encode($response);
    exit();
}

try {
    // Step 1: Retrieve user data from Buwana database
    $sql_get_user = "SELECT buwana_id, first_name, last_name, full_name, email, profile_pic, country_id,
        language_id, earthen_newsletter_join, birth_date, continent_code, location_full,
        location_watershed, location_lat, location_long, community_id
        FROM users_tb WHERE buwana_id = ?";
    $stmt_get_user = $buwana_conn->prepare($sql_get_user);
    $stmt_get_user->bind_param("i", $buwana_id);
    $stmt_get_user->execute();
    $result = $stmt_get_user->get_result();

    if ($result->num_rows !== 1) {
        throw new Exception("User not found in Buwana database.");
    }

    $user_data = $result->fetch_assoc();
    $stmt_get_user->close();

   // Step 2: Insert user data into EarthCal database
$sql_insert_earthcal = "INSERT INTO users_tb (
    buwana_id, first_name, last_name, full_name, email, profile_pic, country_id, language_id,
    earthen_newsletter_join, birth_date, continent_code, location_full, location_watershed,
    location_lat, location_long, community_id, account_status, created_at, last_login, role,
    terms_of_service, notes, login_count
) VALUES (
    ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active', NOW(), NOW(), 'user', 1,
    'account activate, no sync', 1
)";
$stmt_insert_earthcal = $cal_conn->prepare($sql_insert_earthcal);

if (!$stmt_insert_earthcal) {
    throw new Exception("Error preparing EarthCal insert statement: " . $cal_conn->error);
}

// Bind the parameters for the fields that need to be passed
$stmt_insert_earthcal->bind_param(
    "isssssisiisssddi",
    $user_data['buwana_id'],                 // INT
    $user_data['first_name'],               // VARCHAR
    $user_data['last_name'],                // VARCHAR
    $user_data['full_name'],                // VARCHAR
    $user_data['email'],                    // VARCHAR
    $user_data['profile_pic'],              // VARCHAR
    $user_data['country_id'],               // INT (nullable)
    $user_data['language_id'],              // VARCHAR
    $user_data['earthen_newsletter_join'],  // TINYINT
    $user_data['birth_date'],               // DATE
    $user_data['continent_code'],           // VARCHAR
    $user_data['location_full'],            // VARCHAR
    $user_data['location_watershed'],       // VARCHAR
    $user_data['location_lat'],             // DECIMAL
    $user_data['location_long'],            // DECIMAL
    $user_data['community_id']              // INT (nullable)
);



    if (!$stmt_insert_earthcal->execute()) {
        throw new Exception("Error inserting user into EarthCal database: " . $stmt_insert_earthcal->error);
    }
    $stmt_insert_earthcal->close();

    // Step 3: Update connected_app_ids in Buwana database
    $sql_update_buwana = "UPDATE users_tb SET connected_app_ids =
        CASE
            WHEN connected_app_ids IS NULL OR connected_app_ids = '' THEN '0002'
            ELSE CONCAT(connected_app_ids, ',0002')
        END
        WHERE buwana_id = ?";
    $stmt_update_buwana = $buwana_conn->prepare($sql_update_buwana);
    $stmt_update_buwana->bind_param("i", $buwana_id);

    if (!$stmt_update_buwana->execute()) {
        throw new Exception("Error updating connected_app_ids in Buwana database: " . $stmt_update_buwana->error);
    }
    $stmt_update_buwana->close();

    // Success response
    $response['success'] = true;
    $response['message'] = 'EarthCal account activated successfully.';
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
} finally {
    $buwana_conn->close();
    $cal_conn->close();
}

echo json_encode($response);
exit();
?>
