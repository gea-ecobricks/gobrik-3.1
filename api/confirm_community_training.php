<?php
require_once '../auth/session_start.php';
require_once '../earthenAuth_helper.php';

header('Content-Type: application/json');

if (!isLoggedIn() || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['ok' => false, 'error' => 'Not authorized']);
    exit();
}

require_once '../gobrikconn_env.php';
require_once '../buwanaconn_env.php';
require_once '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;

$buwana_id   = (int)$_SESSION['buwana_id'];
$training_id = isset($_POST['training_id']) ? intval($_POST['training_id']) : 0;
$reply_msg   = trim($_POST['reply_message'] ?? '');
$confirm     = isset($_POST['confirm']) && $_POST['confirm'] == '1';

if ($training_id <= 0) {
    echo json_encode(['ok' => false, 'error' => 'Invalid training_id']);
    exit();
}

// Verify caller is a trainer on this training
$stmt_check = $gobrik_conn->prepare(
    "SELECT tt.ecobricker_id FROM tb_training_trainers tt
     INNER JOIN tb_ecobrickers e ON e.ecobricker_id = tt.ecobricker_id
     WHERE tt.training_id = ? AND e.buwana_id = ?
     LIMIT 1"
);
if (!$stmt_check) {
    echo json_encode(['ok' => false, 'error' => 'DB error']);
    exit();
}
$stmt_check->bind_param("ii", $training_id, $buwana_id);
$stmt_check->execute();
$stmt_check->store_result();
if ($stmt_check->num_rows === 0) {
    $stmt_check->close();
    echo json_encode(['ok' => false, 'error' => 'Not authorized for this training']);
    exit();
}
$stmt_check->close();

// Fetch training details
$sql_t = "SELECT training_title, funding_goal_idr, min_participants_required,
                 training_date, training_time_txt, training_location, training_language,
                 threshold_status
          FROM tb_trainings WHERE training_id = ?";
$stmt_t = $gobrik_conn->prepare($sql_t);
$stmt_t->bind_param("i", $training_id);
$stmt_t->execute();
$stmt_t->bind_result(
    $title, $funding_goal, $min_part,
    $t_date, $t_time, $t_location, $t_lang,
    $current_status
);
$stmt_t->fetch();
$stmt_t->close();

if ($current_status !== 'open_request') {
    echo json_encode(['ok' => false, 'error' => 'Training is not in open_request status']);
    exit();
}

// Fetch requester (first reserved registration)
$requester_buwana_id = null;
$requester_email = '';
$requester_name  = '';
$stmt_req = $gobrik_conn->prepare(
    "SELECT r.buwana_id, e.full_name, e.email_addr
     FROM training_registrations_tb r
     LEFT JOIN tb_ecobrickers e ON e.buwana_id = r.buwana_id
     WHERE r.training_id = ? AND r.status = 'reserved'
     ORDER BY r.registration_id ASC LIMIT 1"
);
if ($stmt_req) {
    $stmt_req->bind_param("i", $training_id);
    $stmt_req->execute();
    $stmt_req->bind_result($requester_buwana_id, $requester_name, $requester_email);
    $stmt_req->fetch();
    $stmt_req->close();
}

$now = date('Y-m-d H:i:s');
$date_display = !empty($t_date) ? date('F j, Y', strtotime($t_date)) : '—';
$amount_display = 'IDR ' . number_format((int)$funding_goal);

if ($confirm && $requester_buwana_id) {
    // 1. Update training: threshold reached, make visible
    $stmt_upd = $gobrik_conn->prepare(
        "UPDATE tb_trainings
         SET threshold_status = 'reached',
             course_confirmed_at = ?,
             threshold_reached_at = ?,
             ready_to_show = 1
         WHERE training_id = ?"
    );
    if ($stmt_upd) {
        $stmt_upd->bind_param("ssi", $now, $now, $training_id);
        $stmt_upd->execute();
        $stmt_upd->close();
    }

    // 2. Create pledge record for requester (full funding amount)
    $funding_int = (int)$funding_goal;
    $stmt_pledge = $gobrik_conn->prepare(
        "INSERT INTO training_pledges_tb
             (training_id, buwana_id, pledge_currency, pledged_amount_idr,
              display_currency, display_amount, suggested_amount_idr,
              pledge_status, invited_to_pay_at)
         VALUES (?, ?, 'IDR', ?, 'IDR', ?, ?, 'invited', ?)
         ON DUPLICATE KEY UPDATE
             pledge_status = 'invited',
             invited_to_pay_at = VALUES(invited_to_pay_at),
             pledged_amount_idr = VALUES(pledged_amount_idr)"
    );
    if ($stmt_pledge) {
        $stmt_pledge->bind_param("iiiiis", $training_id, $requester_buwana_id, $funding_int, $funding_int, $funding_int, $now);
        $stmt_pledge->execute();
        $stmt_pledge->close();
    }

    // 3. Update registration status to 'awaiting_payment'
    $stmt_reg_upd = $gobrik_conn->prepare(
        "UPDATE training_registrations_tb
         SET status = 'awaiting_payment'
         WHERE training_id = ? AND buwana_id = ? AND status = 'reserved'"
    );
    if ($stmt_reg_upd) {
        $stmt_reg_upd->bind_param("ii", $training_id, $requester_buwana_id);
        $stmt_reg_upd->execute();
        $stmt_reg_upd->close();
    }
}

function sendCommunityConfirmEmail(string $to, string $subject, string $htmlBody): bool {
    $host   = (string)getenv('SMTP_HOST');
    $port   = (int)getenv('SMTP_PORT');
    $user   = (string)getenv('SMTP_USERNAME');
    $pass   = (string)getenv('SMTP_PASSWORD');
    $secure = strtolower((string)getenv('SMTP_SECURE'));
    if ($host === '' || $port === 0 || $user === '' || $pass === '') {
        return false;
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
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        } elseif ($secure === 'tls') {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        }
        $mail->SMTPAutoTLS = true;
        $mail->setFrom($user, 'GoBrik Training');
        $mail->addAddress($to);
        $mail->addReplyTo($user, 'GoBrik Training');
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $htmlBody;
        $mail->AltBody = strip_tags($htmlBody);
        $mail->send();
        return true;
    } catch (\Throwable $e) {
        error_log('[confirm_community_training] PHPMailer: ' . $e->getMessage());
        return false;
    }
}

$requester_first = explode(' ', trim($requester_name))[0] ?: 'there';
$safe_title  = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
$safe_date   = htmlspecialchars($date_display, ENT_QUOTES, 'UTF-8');
$safe_loc    = htmlspecialchars($t_location ?? '', ENT_QUOTES, 'UTF-8');
$safe_lang   = htmlspecialchars(strtoupper($t_lang ?? ''), ENT_QUOTES, 'UTF-8');
$safe_amount = htmlspecialchars($amount_display, ENT_QUOTES, 'UTF-8');
$safe_msg    = nl2br(htmlspecialchars($reply_msg, ENT_QUOTES, 'UTF-8'));
$payment_link = 'https://gobrik.com/en/community-pledge-pay.php?id=' . (int)$training_id;

if ($confirm) {
    $email_html = '
<!DOCTYPE html><html><body style="font-family:sans-serif;color:#222;max-width:600px;margin:auto;">
<h2 style="color:#1e8c40;">Your Community Training is Confirmed!</h2>
<p>Hello ' . htmlspecialchars($requester_first, ENT_QUOTES, 'UTF-8') . ',</p>
' . (!empty($reply_msg) ? '<p>' . $safe_msg . '</p>' : '') . '
<p>We are delighted to confirm that we will be going ahead with your community training for <strong>' . $safe_title . '</strong> as requested!</p>
<table style="width:100%;border-collapse:collapse;margin:16px 0;">
    <tr><td style="padding:8px 12px;background:#f5f5f5;font-weight:600;width:40%;">Date</td><td style="padding:8px 12px;">' . $safe_date . '</td></tr>
    <tr><td style="padding:8px 12px;background:#f0f0f0;font-weight:600;">Location</td><td style="padding:8px 12px;">' . $safe_loc . '</td></tr>
    <tr><td style="padding:8px 12px;background:#f5f5f5;font-weight:600;">Language</td><td style="padding:8px 12px;">' . $safe_lang . '</td></tr>
    <tr><td style="padding:8px 12px;background:#f0f0f0;font-weight:600;">Full Course Amount</td><td style="padding:8px 12px;">' . $safe_amount . '</td></tr>
</table>
<p>To proceed, please complete your community course payment using the link below:</p>
<p style="text-align:center;margin:24px 0;">
    <a href="' . htmlspecialchars($payment_link, ENT_QUOTES, 'UTF-8') . '"
       style="background:#1a56a0;color:#fff;padding:14px 28px;border-radius:8px;text-decoration:none;font-weight:700;font-size:1.05em;">
        Complete Community Course Payment
    </a>
</p>
<p style="font-size:0.9em;color:#666;">Once payment is received, we will send you the final joining instructions for your community.</p>
<p style="font-size:0.9em;color:#666;">Sent via GoBrik &middot; <a href="https://gobrik.com">gobrik.com</a></p>
</body></html>';
} else {
    $email_html = '
<!DOCTYPE html><html><body style="font-family:sans-serif;color:#222;max-width:600px;margin:auto;">
<h2 style="color:#1e8c40;">Update on Your Community Training Request</h2>
<p>Hello ' . htmlspecialchars($requester_first, ENT_QUOTES, 'UTF-8') . ',</p>
' . (!empty($reply_msg) ? '<p>' . $safe_msg . '</p>' : '<p>Thank you for your community training request for <strong>' . $safe_title . '</strong>. The training team has reviewed your request and will be in touch soon.</p>') . '
<p style="font-size:0.9em;color:#666;">Sent via GoBrik &middot; <a href="https://gobrik.com">gobrik.com</a></p>
</body></html>';
}

$email_subject = $confirm
    ? 'Your Community Training is Confirmed: ' . $title
    : 'Update on Your Community Training Request: ' . $title;

$sent = false;
if (!empty($requester_email)) {
    $sent = sendCommunityConfirmEmail($requester_email, $email_subject, $email_html);
}

echo json_encode([
    'ok'        => true,
    'confirmed' => $confirm,
    'email_sent' => $sent,
]);
