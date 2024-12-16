<?php
require_once '../earthenAuth_helper.php'; // Include the authentication helper functions
require_once '../buwanaconn_env.php';   // Database connection

header('Content-Type: application/json'); // Set JSON response
header('Access-Control-Allow-Origin: https://cycles.earthen.io'); // Allow requests from Earthcal
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Start a secure session
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
    // SQL query to get buwana_id from credentials_tb using credential_key
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

        // SQL query to get password_hash from users_tb using buwana_id
        $sql_user = "SELECT password_hash FROM users_tb WHERE buwana_id = ?";
        $stmt_user = $buwana_conn->prepare($sql_user);

        if (!$stmt_user) {
            throw new Exception('Error preparing statement for users_tb: ' . $buwana_conn->error);
        }

        $stmt_user->bind_param('i', $buwana_id);
        $stmt_user->execute();
        $stmt_user->store_result();

        if ($stmt_user->num_rows === 1) {
            $stmt_user->bind_result($password_hash);
            $stmt_user->fetch();

            // Verify the password entered by the user
            if (password_verify($password, $password_hash)) {
                // PART 4: Update Buwana Account
                $sql_update_user = "UPDATE users_tb SET last_login = NOW(), login_count = login_count + 1 WHERE buwana_id = ?";
                $stmt_update_user = $buwana_conn->prepare($sql_update_user);

                if ($stmt_update_user) {
                    $stmt_update_user->bind_param('i', $buwana_id);
                    $stmt_update_user->execute();
                    $stmt_update_user->close();
                }

                $sql_update_credential = "UPDATE credentials_tb SET last_login = NOW(), times_used = times_used + 1 WHERE credential_key = ?";
                $stmt_update_credential = $buwana_conn->prepare($sql_update_credential);

                if ($stmt_update_credential) {
                    $stmt_update_credential->bind_param('s', $credential_key);
                    $stmt_update_credential->execute();
                    $stmt_update_credential->close();
                }

                // Set session variable
                $_SESSION['buwana_id'] = $buwana_id;

                // Respond with success
                echo json_encode([
                    'success' => true,
                    'message' => 'Login successful',
                    'buwana_id' => $buwana_id
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
