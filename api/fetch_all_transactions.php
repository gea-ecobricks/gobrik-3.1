<?php
// Include the GoBrik server connection credentials
require_once '../gobrikconn_env.php';

// Fetch data for all transactions
$sql = "SELECT cash_tran_id,
               transaction_date_dt,
               sender_for_display,
               type_of_transaction,
               tran_name_desc,
               usd_amount,
               idr_amount,
               revenue_accounting_type AS account_note -- Rename revenue_accounting_type as account_note
        FROM tb_cash_transaction
        ORDER BY transaction_date_dt DESC"; // Sort by date descending (newest first)

$result = $gobrik_conn->query($sql);

// Check for query execution errors
if (!$result) {
    http_response_code(500);
    echo json_encode(["error" => "Database query failed: " . $gobrik_conn->error]);
    exit;
}

$data = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $data[] = [
            "ID" => $row["cash_tran_id"],
            "AmountIDR" => number_format((int)$row["idr_amount"], 0, '.', ','), // Format Amount IDR
            "Date" => $row["transaction_date_dt"],
            "Sender" => $row["sender_for_display"],
            "Type" => $row["type_of_transaction"],
            "Category" => $row["type_of_transaction"], // Assuming this is also the category
            "Transaction" => $row["tran_name_desc"],
            "AmountUSD" => number_format((float)$row["usd_amount"], 2, '.', ','), // Format Amount USD
            "AccountNote" => $row["account_note"] // Renamed column for "Account Note"
        ];
    }
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode(["data" => $data]);

$gobrik_conn->close();
?>
