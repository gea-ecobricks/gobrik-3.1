<?php
ob_start(); // Start output buffering to prevent unexpected output

require_once '../earthenAuth_helper.php';
require_once '../gobrikconn_env.php';
require_once '../buwanaconn_env.php';
require_once '../scripts/earthen_subscribe_functions.php';

header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

$response = [];

try {
    $target_buwana_id = $_GET['id'] ?? '';
    if (empty($target_buwana_id) || !is_numeric($target_buwana_id)) {
        throw new Exception('Invalid account ID. Please provide a valid ID.');
    }

    // Fetch email_addr from tb_ecobrickers
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

    // Begin transactions
    $buwana_conn->begin_transaction();
    $gobrik_conn->begin_transaction();

    // Delete from tb_ecobrickers
    $sql_delete_ecobricker = "DELETE FROM tb_ecobrickers WHERE buwana_id = ?";
    $stmt_delete_ecobricker = $gobrik_conn->prepare($sql_delete_ecobricker);
    if (!$stmt_delete_ecobricker) {
        throw new Exception('Error preparing statement for deleting ecobricker: ' . $gobrik_conn->error);
    }
    $stmt_delete_ecobricker->bind_param('i', $target_buwana_id);
    $stmt_delete_ecobricker->execute();
    $stmt_delete_ecobricker->close();

    // Delete from users_tb
    $sql_delete_user = "DELETE FROM users_tb WHERE buwana_id = ?";
    $stmt_delete_user = $buwana_conn->prepare($sql_delete_user);
    if (!$stmt_delete_user) {
        throw new Exception('Error preparing statement for deleting user: ' . $buwana_conn->error);
    }
    $stmt_delete_user->bind_param('i', $target_buwana_id);
    $stmt_delete_user->execute();
    $stmt_delete_user->close();

    // Delete from credentials_tb
    $sql_delete_credentials = "DELETE FROM credentials_tb WHERE buwana_id = ?";
    $stmt_delete_credentials = $buwana_conn->prepare($sql_delete_credentials);
    if (!$stmt_delete_credentials) {
        throw new Exception('Error preparing statement for deleting credentials: ' . $buwana_conn->error);
    }
    $stmt_delete_credentials->bind_param('i', $target_buwana_id);
    $stmt_delete_credentials->execute();
    $stmt_delete_credentials->close();

    // Call Earthen unsubscribe
    if (!empty($email_addr)) {
        earthenUnsubscribe($email_addr);
    }

    // Commit transactions
    $buwana_conn->commit();
    $gobrik_conn->commit();

    $response = [
        'success' => true,
        'message' => 'User deleted successfully.',
    ];
} catch (Exception $e) {
    // Rollback on error
    if ($buwana_conn->in_transaction) {
        $buwana_conn->rollback();
    }
    if ($gobrik_conn->in_transaction) {
        $gobrik_conn->rollback();
    }

    $response = [
        'success' => false,
        'error' => $e->getMessage(),
    ];
}

// Clean output buffer and return JSON
ob_end_clean();
echo json_encode($response);
exit();
?>
