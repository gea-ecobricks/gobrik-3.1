<?php
// Include the GoBrik server connection credentials
require_once '../gobrikconn_env.php';

try {
    // Prepare the SQL query
    $sql = "SELECT
                chain_ledger_id,
                tran_id,
                tran_name,
                individual_amt,
                status,
                send_ts,
                sender_ecobricker,
                block_tran_type,
                block_amt,
                sender,
                receiver_or_receivers,
                receiver_1,
                receiver_2,
                receiver_3,
                receiver_central_reserve,
                sender_central_reserve,
                ecobrick_serial_no,
                tran_sender_note,
                product,
                send_dt,
                accomp_payment,
                authenticator_version,
                expense_type,
                gea_accounting_category,
                shipping_cost_brk,
                product_cost_brk,
                total_cost_incl_shipping,
                shipping_with_currency,
                aes_officially_purchased,
                country_of_buyer,
                currency_for_shipping,
                credit_other_ecobricker_yn,
                catalyst_name
            FROM tb_brk_transaction";

    // Prepare the statement
    $stmt = $gobrik_conn->prepare($sql);

    if (!$stmt) {
        throw new Exception("Failed to prepare the statement: " . $gobrik_conn->error);
    }

    // Execute the query
    $stmt->execute();

    // Bind result variables
    $stmt->store_result();
    $data = [];
    $result = $stmt->get_result();

    // Fetch data row by row
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;

        // Optional: Limit the number of rows fetched (for testing purposes)
        if (count($data) > 1000) break;
    }

    // Return the data as JSON
    echo json_encode($data);

} catch (Exception $e) {
    // Return the error as JSON
    echo json_encode(['error' => $e->getMessage()]);
}
?>
