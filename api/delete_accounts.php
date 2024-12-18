<?php

require_once '../earthenAuth_helper.php'; // Include the authentication helper functions
require_once '../gobrikconn_env.php'; // GoBrik database connection
require_once '../buwanaconn_env.php'; // Buwana database connection

header('Content-Type: application/json'); // Ensure JSON response
ob_start(); // Start output buffering to catch extraneous output

error_reporting(E_ALL);
ini_set('display_errors', 1);

$response = []; // Initialize response array

// Check if the user is logged in and has admin privileges
checkAdminStatus(); // Call the reusable function

// Validate buwana_id
$buwana_id = $_GET['id'] ?? '';
if (empty($buwana_id) || !is_numeric($buwana_id)) {
    $response = [
        'success' => false,
        'error' => 'Invalid account ID. Please provide a valid ID.'
    ];
    ob_end_clean();
    echo json_encode($response);
    exit();
}

try {
    // Start transaction to ensure all operations succeed or none
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

    // Return success response
    $response = [
        'success' => true,
        'message' => 'User deleted successfully.'
    ];
} catch (Exception $e) {
    // Rollback transactions if there was an error
    $buwana_conn->rollback();
    $gobrik_conn->rollback();

    $response = [
        'success' => false,
        'error' => 'An error occurred while deleting the account: ' . $e->getMessage()
    ];
}

// Clear any buffered output and return JSON
ob_end_clean();
echo json_encode($response);

// Close the database connections
$buwana_conn->close();
$gobrik_conn->close();

?>
