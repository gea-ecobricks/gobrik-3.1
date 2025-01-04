<?php
ob_start(); // Start output buffering
require_once '../earthenAuth_helper.php';
require_once '../gobrikconn_env.php';
require_once '../buwanaconn_env.php';
require_once '../scripts/earthen_subscribe_functions.php';

header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

$response = [];

try {
    // Start transactions and track status
    $buwana_conn->begin_transaction();
    $gobrik_conn->begin_transaction();
    $in_buwana_transaction = true;
    $in_gobrik_transaction = true;

    // Fetch first 5 accounts with 'failed' status
    $fetch_query = "SELECT ecobricker_id, email_addr, buwana_id FROM tb_ecobrickers WHERE emailing_status = 'failed' LIMIT 5";
    $fetch_result = $gobrik_conn->query($fetch_query);

    if ($fetch_result && $fetch_result->num_rows > 0) {
        $accounts_to_delete = $fetch_result->fetch_all(MYSQLI_ASSOC);

        foreach ($accounts_to_delete as $account) {
            $ecobricker_id = $account['ecobricker_id'];
            $email_addr = $account['email_addr'];
            $buwana_id = $account['buwana_id'];
            $log = ["email" => $email_addr];

            // Delete related rows in other tables referencing ecobricker_id
            $delete_related_query = "DELETE FROM related_table WHERE maker_id = ?";
            $stmt_related = $gobrik_conn->prepare($delete_related_query);
            $stmt_related->bind_param("i", $ecobricker_id);
            $stmt_related->execute();
            $stmt_related->close();

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
                $log["earthen_status"] = strpos($unsubscribe_result, "success") !== false
                    ? "User successfully unsubscribed from Earthen."
                    : "Error unsubscribing user from Earthen: " . $unsubscribe_result;
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
} catch (Exception $e) {
    // Rollback transactions
    if (isset($in_buwana_transaction)) {
        $buwana_conn->rollback();
    }
    if (isset($in_gobrik_transaction)) {
        $gobrik_conn->rollback();
    }

    $response = [
        'error' => $e->getMessage(),
    ];
}

ob_end_clean();
echo json_encode($response);
exit();
?>
