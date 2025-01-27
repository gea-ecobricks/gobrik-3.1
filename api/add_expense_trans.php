<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include the GoBrik server connection credentials
require_once '../gobrikconn_env.php';

// Check if required POST fields are set (only required fields are checked here)
if (!isset($_POST['amount_idr'], $_POST['transaction_date_dt'], $_POST['description'], $_POST['expense_type'])) {
    http_response_code(400); // Bad Request
    echo json_encode(['error' => 'Amount, Transaction Date, Description, and Expense Type are required.']);
    exit;
}

// Sanitize input data
$amount_idr = intval($_POST['amount_idr']); // Convert to integer
$receiver = isset($_POST['receiver']) && !empty($_POST['receiver']) ? $gobrik_conn->real_escape_string($_POST['receiver']) : null; // Allow null if blank
$transaction_date_dt = $gobrik_conn->real_escape_string($_POST['transaction_date_dt']);
$description = $gobrik_conn->real_escape_string($_POST['description']);
$expense_type = $gobrik_conn->real_escape_string($_POST['expense_type']);
$expense_vendor = isset($_POST['expense_vendor']) && !empty($_POST['expense_vendor']) ? $gobrik_conn->real_escape_string($_POST['expense_vendor']) : null; // Allow null if blank

// Additional fields
$currency_code = 'IDR';
$type_of_transaction = 'Expense'; // Fixed value
$sender_for_display = 'Global Ecobrick Alliance'; // Fixed value
$sender_ecobricker = 'Global Ecobrick Alliance'; // Fixed value
$current_datetime = date('Y-m-d H:i:s'); // Current datetime for datetime_sent_ts

// Insert transaction into the database
$sql = "INSERT INTO tb_cash_transaction (
            native_ccy_amt, idr_amount, receiver_for_display,
            transaction_date_dt, datetime_sent_ts, tran_name_desc, currency_code,
            expense_accounting_type, type_of_transaction,
            expense_vendor, sender_for_display, sender_ecobricker
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $gobrik_conn->prepare($sql);
if (!$stmt) {
    http_response_code(500); // Internal Server Error
    echo json_encode(['error' => 'Failed to prepare database query: ' . $gobrik_conn->error]);
    exit;
}

// Use `s` (string) for null values in bind_param
$stmt->bind_param(
    'iissssssssss',
    $amount_idr, // native_ccy_amt
    $amount_idr, // idr_amount
    $receiver, // receiver_for_display (can be null)
    $transaction_date_dt, // transaction_date_dt
    $current_datetime, // datetime_sent_ts
    $description, // tran_name_desc
    $currency_code, // currency_code
    $expense_type, // expense_accounting_type
    $type_of_transaction, // type_of_transaction
    $expense_vendor, // expense_vendor (can be null)
    $sender_for_display, // sender_for_display
    $sender_ecobricker // sender_ecobricker
);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    http_response_code(500); // Internal Server Error
    echo json_encode(['error' => 'Failed to insert transaction: ' . $stmt->error]);
}

// Clean up
$stmt->close();
$gobrik_conn->close();
?>
