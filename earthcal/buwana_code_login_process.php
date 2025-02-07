<?php
require_once '../earthenAuth_helper.php';
require_once '../buwanaconn_env.php';
require_once '../calconn_env.php'; // Include EarthCal database connection

error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING); // Suppress warnings and notices
ini_set('display_errors', '0'); // Disable error display for production

$allowed_origins = [
    'https://cycles.earthen.io',
    'https://ecobricks.org',
    'https://gobrik.com',
    'http://localhost',
    'file://' // Allow local Snap apps or filesystem-based origins
];

// Normalize the HTTP_ORIGIN (remove trailing slashes or fragments)
$origin = isset($_SERVER['HTTP_ORIGIN']) ? rtrim($_SERVER['HTTP_ORIGIN'], '/') : '';

// Log the detected origin
error_log('Incoming HTTP_ORIGIN: ' . $origin);

if (empty($origin)) {
    // Allow requests with no origin for local development
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
    header('Access-Control-Allow-Credentials: true');
} elseif (in_array($origin, $allowed_origins)) {
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
// PART 1: Initialize
$response = array('status' => 'error', 'message' => 'Something went wrong');

// Helper function to log debug messages
function log_debug($message) {
    file_put_contents('debug.log', "[" . date('Y-m-d H:i:s') . "] " . $message . "\n", FILE_APPEND);
}

// Helper function to send JSON response and exit
function send_response($response) {
    header('Content-Type: application/json'); // Ensure correct content type for JSON response
    echo json_encode($response);
    exit();
}

// PART 2: Check Input Fields
if (!empty($_POST['code']) && !empty($_POST['credential_key'])) {
    $credential_key = strtolower(trim($_POST['credential_key'])); // Make it case-insensitive
    $code = strtoupper(trim($_POST['code'])); // Make it case-insensitive

    // PART 3: Database Connection Check
    if ($buwana_conn->connect_error) {
        log_debug("Connection failed: " . $buwana_conn->connect_error);
        $response['message'] = "Database connection failed: " . $buwana_conn->connect_error;
        send_response($response);
    }

    // PART 4: Validate Code
    $stmt = $buwana_conn->prepare("SELECT buwana_id FROM credentials_tb WHERE credential_key = ? AND UPPER(2fa_temp_code) = ?");
    if ($stmt) {
        $stmt->bind_param("ss", $credential_key, $code);
        if ($stmt->execute()) {
            $stmt->store_result(); // Store result to prevent "Commands out of sync" error
            $stmt->bind_result($buwana_id);
            if ($stmt->fetch()) {
                // Login success
                $_SESSION['user_logged_in'] = true;
                $_SESSION['user_id'] = $buwana_id;
                $_SESSION['buwana_id'] = $buwana_id;
                log_debug("Login successful. Session started for user ID: $buwana_id");

                // PART 5: Update Buwana Account
                // Update `last_login` and `login_count` in `users_tb`
                $sql_update_user = "UPDATE users_tb SET last_login = NOW(), login_count = login_count + 1 WHERE buwana_id = ?";
                $stmt_update_user = $buwana_conn->prepare($sql_update_user);
                if ($stmt_update_user) {
                    $stmt_update_user->bind_param('i', $buwana_id);
                    if (!$stmt_update_user->execute()) {
                        log_debug('Error executing statement for updating users_tb: ' . $stmt_update_user->error);
                    }
                    $stmt_update_user->close();
                } else {
                    log_debug('Error preparing statement for updating users_tb: ' . $buwana_conn->error);
                }

                // Update `last_login` and `times_used` in `credentials_tb`
                $sql_update_credential = "UPDATE credentials_tb SET last_login = NOW(), times_used = times_used + 1 WHERE buwana_id = ?";
                $stmt_update_credential = $buwana_conn->prepare($sql_update_credential);
                if ($stmt_update_credential) {
                    $stmt_update_credential->bind_param('i', $buwana_id);
                    if (!$stmt_update_credential->execute()) {
                        log_debug('Error executing statement for updating credentials_tb: ' . $stmt_update_credential->error);
                    }
                    $stmt_update_credential->close();
                } else {
                    log_debug('Error preparing statement for updating credentials_tb: ' . $buwana_conn->error);
                }

                // PART 6: Update GoBrik Account
                // Update `last_login` and `login_count` in `tb_ecobrickers`
                $sql_update_ecobricker = "UPDATE tb_ecobrickers SET last_login = NOW(), login_count = login_count + 1 WHERE email_addr = ?";
                $stmt_update_ecobricker = $gobrik_conn->prepare($sql_update_ecobricker);
                if ($stmt_update_ecobricker) {
                    $stmt_update_ecobricker->bind_param('s', $credential_key);
                    if (!$stmt_update_ecobricker->execute()) {
                        log_debug('Error executing statement for updating tb_ecobrickers: ' . $stmt_update_ecobricker->error);
                    }
                    $stmt_update_ecobricker->close();
                } else {
                    log_debug('Error preparing statement for updating tb_ecobrickers: ' . $gobrik_conn->error);
                }

                $response = array('status' => 'success', 'redirect' => 'dashboard.php');
            } else {
                // Invalid code
                log_debug("Invalid code for credential: $credential_key");
                $response = array('status' => 'invalid', 'message' => 'Invalid code');
            }
        } else {
            // Error during statement execution
            log_debug('SQL execution error: ' . $stmt->error);
            $response['message'] = 'SQL execution error: ' . $stmt->error;
        }
        $stmt->close();
    } else {
        // SQL preparation error
        log_debug('SQL preparation error: ' . $buwana_conn->error);
        $response['message'] = 'SQL preparation error: ' . $buwana_conn->error;
    }
    $buwana_conn->close();
    $gobrik_conn->close();
} else {
    $response['message'] = 'Required fields are missing';
}

// PART 7: Send Response
send_response($response);
?>
