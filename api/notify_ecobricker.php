<?php
require '../vendor/autoload.php';
require_once '../gobrikconn_env.php';

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

// -----------------------------------------------------------------------------
// 1. Bootstrap & Request Parsing
// -----------------------------------------------------------------------------
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
$formattedBrkValue = $brkValue !== null ? number_format($brkValue, 2) : '0.00';
$brkTranId = isset($data['brk_tran_id']) && $data['brk_tran_id'] !== '' ? (int) $data['brk_tran_id'] : null;
$existingBrkAmt = isset($data['existing_brk_amt']) ? (float) $data['existing_brk_amt'] : null;
$ecobrickFullPhotoUrl = trim((string) ($data['ecobrick_full_photo_url'] ?? ''));

if ($ecobrickerId <= 0 && $makerId !== '' && ctype_digit($makerId)) {
    $ecobrickerId = (int) $makerId;
}

// -----------------------------------------------------------------------------
// 2. Permission Verification
// -----------------------------------------------------------------------------
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

// -----------------------------------------------------------------------------
// 3. Ecobricker Lookup
// -----------------------------------------------------------------------------
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

// -----------------------------------------------------------------------------
// 4. Message Composition
// -----------------------------------------------------------------------------
$makerFirstName = $makerFirstName ?: 'there';
$statusNormalized = strtolower($status);
$statusLabel = ucfirst($statusNormalized);
$authenticatorVersion = $authenticatorVersion !== '' ? $authenticatorVersion : '2.0';

$commentsDisplayText = $validatorComments !== ''
    ? $validatorComments
    : 'No validator comments were provided.';
$commentsHtmlSafe = nl2br(htmlspecialchars($commentsDisplayText, ENT_QUOTES, 'UTF-8'));
$commentsTextBlock = "They left you the following comments:\n\"{$commentsDisplayText}\"\n\n";
$commentsHtmlBlock = '<p style="margin-bottom:1.5em;">They left you the following comments:<br><span style="display:inline-block;font-size:1.1em;margin-top:0.35em;">&ldquo;' .
    $commentsHtmlSafe .
    '&rdquo;</span></p>';

$validationNoteDisplay = $validationNote !== ''
    ? $validationNote
    : 'No validation note provided.';
$validatorDisplayName = $validatorName !== '' ? $validatorName : 'Not specified';
$brikTranDisplay = $brkTranId !== null ? (string) $brkTranId : 'Not available';

$infoItems = [
    'Validation note' => $validationNoteDisplay,
    'Status' => $statusLabel,
    'Ecobrick Serial' => $serialNo,
    'Validator' => $validatorDisplayName,
    'Authenticator Version' => $authenticatorVersion,
    'Brik Tran ID' => $brikTranDisplay
];

$infoHtml = '<div class="validation-summary" style="margin:1.5em 0;">';
$infoTextLines = [];
foreach ($infoItems as $label => $value) {
    $infoHtml .= '<p><strong>' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . ':</strong> ' . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . '</p>';
    $infoTextLines[] = $label . ': ' . $value;
}
$infoHtml .= '</div>';
$infoTextBlock = implode("\n", $infoTextLines) . "\n\n";

$imageHtml = '';
if ($ecobrickFullPhotoUrl !== '' && $ecobrickFullPhotoUrl !== 'url missing') {
    $imageHtml = '<p><img src="' . htmlspecialchars($ecobrickFullPhotoUrl, ENT_QUOTES, 'UTF-8') . '" alt="Ecobrick photo" style="max-width:555px;width:100%;height:auto;"></p>';
}

$textIntro = "Hi there {$makerFirstName},\n\n" .
    "Heads up!  Your ecobrick has just been validated by a GEA admin.  While we implement our new v{$authenticatorVersion} Authentication system, ecobricks are being manually reviewed.\n\n";

$htmlIntro = '<p>Hi there ' . htmlspecialchars($makerFirstName, ENT_QUOTES, 'UTF-8') . ',</p>' .
    '<p>Heads up!  Your ecobrick has just been validated by a GEA admin.  While we implement our new v' .
    htmlspecialchars($authenticatorVersion, ENT_QUOTES, 'UTF-8') . ' Authentication system, ecobricks are being manually reviewed.</p>' .
    $commentsHtmlBlock .
    $infoHtml;

$textBodyBase = $textIntro .
    $commentsTextBlock .
    $infoTextBlock;

$additionalText = '';
$additionalHtml = '';

if ($statusNormalized === 'authenticated') {
    $subject = "Your ecobrick {$serialNo} has been authenticated";
    $additionalText .= "With the authentication of your ecobrick, {$formattedBrkValue} brikcoins have been generated on the Brikchain.  The generative block can be viewed at https://ecobricks.org/en/brikchain.php\n\n";
    $additionalHtml .= '<p>With the authentication of your ecobrick, ' . htmlspecialchars($formattedBrkValue, ENT_QUOTES, 'UTF-8') . ' brikcoins have been generated on the Brikchain.  The generative block can be viewed at <a href="https://ecobricks.org/en/brikchain.php">https://ecobricks.org/en/brikchain.php</a></p>';
} elseif ($statusNormalized === 'rejected') {
    $subject = "Update on ecobrick {$serialNo}: rejected";
} else {
    $subject = "Your ecobrick {$serialNo} has been {$statusLabel}";
}

if ($brkValue !== null && $statusNormalized !== 'authenticated') {
    $additionalText .= "Brik Value: {$formattedBrkValue}\n\n";
    $additionalHtml .= '<p><strong>Brik Value:</strong> ' . htmlspecialchars($formattedBrkValue, ENT_QUOTES, 'UTF-8') . '</p>';
}

$signOffText = "Towards a transition in our households, communities and enterprises from plastic to an ever greener harmony with Earth's cycles.\n\n- The GoBrik Team";
$signOffHtml = '<p>Towards a transition in our households, communities and enterprises from plastic to an ever greener harmony with Earth\'s cycles.</p>' .
    '<p>- The GoBrik Team</p>';

$textBody = $textBodyBase . $additionalText . $signOffText;
$htmlBody = $imageHtml . $htmlIntro . $additionalHtml . $signOffHtml;

// -----------------------------------------------------------------------------
// 5. Notification Dispatch
// -----------------------------------------------------------------------------
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
                'bcc' => 'russmaier@gmail.com',
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
        $mailer->addBCC('russmaier@gmail.com');
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

// -----------------------------------------------------------------------------
// 6. API Response
// -----------------------------------------------------------------------------
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
