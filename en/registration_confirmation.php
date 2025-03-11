<?php
require_once '../earthenAuth_helper.php'; // Include authentication helper functions

// Ensure user is logged in
if (!isLoggedIn()) {
    header("Location: login.php?redirect=register.php");
    exit();
}

// Include database connection
require_once '../gobrikconn_env.php';

$training_id = isset($_GET['training_id']) ? intval($_GET['training_id']) : 0;
$ecobricker_id = isset($_GET['ecobricker_id']) ? intval($_GET['ecobricker_id']) : 0;

// Validate inputs
if ($training_id <= 0 || $ecobricker_id <= 0) {
    header("Location: register.php?error=invalid");
    exit();
}

// Check if the user is already registered
$sql_check = "SELECT id FROM tb_training_trainees WHERE training_id = ? AND ecobricker_id = ?";
$stmt_check = $gobrik_conn->prepare($sql_check);
$stmt_check->bind_param("ii", $training_id, $ecobricker_id);
$stmt_check->execute();
$stmt_check->store_result();

if ($stmt_check->num_rows > 0) {
    // User is already registered, redirect back with success modal
    $stmt_check->close();
    $gobrik_conn->close();
    header("Location: register.php?training_id=$training_id&registered=1");
    exit();
}
$stmt_check->close();

// Register the user
$sql_insert = "INSERT INTO tb_training_trainees (training_id, ecobricker_id, rsvp_status) VALUES (?, ?, 'confirmed')";
$stmt_insert = $gobrik_conn->prepare($sql_insert);
$stmt_insert->bind_param("ii", $training_id, $ecobricker_id);

if ($stmt_insert->execute()) {
    // Successfully registered, redirect back with success message
    header("Location: register.php?training_id=$training_id&registered=1");
    exit();
} else {
    // Registration failed, redirect with error message
    header("Location: register.php?training_id=$training_id&error=failed");
    exit();
}

$stmt_insert->close();
$gobrik_conn->close();
?>
