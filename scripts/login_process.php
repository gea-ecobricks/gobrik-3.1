<?php
session_start();
include '../buwana_env.php'; // Gobrik DB connection
include '../buwanaconn_env.php'; // Buwana DB connection

// Constants
$client_id = 'gbrk_f2c61a85a4cd4b8b89a7'; // GoBrik client_id
$lang = $_POST['lang'] ?? 'en'; // Fallback language

// Retrieve form data
$credential_value = $_POST['credential_value'] ?? '';
$password = $_POST['password'] ?? '';

// Validate input
if (empty($credential_value) || empty($password)) {
    header("Location: ../$lang/login.php?error=empty_fields");
    exit();
}

// Check if legacy activation is needed
$sql_check_email = "SELECT user_id, legacy_unactivated FROM users_tb WHERE email = ?";
$stmt_check_email = $conn->prepare($sql_check_email);

if ($stmt_check_email) {
    $stmt_check_email->bind_param('s', $credential_value);
    $stmt_check_email->execute();
    $stmt_check_email->store_result();

    if ($stmt_check_email->num_rows === 1) {
        $stmt_check_email->bind_result($user_id, $legacy_unactivated);
        $stmt_check_email->fetch();

        if ($legacy_unactivated === 'yes') {
            header("Location: ../$lang/activate.php?user_id=$user_id");
            exit();
        }

        $stmt_check_email->close();
    } else {
        $stmt_check_email->close();
    }
}

// Check credentials
$sql_credential = "SELECT user_id FROM credentials_tb WHERE credential_key = ?";
$stmt_credential = $conn->prepare($sql_credential);

if ($stmt_credential) {
    $stmt_credential->bind_param('s', $credential_value);
    $stmt_credential->execute();
    $stmt_credential->store_result();

    if ($stmt_credential->num_rows === 1) {
        $stmt_credential->bind_result($user_id);
        $stmt_credential->fetch();
        $stmt_credential->close();

        // Fetch password hash
        $sql_user = "SELECT password_hash FROM users_tb WHERE user_id = ?";
        $stmt_user = $conn->prepare($sql_user);

        if ($stmt_user) {
            $stmt_user->bind_param('i', $user_id);
            $stmt_user->execute();
            $stmt_user->store_result();

            if ($stmt_user->num_rows === 1) {
                $stmt_user->bind_result($password_hash);
                $stmt_user->fetch();

                if (password_verify($password, $password_hash)) {
                    $_SESSION['user_id'] = $user_id;

                    // 🔍 Lookup buwana_id from GoBrik users_tb
                    $sql_buwana = "SELECT buwana_id FROM users_tb WHERE user_id = ?";
                    $stmt_buwana = $conn->prepare($sql_buwana);
                    if ($stmt_buwana) {
                        $stmt_buwana->bind_param('i', $user_id);
                        $stmt_buwana->execute();
                        $stmt_buwana->bind_result($buwana_id);
                        $stmt_buwana->fetch();
                        $stmt_buwana->close();
                    }

                    // ✅ Gatekeeper: check if user is already connected in Buwana
                    $check_sql = "SELECT 1 FROM user_app_connections_tb WHERE buwana_id = ? AND client_id = ? LIMIT 1";
                    $check_stmt = $buwana_conn->prepare($check_sql);
                    if ($check_stmt) {
                        $check_stmt->bind_param('is', $buwana_id, $client_id);
                        $check_stmt->execute();
                        $check_stmt->store_result();

                        if ($check_stmt->num_rows === 0) {
                            $check_stmt->close();

                            // 🚪 Not connected → Redirect to Buwana connection page
                            $connect_url = "https://buwana.ecobricks.org/app-connect.php?id=" . urlencode($buwana_id) . "&client_id=" . urlencode($client_id);
                            header("Location: $connect_url");
                            exit();
                        }

                        $check_stmt->close();
                    }

                    // ✅ All clear: redirect to dashboard
                    header("Location: ../$lang/dashboard.php");
                    exit();
                } else {
                    header("Location: ../$lang/login.php?error=invalid_password");
                    exit();
                }
            } else {
                header("Location: ../$lang/login.php?error=invalid_user");
                exit();
            }

            $stmt_user->close();
        } else {
            die('Error preparing user lookup: ' . $conn->error);
        }
    } else {
        header("Location: ../$lang/login.php?error=invalid_credential");
        exit();
    }
} else {
    die('Error preparing credential lookup: ' . $conn->error);
}

$conn->close();
?>
