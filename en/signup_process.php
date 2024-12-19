<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

ob_start(); // Start output buffering

$response = ['success' => false];

include '../buwanaconn_env.php'; // Buwana database connection
include '../gobrikconn_env.php'; // GoBrik database connection

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $buwana_id = $_GET['id'] ?? null;

    // Validate buwana_id
    if (empty($buwana_id) || !is_numeric($buwana_id)) {
        $response['error'] = 'invalid_buwana_id';
        echo json_encode($response);
        ob_end_clean();
        exit();
    }

    // Sanitize and validate inputs
    $credential_value = filter_var($_POST['credential_value'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password_hash'];

    if (empty($password) || strlen($password) < 6) {
        $response['error'] = 'invalid_password';
        echo json_encode($response);
        ob_end_clean();
        exit();
    }

    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // Retrieve the Buwana first_name
    $sql_get_first_name = "SELECT first_name FROM users_tb WHERE buwana_id = ?";
    $stmt_get_first_name = $buwana_conn->prepare($sql_get_first_name);
    if (!$stmt_get_first_name) {
        $response['error'] = 'db_error: ' . $buwana_conn->error;
        echo json_encode($response);
        ob_end_clean();
        exit();
    }

    $stmt_get_first_name->bind_param("i", $buwana_id);
    $stmt_get_first_name->execute();
    $stmt_get_first_name->bind_result($first_name);
    $stmt_get_first_name->fetch();
    $stmt_get_first_name->close();

    if (empty($first_name)) {
        $response['error'] = 'missing_first_name';
        echo json_encode($response);
        ob_end_clean();
        exit();
    }

    // Insert ecobricker record
    $sql_create_ecobricker = "INSERT INTO tb_ecobrickers
    (first_name, full_name, buwana_id, email_addr, date_registered, maker_id, buwana_activated, buwana_activation_dt, account_notes)
    VALUES (?, ?, ?, ?, NOW(), ?, 1, NOW(), ?)";
    $stmt_create_ecobricker = $gobrik_conn->prepare($sql_create_ecobricker);

    if (!$stmt_create_ecobricker) {
        $response['error'] = 'db_error: ' . $gobrik_conn->error;
        echo json_encode($response);
        ob_end_clean();
        exit();
    }

    $full_name = $first_name;
    $account_notes = "signup_process has run";

    $stmt_create_ecobricker->bind_param("ssisi", $first_name, $full_name, $buwana_id, $credential_value, $account_notes);
    if ($stmt_create_ecobricker->execute()) {
        $ecobricker_id = $gobrik_conn->insert_id;
        $response['success'] = true;
        $response['redirect'] = "confirm-email.php?id=$ecobricker_id";
    } else {
        $response['error'] = 'db_error_ecobricker: ' . $stmt_create_ecobricker->error;
    }

    $stmt_create_ecobricker->close();
    $buwana_conn->close();
    $gobrik_conn->close();
} else {
    $response['error'] = 'invalid_request';
}

ob_end_clean();
echo json_encode($response);
exit();
?>