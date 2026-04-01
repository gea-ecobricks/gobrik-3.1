<?php
require_once '../earthenAuth_helper.php';
require_once '../auth/session_start.php';

if (!isLoggedIn()) {
    header('Location: /en/login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /en/courses.php');
    exit();
}

require_once '../gobrikconn_env.php';
require_once '../buwanaconn_env.php';
require_once '../vendor/autoload.php';

require_once __DIR__ . '/../vendor/autoload.php';

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use PHPMailer\PHPMailer\PHPMailer;

$buwana_id = (int)$_SESSION['buwana_id'];

// Read and sanitize POST data
$source_training_id = isset($_POST['source_training_id']) ? intval($_POST['source_training_id']) : 0;
$proposed_date      = trim($_POST['proposed_date']      ?? '');
$time_txt           = trim($_POST['time_txt']           ?? '');
$proposed_language  = preg_replace('/[^a-z]/', '', strtolower(trim($_POST['proposed_language'] ?? 'en')));
$proposed_location  = trim($_POST['proposed_location']  ?? '');
$community_id       = isset($_POST['community_id']) && $_POST['community_id'] !== '' ? intval($_POST['community_id']) : null;
$community_name     = trim($_POST['community_search']   ?? '');

if ($source_training_id <= 0 || empty($proposed_date) || empty($proposed_location)) {
    header("Location: /en/community-3p.php?id=$source_training_id&error=missing_fields");
    exit();
}

// Validate and parse date
$proposed_dt = DateTime::createFromFormat('Y-m-d\TH:i', $proposed_date);
if (!$proposed_dt) {
    $proposed_dt = new DateTime($proposed_date);
}
$proposed_date_sql = $proposed_dt ? $proposed_dt->format('Y-m-d H:i:s') : null;

if (!$proposed_date_sql) {
    header("Location: /en/community-3p.php?id=$source_training_id&error=invalid_date");
    exit();
}

// Fetch the requester's GoBrik details
$requester_ecobricker_id = null;
$requester_email = '';
$requester_name = '';
$stmt_req = $gobrik_conn->prepare("SELECT ecobricker_id, email_addr, full_name FROM tb_ecobrickers WHERE buwana_id = ?");
$stmt_req->bind_param("i", $buwana_id);
$stmt_req->execute();
$stmt_req->bind_result($requester_ecobricker_id, $requester_email, $requester_name);
$stmt_req->fetch();
$stmt_req->close();

// Fetch source training
$sql_src = "SELECT * FROM tb_trainings WHERE training_id = ? AND ready_to_show = 1 AND payment_mode = 'pledge_threshold'";
$stmt_src = $gobrik_conn->prepare($sql_src);
$stmt_src->bind_param("i", $source_training_id);
$stmt_src->execute();
$result_src = $stmt_src->get_result();
if ($result_src->num_rows === 0) {
    header("Location: /en/community-3p.php?id=$source_training_id&error=not_found");
    exit();
}
$src = $result_src->fetch_assoc();
$stmt_src->close();

$trainer_contact_email = $src['trainer_contact_email'] ?? '';

// Look up community name from Buwana if ID provided
$resolved_community_name = $community_name;
if ($community_id) {
    $stmt_com = $buwana_conn->prepare("SELECT com_name FROM communities_tb WHERE community_id = ?");
    if ($stmt_com) {
        $stmt_com->bind_param("i", $community_id);
        $stmt_com->execute();
        $stmt_com->bind_result($db_com_name);
        if ($stmt_com->fetch()) {
            $resolved_community_name = $db_com_name ?? $community_name;
        }
        $stmt_com->close();
    }
}

// Insert the new community training request
$now = date('Y-m-d H:i:s');

$insert_sql = "INSERT INTO tb_trainings (
    training_title, training_subtitle, training_date, training_time_txt,
    training_language, training_location, training_type, lead_trainer,
    training_logged, ready_to_show,
    featured_description, training_agenda,
    payment_mode, base_currency, default_price_idr,
    funding_goal_idr, min_participants_required,
    threshold_status,
    no_participants,
    trainer_contact_email,
    community_id,
    country_id,
    registration_scope,
    feature_photo1_main, feature_photo1_tmb,
    feature_photo2_main, feature_photo2_tmb,
    feature_photo3_main, feature_photo3_tmb,
    training_photo0_main, training_photo0_tmb,
    show_signup_count, show_report,
    allow_overpledge, auto_confirm_threshold,
    min_pledge_idr
) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";

$stmt_ins = $gobrik_conn->prepare($insert_sql);
if (!$stmt_ins) {
    error_log('community_training_request_process: prepare failed: ' . $gobrik_conn->error);
    header("Location: /en/community-3p.php?id=$source_training_id&error=db");
    exit();
}

$new_title     = $src['training_title'];
$new_subtitle  = $src['training_subtitle'];
$new_type      = $src['training_type'] ?? '';
$new_trainer   = $src['lead_trainer'] ?? '';
$new_desc      = $src['featured_description'] ?? '';
$new_agenda    = $src['training_agenda'] ?? '';
$new_pay_mode  = 'pledge_threshold';
$new_cur       = $src['base_currency'] ?? 'IDR';
$new_price     = intval($src['default_price_idr'] ?? 0);
$new_goal      = intval($src['funding_goal_idr'] ?? 0);
$new_min_part  = intval($src['min_participants_required'] ?? 0);
$new_status    = 'open_request';
$new_no_part   = intval($src['no_participants'] ?? $new_min_part);
$new_trainer_email = $trainer_contact_email;
$new_scope     = $src['registration_scope'] ?? 'anyone';
$f1m = $src['feature_photo1_main'] ?? '';
$f1t = $src['feature_photo1_tmb'] ?? '';
$f2m = $src['feature_photo2_main'] ?? '';
$f2t = $src['feature_photo2_tmb'] ?? '';
$f3m = $src['feature_photo3_main'] ?? '';
$f3t = $src['feature_photo3_tmb'] ?? '';
$p0m = $src['training_photo0_main'] ?? '';
$p0t = $src['training_photo0_tmb'] ?? '';
$show_signup   = 0;
$show_report   = 0;
$allow_over    = 1;
$auto_confirm  = 1;
$min_pledge    = intval($src['min_pledge_idr'] ?? 0);
$src_country   = $src['country_id'] ? intval($src['country_id']) : null;
$ready = 0;

$stmt_ins->bind_param(
    "sssssssssissssiiisisiisssssssssiiiii",
    $new_title,
    $new_subtitle,
    $proposed_date_sql,
    $time_txt,
    $proposed_language,
    $proposed_location,
    $new_type,
    $new_trainer,
    $now,
    $ready,
    $new_desc,
    $new_agenda,
    $new_pay_mode,
    $new_cur,
    $new_price,
    $new_goal,
    $new_min_part,
    $new_status,
    $new_no_part,
    $new_trainer_email,
    $community_id,
    $src_country,
    $new_scope,
    $f1m, $f1t,
    $f2m, $f2t,
    $f3m, $f3t,
    $p0m, $p0t,
    $show_signup,
    $show_report,
    $allow_over,
    $auto_confirm,
    $min_pledge
);

if (!$stmt_ins->execute()) {
    error_log('community_training_request_process: insert failed: ' . $stmt_ins->error);
    $stmt_ins->close();
    header("Location: /en/community-3p.php?id=$source_training_id&error=db");
    exit();
}
$new_training_id = $gobrik_conn->insert_id;
$stmt_ins->close();

// Copy trainers from source training to new training
$stmt_tr_fetch = $gobrik_conn->prepare("SELECT ecobricker_id FROM tb_training_trainers WHERE training_id = ?");
if ($stmt_tr_fetch) {
    $stmt_tr_fetch->bind_param("i", $source_training_id);
    $stmt_tr_fetch->execute();
    $result_tr = $stmt_tr_fetch->get_result();
    $trainer_ids = [];
    while ($tr_row = $result_tr->fetch_assoc()) {
        $trainer_ids[] = intval($tr_row['ecobricker_id']);
    }
    $stmt_tr_fetch->close();

    if (!empty($trainer_ids)) {
        $stmt_tr_ins = $gobrik_conn->prepare("INSERT IGNORE INTO tb_training_trainers (training_id, ecobricker_id) VALUES (?, ?)");
        if ($stmt_tr_ins) {
            foreach ($trainer_ids as $tid) {
                $stmt_tr_ins->bind_param("ii", $new_training_id, $tid);
                $stmt_tr_ins->execute();
            }
            $stmt_tr_ins->close();
        }
    }
}

// Register the requester in training_registrations_tb (status='reserved')
if ($requester_ecobricker_id) {
    $stmt_reg = $gobrik_conn->prepare(
        "INSERT IGNORE INTO training_registrations_tb (training_id, buwana_id, status)
         VALUES (?, ?, 'reserved')"
    );
    if ($stmt_reg) {
        $stmt_reg->bind_param("ii", $new_training_id, $buwana_id);
        $stmt_reg->execute();
        $stmt_reg->close();
    }
}

// ---------------------------------------------------------------
// Send notification emails
// ---------------------------------------------------------------

$proposed_date_display = $proposed_dt ? $proposed_dt->format('F j, Y \a\t g:i A') : $proposed_date;
$funding_display = 'IDR ' . number_format($new_goal);

function sendCommunityRequestEmail(string $to, string $subject, string $htmlBody, string $cc = ''): bool {
    $host   = (string)getenv('SMTP_HOST');
    $port   = (int)getenv('SMTP_PORT');
    $user   = (string)getenv('SMTP_USERNAME');
    $pass   = (string)getenv('SMTP_PASSWORD');
    $secure = strtolower((string)getenv('SMTP_SECURE'));

    if ($host === '' || $port === 0 || $user === '' || $pass === '') {
        error_log('[community_training_request] Missing SMTP env vars');
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
        if ($cc !== '') {
            $mail->addCC($cc);
        }
        $mail->addReplyTo($user, 'GoBrik Training');
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $htmlBody;
        $mail->AltBody = strip_tags($htmlBody);
        $mail->send();
        return true;
    } catch (\Throwable $e) {
        error_log('[community_training_request] PHPMailer error: ' . $e->getMessage());
        return false;
    }
}

$requester_display_name = $requester_name ?: $requester_email;

// Email to trainer (cc: requester)
$trainer_email_body = '
<!DOCTYPE html><html><body style="font-family:sans-serif;color:#222;max-width:600px;margin:auto;">
<h2 style="color:#1e8c40;">New Community Training Request</h2>
<p>Hello,</p>
<p>A community training request has been submitted on GoBrik for <strong>' . htmlspecialchars($new_title, ENT_QUOTES, 'UTF-8') . '</strong>.</p>
<table style="width:100%;border-collapse:collapse;margin:16px 0;">
    <tr><td style="padding:8px 12px;background:#f5f5f5;font-weight:600;width:40%;">Requested by</td><td style="padding:8px 12px;">' . htmlspecialchars($requester_display_name, ENT_QUOTES, 'UTF-8') . ' &lt;' . htmlspecialchars($requester_email, ENT_QUOTES, 'UTF-8') . '&gt;</td></tr>
    <tr><td style="padding:8px 12px;background:#f0f0f0;font-weight:600;">Proposed Date</td><td style="padding:8px 12px;">' . htmlspecialchars($proposed_date_display, ENT_QUOTES, 'UTF-8') . '</td></tr>
    <tr><td style="padding:8px 12px;background:#f5f5f5;font-weight:600;">Time Zones</td><td style="padding:8px 12px;">' . htmlspecialchars($time_txt ?: '(not specified)', ENT_QUOTES, 'UTF-8') . '</td></tr>
    <tr><td style="padding:8px 12px;background:#f0f0f0;font-weight:600;">Language</td><td style="padding:8px 12px;">' . htmlspecialchars(strtoupper($proposed_language), ENT_QUOTES, 'UTF-8') . '</td></tr>
    <tr><td style="padding:8px 12px;background:#f5f5f5;font-weight:600;">Location</td><td style="padding:8px 12px;">' . htmlspecialchars($proposed_location, ENT_QUOTES, 'UTF-8') . '</td></tr>
    <tr><td style="padding:8px 12px;background:#f0f0f0;font-weight:600;">Community</td><td style="padding:8px 12px;">' . htmlspecialchars($resolved_community_name ?: '(not specified)', ENT_QUOTES, 'UTF-8') . '</td></tr>
    <tr><td style="padding:8px 12px;background:#f5f5f5;font-weight:600;">Full Course Amount</td><td style="padding:8px 12px;">' . htmlspecialchars($funding_display, ENT_QUOTES, 'UTF-8') . '</td></tr>
    <tr><td style="padding:8px 12px;background:#f0f0f0;font-weight:600;">Min. Participants</td><td style="padding:8px 12px;">' . (int)$new_min_part . '</td></tr>
</table>
<p>You can review and respond to this request from your <strong>My Trainings v2</strong> panel on your GoBrik dashboard.</p>
<p style="margin-top:24px;font-size:0.9em;color:#666;">This notification was sent by GoBrik on behalf of ' . htmlspecialchars($requester_display_name, ENT_QUOTES, 'UTF-8') . '.</p>
</body></html>
';

if (!empty($trainer_contact_email)) {
    sendCommunityRequestEmail(
        $trainer_contact_email,
        'New Community Training Request: ' . $new_title,
        $trainer_email_body,
        $requester_email
    );
}

// Confirmation email to requester
$requester_email_body = '
<!DOCTYPE html><html><body style="font-family:sans-serif;color:#222;max-width:600px;margin:auto;">
<h2 style="color:#1e8c40;">Your Community Training Request Has Been Submitted</h2>
<p>Hello ' . htmlspecialchars($requester_name ?: 'there', ENT_QUOTES, 'UTF-8') . ',</p>
<p>Thank you for submitting a community training request for <strong>' . htmlspecialchars($new_title, ENT_QUOTES, 'UTF-8') . '</strong>.</p>
<p>The training team has been notified and will be in touch shortly to confirm the details. Once confirmed, you\'ll receive a link to complete the full course payment for your community.</p>
<table style="width:100%;border-collapse:collapse;margin:16px 0;">
    <tr><td style="padding:8px 12px;background:#f5f5f5;font-weight:600;width:40%;">Proposed Date</td><td style="padding:8px 12px;">' . htmlspecialchars($proposed_date_display, ENT_QUOTES, 'UTF-8') . '</td></tr>
    <tr><td style="padding:8px 12px;background:#f0f0f0;font-weight:600;">Location</td><td style="padding:8px 12px;">' . htmlspecialchars($proposed_location, ENT_QUOTES, 'UTF-8') . '</td></tr>
    <tr><td style="padding:8px 12px;background:#f5f5f5;font-weight:600;">Community</td><td style="padding:8px 12px;">' . htmlspecialchars($resolved_community_name ?: '(not specified)', ENT_QUOTES, 'UTF-8') . '</td></tr>
    <tr><td style="padding:8px 12px;background:#f0f0f0;font-weight:600;">Full Course Amount</td><td style="padding:8px 12px;">' . htmlspecialchars($funding_display, ENT_QUOTES, 'UTF-8') . '</td></tr>
</table>
<p style="margin-top:24px;font-size:0.9em;color:#666;">Sent via GoBrik &middot; <a href="https://gobrik.com">gobrik.com</a></p>
</body></html>
';

if (!empty($requester_email)) {
    sendCommunityRequestEmail(
        $requester_email,
        'Your Community Training Request: ' . $new_title,
        $requester_email_body
    );
}

$gobrik_conn->close();

// Redirect back to community-3p page with success flags
header("Location: /en/community-3p.php?id=$source_training_id&requested=1&new_id=$new_training_id");
exit();
