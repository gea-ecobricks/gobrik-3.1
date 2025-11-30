<?php
require_once '../auth/session_start.php';
require_once '../vendor/autoload.php';
require_once '../gobrikconn_env.php';
require_once __DIR__ . '/earthen_helpers.php';

use GuzzleHttp\Client;

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid method']);
    exit();
}

$email_from = 'Earthen <earthen@ecobricks.org>';
$email_subject = 'Writing Earth Right';

$email_html = $_POST['email_html'] ?? '';
$recipient_email = $_POST['email_to'] ?? '';
$subscriber_id = $_POST['subscriber_id'] ?? null;
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
    $send_ok = sendEarthenMailgun($recipient_email, $email_html, $email_from, $email_subject);

    if ($subscriber_id && !$is_test_mode && $send_ok) {
        try {
            ensureMemberHasLabel($subscriber_id, 'sent-001');
        } catch (Exception $labelException) {
            error_log('[EARTHEN] ❌ Failed to add sent label: ' . $labelException->getMessage());
        }
    }

    if (!$send_ok && !$is_test_mode && !empty($recipient_email)) {
        logFailedEmail($recipient_email, 'Mailgun send failure');
    }

    echo json_encode(['success' => $send_ok, 'message' => $send_ok ? '' : 'Sending failed']);
} catch (Exception $e) {
    if (!$is_test_mode && $subscriber_id) {
        error_log('[EARTHEN] ❌ Failed send for ' . $subscriber_id . ': ' . $e->getMessage());
    }
    if (!$is_test_mode && !empty($recipient_email)) {
        logFailedEmail($recipient_email, $e->getMessage());
    }
    echo json_encode(['success' => false, 'message' => 'Server error']);
}

function sendEarthenMailgun(string $to, string $htmlBody, string $email_from, string $email_subject): bool
{
    $client = new Client(['base_uri' => 'https://api.eu.mailgun.net/v3/']);
    $mailgunApiKey = getenv('EARTHEN_MAILGUN_SENDING_KEY');
    $mailgunDomain = 'earthen.ecobricks.org';

    $response = $client->post("https://api.eu.mailgun.net/v3/{$mailgunDomain}/messages", [
        'auth' => ['api', $mailgunApiKey],
        'form_params' => [
            'from' => $email_from,
            'to' => $to,
            'subject' => $email_subject,
            'html' => $htmlBody,
            'text' => strip_tags($htmlBody),
            'o:stop-retrying' => 'yes',
            'o:deliverytime' => gmdate('D, d M Y H:i:s T', strtotime('-1 hour'))
        ]
    ]);

    return $response->getStatusCode() == 200;
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
