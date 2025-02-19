<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include the GoBrik server connection credentials
require_once '../gobrikconn_env.php';

// Check if required POST fields are set
if (!isset($_POST['amount_idr'], $_POST['sender'], $_POST['transaction_date'], $_POST['description'], $_POST['revenue_type'], $_POST['receiving_gea_acct'])) {
    http_response_code(400); // Bad Request
    echo json_encode(['error' => 'All fields are required.']);
    exit;
}

// Sanitize input data
$amount_idr = intval($_POST['amount_idr']); // Convert to integer
$sender = $gobrik_conn->real_escape_string($_POST['sender']);
$transaction_date = $gobrik_conn->real_escape_string($_POST['transaction_date']);
$description = $gobrik_conn->real_escape_string($_POST['description']);
$revenue_type = $gobrik_conn->real_escape_string($_POST['revenue_type']);
$receiving_gea_acct = $gobrik_conn->real_escape_string($_POST['receiving_gea_acct']);

// Additional fields
$currency_code = 'IDR';
$type_of_transaction = 'Revenue';
$receiver_for_display = 'Global Ecobrick Alliance'; // Set receiver_for_display field

// Set datetime_sent_ts to the current date and time
$datetime_sent_ts = date('Y-m-d H:i:s');

// Set transaction_date_dt to the passed transaction_date
$transaction_date_dt = date('Y-m-d', strtotime($transaction_date));

// Insert transaction into the database
$sql = "INSERT INTO tb_cash_transaction (
            native_ccy_amt, idr_amount, sender_for_display,
            datetime_sent_ts, tran_name_desc, currency_code,
            receiving_gea_acct, type_of_transaction,
            revenue_accounting_type, receiver_for_display,
            transaction_date_dt
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $gobrik_conn->prepare($sql);
if (!$stmt) {
    http_response_code(500); // Internal Server Error
    echo json_encode(['error' => 'Failed to prepare database query: ' . $gobrik_conn->error]);
    exit;
}

$stmt->bind_param(
    'iisssssssss',
    $amount_idr, $amount_idr, $sender,
    $datetime_sent_ts, $description, $currency_code,
    $receiving_gea_acct, $type_of_transaction,
    $revenue_type, $receiver_for_display,
    $transaction_date_dt
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


