<?php

require_once '../earthenAuth_helper.php'; // Include the authentication helper functions
require_once '../gobrikconn_env.php'; // GoBrik database connection
require_once '../buwanaconn_env.php'; // Buwana database connection
require_once '../scripts/earthen_subscribe_functions.php'; // Include Earthen functions

header('Content-Type: application/json'); // Ensure JSON response
ob_start(); // Start output buffering to catch extraneous output

error_reporting(E_ALL);
ini_set('display_errors', 1);

$response = []; // Initialize response array

try {
    // Validate target buwana_id to delete
    $target_buwana_id = $_GET['id'] ?? '';
    if (empty($target_buwana_id) || !is_numeric($target_buwana_id)) {
        throw new Exception('Invalid account ID. Please provide a valid ID.');
    }

    // PART 1: Fetch email_addr from tb_ecobrickers
    $sql_fetch_email = "SELECT email_addr FROM tb_ecobrickers WHERE buwana_id = ?";
    $stmt_fetch_email = $gobrik_conn->prepare($sql_fetch_email);
    if (!$stmt_fetch_email) {
        throw new Exception('Error preparing statement for fetching email: ' . $gobrik_conn->error);
    }

    $stmt_fetch_email->bind_param('i', $target_buwana_id);
    $stmt_fetch_email->execute();
    $stmt_fetch_email->bind_result($email_addr);
    $stmt_fetch_email->fetch();
    $stmt_fetch_email->close();

    if (empty($email_addr)) {
        throw new Exception('Email address not found for the given buwana_id.');
    }

    // Start transaction to ensure all operations succeed or none
    $buwana_conn->begin_transaction();
    $gobrik_conn->begin_transaction();

    // PART 2: Delete from tb_ecobrickers
    $sql_delete_ecobricker = "DELETE FROM tb_ecobrickers WHERE buwana_id = ?";
    $stmt_delete_ecobricker = $gobrik_conn->prepare($sql_delete_ecobricker);
    if (!$stmt_delete_ecobricker) {
        throw new Exception('Error preparing statement for deleting ecobricker: ' . $gobrik_conn->error);
    }
    $stmt_delete_ecobricker->bind_param('i', $target_buwana_id);
    $stmt_delete_ecobricker->execute();
    $stmt_delete_ecobricker->close();
    echo "<script>console.log('Deleted user from tb_ecobrickers: {$target_buwana_id}');</script>";

    // PART 3: Delete from users_tb
    $sql_delete_user = "DELETE FROM users_tb WHERE buwana_id = ?";
    $stmt_delete_user = $buwana_conn->prepare($sql_delete_user);
    if (!$stmt_delete_user) {
        throw new Exception('Error preparing statement for deleting user: ' . $buwana_conn->error);
    }
    $stmt_delete_user->bind_param('i', $target_buwana_id);
    $stmt_delete_user->execute();
    $stmt_delete_user->close();
    echo "<script>console.log('Deleted user from users_tb: {$target_buwana_id}');</script>";

    // PART 4: Delete from credentials_tb
    $sql_delete_credentials = "DELETE FROM credentials_tb WHERE buwana_id = ?";
    $stmt_delete_credentials = $buwana_conn->prepare($sql_delete_credentials);
    if (!$stmt_delete_credentials) {
        throw new Exception('Error preparing statement for deleting credentials: ' . $buwana_conn->error);
    }
    $stmt_delete_credentials->bind_param('i', $target_buwana_id);
    $stmt_delete_credentials->execute();
    $stmt_delete_credentials->close();
    echo "<script>console.log('Deleted user from credentials_tb: {$target_buwana_id}');</script>";

    // PART 5: Delete from Earthen
    if (!empty($email_addr)) {
        try {
            earthenUnsubscribe($email_addr);
            echo "<script>console.log('Deleted user from Earthen: {$email_addr}');</script>";
        } catch (Exception $e) {
            error_log('Error deleting user from Earthen: ' . $e->getMessage());
            throw new Exception('Failed to delete user from Earthen account: ' . $e->getMessage());
        }
    }

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
    if ($buwana_conn->in_transaction) {
        $buwana_conn->rollback();
    }
    if ($gobrik_conn->in_transaction) {
        $gobrik_conn->rollback();
    }

    $response = [
        'success' => false,
        'error' => $e->getMessage()
    ];
}

// Clear any buffered output and return JSON
ob_end_clean();
echo json_encode($response);

// Close the database connections
$buwana_conn->close();
$gobrik_conn->close();

?>
