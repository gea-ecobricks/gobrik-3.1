<?php
require '../vendor/autoload.php';
require_once '../gobrikconn_env.php';

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use PHPMailer\PHPMailer\PHPMailer;

// -----------------------------------------------------------------------------
// 0. Lightweight logging destination (optional but recommended)
// -----------------------------------------------------------------------------
$log_file = __DIR__ . '/../logs/ecobrick-validation-email.log';
ini_set('log_errors', 1);
ini_set('error_log', $log_file);

// -----------------------------------------------------------------------------
// Feature flag: primary sender (Mailgun) on/off
// -----------------------------------------------------------------------------
if (!defined('USE_PRIMARY_EMAIL_SENDER')) {
    define('USE_PRIMARY_EMAIL_SENDER', false); // set true only when Mailgun is stable
}

// -----------------------------------------------------------------------------
// Helpers
// -----------------------------------------------------------------------------
function parseNameEmail(string $input): array {
    $input = trim($input);
    if (preg_match('/^\s*([^<]+)\s*<([^>]+)>\s*$/', $input, $m)) {
        return [trim($m[1]), trim($m[2])];
    }
    return ['', $input];
}

function addReplyToSafe(PHPMailer $mail, string $replyTo, string $fallback): void {
    $replyTo = trim($replyTo);
    if ($replyTo === '') $replyTo = $fallback;

    [$name, $email] = parseNameEmail($replyTo);

    if (!PHPMailer::validateAddress($email)) {
        [$name2, $email2] = parseNameEmail($fallback);
        $email = $email2;
        $name  = $name2;
    }

    if ($name !== '') $mail->addReplyTo($email, $name);
    else $mail->addReplyTo($email);
}

function sendViaMailgun(
    string $to,
    string $subject,
    string $textBody,
    string $htmlBody,
    string $fromName,
    string $fromAddress,
    float $timeoutSec = 1.0
): array {
    $mailgunApiKey = getenv('MAILGUN_API_KEY');
    if (!$mailgunApiKey) {
        return ['sent' => false, 'error' => 'MAILGUN_API_KEY missing'];
    }

    // Your existing domain pattern
    $mailgunDomain = 'mail.gobrik.com';

    $client = new Client([
        'base_uri'        => 'https://api.eu.mailgun.net/v3/',
        'timeout'         => $timeoutSec,
        'connect_timeout' => $timeoutSec,
        'http_errors'     => true,
    ]);

    try {
        $resp = $client->post("{$mailgunDomain}/messages", [
            'auth' => ['api', $mailgunApiKey],
            'form_params' => [
                'from'    => sprintf('%s <%s>', $fromName, $fromAddress),
                'to'      => $to,
                'subject' => $subject,
                'text'    => $textBody,
                'html'    => $htmlBody,
            ]
        ]);

        return ['sent' => ($resp->getStatusCode() === 200), 'error' => ''];
    } catch (RequestException $e) {
        return ['sent' => false, 'error' => $e->getMessage()];
    } catch (\Throwable $e) {
        return ['sent' => false, 'error' => $e->getMessage()];
    }
}

function sendViaSmtp(
    string $toEmail,
    string $toName,
    string $subject,
    string $textBody,
    string $htmlBody,
    string $displayFromAddress, // what you WANT users to see (we'll put it in Reply-To)
    string $displayFromName
): array {
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
            $mail->SMTPSecure = false; // not recommended
        }

        $mail->SMTPAutoTLS = true;

        // IMPORTANT: Use the authenticated mailbox as the actual From to avoid Exim "forged sender" rejections.
        // Your "display" from identity goes into Reply-To instead.
        $mail->setFrom($user, $displayFromName);

        // Preserve intended identity for replies
        addReplyToSafe($mail, sprintf('%s <%s>', $displayFromName, $displayFromAddress), $user);

        $mail->addAddress($toEmail, $toName);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $htmlBody;
        $mail->AltBody = $textBody;

        $mail->send();
        return ['sent' => true, 'error' => ''];
    } catch (\Throwable $e) {
        return ['sent' => false, 'error' => $e->getMessage()];
    }
}

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
$validatorComments = trim((string) ($data['validator_comments'] ?? ''));
$validationNote = trim((string) ($data['validation_note'] ?? ''));
$authenticatorVersion = trim((string) ($data['authenticator_version'] ?? ''));
$validatorName = trim((string) ($data['validator_name'] ?? ''));
$brkValue = isset($data['brk_value']) ? (float) $data['brk_value'] : null;
$formattedBrkValue = $brkValue !== null ? number_format($brkValue, 2) : '0.00';
$brkTranId = isset($data['brk_tran_id']) && $data['brk_tran_id'] !== '' ? (int) $data['brk_tran_id'] : null;
$starRatingRaw = $data['star_rating'] ?? null;
$starRating = null;
if ($starRatingRaw !== null && $starRatingRaw !== '') {
    $starRating = (int) $starRatingRaw;
    if ($starRating < 0) $starRating = 0;
    if ($starRating > 5) $starRating = 5;
}
$existingBrkAmt = isset($data['existing_brk_amt']) ? (float) $data['existing_brk_amt'] : null;
$ecobrickFullPhotoUrl = trim((string) ($data['ecobrick_full_photo_url'] ?? ''));

// -----------------------------------------------------------------------------
// 2. Permission Verification
// -----------------------------------------------------------------------------
if (!isset($_SESSION['buwana_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Authentication required.']);
    exit;
}

$adminCheck = $gobrik_conn->prepare('SELECT user_roles, user_capabilities FROM tb_ecobrickers WHERE buwana_id = ?');
if (!$adminCheck) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Unable to verify permissions.']);
    exit;
}
$adminCheck->bind_param('i', $_SESSION['buwana_id']);
$adminCheck->execute();
$adminCheck->bind_result($adminRoles, $reviewerCapabilities);
$adminCheck->fetch();
$adminCheck->close();

if (stripos($adminRoles ?? '', 'admin') === false && stripos($reviewerCapabilities ?? '', 'review ecobricks') === false) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Reviewer privileges are required.']);
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

if (!$makerFound || empty($makerEmail)) {
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'Unable to locate ecobricker contact details.']);
    exit;
}

// -----------------------------------------------------------------------------
// 4. Message Composition (UNCHANGED from your code below)
// -----------------------------------------------------------------------------
$makerFirstName = $makerFirstName ?: 'there';
$statusNormalized = strtolower($status);
$statusLabel = ucfirst($statusNormalized);
$authenticatorVersion = $authenticatorVersion !== '' ? $authenticatorVersion : '2.0';

$commentsDisplayText = $validatorComments !== ''
    ? $validatorComments
    : 'No validator comments were provided.';
$commentsHtmlSafe = nl2br(htmlspecialchars($commentsDisplayText, ENT_QUOTES, 'UTF-8'));
$commentsTextBlock = "They left you the following comments:\n\"{$commentsDisplayText}\"\n";

$starRatingDisplay = null;
if ($starRating !== null) {
    $starSymbols = [];
    for ($i = 1; $i <= 5; $i++) {
        $starSymbols[] = $i <= $starRating ? '★' : '☆';
    }
    $starRatingDisplay = implode(' ', $starSymbols);
    $commentsTextBlock .= "Their rating: {$starRatingDisplay}\n";
}
$commentsTextBlock .= "\n";

$commentsHtmlBlock = '<div class="validator-comments" style="margin-bottom:1.5em;">'
    . '<p style="margin:0 0 0.5em;">They left you the following comments:</p>'
    . '<p style="margin:0;font-size:1.1em;">&ldquo;' . $commentsHtmlSafe . '&rdquo;</p>';

if ($starRatingDisplay !== null) {
    $commentsHtmlBlock .= '<p style="margin:0.75em 0 0;font-size:1.1em;">Their rating: '
        . '<span style="display:inline-block;font-size:1.25em;letter-spacing:0.1em;">'
        . htmlspecialchars($starRatingDisplay, ENT_QUOTES, 'UTF-8')
        . '</span></p>';
}
$commentsHtmlBlock .= '</div>';

$validationNoteDisplay = $validationNote !== '' ? $validationNote : 'No validation note provided.';
$validatorDisplayName = $validatorName !== '' ? $validatorName : 'Not specified';
$brikTranDisplay = $brkTranId !== null ? (string) $brkTranId : 'Not available';

$infoItems = [
    'Ecobrick Serial' => $serialNo,
    'Status' => $statusLabel,
    'Authenticator Version' => $authenticatorVersion,
    'Validator' => $validatorDisplayName,
    'Brik Tran ID' => $brikTranDisplay,
    'Note' => $validationNoteDisplay
];

$infoHtml = '<div class="validation-summary" style="margin:1.5em 0;">';
$infoTextLines = [];
foreach ($infoItems as $label => $value) {
    $infoHtml .= '<div style="margin:0;line-height:1.4;"><strong>' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . ':</strong> ' . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . '</div>';
    $infoTextLines[] = $label . ': ' . $value;
}
$infoHtml .= '</div>';
$infoTextBlock = implode("\n", $infoTextLines) . "\n\n";

$imageHtml = '';
if ($ecobrickFullPhotoUrl !== '' && $ecobrickFullPhotoUrl !== 'url missing') {
    $normalizedPhotoUrl = preg_replace('#(\.\./|\.\/)#', '', $ecobrickFullPhotoUrl);
    if (strpos($normalizedPhotoUrl, '/') !== 0) {
        $normalizedPhotoUrl = '/' . $normalizedPhotoUrl;
    }
    if (stripos($normalizedPhotoUrl, 'https://gobrik.com') !== 0) {
        $normalizedPhotoUrl = 'https://gobrik.com' . $normalizedPhotoUrl;
    }
    $imageHtml = '<p><img src="' . htmlspecialchars($normalizedPhotoUrl, ENT_QUOTES, 'UTF-8') . '" alt="Ecobrick photo" style="max-width:467px;width:100%;height:auto;"></p>';
}

$textIntro = "Hi there {$makerFirstName},\n\n" .
    "Heads up!  Your ecobrick has just been validated by a member of the GEA review team.  While we implement our new v{$authenticatorVersion} Authentication system, ecobricks are being manually reviewed.\n\n";

$htmlIntro = '<p>Hi there ' . htmlspecialchars($makerFirstName, ENT_QUOTES, 'UTF-8') . ',</p>' .
    '<p>Heads up!  Your ecobrick has just been validated by a member of the GEA review team.  While we implement our new v' .
    htmlspecialchars($authenticatorVersion, ENT_QUOTES, 'UTF-8') . ' Authentication system, ecobricks are being manually reviewed.</p>' .
    $commentsHtmlBlock .
    $infoHtml;

$textBodyBase = $textIntro . $commentsTextBlock . $infoTextBlock;

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
// 5. Notification Dispatch (Mailgun optional -> SMTP fallback)
// -----------------------------------------------------------------------------
$displayFromAddress = 'noreply@gobrik.com';
$displayFromName = 'GoBrik Authentication System';

$mailgunSent = false;
$mailgunErr = '';

if (USE_PRIMARY_EMAIL_SENDER === true) {
    $mg = sendViaMailgun($makerEmail, $subject, $textBody, $htmlBody, $displayFromName, $displayFromAddress, 1.0);
    $mailgunSent = (bool)$mg['sent'];
    $mailgunErr = (string)($mg['error'] ?? '');
    if ($mailgunSent) {
        error_log("ecobrick-notify | sent | method=mailgun | to={$makerEmail}");
    } else if ($mailgunErr !== '') {
        error_log("ecobrick-notify | mailgun failed | to={$makerEmail} | err={$mailgunErr}");
    }
} else {
    // Mailgun disabled
    // (no log needed unless you want it)
}

$smtpSent = false;
$smtpErr = '';

if (!$mailgunSent) {
    $smtp = sendViaSmtp($makerEmail, $makerFirstName, $subject, $textBody, $htmlBody, $displayFromAddress, $displayFromName);
    $smtpSent = (bool)$smtp['sent'];
    $smtpErr = (string)($smtp['error'] ?? '');

    if ($smtpSent) {
        error_log("ecobrick-notify | sent | method=smtp | to={$makerEmail}");
    } else {
        error_log("ecobrick-notify | smtp failed | to={$makerEmail} | err={$smtpErr}");
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
