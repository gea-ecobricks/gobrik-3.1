<?php
declare(strict_types=1);

require_once '../auth/session_start.php';
require_once '../gobrikconn_env.php';
require_once '../vendor/autoload.php';
require_once '../config/stripe_env.php';

\Stripe\Stripe::setApiKey($stripe_secret_key);

$buwana_id = (int)($_SESSION['buwana_id'] ?? 0);
$pledge_id = isset($_GET['pledge_id']) ? (int)$_GET['pledge_id'] : 0;

if (!$buwana_id || !$pledge_id) {
    http_response_code(400);
    exit('Missing user or pledge.');
}

if (!function_exists('stripe_to_minor_units') || !function_exists('stripe_currency_allowed')) {
    http_response_code(500);
    exit('Stripe config helpers missing.');
}

/**
 * Load pledge + training
 */
$sql = "
    SELECT
        p.pledge_id,
        p.training_id,
        p.buwana_id,
        p.pledged_amount_idr,
        p.display_currency,
        p.display_amount,
        p.pledge_status,
        p.invited_to_pay_at,
        p.payment_due_at,
        p.payment_id,
        t.training_title,
        t.threshold_status,
        t.payment_deadline
    FROM training_pledges_tb p
    INNER JOIN tb_trainings t ON p.training_id = t.training_id
    WHERE p.pledge_id = ?
      AND p.buwana_id = ?
    LIMIT 1
";

$stmt = $gobrik_conn->prepare($sql);
$stmt->bind_param("ii", $pledge_id, $buwana_id);
$stmt->execute();
$result = $stmt->get_result();
$pledge = $result->fetch_assoc();
$stmt->close();

if (!$pledge) {
    http_response_code(404);
    exit('Pledge not found.');
}

$pledge_status = (string)($pledge['pledge_status'] ?? '');

$allow_beta_test_payment =
    ($stripe_mode === 'test' && in_array($pledge_status, ['active', 'invited'], true));

if (!$allow_beta_test_payment && $pledge_status !== 'invited') {
    http_response_code(409);
    exit('This pledge is not currently payable.');
}

$threshold_status = (string)($pledge['threshold_status'] ?? 'open');
$allow_beta_threshold =
    ($stripe_mode === 'test' && in_array($pledge_status, ['active', 'invited'], true));

if (!$allow_beta_threshold && !in_array($threshold_status, ['go', 'payment_open', 'reached'], true)) {
    http_response_code(409);
    exit('This training is not yet open for payment.');
}

$deadline_raw = $pledge['payment_due_at'] ?: $pledge['payment_deadline'] ?: null;
if ($deadline_raw && strtotime((string)$deadline_raw) < time()) {
    http_response_code(410);
    exit('This payment window has expired.');
}

/**
 * Determine charge currency/amount from stored pledge values.
 * Never trust browser-side conversions.
 */
$currency = strtoupper(trim((string)($pledge['display_currency'] ?? 'IDR')));
$display_amount = (float)($pledge['display_amount'] ?? 0);

if ($display_amount <= 0) {
    $currency = 'IDR';
    $display_amount = (float)($pledge['pledged_amount_idr'] ?? 0);
}

if (!stripe_currency_allowed($currency, $stripe_allowed_currencies)) {
    http_response_code(400);
    exit('Unsupported Stripe currency.');
}

$amount_total = stripe_to_minor_units($currency, $display_amount, $stripe_zero_decimal_currencies);

if ($amount_total <= 0) {
    http_response_code(400);
    exit('Invalid payment amount.');
}

/**
 * Fetch customer email if available
 */
$customer_email = null;
$sql_email = "SELECT email_addr FROM tb_ecobrickers WHERE buwana_id = ? LIMIT 1";
$stmt = $gobrik_conn->prepare($sql_email);
$stmt->bind_param("i", $buwana_id);
$stmt->execute();
$stmt->bind_result($email_addr);
if ($stmt->fetch() && !empty($email_addr)) {
    $customer_email = $email_addr;
}
$stmt->close();

/**
 * Payment constants
 */
$app_code = 'gobrik';
$payment_purpose = 'training_pledge';
$status_created = 'created';
$gateway = 'stripe';
$gateway_method = 'checkout_session';
$client_reference = 'training_pledge:' . $pledge_id . ':user:' . $buwana_id;
$idempotency_key = hash('sha256', 'stripe|training_pledge|' . $pledge_id . '|user|' . $buwana_id);

$gateway_payload_json = json_encode([
    'source' => 'training_pledge',
    'pledge_id' => (int)$pledge_id,
    'training_id' => (int)$pledge['training_id'],
    'currency' => $currency,
    'display_amount' => $display_amount,
    'mode' => $stripe_mode
], JSON_UNESCAPED_UNICODE);

$expires_at = null;
if (!empty($pledge['payment_due_at'])) {
    $expires_at = date('Y-m-d H:i:s', strtotime((string)$pledge['payment_due_at']));
} elseif (!empty($pledge['payment_deadline'])) {
    $expires_at = date('Y-m-d H:i:s', strtotime((string)$pledge['payment_deadline']));
}

$gobrik_conn->begin_transaction();

try {
    /**
     * Reuse existing payment by idempotency key if present
     */
    $existing_payment = null;
    $stmt = $gobrik_conn->prepare("
        SELECT payment_id, gateway_ref, status
        FROM payments_tb
        WHERE idempotency_key = ?
        LIMIT 1
    ");
    $stmt->bind_param("s", $idempotency_key);
    $stmt->execute();
    $res = $stmt->get_result();
    $existing_payment = $res->fetch_assoc();
    $stmt->close();

    if ($existing_payment) {
        $payment_id = (int)$existing_payment['payment_id'];
    } else {
        $stmt = $gobrik_conn->prepare("
            INSERT INTO payments_tb
            (
                buwana_id,
                pledge_id,
                app_code,
                payment_purpose,
                currency,
                amount_total,
                amount_tax,
                amount_fee,
                amount_net,
                status,
                gateway,
                gateway_method,
                client_reference,
                idempotency_key,
                payment_requested_at,
                expires_at,
                gateway_payload_json
            )
            VALUES (?, ?, ?, ?, ?, ?, 0, 0, ?, ?, ?, ?, ?, ?, NOW(), ?, ?)
        ");
        $stmt->bind_param(
            "iisssisssssss",
            $buwana_id,
            $pledge_id,
            $app_code,
            $payment_purpose,
            $currency,
            $amount_total,
            $amount_total,
            $status_created,
            $gateway,
            $gateway_method,
            $client_reference,
            $idempotency_key,
            $expires_at,
            $gateway_payload_json
        );
        $stmt->execute();
        $payment_id = (int)$stmt->insert_id;
        $stmt->close();

        /**
         * Link pledge to payment
         */
        $stmt = $gobrik_conn->prepare("
            UPDATE training_pledges_tb
            SET payment_id = ?
            WHERE pledge_id = ?
            LIMIT 1
        ");
        $stmt->bind_param("ii", $payment_id, $pledge_id);
        $stmt->execute();
        $stmt->close();
    }

    $success_url = stripe_success_url((int)$pledge['training_id'], $site_base_url);
    $cancel_url  = stripe_cancel_url((int)$pledge['training_id'], $site_base_url);

    $session = \Stripe\Checkout\Session::create([
        'mode' => 'payment',
        'success_url' => $success_url,
        'cancel_url' => $cancel_url,
        'client_reference_id' => (string)$payment_id,
        'customer_email' => $customer_email,
        'billing_address_collection' => 'auto',
        'line_items' => [[
            'quantity' => 1,
            'price_data' => [
                'currency' => strtolower($currency),
                'unit_amount' => $amount_total,
                'product_data' => [
                    'name' => 'GoBrik Training: ' . (string)$pledge['training_title'],
                    'description' => '3P training pledge payment'
                ]
            ]
        ]],
        'metadata' => [
            'payment_id' => (string)$payment_id,
            'pledge_id' => (string)$pledge_id,
            'training_id' => (string)$pledge['training_id'],
            'buwana_id' => (string)$buwana_id,
            'payment_purpose' => $payment_purpose
        ]
    ], [
        'idempotency_key' => $idempotency_key
    ]);

    $gateway_ref = (string)$session->id;
    $gateway_status = (string)($session->payment_status ?? 'unpaid');
    $session_payload_json = json_encode($session, JSON_UNESCAPED_UNICODE);

    $stmt = $gobrik_conn->prepare("
        UPDATE payments_tb
        SET gateway_ref = ?,
            gateway_status = ?,
            status = 'pending_action',
            gateway_payload_json = ?
        WHERE payment_id = ?
        LIMIT 1
    ");
    $stmt->bind_param("sssi", $gateway_ref, $gateway_status, $session_payload_json, $payment_id);
    $stmt->execute();
    $stmt->close();

    $gobrik_conn->commit();

    header('Location: ' . $session->url, true, 303);
    exit();

} catch (Throwable $e) {
    $gobrik_conn->rollback();
    error_log('Stripe create session failed: ' . $e->getMessage());
    http_response_code(500);
    exit('Unable to create Stripe Checkout session.');
}