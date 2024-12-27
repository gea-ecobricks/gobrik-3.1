<?php
require_once '../earthenAuth_helper.php';
require_once '../buwanaconn_env.php';
require_once '../calconn_env.php'; // Include EarthCal database connection

$allowed_origins = [
    'https://cycles.earthen.io',
    'https://ecobricks.org',
    'https://gobrik.com',
    'http://localhost', // Allow localhost
    'http://127.0.0.1', // Allow loopback address
    'http://localhost:8000', // Allow specific localhost ports (adjust as needed)
    'http://127.0.0.1:8000'
];

// Normalize the HTTP_ORIGIN (remove trailing slashes or fragments)
$origin = isset($_SERVER['HTTP_ORIGIN']) ? rtrim($_SERVER['HTTP_ORIGIN'], '/') : '';

if ($origin && in_array($origin, $allowed_origins)) {
    header('Access-Control-Allow-Origin: ' . $origin);
    header('Access-Control-Allow-Methods: POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
    header('Access-Control-Allow-Credentials: true');
} else {
    error_log('CORS error: Invalid or missing HTTP_ORIGIN - ' . $origin);
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['success' => false, 'message' => 'CORS error: Invalid origin']);
    exit();
}

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Methods: POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
    header('Access-Control-Allow-Credentials: true');
    exit(0);
}

$response = ['success' => false];

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    $response['message'] = 'Invalid request method.';
    echo json_encode($response);
    exit();
}

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

    $stmt_insert_earthcal->bind_param(
        "isssssisiisssddi",
        $user_data['buwana_id'], $user_data['first_name'], $user_data['last_name'],
        $user_data['full_name'], $user_data['email'], $user_data['profile_pic'],
        $user_data['country_id'], $user_data['language_id'], $user_data['earthen_newsletter_join'],
        $user_data['birth_date'], $user_data['continent_code'], $user_data['location_full'],
        $user_data['location_watershed'], $user_data['location_lat'], $user_data['location_long'],
        $user_data['community_id']
    );

    if (!$stmt_insert_earthcal->execute()) {
        throw new Exception("Error inserting user into EarthCal database: " . $stmt_insert_earthcal->error);
    }
    $stmt_insert_earthcal->close();

    // Step 3: Create default calendar for the user
    $sql_create_calendar = "INSERT INTO calendars_tb (
        buwana_id, calendar_name, calendar_created, last_updated, calendar_color, calendar_public
    ) VALUES (
        ?, 'My Calendar', NOW(), NOW(), 'blue', 0
    )";
    $stmt_create_calendar = $cal_conn->prepare($sql_create_calendar);

    if (!$stmt_create_calendar) {
        throw new Exception("Error preparing calendar creation statement: " . $cal_conn->error);
    }

    $stmt_create_calendar->bind_param("i", $buwana_id);

    if (!$stmt_create_calendar->execute()) {
        throw new Exception("Error creating default calendar for user: " . $stmt_create_calendar->error);
    }
    $stmt_create_calendar->close();

    // Step 4: Update connected_app_ids in Buwana database
    $sql_update_buwana = "UPDATE users_tb SET connected_app_ids =
        CASE
            WHEN connected_app_ids IS NULL OR connected_app_ids = '' THEN '00002'
            ELSE CONCAT(connected_app_ids, ',00002')
        END
        WHERE buwana_id = ?";
    $stmt_update_buwana = $buwana_conn->prepare($sql_update_buwana);
    $stmt_update_buwana->bind_param("i", $buwana_id);

    if (!$stmt_update_buwana->execute()) {
        throw new Exception("Error updating connected_app_ids in Buwana database: " . $stmt_update_buwana->error);
    }
    $stmt_update_buwana->close();

    // Part 5: Success response with user data
    $response['success'] = true;
    $response['message'] = 'EarthCal account activated successfully.';
    $response['user_data'] = [
        'first_name' => $user_data['first_name'],
        'continent_code' => $user_data['continent_code'],
        'location_full' => $user_data['location_full']
    ];
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
} finally {
    $buwana_conn->close();
    $cal_conn->close();
}

echo json_encode($response);
exit();
?>