<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../gobrikconn_env.php'; // Database connection for tb_ecobrickers
include '../buwanaconn_env.php'; // Database connection for buwana

// Sanitize and retrieve inputs
$buwana_id = $_POST['buwana_id'] ?? null;
$credential_value = filter_var($_POST['credential_value'], FILTER_SANITIZE_EMAIL);
$first_name = htmlspecialchars($_POST['first_name'], ENT_QUOTES);

// Validate input data
if (!$buwana_id || !$credential_value || !$first_name) {
    die('Invalid input');
}

// Check if the email already exists in tb_ecobrickers
$sql_check_ecobricker = "SELECT ecobricker_id FROM tb_ecobrickers WHERE email_addr = ?";
$stmt_check_ecobricker = $gobrik_conn->prepare($sql_check_ecobricker);

if ($stmt_check_ecobricker) {
    $stmt_check_ecobricker->bind_param("s", $credential_value);
    $stmt_check_ecobricker->execute();
    $stmt_check_ecobricker->bind_result($ecobricker_id);
    $stmt_check_ecobricker->fetch();
    $stmt_check_ecobricker->close();

    if ($ecobricker_id) {
        // Email already exists, redirect to activate.php
        header("Location: activate.php?id=$ecobricker_id");
        exit();
    }
} else {
    die('Database error');
}

// If the email does not exist, create a new ecobricker
$date_now = date("Y-m-d H:i:s");
$sql_create_ecobricker = "INSERT INTO tb_ecobrickers (first_name, buwana_id, email_addr, date_registered, maker_id, buwana_activated, buwana_activation_dt) VALUES (?, ?, ?, ?, ?, 1, ?)";
$stmt_create_ecobricker = $gobrik_conn->prepare($sql_create_ecobricker);

if ($stmt_create_ecobricker) {
    $stmt_create_ecobricker->bind_param("sissss", $first_name, $buwana_id, $credential_value, $date_now, $buwana_id, $date_now);
    $stmt_create_ecobricker->execute();
    $new_ecobricker_id = $stmt_create_ecobricker->insert_id;
    $stmt_create_ecobricker->close();

    // Redirect to confirm-email.php with the new ecobricker_id
    header("Location: confirm-email.php?id=$new_ecobricker_id");
    exit();
} else {
    die('Database error');
}

// Close the database connections
$gobrik_conn->close();
$buwana_conn->close();
?>
