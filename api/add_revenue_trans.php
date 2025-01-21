<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include the GoBrik server connection credentials
require_once '../gobrikconn_env.php';

// Check if required POST fields are set
if (!isset($_POST['amount_idr'], $_POST['sender'], $_POST['transaction_date'], $_POST['description'])) {
    http_response_code(400); // Bad Request
    echo json_encode(['error' => 'All fields are required.']);
    exit;
}

// Sanitize input data
$amount_idr = intval($_POST['amount_idr']); // Convert to integer
$sender = $gobrik_conn->real_escape_string($_POST['sender']);
$transaction_date = $gobrik_conn->real_escape_string($_POST['transaction_date']);
$description = $gobrik_conn->real_escape_string($_POST['description']);

// Additional fields
$currency_code = 'IDR';
$receiving_gea_acct = 'Yayasan IDR Bank Account';
$type_of_transaction = 'Revenue';
$datetime_sent_ts = date('Y-m-d H:i:s', strtotime($transaction_date));

// Insert transaction into the database
$sql = "INSERT INTO tb_cash_transaction (
            native_ccy_amt, idr_amount, sender_for_display,
            datetime_sent_ts, tran_name_desc, currency_code,
            receiving_gea_acct, type_of_transaction
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $gobrik_conn->prepare($sql);
if (!$stmt) {
    http_response_code(500); // Internal Server Error
    echo json_encode(['error' => 'Failed to prepare database query: ' . $gobrik_conn->error]);
    exit;
}

$stmt->bind_param(
    'iissssss',
    $amount_idr, $amount_idr, $sender,
    $datetime_sent_ts, $description, $currency_code,
    $receiving_gea_acct, $type_of_transaction
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
