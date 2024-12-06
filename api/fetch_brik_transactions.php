<?php
// Include the GoBrik server connection credentials
require_once '../gobrikconn_env.php';

try {
    // Create a new PDO connection using credentials from gobrikconn_env.php
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Query to fetch all fields from tb_brk_transaction
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

    // Execute the query
    $stmt = $pdo->prepare($sql);
    $stmt->execute();

    // Fetch all data as an associative array
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Return data as JSON
    echo json_encode($result);

} catch (PDOException $e) {
    // Return error message if connection or query fails
    echo json_encode(['error' => $e->getMessage()]);
}

?>
