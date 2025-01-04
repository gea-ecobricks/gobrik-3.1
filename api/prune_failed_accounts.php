<?php
require_once '../earthenAuth_helper.php';
require_once '../gobrikconn_env.php';
require_once '../buwanaconn_env.php';
require_once '../scripts/earthen_subscribe_functions.php';

header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

$response = [];

try {
    // Start transactions
    $buwana_conn->begin_transaction();
    $gobrik_conn->begin_transaction();

    // Fetch the first 5 accounts with status 'failed'
    $fetch_query = "SELECT ecobricker_id, email_addr, buwana_id FROM tb_ecobrickers WHERE emailing_status = 'failed' LIMIT 5";
    $fetch_result = $gobrik_conn->query($fetch_query);

    if ($fetch_result && $fetch_result->num_rows > 0) {
        $accounts_to_delete = $fetch_result->fetch_all(MYSQLI_ASSOC);

        foreach ($accounts_to_delete as $account) {
            $ecobricker_id = $account['ecobricker_id'];
            $email_addr = $account['email_addr'];
            $buwana_id = $account['buwana_id'];

            // Delete the ecobricker record
            $delete_ecobricker_query = "DELETE FROM tb_ecobrickers WHERE ecobricker_id = ?";
            $stmt_ecobricker = $gobrik_conn->prepare($delete_ecobricker_query);
            $stmt_ecobricker->bind_param("i", $ecobricker_id);
            if (!$stmt_ecobricker->execute()) {
                throw new Exception('Error deleting ecobricker: ' . $stmt_ecobricker->error);
            }
            $stmt_ecobricker->close();

            // Delete the Buwana user and credentials (if exists)
            if (!empty($buwana_id)) {
                $delete_user_query = "DELETE FROM users_tb WHERE buwana_id = ?";
                $stmt_user = $buwana_conn->prepare($delete_user_query);
                $stmt_user->bind_param("i", $buwana_id);
                if (!$stmt_user->execute()) {
                    throw new Exception('Error deleting Buwana user: ' . $stmt_user->error);
                }
                $stmt_user->close();

                $delete_credentials_query = "DELETE FROM credentials_tb WHERE buwana_id = ?";
                $stmt_credentials = $buwana_conn->prepare($delete_credentials_query);
                $stmt_credentials->bind_param("i", $buwana_id);
                if (!$stmt_credentials->execute()) {
                    throw new Exception('Error deleting Buwana credentials: ' . $stmt_credentials->error);
                }
                $stmt_credentials->close();
            }

            // Call Earthen unsubscribe
            if (!empty($email_addr)) {
                earthenUnsubscribe($email_addr);
            }
        }

        // Commit transactions
        $buwana_conn->commit();
        $gobrik_conn->commit();

        $response = [
            'success' => true,
            'message' => 'Accounts pruned successfully.',
        ];
    } else {
        throw new Exception('No accounts found with status "failed".');
    }
} catch (Exception $e) {
    // Rollback transactions on error
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

echo json_encode($response);
exit();
?>
