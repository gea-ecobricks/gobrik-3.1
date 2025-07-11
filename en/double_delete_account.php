<?php
session_start();
require_once '../auth/session_start.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database connections
require_once '../gobrikconn_env.php'; // GoBrik database connection
require_once '../buwanaconn_env.php'; // Buwana database connection

// Check if the user is logged in and if buwana_id is set
if (!isset($_SESSION['buwana_id'])) {
    echo '<script>
        alert("Please login before viewing this page.");
        window.location.href = "login.php?redirect=profile";
    </script>';
    exit();
}

$buwana_id = $_GET['id'] ?? '';

// Ensure buwana_id is valid
if (empty($buwana_id)) {
    echo '<script>
        alert("Invalid account ID. Please try again.");
        window.location.href = "profile.php";
    </script>';
    exit();
}

try {
    // Start transaction to ensure all operations are successful or none are
    $buwana_conn->begin_transaction();
    $gobrik_conn->begin_transaction();

    // Delete from users_tb
    $sql_delete_user = "DELETE FROM users_tb WHERE buwana_id = ?";
    $stmt_delete_user = $buwana_conn->prepare($sql_delete_user);
    if (!$stmt_delete_user) {
        throw new Exception('Error preparing statement for deleting user: ' . $buwana_conn->error);
    }
    $stmt_delete_user->bind_param('i', $buwana_id);
    $stmt_delete_user->execute();
    $stmt_delete_user->close();

    // Delete from credentials_tb
    $sql_delete_credentials = "DELETE FROM credentials_tb WHERE buwana_id = ?";
    $stmt_delete_credentials = $buwana_conn->prepare($sql_delete_credentials);
    if (!$stmt_delete_credentials) {
        throw new Exception('Error preparing statement for deleting credentials: ' . $buwana_conn->error);
    }
    $stmt_delete_credentials->bind_param('i', $buwana_id);
    $stmt_delete_credentials->execute();
    $stmt_delete_credentials->close();

    // Delete from tb_ecobrickers
    $sql_delete_ecobricker = "DELETE FROM tb_ecobrickers WHERE buwana_id = ?";
    $stmt_delete_ecobricker = $gobrik_conn->prepare($sql_delete_ecobricker);
    if (!$stmt_delete_ecobricker) {
        throw new Exception('Error preparing statement for deleting ecobricker: ' . $gobrik_conn->error);
    }
    $stmt_delete_ecobricker->bind_param('i', $buwana_id);
    $stmt_delete_ecobricker->execute();
    $stmt_delete_ecobricker->close();

    // Commit the transactions
    $buwana_conn->commit();
    $gobrik_conn->commit();

    // Terminate the session and clear session data
    session_unset(); // Remove all session variables
    session_destroy(); // Destroy the session

    // Redirect to goodbye page with success message
    echo '<script>
        window.location.href = "goodbye.php?status=deleted";
    </script>';

} catch (Exception $e) {
    // Rollback transactions if there was an error
    $buwana_conn->rollback();
    $gobrik_conn->rollback();
    echo '<script>
        alert("An error occurred while deleting your account. Please try again.");
        window.location.href = "profile.php?status=failed";
    </script>';
}

// Close the database connections
$buwana_conn->close();
$gobrik_conn->close();
?>
