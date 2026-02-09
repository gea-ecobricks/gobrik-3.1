<?php
require_once '../auth/session_start.php';
require_once '../vendor/autoload.php';
require_once '../gobrikconn_env.php';
require_once __DIR__ . '/earthen_helpers.php';

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use PHPMailer\PHPMailer\PHPMailer;

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid method']);
    exit();
}

/**
 * Feature flag: primary sender (Mailgun) on/off.
 * For now, you want this OFF.
 */
if (!defined('USE_PRIMARY_EMAIL_SENDER')) {
    define('USE_PRIMARY_EMAIL_SENDER', false);
}

function buildSentLabel(?string $newsletterId): string
{
    $safeId = preg_replace('/[^0-9A-Za-z_-]/', '', (string) $newsletterId);
    if ($safeId === '') return 'sent-unknown';
    return 'sent-' . $safeId;
}

function loadNewsletterMetadata(?string $newsletterId): array
{
    $safeId = preg_replace('/[^0-9A-Za-z_-]/', '', (string) $newsletterId);
    $path = __DIR__ . "/newsletters/{$safeId}.php";

    if (!is_file($path)) {
        return ['from' => '', 'reply_to' => '', 'subject' => ''];
    }

    $email_from = '';
    $email_reply_to = '';
    $email_subject = '';
    $email_template = '';
    $recipient_uuid = null;
    $recipient_email = null;

    include $path;

    return [
        'from' => $email_from,
        'reply_to' => $email_reply_to ?: $email_from,
        'subject' => $email_subject,
    ];
}

$selected_newsletter = $_POST['newsletter_choice'] ?? null;
$newsletter_meta = loadNewsletterMetadata($selected_newsletter);

$email_from = $newsletter_meta['from'] ?: 'Earthen <earthen@ecobricks.org>';
$email_reply_to = $newsletter_meta['reply_to'] ?: $email_from;
$email_subject = $newsletter_meta['subject'] ?: 'Writing Earth Right';
$sent_label = buildSentLabel($selected_newsletter ?? '001');

$email_html = $_POST['email_html'] ?? '';
$recipient_email = $_POST['email_to'] ?? '';
$subscriber_id = $_POST['subscriber_id'] ?? null;
$batch_row_id = isset($_POST['batch_row_id']) ? (int) $_POST['batch_row_id'] : null;
$is_test_mode = isset($_POST['test_mode']) && $_POST['test_mode'] == '1';

$recipient_uuid = null;

try {
    if ($subscriber_id) {
        $member_details = fetchGhostMembers([
            'limit' => 1,
            'filter' => "id:$subscriber_id",
        ]);

        if (!empty($member_details)) {
            $recipient_uuid = $member_details[0]['uuid'] ?? null;
            $recipient_email = $recipient_email ?: ($member_details[0]['email'] ?? '');
        }
    }
} catch (Exception $memberException) {
    error_log('[EARTHEN] Unable to fetch member details: ' . $memberException->getMessage());
}

$email_html = personalizeEmailHtml($email_html, $recipient_uuid, $recipient_email);

if (empty($email_html) || empty($recipient_email) || (!$subscriber_id && !$is_test_mode)) {
    echo json_encode(['success' => false, 'message' => 'Missing recipient or content']);
    exit();
}

try {

    // ------------------------------------------------------------
    // SEND: Mailgun (optional) -> SMTP fallback (default)
    // ------------------------------------------------------------
    $send_ok = false;
    $send_method = 'none';
    $send_error = '';

    if (USE_PRIMARY_EMAIL_SENDER === true) {
        try {
            $send_ok = sendEarthenMailgun($recipient_email, $email_html, $email_from, $email_subject, $email_reply_to);
            $send_method = 'mailgun';
            if (!$send_ok) $send_error = 'Mailgun send returned false';
        } catch (Exception $mgEx) {
            $send_ok = false;
            $send_method = 'mailgun';
            $send_error = $mgEx->getMessage();
        }
    } else {
        // Primary disabled: skip Mailgun entirely
        $send_ok = false;
        $send_method = 'mailgun-disabled';
    }

    if (!$send_ok) {
        $smtp = sendEarthenSMTP($recipient_email, $email_html, $email_from, $email_subject, $email_reply_to);
        $send_ok = $smtp['sent'];
        $send_method = 'smtp';
        $send_error = $smtp['error'] ?? '';
    }

    if ($send_ok) {
        error_log("[EARTHEN] ✅ Sent | method={$send_method} | to={$recipient_email} | newsletter={$selected_newsletter}");

        if ($subscriber_id && !$is_test_mode) {
            try {
                ensureMemberHasLabel($subscriber_id, $sent_label);
            } catch (Exception $labelException) {
                error_log('[EARTHEN] ❌ Failed to add sent label: ' . $labelException->getMessage());
            }
        }

        if ($batch_row_id && !$is_test_mode) {
            $status_stmt = $gobrik_conn->prepare(
                'UPDATE earthen_send_batch_tb SET test_sent = 1, test_sent_date_time = NOW(), processing = 0, updated_at = NOW() WHERE id = ?'
            );
            if ($status_stmt) {
                $status_stmt->bind_param('i', $batch_row_id);
                $status_stmt->execute();
                $status_stmt->close();
            }
        }

        echo json_encode(['success' => true, 'message' => '']);
        exit();
    }

    // If we got here: sending failed
    error_log("[EARTHEN] ❌ Send failed | method={$send_method} | to={$recipient_email} | err={$send_error}");

    if ($subscriber_id && !$is_test_mode) {
        try {
            // Keeping your existing log table function (even though it's named mailgun events)
            logFailedEmail($recipient_email, "Send failure ({$send_method}): {$send_error}");
        } catch (Exception $logEx) {
            error_log('[EARTHEN] ❌ Failed to log email failure: ' . $logEx->getMessage());
        }
    } elseif (!$is_test_mode && !empty($recipient_email)) {
        logFailedEmail($recipient_email, "Send failure ({$send_method}): {$send_error}");
    }

    if ($batch_row_id && !$is_test_mode) {
        $reason = "Send failure ({$send_method}): " . ($send_error ?: 'unknown');
        $error_stmt = $gobrik_conn->prepare(
            'UPDATE earthen_send_batch_tb SET processing = 2, last_error = ?, updated_at = NOW() WHERE id = ?'
        );
        if ($error_stmt) {
            $error_stmt->bind_param('si', $reason, $batch_row_id);
            $error_stmt->execute();
            $error_stmt->close();
        }
    }

    echo json_encode(['success' => false, 'message' => 'Sending failed']);
    exit();

} catch (Exception $e) {
    if (!$is_test_mode && $subscriber_id) {
        error_log('[EARTHEN] ❌ Failed send for ' . $subscriber_id . ': ' . $e->getMessage());
    }
    if (!$is_test_mode && !empty($recipient_email)) {
        logFailedEmail($recipient_email, $e->getMessage());
    }
    echo json_encode(['success' => false, 'message' => 'Server error']);
    exit();
}

/**
 * Primary sender (Mailgun) — kept intact, but will be skipped when flag is false.
 */
function sendEarthenMailgun(string $to, string $htmlBody, string $email_from, string $email_subject, string $email_reply_to): bool
{
    $client = new Client(['base_uri' => 'https://api.eu.mailgun.net/v3/']);
    $mailgunApiKey = getenv('EARTHEN_MAILGUN_SENDING_KEY');
    $mailgunDomain = 'earthen.ecobricks.org';

    $response = $client->post("https://api.eu.mailgun.net/v3/{$mailgunDomain}/messages", [
        'auth' => ['api', $mailgunApiKey],
        'form_params' => [
            'from' => $email_from,
            'to' => $to,
            'h:Reply-To' => $email_reply_to ?: $email_from,
            'subject' => $email_subject,
            'html' => $htmlBody,
            'text' => strip_tags($htmlBody),
            'o:stop-retrying' => 'yes',
            'o:deliverytime' => gmdate('D, d M Y H:i:s T', strtotime('-1 hour'))
        ]
    ]);

    return $response->getStatusCode() == 200;
}

/**
 * SMTP sender (PHPMailer)
 * Uses same env keys as Buwana:
 *   SMTP_HOST, SMTP_PORT, SMTP_USERNAME, SMTP_PASSWORD, SMTP_SECURE (ssl|tls)
 */
function sendEarthenSMTP(string $to, string $htmlBody, string $email_from, string $email_subject, string $email_reply_to): array
{
    $host   = (string)getenv('SMTP_HOST');
    $port   = (int)getenv('SMTP_PORT');
    $user   = (string)getenv('SMTP_USERNAME');
    $pass   = (string)getenv('SMTP_PASSWORD');
    $secure = strtolower((string)getenv('SMTP_SECURE')); // ssl|tls

    if ($host === '' || $port === 0 || $user === '' || $pass === '') {
        return ['sent' => false, 'error' => 'Missing SMTP env vars (host/port/user/pass)'];
    }

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = $host;
        $mail->SMTPAuth = true;
        $mail->Username = $user;
        $mail->Password = $pass;
        $mail->Port = $port;

        if ($secure === 'ssl') {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;      // 465
        } elseif ($secure === 'tls') {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;   // 587
        } else {
            // not recommended, but allowed
            $mail->SMTPSecure = false;
        }

        $mail->SMTPAutoTLS = true;

        // Use your chosen from header; if it causes problems, you can force From to the SMTP user instead.
        $mail->setFrom($user, $extractName = extractDisplayName($email_from) ?: 'Earthen');
        $mail->addAddress($to);

        // Reply-To
        $mail->addReplyTo($email_reply_to ?: $email_from);

        $mail->isHTML(true);
        $mail->Subject = $email_subject;
        $mail->Body = $htmlBody;
        $mail->AltBody = strip_tags($htmlBody);

        $mail->send();
        return ['sent' => true, 'error' => ''];

    } catch (\Throwable $e) {
        return ['sent' => false, 'error' => $e->getMessage()];
    }
}

/**
 * Extract display name from a "Name <email@domain>" string.
 * Returns "" if not present.
 */
function extractDisplayName(string $from): string
{
    if (preg_match('/^\s*([^<]+)\s*<[^>]+>\s*$/', $from, $m)) {
        return trim($m[1]);
    }
    return '';
}

function resolveMemberIdByEmail(string $email): ?int
{
    global $gobrik_conn;

    if (!isset($gobrik_conn)) {
        return null;
    }

    $stmt = $gobrik_conn->prepare('SELECT id FROM earthen_members_tb WHERE email = ? LIMIT 1');

    if (!$stmt) {
        error_log('[EARTHEN] Failed to prepare member lookup: ' . $gobrik_conn->error);
        return null;
    }

    $stmt->bind_param('s', $email);
    $stmt->execute();
    $stmt->bind_result($member_id);
    $found = $stmt->fetch();
    $stmt->close();

    return $found ? (int) $member_id : null;
}

function logFailedEmail(string $email, string $reason): void
{
    global $gobrik_conn;

    if (!isset($gobrik_conn)) {
        error_log('[EARTHEN] No DB connection available to log failed email.');
        return;
    }

    $member_id = resolveMemberIdByEmail($email);
    $event_timestamp = date('Y-m-d H:i:s');

    $stmt = $gobrik_conn->prepare(
        "INSERT INTO earthen_mailgun_events_tb (
            member_id,
            recipient_email,
            event_type,
            event_timestamp,
            severity,
            reason,
            error_message
        ) VALUES (?, ?, 'send_error', ?, 'temporary', 'send_error', ?)"
    );

    if (!$stmt) {
        error_log('[EARTHEN] Failed to prepare earthen_mailgun_events_tb insert: ' . $gobrik_conn->error);
        return;
    }

    $stmt->bind_param('isss', $member_id, $email, $event_timestamp, $reason);

    if (!$stmt->execute()) {
        error_log('[EARTHEN] Failed to log email failure: ' . $stmt->error);
    }

    $stmt->close();
}
