<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if the user is logged in
if (!isset($_SESSION['buwana_id'])) {
    header("Location: login.php");
    exit();
}

// Get and validate ecobricker_id from the URL
$ecobricker_id = $_GET['id'] ?? null;
if (is_null($ecobricker_id) || !is_numeric($ecobricker_id)) {
    echo '<script>
        alert("Invalid or missing ecobricker ID. Please try again.");
        window.location.href = "activate.php";
    </script>';
    exit();
}

// GoBrik database credentials (consider using a more secure method for production)
$gobrik_servername = "localhost";
$gobrik_username = "ecobricks_brikchain_viewer";
$gobrik_password = "desperate-like-the-Dawn";
$gobrik_dbname = "ecobricks_gobrik_msql_db";

// Establish a secure database connection
$gobrik_conn = new mysqli($gobrik_servername, $gobrik_username, $gobrik_password, $gobrik_dbname);
if ($gobrik_conn->connect_error) {
    die("Connection failed: " . $gobrik_conn->connect_error);
}
$gobrik_conn->set_charset("utf8mb4");

// Prepare and execute the SQL statement to delete the user
$sql_delete_user = "DELETE FROM tb_ecobrickers WHERE ecobricker_id = ?";
$stmt_delete_user = $gobrik_conn->prepare($sql_delete_user);
if ($stmt_delete_user) {
    $stmt_delete_user->bind_param('i', $ecobricker_id);
    $stmt_delete_user->execute();
    $stmt_delete_user->close();

    // Destroy session and redirect to confirmation page
    session_destroy();
    echo '<script>
        alert("Your account has been permanently deleted.");
        window.location.href = "goodbye.php";
    </script>';
    exit();
} else {
    die('Error preparing statement for deleting user: ' . $gobrik_conn->error);
}

$gobrik_conn->close();

?>
