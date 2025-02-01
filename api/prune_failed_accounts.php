<?php
ob_start(); // Start output buffering
require_once '../earthenAuth_helper.php';
require_once '../gobrikconn_env.php';
require_once '../buwanaconn_env.php';
require_once '../scripts/earthen_subscribe_functions.php';

header('Content-Type: application/json');

// Suppress direct error output and log errors instead
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '/path/to/error.log');

$response = []; // Response array to store details for each email processed

try {
    // Start transactions
    $buwana_conn->begin_transaction();
    $gobrik_conn->begin_transaction();

    // Fetch the first 50 accounts with status 'failed'
    $fetch_query = "SELECT ecobricker_id, email_addr, buwana_id FROM tb_ecobrickers WHERE emailing_status = 'failed' LIMIT 25";
    $fetch_result = $gobrik_conn->query($fetch_query);

    if ($fetch_result && $fetch_result->num_rows > 0) {
        $accounts_to_delete = $fetch_result->fetch_all(MYSQLI_ASSOC);

        foreach ($accounts_to_delete as $account) {
            $email_addr = $account['email_addr'];
            $ecobricker_id = $account['ecobricker_id'];
            $buwana_id = $account['buwana_id'];
            $log = ["email" => $email_addr];

            // Delete the ecobricker record
            $delete_ecobricker_query = "DELETE FROM tb_ecobrickers WHERE ecobricker_id = ?";
            $stmt_ecobricker = $gobrik_conn->prepare($delete_ecobricker_query);
            $stmt_ecobricker->bind_param("i", $ecobricker_id);
            if ($stmt_ecobricker->execute()) {
                $log["ecobricker_status"] = "User successfully deleted from GoBrik database.";
            } else {
                $log["ecobricker_status"] = "Error deleting user from GoBrik: " . $stmt_ecobricker->error;
            }
            $stmt_ecobricker->close();

            // Delete the Buwana user and credentials (if exists)
            if (!empty($buwana_id)) {
                $delete_user_query = "DELETE FROM users_tb WHERE buwana_id = ?";
                $stmt_user = $buwana_conn->prepare($delete_user_query);
                $stmt_user->bind_param("i", $buwana_id);
                if ($stmt_user->execute()) {
                    $log["buwana_status"] = "User successfully deleted from Buwana database.";
                } else {
                    $log["buwana_status"] = "Error deleting user from Buwana: " . $stmt_user->error;
                }
                $stmt_user->close();

                $delete_credentials_query = "DELETE FROM credentials_tb WHERE buwana_id = ?";
                $stmt_credentials = $buwana_conn->prepare($delete_credentials_query);
                $stmt_credentials->bind_param("i", $buwana_id);
                if ($stmt_credentials->execute()) {
                    $log["credentials_status"] = "User credentials successfully deleted from Buwana.";
                } else {
                    $log["credentials_status"] = "Error deleting credentials from Buwana: " . $stmt_credentials->error;
                }
                $stmt_credentials->close();
            } else {
                $log["buwana_status"] = "No Buwana account associated with this user.";
            }

            // Call Earthen unsubscribe
            if (!empty($email_addr)) {
                $unsubscribe_result = earthenUnsubscribe($email_addr);
                if (!empty($unsubscribe_result) && strpos((string)$unsubscribe_result, "success") !== false) {
                    $log["earthen_status"] = "User successfully unsubscribed from Earthen.";
                } else {
                    $log["earthen_status"] = "Error unsubscribing user from Earthen: " . ($unsubscribe_result ?? 'No response received.');
                }
            } else {
                $log["earthen_status"] = "No email provided for Earthen unsubscribe.";
            }

            $response[] = $log;
        }

        // Commit transactions
        $buwana_conn->commit();
        $gobrik_conn->commit();

    } else {
        throw new Exception('No accounts found with status "failed".');
    }

    // Success response
    $final_response = [
        'status' => 'success',
        'details' => $response,
    ];

} catch (Exception $e) {
    // Rollback transactions on error
    if ($buwana_conn->in_transaction) {
        $buwana_conn->rollback();
    }
    if ($gobrik_conn->in_transaction) {
        $gobrik_conn->rollback();
    }

    // Error response
    $final_response = [
        'status' => 'error',
        'message' => $e->getMessage(),
    ];
}

// Clear any unintended output before sending JSON
ob_end_clean();
echo json_encode($final_response);
exit();
?>
