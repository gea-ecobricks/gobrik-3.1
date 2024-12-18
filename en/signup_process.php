<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

ob_start(); // Start output buffering

$response = ['success' => false];

include '../buwanaconn_env.php'; // Buwana database connection
include '../gobrikconn_env.php'; // GoBrik database connection

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $buwana_id = $_GET['id'] ?? null;  // Get the buwana_id from the URL

    // Sanitize and validate inputs
    $credential_value = filter_var($_POST['credential_value'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password_hash'];

    // Validate password length
    if (strlen($password) < 6) {
        $response['error'] = 'invalid_password';
        echo json_encode($response);
        ob_end_clean(); // Clear buffer
        exit();
    }

    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // Retrieve the Buwana first_name from the database
    $sql_get_first_name = "SELECT first_name FROM users_tb WHERE buwana_id = ?";
    $stmt_get_first_name = $buwana_conn->prepare($sql_get_first_name);
    if ($stmt_get_first_name) {
        $stmt_get_first_name->bind_param("i", $buwana_id);
        $stmt_get_first_name->execute();
        $stmt_get_first_name->bind_result($first_name);
        $stmt_get_first_name->fetch();
        $stmt_get_first_name->close();
    } else {
        $response['error'] = 'db_error';
        echo json_encode($response);
        ob_end_clean();
        exit();
    }

    // Check if the email already exists in the Buwana database
    $sql_check_email_buwana = "SELECT COUNT(*), buwana_id FROM users_tb WHERE email = ?";
    $stmt_check_email_buwana = $buwana_conn->prepare($sql_check_email_buwana);
    if ($stmt_check_email_buwana) {
        $stmt_check_email_buwana->bind_param("s", $credential_value);
        $stmt_check_email_buwana->execute();
        $stmt_check_email_buwana->bind_result($email_count_buwana, $existing_buwana_id);
        $stmt_check_email_buwana->fetch();
        $stmt_check_email_buwana->close();

        if ($email_count_buwana > 0 && $existing_buwana_id != $buwana_id) {
            // If email exists and doesn't belong to the current user, return an error
            $response['error'] = 'duplicate_email';
            echo json_encode($response);
            ob_end_clean();
            exit();
        }
    } else {
        $response['error'] = 'db_error';
        echo json_encode($response);
        ob_end_clean();
        exit();
    }

    // Check if the email already exists in the GoBrik database
    $sql_check_email_gobrik = "SELECT ecobricker_id FROM tb_ecobrickers WHERE email_addr = ?";
    $stmt_check_email_gobrik = $gobrik_conn->prepare($sql_check_email_gobrik);
    if ($stmt_check_email_gobrik) {
        $stmt_check_email_gobrik->bind_param("s", $credential_value);
        $stmt_check_email_gobrik->execute();
        $stmt_check_email_gobrik->bind_result($ecobricker_id);
        $stmt_check_email_gobrik->fetch();
        $stmt_check_email_gobrik->close();

        if ($ecobricker_id) {
            // If email exists in GoBrik, alert and redirect to activate.php
            $response['error'] = 'duplicate_gobrik_email';
            $response['redirect'] = "activate.php?id=$ecobricker_id";
            echo json_encode($response);
            ob_end_clean();
            exit();
        }
    } else {
        $response['error'] = 'db_error';
        echo json_encode($response);
        ob_end_clean();
        exit();
    }

    // Update the Buwana user (only update email and password, not first_name)
    $sql_update_user = "UPDATE users_tb SET email = ?, password_hash = ?, account_status = 'registered no login', last_login = NOW() WHERE buwana_id = ?";
    $stmt_update_user = $buwana_conn->prepare($sql_update_user);

    if ($stmt_update_user) {
        $stmt_update_user->bind_param("ssi", $credential_value, $password_hash, $buwana_id);

        if ($stmt_update_user->execute()) {
            // Now update the credentials_tb table for the user
            $sql_update_credentials = "UPDATE credentials_tb SET credential_key = ?, credential_type = 'e-mail' WHERE buwana_id = ?";
            $stmt_update_credentials = $buwana_conn->prepare($sql_update_credentials);
            if ($stmt_update_credentials) {
                $stmt_update_credentials->bind_param("si", $credential_value, $buwana_id);
                $stmt_update_credentials->execute();
                $stmt_update_credentials->close();
            } else {
                $response['error'] = 'db_error_credentials';
                echo json_encode($response);
                ob_end_clean();
                exit();
            }

            // Now create the Ecobricker account in GoBrik using the first_name and other info from Buwana
// Insert Ecobricker record into GoBrik
$sql_create_ecobricker = "INSERT INTO tb_ecobrickers
(first_name, full_name, buwana_id, email_addr, date_registered, maker_id, buwana_activated, buwana_activation_dt, account_notes)
VALUES (?, ?, ?, ?, NOW(), ?, 1, NOW(), ?)";

$stmt_create_ecobricker = $gobrik_conn->prepare($sql_create_ecobricker);

if (!$stmt_create_ecobricker) {
    handleError('db_error', $response);
}

$full_name = $first_name; // full_name is set to first_name
$account_notes = "signup_process has run";

$stmt_create_ecobricker->bind_param("ssisi", $first_name, $full_name, $buwana_id, $credential_value, $account_notes);

if (!$stmt_create_ecobricker->execute()) {
    error_log("Ecobricker creation failed: {$stmt_create_ecobricker->error}");
    handleError('db_error_ecobricker', $response);
}

$ecobricker_id = $gobrik_conn->insert_id;
$stmt_create_ecobricker->close();

// Redirect upon success
$response['success'] = true;
$response['redirect'] = "confirm-email.php?id=" . urlencode($ecobricker_id);

    } else {
        $response['error'] = 'db_error_ecobricker';
    }
    $stmt_create_ecobricker->close();
} else {
    $response['error'] = 'db_error';
}


            $stmt_update_user->close();
        } else {
            $response['error'] = 'db_error';
        }
    } else {
        $response['error'] = 'db_error';
    }

    // Close database connections
    $buwana_conn->close();
    $gobrik_conn->close();
} else {
    $response['error'] = 'invalid_request';
}

ob_end_clean(); // Clear any previous output

// Return the JSON response
echo json_encode($response);
exit();
?>
