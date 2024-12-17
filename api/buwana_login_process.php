<?php
require_once '../earthenAuth_helper.php'; // Include the authentication helper functions
require_once '../buwanaconn_env.php';   // Database connection

$allowed_origins = [
    'https://cycles.earthen.io',
    'https://ecobricks.org',
    'http://localhost:8000' // Added for local testing
];

// CORS headers
if (isset($_SERVER['HTTP_ORIGIN']) && in_array($_SERVER['HTTP_ORIGIN'], $allowed_origins)) {
    header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
    header('Access-Control-Allow-Methods: POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
    header('Access-Control-Allow-Credentials: true');
}

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
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

        // Retrieve user details: first_name, continent_code, location_full
        $sql_user = "SELECT password_hash, first_name, continent_code, location_full FROM users_tb WHERE buwana_id = ?";
        $stmt_user = $buwana_conn->prepare($sql_user);

        if (!$stmt_user) {
            throw new Exception('Error preparing statement for users_tb: ' . $buwana_conn->error);
        }

        $stmt_user->bind_param('i', $buwana_id);
        $stmt_user->execute();
        $stmt_user->store_result();

        if ($stmt_user->num_rows === 1) {
            $stmt_user->bind_result($password_hash, $first_name, $continent_code, $location_full);
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

                // Respond with success and user details
                echo json_encode([
                    'success' => true,
                    'message' => 'Login successful',
                    'buwana_id' => $buwana_id,
                    'first_name' => $first_name,
                    'continent_code' => $continent_code,
                    'location_full' => $location_full
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
