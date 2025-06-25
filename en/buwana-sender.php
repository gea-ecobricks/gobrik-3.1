<?php
require_once '../earthenAuth_helper.php';
require '../vendor/autoload.php';
use GuzzleHttp\Client;

// Page variables
$lang = basename(dirname($_SERVER['SCRIPT_NAME']));
$version = '0.55';
$page = 'admin-panel';
$lastModified = date("Y-m-d\TH:i:s\Z", filemtime(__FILE__));
startSecureSession();

// Login check
if (!$is_logged_in) {
    echo '<script>alert("Please login before viewing this page."); window.location.href = "login.php?redirect=buwana-sender.php";</script>';
    exit();
}

$buwana_id = $_SESSION['buwana_id'] ?? '';
require_once '../gobrikconn_env.php';
require_once '../buwanaconn_env.php';

// Admin role check
$stmt = $gobrik_conn->prepare("SELECT user_roles FROM tb_ecobrickers WHERE buwana_id = ?");
$stmt->bind_param("i", $buwana_id);
$stmt->execute();
$stmt->bind_result($user_roles);
if (!$stmt->fetch() || stripos($user_roles, 'admin') === false) {
    echo "<script>alert('Only admins allowed.'); window.location.href='dashboard.php';</script>";
    exit();
}
$stmt->close();

// Email defaults
$email_from = 'Earthen <earthen@ecobricks.org>';
$email_subject = 'Solstice, Ayyew & Earthen';

// Check admin alerts
$has_alerts = false;
$alerts = [];
$result = $buwana_conn->query("SELECT alert_title, alert_message FROM admin_alerts WHERE addressed = 0 ORDER BY date_posted DESC LIMIT 3");
if ($result && $result->num_rows > 0) {
    $has_alerts = true;
    while ($row = $result->fetch_assoc()) { $alerts[] = $row; }
}

// Stats for header
$res = $buwana_conn->query("SELECT COUNT(*) AS total, SUM(CASE WHEN test_sent = 1 THEN 1 ELSE 0 END) AS sent FROM users_tb");
$row = $res->fetch_assoc();
$total_members = intval($row['total']);
$sent_count = intval($row['sent']);
$sent_percentage = ($total_members > 0) ? round(($sent_count/$total_members)*100, 2) : 0;

// Send POST handling
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_email']) && !$has_alerts) {
    $email_html = trim($_POST['email_html'] ?? '');
    $recipient_email = trim($_POST['email_to'] ?? '');
    $subscriber_id = isset($_POST['subscriber_id']) ? intval($_POST['subscriber_id']) : null;
    $is_test_mode = isset($_POST['test_mode']) && $_POST['test_mode'] == '1';

    if (!empty($email_html) && !empty($recipient_email) && ($subscriber_id || $is_test_mode)) {
        error_log("[BUWANA] Sending ".($is_test_mode?'TEST ':'').$recipient_email);
        $send_ok = sendEmail($recipient_email, $email_html);

        if ($subscriber_id && !$is_test_mode) {
            $stmt = $buwana_conn->prepare("UPDATE users_tb SET test_sent=1, test_sent_date_time=NOW(), processing=1 WHERE buwana_id=?");
            $stmt->bind_param('i', $subscriber_id);
            $stmt->execute();
            $stmt->close();
        }
        echo json_encode(['success' => $send_ok]);
        exit();
    } else {
        error_log("[BUWANA] Missing recipient or content");
        echo json_encode(['success' => false, 'message' => 'Missing recipient or content']);
        exit();
    }
}

// Email sending function
function sendEmail($to, $htmlBody) {
    global $email_from, $email_subject;
    $client = new Client(['base_uri' => 'https://api.eu.mailgun.net/v3/']);
    $mailgunApiKey = getenv('EARTHEN_MAILGUN_SENDING_KEY');
    $mailgunDomain = 'earthen.ecobricks.org';

    try {
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
    } catch (Exception $e) {
        error_log("Mailgun error: ".$e->getMessage());
        return false;
    }
}
?>

<!DOCTYPE html>
<html lang="<?= htmlspecialchars($lang) ?>">
<head>
    <meta charset="UTF-8">
    <title>Buwana Sender</title>
</head>
<?php require_once("../includes/admin-panel-inc.php"); ?>

<body>
<div class="form-container" style="margin-top:100px;">

<?php if ($has_alerts): ?>
    <div style="background:#ffdddd;padding:15px;border-left:5px solid red;">
        <h3>⚠️ Admin Alerts Found!</h3>
        <ul><?php foreach ($alerts as $alert): ?>
            <li><strong><?= htmlspecialchars($alert['alert_title']) ?>:</strong> <?= htmlspecialchars($alert['alert_message']) ?></li>
        <?php endforeach; ?></ul>
    </div>
<?php endif; ?>

<h2>Buwana Newsletter Sender</h2>
<p>Total Members: <?= $total_members ?> | Sent: <?= $sent_count ?> (<?= $sent_percentage ?>%)</p>

<form id="email-form" method="POST">
    <p><strong>From:</strong> <?= htmlspecialchars($email_from) ?></p>
    <p><strong>Subject:</strong> <?= htmlspecialchars($email_subject) ?></p>
    <label>Newsletter HTML:</label>
    <textarea name="email_html" id="email_html" rows="10" style="width:100%;"></textarea>

    <input type="hidden" id="email_to" name="email_to" value="">
    <input type="hidden" id="subscriber_id" name="subscriber_id" value="">

    <button id="auto-send-button" type="submit" name="send_email" style="display:none;">📨 Send</button>
    <button id="test-send-button" type="submit" name="send_email">📨 Send Test</button>
</form>

<script>
$(document).ready(function(){
    let recipientEmail = '';
    let recipientId = null;

    function fetchNextRecipient() {
        $.getJSON('../scripts/get_next_buwana_recipient.php', function(response) {
            if (response.success) {
                const sub = response.subscriber;
                recipientEmail = sub.email;
                recipientId = sub.buwana_id;

                $('#email_to').val(recipientEmail);
                $('#subscriber_id').val(recipientId);
                $('#auto-send-button').text(`📨 Send to ${recipientEmail}`).show().prop('disabled', false);
            } else {
                recipientEmail = '';
                recipientId = null;
                $('#email_to').val('');
                $('#subscriber_id').val('');
                $('#auto-send-button').text("✅ All emails sent").prop('disabled', true);
            }
        });
    }

    $('#email-form').submit(function(e){
        e.preventDefault();
        const emailBody = $('#email_html').val().trim();
        const isTest = $('#test-send-button').is(':visible');

        if (!emailBody || (!recipientEmail && !isTest)) {
            alert("Missing email body or recipient.");
            return;
        }

        $.post("", {
            send_email: 1,
            email_html: emailBody,
            email_to: isTest ? "russmaier@gmail.com" : recipientEmail,
            test_mode: isTest ? 1 : 0,
            subscriber_id: recipientId
        }, function(resp){
            if (resp.success) {
                if (!isTest) fetchNextRecipient();
                alert("Email sent!");
            } else {
                alert("Failed: "+resp.message);
            }
        }, 'json');
    });

    // Initial fetch
    fetchNextRecipient();
});
</script>

</body>
</html>
