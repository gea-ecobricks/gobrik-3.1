<?php
declare(strict_types=1);

require_once '../gobrikconn_env.php';
require_once '../vendor/autoload.php';
require_once '../config/stripe_env.php';

\Stripe\Stripe::setApiKey($stripe_secret_key);

$payload = @file_get_contents('php://input');
$sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';

if (!$payload || !$sig_header) {
    http_response_code(400);
    exit('Missing webhook payload or signature.');
}

try {
    $event = \Stripe\Webhook::constructEvent(
        $payload,
        $sig_header,
        $stripe_webhook_secret
    );
} catch (\UnexpectedValueException $e) {
    error_log('Stripe webhook invalid payload: ' . $e->getMessage());
    http_response_code(400);
    exit('Invalid payload.');
} catch (\Stripe\Exception\SignatureVerificationException $e) {
    error_log('Stripe webhook invalid signature: ' . $e->getMessage());
    http_response_code(400);
    exit('Invalid signature.');
}

$event_id = (string)($event->id ?? '');
$event_type = (string)($event->type ?? 'unknown');
$object = $event->data->object ?? null;

$payment_id = 0;
$gateway_ref = '';

if ($object) {
    $gateway_ref = (string)($object->id ?? '');

    if (!empty($object->metadata->payment_id)) {
        $payment_id = (int)$object->metadata->payment_id;
    } elseif (!empty($object->client_reference_id)) {
        $payment_id = (int)$object->client_reference_id;
    }
}

/**
 * Log every event.
 * Assumes payment_events_tb exists as designed.
 */
$verified = 1;
$payload_json = $payload;

$stmt = $gobrik_conn->prepare("
    INSERT IGNORE INTO payment_events_tb
    (payment_id, gateway, gateway_event_id, gateway_ref, event_type, verified, payload_json)
    VALUES (?, 'stripe', ?, ?, ?, ?, ?)
");
$stmt->bind_param("isssis", $payment_id, $event_id, $gateway_ref, $event_type, $verified, $payload_json);
$stmt->execute();
$stmt->close();

/**
 * Handle successful completed session
 */
if ($event_type === 'checkout.session.completed' && $payment_id > 0) {
    $gobrik_conn->begin_transaction();

    try {
        $payment_status = (string)($object->payment_status ?? 'paid');

        $stmt = $gobrik_conn->prepare("
            UPDATE payments_tb
            SET status = 'paid',
                gateway_status = ?,
                paid_at = NOW()
            WHERE payment_id = ?
            LIMIT 1
        ");
        $stmt->bind_param("si", $payment_status, $payment_id);
        $stmt->execute();
        $stmt->close();

        $pledge_id = 0;
        $stmt = $gobrik_conn->prepare("
            SELECT pledge_id
            FROM payments_tb
            WHERE payment_id = ?
            LIMIT 1
        ");
        $stmt->bind_param("i", $payment_id);
        $stmt->execute();
        $stmt->bind_result($pledge_id);
        $stmt->fetch();
        $stmt->close();

        if ($pledge_id > 0) {
            $stmt = $gobrik_conn->prepare("
                UPDATE training_pledges_tb
                SET pledge_status = 'paid',
                    paid_at = NOW(),
                    converted_payment_id = ?,
                    payment_id = ?
                WHERE pledge_id = ?
                LIMIT 1
            ");
            $stmt->bind_param("iii", $payment_id, $payment_id, $pledge_id);
            $stmt->execute();
            $stmt->close();
        }

        $gobrik_conn->commit();
    } catch (Throwable $e) {
        $gobrik_conn->rollback();
        error_log('Stripe webhook processing failed: ' . $e->getMessage());
        http_response_code(500);
        exit('Webhook processing failed.');
    }
}

/**
 * Handle expired session
 */
if ($event_type === 'checkout.session.expired' && $payment_id > 0) {
    $stmt = $gobrik_conn->prepare("
        UPDATE payments_tb
        SET status = 'expired',
            gateway_status = 'expired'
        WHERE payment_id = ?
          AND status NOT IN ('paid', 'refunded', 'partially_refunded')
        LIMIT 1
    ");
    $stmt->bind_param("i", $payment_id);
    $stmt->execute();
    $stmt->close();
}

/**
 * Handle async failed session
 */
if ($event_type === 'checkout.session.async_payment_failed' && $payment_id > 0) {
    $stmt = $gobrik_conn->prepare("
        UPDATE payments_tb
        SET status = 'failed',
            gateway_status = 'async_payment_failed'
        WHERE payment_id = ?
          AND status NOT IN ('paid', 'refunded', 'partially_refunded')
        LIMIT 1
    ");
    $stmt->bind_param("i", $payment_id);
    $stmt->execute();
    $stmt->close();
}

/**
 * Handle async succeeded session
 */
if ($event_type === 'checkout.session.async_payment_succeeded' && $payment_id > 0) {
    $gobrik_conn->begin_transaction();

    try {
        $stmt = $gobrik_conn->prepare("
            UPDATE payments_tb
            SET status = 'paid',
                gateway_status = 'paid',
                paid_at = NOW()
            WHERE payment_id = ?
            LIMIT 1
        ");
        $stmt->bind_param("i", $payment_id);
        $stmt->execute();
        $stmt->close();

        $pledge_id = 0;
        $stmt = $gobrik_conn->prepare("
            SELECT pledge_id
            FROM payments_tb
            WHERE payment_id = ?
            LIMIT 1
        ");
        $stmt->bind_param("i", $payment_id);
        $stmt->execute();
        $stmt->bind_result($pledge_id);
        $stmt->fetch();
        $stmt->close();

        if ($pledge_id > 0) {
            $stmt = $gobrik_conn->prepare("
                UPDATE training_pledges_tb
                SET pledge_status = 'paid',
                    paid_at = NOW(),
                    converted_payment_id = ?,
                    payment_id = ?
                WHERE pledge_id = ?
                LIMIT 1
            ");
            $stmt->bind_param("iii", $payment_id, $payment_id, $pledge_id);
            $stmt->execute();
            $stmt->close();
        }

        $gobrik_conn->commit();
    } catch (Throwable $e) {
        $gobrik_conn->rollback();
        error_log('Stripe webhook async success failed: ' . $e->getMessage());
        http_response_code(500);
        exit('Webhook processing failed.');
    }
}

http_response_code(200);
echo 'ok';