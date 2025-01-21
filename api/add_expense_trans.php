<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include the GoBrik server connection credentials
require_once '../gobrikconn_env.php';

// Check if required POST fields are set
if (!isset($_POST['amount_idr'], $_POST['receiver'], $_POST['transaction_date'], $_POST['description'], $_POST['expense_type'], $_POST['expense_vendor'])) {
    http_response_code(400); // Bad Request
    echo json_encode(['error' => 'All fields are required.']);
    exit;
}

// Sanitize input data
$amount_idr = intval($_POST['amount_idr']); // Convert to integer
$receiver = $gobrik_conn->real_escape_string($_POST['receiver']);
$transaction_date = $gobrik_conn->real_escape_string($_POST['transaction_date']);
$description = $gobrik_conn->real_escape_string($_POST['description']);
$expense_type = $gobrik_conn->real_escape_string($_POST['expense_type']);
$expense_vendor = $gobrik_conn->real_escape_string($_POST['expense_vendor']);

// Additional fields
$currency_code = 'IDR';
$type_of_transaction = 'Expense'; // Fixed value
$sender_for_display = 'Global Ecobrick Alliance'; // Fixed value
$sender_ecobricker = 'Global Ecobrick Alliance'; // Fixed value
$datetime_sent_ts = date('Y-m-d H:i:s', strtotime($transaction_date));

// Insert transaction into the database
$sql = "INSERT INTO tb_cash_transaction (
            native_ccy_amt, idr_amount, receiver_for_display,
            datetime_sent_ts, tran_name_desc, currency_code,
            expense_accounting_type, type_of_transaction,
            expense_vendor, sender_for_display, sender_ecobricker
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $gobrik_conn->prepare($sql);
if (!$stmt) {
    http_response_code(500); // Internal Server Error
    echo json_encode(['error' => 'Failed to prepare database query: ' . $gobrik_conn->error]);
    exit;
}

$stmt->bind_param(
    'iisssssssss',
    $amount_idr, $amount_idr, $receiver,
    $datetime_sent_ts, $description, $currency_code,
    $expense_type, $type_of_transaction,
    $expense_vendor, $sender_for_display, $sender_ecobricker
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
