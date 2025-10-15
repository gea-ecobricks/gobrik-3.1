<?php
require '../vendor/autoload.php';
require_once '../gobrikconn_env.php';

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

session_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Invalid request method.']);
    exit;
}

$rawInput = file_get_contents('php://input');
$data = json_decode($rawInput, true);
if (!is_array($data)) {
    $data = $_POST;
}

$status = trim((string) ($data['status'] ?? ''));
$serialNo = trim((string) ($data['serial_no'] ?? ''));
$ecobrickerId = isset($data['ecobricker_id']) ? (int) $data['ecobricker_id'] : 0;
$makerId = trim((string) ($data['maker_id'] ?? ''));
$validatorComments = trim((string) ($data['validator_comments'] ?? ''));
$validationNote = trim((string) ($data['validation_note'] ?? ''));
$authenticatorVersion = trim((string) ($data['authenticator_version'] ?? ''));
$validatorName = trim((string) ($data['validator_name'] ?? ''));
$brkValue = isset($data['brk_value']) ? (float) $data['brk_value'] : null;
$brkTranId = isset($data['brk_tran_id']) && $data['brk_tran_id'] !== '' ? (int) $data['brk_tran_id'] : null;
$existingBrkAmt = isset($data['existing_brk_amt']) ? (float) $data['existing_brk_amt'] : null;

if ($ecobrickerId <= 0 && $makerId !== '' && ctype_digit($makerId)) {
    $ecobrickerId = (int) $makerId;
}

if (!isset($_SESSION['buwana_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Authentication required.']);
    exit;
}

$adminCheck = $gobrik_conn->prepare('SELECT user_roles FROM tb_ecobrickers WHERE buwana_id = ?');
if (!$adminCheck) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Unable to verify permissions.']);
    exit;
}
$adminCheck->bind_param('i', $_SESSION['buwana_id']);
$adminCheck->execute();
$adminCheck->bind_result($adminRoles);
$adminCheck->fetch();
$adminCheck->close();

if (stripos($adminRoles ?? '', 'admin') === false) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Admin privileges are required.']);
    exit;
}

if ($status === '' || $serialNo === '' || $ecobrickerId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing required notification details.']);
    exit;
}

$makerStmt = $gobrik_conn->prepare('SELECT first_name, email_addr FROM tb_ecobrickers WHERE ecobricker_id = ? LIMIT 1');
if (!$makerStmt) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Failed to prepare maker lookup.']);
    exit;
}
$makerStmt->bind_param('i', $ecobrickerId);
$makerStmt->execute();
$makerStmt->bind_result($makerFirstName, $makerEmail);
$makerFound = $makerStmt->fetch();
$makerStmt->close();

if (!$makerFound && $makerId !== '') {
    if (ctype_digit($makerId)) {
        $fallbackId = (int) $makerId;
        $makerStmt = $gobrik_conn->prepare('SELECT first_name, email_addr FROM tb_ecobrickers WHERE ecobricker_id = ? LIMIT 1');
        if ($makerStmt) {
            $makerStmt->bind_param('i', $fallbackId);
            $makerStmt->execute();
            $makerStmt->bind_result($makerFirstName, $makerEmail);
            $makerFound = $makerStmt->fetch();
            $makerStmt->close();
        }
    }

    if (!$makerFound) {
        $makerStmt = $gobrik_conn->prepare('SELECT first_name, email_addr FROM tb_ecobrickers WHERE maker_id = ? LIMIT 1');
        if ($makerStmt) {
            $makerStmt->bind_param('s', $makerId);
            $makerStmt->execute();
            $makerStmt->bind_result($makerFirstName, $makerEmail);
            $makerFound = $makerStmt->fetch();
            $makerStmt->close();
        }
    }
}

if (!$makerFound || empty($makerEmail)) {
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'Unable to locate ecobricker contact details.']);
    exit;
}

$makerFirstName = $makerFirstName ?: 'there';
$statusLabel = ucfirst(strtolower($status));
$authenticatorVersion = $authenticatorVersion !== '' ? $authenticatorVersion : '2.0';

$commentsBlock = trim($validatorComments . "\n" . $validationNote);
if ($commentsBlock === '') {
    $commentsBlock = 'No additional comments were provided.';
}

$textBody = "Hi there {$makerFirstName},\n\n" .
    "Heads up!  Your ecobrick has just been validated by a GEA admin.  While we impliment our new v{$authenticatorVersion} Authentication system, ecobricks are being manually reviewed.\n\n" .
    "They left you the follow comments:\n{$commentsBlock}\n\n" .
    "Status: {$statusLabel}\n" .
    "Ecobrick Serial: {$serialNo}";

if ($validatorName !== '') {
    $textBody .= "\nValidator: {$validatorName}";
}
if ($brkValue !== null) {
    $textBody .= "\nBrik Value: {$brkValue}";
}
if ($brkTranId !== null) {
    $textBody .= "\nBrik Tran ID: {$brkTranId}";
}

$subject = "Your ecobrick {$serialNo} has been {$statusLabel}";

$htmlComments = nl2br(htmlspecialchars($commentsBlock, ENT_QUOTES, 'UTF-8'));
$htmlBody = '<p>Hi there ' . htmlspecialchars($makerFirstName, ENT_QUOTES, 'UTF-8') . ',</p>' .
    '<p>Heads up!  Your ecobrick has just been validated by a GEA admin.  While we impliment our new v' .
    htmlspecialchars($authenticatorVersion, ENT_QUOTES, 'UTF-8') . ' Authentication system, ecobricks are being manually reviewed.</p>' .
    '<p>They left you the follow comments:<br>' . $htmlComments . '</p>' .
    '<p><strong>Status:</strong> ' . htmlspecialchars($statusLabel, ENT_QUOTES, 'UTF-8') . '<br>' .
    '<strong>Ecobrick Serial:</strong> ' . htmlspecialchars($serialNo, ENT_QUOTES, 'UTF-8');
if ($validatorName !== '') {
    $htmlBody .= '<br><strong>Validator:</strong> ' . htmlspecialchars($validatorName, ENT_QUOTES, 'UTF-8');
}
if ($brkValue !== null) {
    $htmlBody .= '<br><strong>Brik Value:</strong> ' . htmlspecialchars(number_format($brkValue, 2), ENT_QUOTES, 'UTF-8');
}
if ($brkTranId !== null) {
    $htmlBody .= '<br><strong>Brik Tran ID:</strong> ' . htmlspecialchars((string) $brkTranId, ENT_QUOTES, 'UTF-8');
}
$htmlBody .= '</p>';

$fromAddress = 'noreply@gobrik.com';
$fromName = 'GoBrik Authentication System';

$mailgunSent = false;
$mailgunApiKey = getenv('MAILGUN_API_KEY');
if ($mailgunApiKey) {
    $mailgunClient = new Client(['base_uri' => 'https://api.eu.mailgun.net/v3/']);
    try {
        $mailgunResponse = $mailgunClient->post('mail.gobrik.com/messages', [
            'auth' => ['api', $mailgunApiKey],
            'form_params' => [
                'from' => sprintf('%s <%s>', $fromName, $fromAddress),
                'to' => $makerEmail,
                'subject' => $subject,
                'text' => $textBody,
                'html' => $htmlBody
            ]
        ]);
        $mailgunSent = $mailgunResponse->getStatusCode() === 200;
    } catch (RequestException $e) {
        error_log('Mailgun notification error: ' . $e->getMessage());
        $mailgunSent = false;
    }
}

$smtpSent = false;
if (!$mailgunSent) {
    $mailer = new PHPMailer(true);
    try {
        $mailer->isSMTP();
        $mailer->Host = getenv('SMTP_HOST');
        $mailer->SMTPAuth = true;
        $mailer->Username = getenv('SMTP_USERNAME');
        $mailer->Password = getenv('SMTP_PASSWORD');
        $mailer->Port = getenv('SMTP_PORT');
        $mailer->SMTPSecure = false;
        $mailer->SMTPAutoTLS = false;
        $mailer->setFrom($fromAddress, $fromName);
        $mailer->addAddress($makerEmail, $makerFirstName);
        $mailer->isHTML(true);
        $mailer->Subject = $subject;
        $mailer->Body = $htmlBody;
        $mailer->AltBody = $textBody;
        $mailer->send();
        $smtpSent = true;
    } catch (PHPMailerException $e) {
        error_log('SMTP notification error: ' . $e->getMessage());
        $smtpSent = false;
    }
}

$response = [
    'success' => $mailgunSent || $smtpSent,
    'method' => $mailgunSent ? 'mailgun' : ($smtpSent ? 'smtp' : 'none'),
    'status' => $statusLabel,
    'serial_no' => $serialNo,
    'ecobricker_id' => $ecobrickerId,
    'brk_value' => $brkValue,
    'brk_tran_id' => $brkTranId,
    'existing_brk_amt' => $existingBrkAmt
];

if (!$response['success']) {
    $response['error'] = 'All notification channels failed.';
    http_response_code(500);
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
$gobrik_conn->close();
exit;
