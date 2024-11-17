<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

$directory = basename(dirname($_SERVER['SCRIPT_NAME']));
$lang = $directory;

include '../buwanaconn_env.php'; // This file provides the first database server, user, dbname information

// Retrieve form data
$buwana_id = filter_input(INPUT_POST, 'buwana_id', FILTER_SANITIZE_NUMBER_INT);
$credential_value = filter_input(INPUT_POST, 'credential_value', FILTER_SANITIZE_EMAIL);
$password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_FULL_SPECIAL_CHARS);


if (!$buwana_id || !$credential_value || !$password) {
    header("Location: signedup-login.php?id=$buwana_id&error=invalid_input");
    exit();
}

// Prepare and execute the query into the Buwana Database
$sql_lookup_password = "SELECT password_hash, first_name FROM users_tb WHERE buwana_id = ?";
$stmt_lookup_password = $buwana_conn->prepare($sql_lookup_password);

if ($stmt_lookup_password) {
    $stmt_lookup_password->bind_param("i", $buwana_id);
    $stmt_lookup_password->execute();
    $stmt_lookup_password->bind_result($stored_password_hash, $first_name);
    $stmt_lookup_password->fetch();
    $stmt_lookup_password->close();

    // Verify the entered password - UPDATE to $Lang
    if (password_verify($password, $stored_password_hash)) {
        // Password is correct, update buwana user account with core user data and login session info
        $sql_update_user = "UPDATE users_tb SET
                            account_status = 'registration login complete',
                            created_at = NOW(),
                            last_login = NOW(),
                            languages_id = ?,
                            login_count = login_count + 1
                            WHERE buwana_id = ?";
        $stmt_update_user = $buwana_conn->prepare($sql_update_user);
        if ($stmt_update_user) {
            $stmt_update_user->bind_param("si", $lang, $buwana_id);
            $stmt_update_user->execute();
            $stmt_update_user->close();
            $buwana_conn->close(); // Close the first database connection

            // Connect to the GoBrik database to see if the ecobricker is already there or create new ecobricker
            require_once ("../gobrikconn_env.php");



            // Check for existing ecobricker by email
            $sql_check_email = "SELECT ecobricker_id FROM tb_ecobrickers WHERE email_addr = ?";
            $stmt_check_email = $gobrik_conn->prepare($sql_check_email);
            if ($stmt_check_email) {
                $stmt_check_email->bind_param("s", $credential_value);
                $stmt_check_email->execute();
                $stmt_check_email->store_result();

                if ($stmt_check_email->num_rows > 0) {
                    // If the ecobricker exists update existing ecobricker: this happens off an activation
                    $sql_update_ecobricker = "UPDATE tb_ecobrickers SET
                                                first_name = ?,
                                                buwana_id = ?,
                                                buwana_activated = 1,
                                                buwana_activation_dt = NOW(),
                                                language_pref = ?
                                              WHERE email_addr = ?";
                    $stmt_update_ecobricker = $gobrik_conn->prepare($sql_update_ecobricker);
                    if ($stmt_update_ecobricker) {
                        $stmt_update_ecobricker->bind_param("siss", $first_name, $buwana_id, $lang, $credential_value);
                        if ($stmt_update_ecobricker->execute()) {
                            error_log("Updated existing ecobricker in tb_ecobrickers: $first_name, $buwana_id");
                        } else {
                            error_log("Error executing update statement in ecobricks_gobrik_msql_db: " . $stmt_update_ecobricker->error);
                            header("Location: signedup-login.php?id=$buwana_id&error=db_update_failed");
                            exit();
                        }
                        $stmt_update_ecobricker->close();
                    } else {
                        error_log("Error preparing update statement in ecobricks_gobrik_msql_db: " . $gobrik_conn->error);
                        header("Location: signedup-login.php?id=$buwana_id&gobrikdberror=db_error");
                        exit();
                    }
                } else {
                    // If the ecobricker doesn't exist in GoBrik then create a new one:  this happens off a fresh signup
                    $sql_insert_ecobricker = "INSERT INTO tb_ecobrickers (first_name, buwana_id, email_addr, date_registered, maker_id, buwana_activated, buwana_activation_dt, language_pref)
                                              VALUES (?, ?, ?, NOW(), ?, 1, NOW(), ?)";
                    $stmt_insert_ecobricker = $gobrik_conn->prepare($sql_insert_ecobricker);
                    if ($stmt_insert_ecobricker) {

                            $stmt_insert_ecobricker->bind_param("sisis", $first_name, $buwana_id, $credential_value, $buwana_id, $lang);
                        if ($stmt_insert_ecobricker->execute()) {
                            error_log("New user inserted into tb_ecobrickers: $first_name, $buwana_id");
                        } else {
                            error_log("Error executing insert statement in ecobricks_gobrik_msql_db: " . $stmt_insert_ecobricker->error);
                            header("Location: signedup-login.php?id=$buwana_id&error=db_insert_failed");
                            exit();
                        }
                        $stmt_insert_ecobricker->close();
                    } else {
                        error_log("Error preparing insert statement in ecobricks_gobrik_msql_db: " . $gobrik_conn->error);
                        header("Location: signedup-login.php?id=$buwana_id&gobrikdberror=db_error");
                        exit();
                    }
                }

                $stmt_check_email->close();
            } else {
                error_log("Error preparing select statement in ecobricks_gobrik_msql_db: " . $gobrik_conn->error);
                header("Location: signedup-login.php?id=$buwana_id&gobrikdberror=db_error");
                exit();
            }

            $gobrik_conn->close(); // Close the second database connection

            // Redirect to dashboard
            $_SESSION['buwana_id'] = $buwana_id;
            header("Location: dashboard.php");
            exit();
        } else {
            error_log("Error updating user data: " . $buwana_conn->error);
            header("Location: signedup-login.php?id=$buwana_id&error=db_error");
            exit();
        }
    } else {
        header("Location: signedup-login.php?id=$buwana_id&error=wrong_password");
        exit();
    }
} else {
    error_log("Error preparing statement for users_tb: " . $buwana_conn->error);
    header("Location: signedup-login.php?id=$buwana_id&error=db_error");
    exit();
}

$buwana_conn->close();
?>
