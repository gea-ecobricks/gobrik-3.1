<?php
require_once '../earthenAuth_helper.php';
require_once '../buwanaconn_env.php';

$allowed_origins = [
    'https://cycles.earthen.io',
    'https://ecobricks.org',
    'https://gobrik.com',
    'http://localhost:8000',   // Localhost
    'http://0.0.0.0:8000'      // Local test server
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

startSecureSession();

// PART 1: Grab user credentials from the POST request
$credential_key = $_POST['credential_key'] ?? '';
$password = $_POST['password'] ?? '';

// Input validation
if (empty($credential_key) || empty($password)) {
    echo json_encode([
        'success' => false,
        'message' => 'Credential key or password is missing.'
    ]);
    exit();
}

try {
    // PART 2: Check Buwana Database
    $sql_credential = "SELECT buwana_id FROM credentials_tb WHERE credential_key = ?";
    $stmt_credential = $buwana_conn->prepare($sql_credential);

    if (!$stmt_credential) {
        throw new Exception('Error preparing statement for credentials_tb: ' . $buwana_conn->error);
    }

    $stmt_credential->bind_param('s', $credential_key);
    $stmt_credential->execute();
    $stmt_credential->store_result();

    if ($stmt_credential->num_rows === 1) {
        $stmt_credential->bind_result($buwana_id);
        $stmt_credential->fetch();
        $stmt_credential->close();

        // Part 3: Retrieve user details: first_name, continent_code, location_full, and connected_apps
$sql_user = "SELECT password_hash, first_name, continent_code, location_full, connected_app_ids FROM users_tb WHERE buwana_id = ?";
$stmt_user = $buwana_conn->prepare($sql_user);

if (!$stmt_user) {
    throw new Exception('Error preparing statement for users_tb: ' . $buwana_conn->error);
}

$stmt_user->bind_param('i', $buwana_id);
$stmt_user->execute();
$stmt_user->store_result();

if ($stmt_user->num_rows === 1) {
    $stmt_user->bind_result($password_hash, $first_name, $continent_code, $location_full, $connected_app_ids);
    $stmt_user->fetch();

    // Verify the password entered by the user
    if (password_verify($password, $password_hash)) {
        // PART 4: Update last_login and login_count
        $sql_update_user = "UPDATE users_tb SET last_login = NOW(), login_count = login_count + 1 WHERE buwana_id = ?";
        $stmt_update_user = $buwana_conn->prepare($sql_update_user);
        if ($stmt_update_user) {
            $stmt_update_user->bind_param('i', $buwana_id);
            $stmt_update_user->execute();
            $stmt_update_user->close();
        }

        // PART 4: Respond with success and user details, including connected_apps
        echo json_encode([
            'success' => true,
            'message' => 'Login successful',
            'buwana_id' => $buwana_id,
            'first_name' => $first_name,
            'continent_code' => $continent_code,
            'location_full' => $location_full,
            'connected_apps' => $connected_app_ids // Include connected_apps in the response
        ]);
        exit();
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid password'
        ]);
        exit();
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid user'
    ]);
    exit();
}

    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid credential key'
        ]);
        exit();
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred: ' . $e->getMessage()
    ]);
    exit();
} finally {
    $buwana_conn->close();
}
?>