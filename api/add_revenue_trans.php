<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include the GoBrik server connection credentials
require_once '../gobrikconn_env.php';

// Check if required POST fields are set
if (!isset($_POST['amount_idr'], $_POST['sender'], $_POST['transaction_date'], $_POST['description'], $_POST['revenue_type'], $_POST['receiving_gea_acct'], $_POST['sender_ecobricker'])) {
    http_response_code(400); // Bad Request
    echo json_encode(['error' => 'All fields are required.']);
    exit;
}

// Sanitize input data
$amount_idr = intval($_POST['amount_idr']); // Convert to integer
$sender = $gobrik_conn->real_escape_string($_POST['sender']);
$sender_ecobricker = $gobrik_conn->real_escape_string($_POST['sender_ecobricker']);
$transaction_date = $gobrik_conn->real_escape_string($_POST['transaction_date']);
$description = $gobrik_conn->real_escape_string($_POST['description']);
$revenue_type = $gobrik_conn->real_escape_string($_POST['revenue_type']);
$receiving_gea_acct = $gobrik_conn->real_escape_string($_POST['receiving_gea_acct']);

// Handle file upload
$transaction_image_url = null;
if (isset($_FILES['transaction_image']) && $_FILES['transaction_image']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = '../uploads/';
    $fileName = uniqid() . '_' . basename($_FILES['transaction_image']['name']);
    $uploadFile = $uploadDir . $fileName;

    if (move_uploaded_file($_FILES['transaction_image']['tmp_name'], $uploadFile)) {
        $transaction_image_url = $uploadFile;
    } else {
        http_response_code(500); // Internal Server Error
        echo json_encode(['error' => 'Failed to upload image.']);
        exit;
    }
}

// Additional fields
$currency_code = 'IDR';
$type_of_transaction = 'Revenue';
$datetime_sent_ts = date('Y-m-d H:i:s', strtotime($transaction_date));

// Insert transaction into the database
$sql = "INSERT INTO tb_cash_transaction (
            native_ccy_amt, idr_amount, sender_for_display, sender_ecobricker,
            datetime_sent_ts, tran_name_desc, currency_code,
            receiving_gea_acct, type_of_transaction, revenue_accounting_type,
            paymt_record_url
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $gobrik_conn->prepare($sql);
if (!$stmt) {
    http_response_code(500); // Internal Server Error
    echo json_encode(['error' => 'Failed to prepare database query: ' . $gobrik_conn->error]);
    exit;
}

$stmt->bind_param(
    'iisssssssss',
    $amount_idr, $amount_idr, $sender, $sender_ecobricker,
    $datetime_sent_ts, $description, $currency_code,
    $receiving_gea_acct, $type_of_transaction, $revenue_type,
    $transaction_image_url
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
