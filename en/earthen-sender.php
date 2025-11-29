<?php
require_once '../earthenAuth_helper.php'; // Authentication helper
require_once '../auth/session_start.php';
require '../vendor/autoload.php'; // Composer autoload

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

// Set page variables
$lang = basename(dirname($_SERVER['SCRIPT_NAME']));
$version = '0.54';
$page = 'admin-panel';
$lastModified = date("Y-m-d\TH:i:s\Z", filemtime(__FILE__));




// Check if user is logged in and session active
if ($is_logged_in) {
    $buwana_id = $_SESSION['buwana_id'] ?? ''; // Retrieve buwana_id from session

    // Include database connections
    require_once '../gobrikconn_env.php';
    require_once '../buwanaconn_env.php';

    // Fetch the user's location data
    $user_continent_icon = getUserContinent($buwana_conn, $buwana_id);
    $earthling_emoji = getUserEarthlingEmoji($buwana_conn, $buwana_id);
    $user_location_watershed = getWatershedName($buwana_conn, $buwana_id);
    $user_location_full = getUserFullLocation($buwana_conn, $buwana_id);
    $gea_status = getGEA_status($buwana_id);
    $user_roles = getUser_Role($buwana_id);
    $user_community_name = getCommunityName($buwana_conn, $buwana_id);
    $first_name = getFirstName($buwana_conn, $buwana_id);

} else {
    $redirect_target = 'earthen-sender.php';
    $login_url = 'login.php?redirect=' . urlencode($redirect_target);

    echo '<script>
        alert("Please login before viewing this page.");
        window.location.href = "' . $login_url . '";
    </script>';
    exit();
}


// Check admin privileges
$query = "SELECT user_roles FROM tb_ecobrickers WHERE buwana_id = ?";
if ($stmt = $gobrik_conn->prepare($query)) {
    $stmt->bind_param("i", $buwana_id);
    $stmt->execute();
    $stmt->bind_result($user_roles);
    if ($stmt->fetch() && stripos($user_roles, 'admin') === false) {
        echo "<script>
            alert('Sorry, only admins can see this page.');
            window.location.href = 'dashboard.php';
        </script>";
        exit();
    }
    $stmt->close();
} else {
    echo "<script>
        alert('Error checking user role. Please try again later.');
        window.location.href = 'dashboard.php';
    </script>";
    exit();
}

require_once '../buwanaconn_env.php';
require_once '../emailing/earthen_helpers.php';

$ghoststats_conn = loadGhostStatsConnection();

// Default newsletter headers
$email_from = 'Earthen <earthen@ecobricks.org>';
$email_subject = 'Writing Earth Right';

function fetchEarthenStats($ghoststats_conn): array
{
    $stats = [
        'total' => 0,
        'sent' => 0,
        'percentage' => 0,
    ];

    if (!$ghoststats_conn || $ghoststats_conn->connect_error) {
        error_log('[GHOST STATS] Connection unavailable.');
        return $stats;
    }

    $totalResult = $ghoststats_conn->query("SELECT COUNT(*) AS total FROM members");
    if ($totalResult && ($row = $totalResult->fetch_assoc())) {
        $stats['total'] = (int) ($row['total'] ?? 0);
    }

    $sentQuery = "SELECT COUNT(*) AS sent
                  FROM members_labels ml
                  INNER JOIN labels l ON ml.label_id = l.id
                  WHERE l.name = 'sent-001'";
    $sentResult = $ghoststats_conn->query($sentQuery);
    if ($sentResult && ($row = $sentResult->fetch_assoc())) {
        $stats['sent'] = (int) ($row['sent'] ?? 0);
    }

    $stats['percentage'] = $stats['total'] > 0
        ? round(($stats['sent'] / $stats['total']) * 100, 2)
        : 0;

    return $stats;
}

// 🚨 CHECK FOR UNADDRESSED ADMIN ALERTS 🚨
$has_alerts = false;
$alerts = [];

$alert_query = "SELECT alert_title, alert_message FROM admin_alerts WHERE addressed = 0 ORDER BY date_posted DESC LIMIT 3";
$result = $buwana_conn->query($alert_query);

if ($result->num_rows > 0) {
    $has_alerts = true;
    while ($row = $result->fetch_assoc()) {
        $alerts[] = $row;
    }
}

try {
    $ghost_members = fetchGhostMembers();
    $summary = summarizeGhostMembers($ghost_members, 'sent-001');

    $earthen_stats = fetchEarthenStats($ghoststats_conn);

    $total_members = $earthen_stats['total'] ?? $summary['total'];
    $sent_count = $earthen_stats['sent'] ?? $summary['sent_count'];
    $sent_percentage = $earthen_stats['percentage'] ?? $summary['sent_percentage'];

    $status_limit = 20; // total rows to display in the status table
    $sent_limit = 4;    // number of most recent sent entries

    usort($summary['sent'], function ($a, $b) {
        return strcmp($b['updated_at'] ?? '', $a['updated_at'] ?? '');
    });

    usort($summary['pending'], function ($a, $b) {
        return strcmp($a['created_at'] ?? '', $b['created_at'] ?? '');
    });

    $sent_members = array_slice($summary['sent'], 0, $sent_limit);
    $pending_limit = $status_limit - count($sent_members);
    $pending_members = array_slice($summary['pending'], 0, $pending_limit);

    $all_members = array_map(function ($member) {
        $member['email_open_rate'] = calculateOpenRate($member);
        $member['test_sent'] = memberHasLabel($member, 'sent-001');
        $member['test_sent_date_time'] = $member['updated_at'] ?? 'N/A';
        return $member;
    }, array_merge($sent_members, $pending_members));
} catch (Exception $e) {
    error_log('[EARTHEN] Ghost sync failed: ' . $e->getMessage());
    $total_members = 0;
    $sent_count = 0;
    $sent_percentage = 0;
    $all_members = [];
}

// Mailgun event breakdown for processing chart
$mailgun_status_counts = [];
$mailgun_total_events = 0;
$mailgun_status_query = "SELECT COALESCE(event_type, 'unknown') AS status, COUNT(*) AS count FROM earthen_mailgun_events_tb GROUP BY status ORDER BY status";
$mailgun_status_result = $gobrik_conn->query($mailgun_status_query);

if ($mailgun_status_result) {
    while ($row = $mailgun_status_result->fetch_assoc()) {
        $status = $row['status'] ?: 'unknown';
        $count = (int) ($row['count'] ?? 0);
        $mailgun_status_counts[$status] = $count;
        $mailgun_total_events += $count;
    }
    $mailgun_status_result->free();
}

if (empty($mailgun_status_counts)) {
    $mailgun_status_counts = ['no data' => 0];
}

require_once '../emailing/live-newsletter.php';  //the newsletter html

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_email']) && !$has_alerts) {
    $email_html = $_POST['email_html'] ?? '';
    $recipient_email = $_POST['email_to'] ?? '';
    $subscriber_id = $_POST['subscriber_id'] ?? null;
    $is_test_mode = isset($_POST['test_mode']) && $_POST['test_mode'] == '1';

    $recipient_uuid = null;

    if ($subscriber_id) {
        try {
            $member_details = fetchGhostMembers([
                'limit' => 1,
                'filter' => "id:$subscriber_id",
            ]);

            if (!empty($member_details)) {
                $recipient_uuid = $member_details[0]['uuid'] ?? null;
                $recipient_email = $recipient_email ?: ($member_details[0]['email'] ?? '');
            }
        } catch (Exception $memberException) {
            error_log('[EARTHEN] Unable to fetch member details: ' . $memberException->getMessage());
        }
    }

    $email_html = personalizeEmailHtml($email_html, $recipient_uuid, $recipient_email);

    if (!empty($email_html) && !empty($recipient_email) && ($subscriber_id || $is_test_mode)) {
        // The webhook previously confirmed sends, now we update immediately
        try {

            error_log("[EARTHEN] → Sending " . ($is_test_mode ? 'TEST ' : '') . "{$recipient_email} by " . session_id());
            $send_ok = sendEmail($recipient_email, $email_html);

            if ($subscriber_id && !$is_test_mode && $send_ok) {
                try {
                    ensureMemberHasLabel($subscriber_id, 'sent-001');
                } catch (Exception $labelException) {
                    error_log('[EARTHEN] ❌ Failed to add sent label: ' . $labelException->getMessage());
                }
            }

            if ($send_ok) {
                error_log("[EARTHEN] ✅ Mailgun accepted " . ($is_test_mode ? 'TEST ' : '') . "{$recipient_email} by " . session_id());
            } else {
                if (!$is_test_mode && !empty($recipient_email)) {
                    logFailedEmail($recipient_email, 'Mailgun send failure');
                }
                error_log("[EARTHEN] ❌ Failed to send " . ($is_test_mode ? 'TEST ' : '') . "{$recipient_email} by " . session_id());
            }

            

            echo json_encode(['success' => $send_ok, 'message' => $send_ok ? '' : 'Sending failed']);
            exit();
        } catch (Exception $e) {
            if (!$is_test_mode && $subscriber_id) {
                error_log('[EARTHEN] ❌ Failed send for ' . $subscriber_id . ': ' . $e->getMessage());
            }
            if (!$is_test_mode && !empty($recipient_email)) {
                logFailedEmail($recipient_email, $e->getMessage());
            }
            error_log("[EARTHEN] ❌ Exception: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Server error']);
            exit();
        }
    } else {
        error_log("[EARTHEN] ❌ Missing recipient or content");
        echo json_encode(['success' => false, 'message' => 'Missing recipient or content']);
        exit();
    }
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

function personalizeEmailHtml(string $html, ?string $recipient_uuid, string $recipient_email): string
{
    $uuid_placeholder = '{{RECIPIENT_UUID}}';
    $fallback_uuid = '4dbbb711-73e9-4fd0-9056-a7cc1af6a905';
    $uuid = $recipient_uuid ?: $fallback_uuid;

    $html = str_replace($uuid_placeholder, $uuid, $html);

    $fallback_unsubscribe = 'https://earthen.io/unsubscribe/?uuid=4dbbb711-73e9-4fd0-9056-a7cc1af6a905&key=6c3ffe5e66725cd21a19a3f06a3f9c57d439ef226283a59999acecb11fb087dc&newsletter=1db69ae6-6504-48ba-9fd9-d78b3928071f';
    $unsubscribe_url = !empty($recipient_email)
        ? 'https://gobrik.com/emailing/unsubscribe.php?email=' . urlencode($recipient_email)
        : $fallback_unsubscribe;

    $html = preg_replace(
        '/https:\/\/gobrik\.com\/emailing\/unsubscribe\.php\?email=[^\s"\']+/i',
        $unsubscribe_url,
        $html
    );

    $html = preg_replace(
        '/https:\/\/earthen\.io\/unsubscribe\/\?uuid=[^&\"]+(&key=[^&\"]+)?(&newsletter=[^&\"]+)?/i',
        $unsubscribe_url,
        $html
    );

    return $html;
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
            'o:stop-retrying' => 'yes',  // Stops Mailgun from retrying if delivery fails
            'o:deliverytime' => gmdate('D, d M Y H:i:s T', strtotime('-1 hour'))  // Past time to prevent retry
        ]
        ]);
        return $response->getStatusCode() == 200;
    } catch (Exception $e) {
        error_log("Error sending email: " . $e->getMessage());
        return false;
    }
}


// Output the HTML structure
echo '<!DOCTYPE html>
<html lang="' . htmlspecialchars($lang, ENT_QUOTES, 'UTF-8') . '">
<head>
<meta charset="UTF-8">
';
?>

<?php require_once("../includes/admin-panel-inc.php"); ?>




<!-- SENDER FORM CONTENT -->
    <div class="form-container" style="padding-top:10px; margin-top: 100px;">

        <div id="processing-chart-wrapper" style="width:300px;margin:20px auto;">
            <div style="position:relative;">
                <canvas id="processingChart" width="300" height="300"></canvas>
                <div id="unsent-percentage-label" style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);font-weight:bold;font-size:1.2em;color:grey;"></div>
            </div>
        </div>

        <?php if ($has_alerts): ?>
        <div style="background: #ffdddd; padding: 15px; border-left: 5px solid red; margin-bottom: 20px;">
            <h3 style="color: red;">⚠️ Admin Alerts Found!</h3>
            <ul>
                <?php foreach ($alerts as $alert): ?>
                    <li><strong><?php echo htmlspecialchars($alert['alert_title']); ?>:</strong> <?php echo htmlspecialchars($alert['alert_message']); ?></li>
                <?php endforeach; ?>
            </ul>
            <p>Please resolve these alerts before proceeding.</p>
            <button id="reset-alerts-button" class="confirm-button delete" style="margin-top:10px;">✅ Alerts Resolved</button>
        </div>
    <?php endif; ?>

        <div id="greeting" style="text-align:center;width:100%;margin:auto;margin-top:25px;">
            <h2 id="greeting">Newsletter Sender</h2>
            <p id="subgreeting">Our tool for sending our newsletter our email by email to our Earthen Database.</p>
        </div>



<p>Total Members: <strong id="total-members"><?php echo $total_members; ?></strong></p>
<p>Emails Sent: <strong id="sent-count"><?php echo $sent_count; ?></strong> (
    <span id="sent-percentage"><?php echo number_format($sent_percentage, 2); ?></span>%)</p>



<!-- Auto-send toggle -->
<div class="form-row" style="display:flex;flex-flow:row;background-color:var(--lighter);padding:20px;border:grey 1px solid;border-radius:12px;margin-top:20px;">
    <div id="left-colum" style="width: 100%;">
        <label>✉️ Auto Send Emails</label>
        <p class="form-caption" style="margin-top:10px;">Uncheck to prevent the email from sending automatically after countdown.</p>

        <label for="send-delay-slider" style="display:block;margin-top:20px;margin-bottom: 5px;">⏱️ Send Delay</label>
        <input type="range" id="send-delay-slider" min="1" max="10" value="5" step="1" style="width:90%;accent-color:var(--emblem-green);">
                <p class="form-caption" style="margin-top:5px;">Adjust sending delay from 1 to 10 seconds.</p>

    </div>


    <div id="right-column" style="width:100px; justify-content:center;">
        <label class="toggle-switch">
            <input type="checkbox" id="auto-send-toggle" value="1">
            <span class="slider"></span>
        </label>
        <div style="margin-top:auto;margin-bottom:10px">
            <p style="text-align:center;margin:45px 30px 10px 0px;font-weight:bold;">⏱ <span id="delay-display">5</span>s</p>
        </div>

    </div>
</div>



        <!-- Send one test email (hidden unless auto-send is off) -->
        <div id="test-email-container" class="form-row" style="display:none;background-color:var(--lighter);padding:20px;border:grey 1px solid;border-radius:12px;margin-top:20px;gap:20px;align-items:flex-start;flex-wrap:wrap;">
            <div id="left-colum" style="flex: 1 1 280px;">
                <label for="test-email-toggle">📨 Send One Test Email</label>
                <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin-top:10px;">
                    <button id="test-send-button" type="submit" name="send_email" class="confirm-button enabled" <?php echo $has_alerts ? 'disabled' : ''; ?>>
                        📨 Send to russmaier@gmail.com
                    </button>
                    <p class="form-caption" style="margin:0;">Will send this email once to <span id="test-email-display">russmaier@gmail.com</span></p>
                </div>
            </div>
            <div id="right-column" style="width:180px;display:flex;flex-direction:column;align-items:center;gap:12px;">
                <label class="toggle-switch" style="margin-bottom:0;">
                    <input type="checkbox" id="test-email-toggle" value="1">
                    <span class="slider"></span>
                </label>
                <button type="button" id="test-email-settings" aria-label="Configure test email address" style="background:none;border:none;font-size:24px;cursor:pointer;line-height:1;">
                    ⚙️
                </button>
                <div id="test-email-config" style="display:none;width:100%;margin-top:5px;">
                    <label for="test-email-input" style="display:block;margin-bottom:6px;text-align:center;">Test email address</label>
                    <input type="email" id="test-email-input" style="width:100%;padding:10px;border:1px solid #ccc;border-radius:8px;" value="russmaier@gmail.com" autocomplete="email">
                </div>
            </div>
        </div>





<div id="send-controls" style="height:500px;">
<form id="email-form" method="POST" style="margin-top: 50px;">
    <p><strong>From:</strong> <?php echo htmlspecialchars($email_from, ENT_QUOTES, 'UTF-8'); ?></p>
    <p><strong>Subject:</strong> <?php echo htmlspecialchars($email_subject, ENT_QUOTES, 'UTF-8'); ?></p>
    <label for="email_html">Newsletter HTML:</label>
    <textarea name="email_html" id="email_html" rows="10" style="width:100%;"><?php echo htmlspecialchars($email_template); ?></textarea>
    <input type="hidden" id="subscriber_id" name="subscriber_id" value="">

    <!-- Hidden field for recipient email
        <input type="hidden" id="email_to" name="email_to" value="<?php echo htmlspecialchars($recipient_email); ?>">
    -->
<input type="hidden" id="email_to" name="email_to" value="">
    <br><br>
<!-- Auto-send Button (hidden by default unless auto-send is enabled) -->
<button id="auto-send-button" style="display:none" type="submit" name="send_email" class="confirm-button enabled" <?php echo $has_alerts ? 'disabled' : ''; ?>>
    📨 Send
</button>


</form>

<div id="countdown-timer" style="margin-top: 10px; display: none; text-align:center; width:100%;">
    <p>Email will send in <span id="countdown">5</span> seconds...</p>
    <button type="button" id="stop-timer-btn" class="confirm-button delete">🛑 Stop Timer</button>
</div>

</div>





    <h3>Email Sending Status:</h3>
    <table id="email-status-table" class="display responsive nowrap mdl-data-table" style="width:100%">
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Open Rate</th>
                <th>Sent Date</th>
                <th>Sent</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($all_members as $member): ?>
                <tr>
                    <td><?php echo htmlspecialchars($member['name'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($member['email'] ?? ''); ?></td>
                    <td><?php echo $member['email_open_rate'] ?? '0%'; ?></td>
                    <td><?php echo $member['test_sent_date_time'] ?? 'N/A'; ?></td>
                    <td><?php echo $member['test_sent'] ? '✅' : '❌'; ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
</div>


<!--FOOTER STARTS HERE-->
<?php require_once ("../footer-2025.php"); ?>




<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function () {
    let recipientEmail = '';
    let recipientName = '';
    let recipientId = null;
    let countdownInterval = null;
    let isSending = false; // prevent duplicate sends
    let sendDelay = 1;
    let sendFailCount = 0;
    const testEmailDefault = 'russmaier@gmail.com';
    let testEmail = localStorage.getItem('testEmail') || testEmailDefault;


    const hasAlerts = <?php echo $has_alerts ? 'true' : 'false'; ?>;

    const mailgunStatusLabels = <?php echo json_encode(array_keys($mailgun_status_counts)); ?>;
    const mailgunStatusCounts = <?php echo json_encode(array_values($mailgun_status_counts)); ?>;
    const mailgunTotalEvents = <?php echo json_encode($mailgun_total_events); ?>;

    const chartCtx = document.getElementById('processingChart');
    if (chartCtx && typeof Chart !== 'undefined') {
        const palette = ['#4caf50', '#2196f3', '#ff9800', '#9c27b0', '#f44336', '#03a9f4', '#8bc34a', '#ffeb3b'];
        const backgroundColors = mailgunStatusLabels.map((_, idx) => palette[idx % palette.length]);

        new Chart(chartCtx.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: mailgunStatusLabels,
                datasets: [{
                    data: mailgunStatusCounts,
                    backgroundColor: backgroundColors,
                    borderColor: 'transparent',
                    borderWidth: 0
                }]
            },
            options: {
                plugins: {
                    legend: { position: 'bottom', labels: { color: 'grey' } },
                    tooltip: { bodyColor: 'grey', titleColor: 'grey' }
                }
            }
        });

        $('#unsent-percentage-label').text(`${mailgunTotalEvents} events`);
    }

    // Initialize DataTable for status overview
    const statusTable = $('#email-status-table').DataTable({
        order: [],
        pageLength: 10,
        responsive: true,
        autoWidth: false
    });
    $("div.dataTables_filter input").attr('placeholder', 'Search emails...');

    const statusTableWrapper = $('#email-status-table_wrapper');
    const mailgunLogsButton = $('<div id="mailgun-logs-action" style="margin-top:10px;text-align:right;"><a class="confirm-button enabled" href="../emailing/mailgun-logs.php" target="_blank" rel="noopener">View Mailgun Logs</a></div>');
    if (statusTableWrapper.length) {
        statusTableWrapper.after(mailgunLogsButton);
    }

    function refreshStatusTable() {
        $.getJSON('../emailing/get_email_status.php', function(resp) {
            if (resp.success) {
                const rows = resp.members.map(m => [
                    m.name,
                    m.email,
                    m.email_open_rate || '0%',
                    m.test_sent_date_time || 'N/A',
                    m.test_sent == 1 ? '✅' : '❌'
                ]);
                statusTable.clear().rows.add(rows).draw(false);
            }
        });
    }

    refreshStatusTable();

    const autoSendEnabled = () => $('#auto-send-toggle').is(':checked');
    const testSendEnabled = () => $('#test-email-toggle').is(':checked');

    function updateTestEmailDisplay() {
        $('#test-email-display').text(testEmail);
        $('#test-email-input').val(testEmail);
    }

    function setTestEmail(email) {
        const trimmed = (email || '').trim();
        testEmail = trimmed || testEmailDefault;
        localStorage.setItem('testEmail', testEmail);
        updateTestEmailDisplay();
        if (!autoSendEnabled() && testSendEnabled()) {
            $('#test-send-button').html(`📨 Send to ${testEmail}`);
        }
    }

    setTestEmail(testEmail);

    function updateVisibleButton() {
        if (!autoSendEnabled()) {
            $('#test-email-container').css('display', 'flex');
            if (testSendEnabled()) {
                $('#test-send-button').show().html(`📨 Send to ${testEmail}`);
            } else {
                $('#test-send-button').hide();
            }
            $('#auto-send-button').hide();
        } else {
            $('#test-email-container').hide();
            $('#test-send-button').hide();

            if (recipientEmail) {
                $('#auto-send-button')
                    .show()
                    .html(`📨 Send to ${recipientEmail}`)
                    .prop('disabled', false);
            } else {
                $('#auto-send-button')
                    .show()
                    .html("📨 No recipient available")
                    .prop('disabled', true);
            }
        }
    }

    function startCountdownAndSend() {
        clearInterval(countdownInterval);
        if (isSending) return; // don't queue another send while sending
        sendDelay = parseInt($('#send-delay-slider').val()) || 1;
        let remaining = sendDelay;
        $('#delay-display').text(sendDelay);
        console.log(`⏰ Countdown ${sendDelay}s for ${recipientEmail}`);

        $('#auto-send-button, #test-send-button').prop('disabled', true);

        $('#countdown').text(remaining);
        $('#countdown-timer').show();
        countdownInterval = setInterval(() => {
            remaining--;
            $('#countdown').text(remaining);
            if (remaining <= 0) {
                clearInterval(countdownInterval);
                $('#countdown-timer').hide();
                sendEmail();
            }
        }, 1000);
    }

    $('#stop-timer-btn').on('click', function () {
        clearInterval(countdownInterval);
        $('#countdown-timer').hide();
        $('#auto-send-button, #test-send-button').prop('disabled', false);
        updateVisibleButton();
    });

    // 🟢 Fetch next recipient via AJAX
function fetchNextRecipient() {
    $.ajax({
        url: '../emailing/get_next_recipient.php',
        type: 'GET',
        dataType: 'json',
        success: function (response) {
            if (response.success) {
                // 🎯 Set recipient
                const sub = response.subscriber;
                recipientEmail = sub?.email || '';
                recipientName = sub?.name || '';
                recipientId = sub?.id || null;

                $('#subscriber_id').val(recipientId);

                console.log("📋 Next recipient:", recipientEmail);

                $('#email_to').val(recipientEmail);

                // 📊 Update stats
                if (response.stats) {
                    $('#total-members').text(response.stats.total);
                    $('#sent-count').text(response.stats.sent);
                    $('#sent-percentage').text(response.stats.percentage.toFixed(2));
                }

                updateVisibleButton();
                refreshStatusTable();

                // 🟢 Auto-send the next email if enabled
                if ($('#auto-send-toggle').is(':checked')) {
                    startCountdownAndSend();
                }

            } else {
                // ✅ All done
                recipientEmail = '';
                recipientId = null;
                $('#email_to').val('');
                $('#subscriber_id').val('');
                $('#auto-send-button').text("✅ All emails sent").prop('disabled', true);
            }
        },
        error: function () {
            logError('Failed to fetch next recipient.');
        }
    });
}

    function logError(message) {
        console.error(message);
        $.post('../emailing/log_sender_error.php', {message});
    }

    function handleSendError(msg) {
        sendFailCount++;
        logError(`${msg} Attempt ${sendFailCount} for ${recipientEmail}`);
        sendFailCount = 0;
        fetchNextRecipient(true);
    }



    // 🟢 Shared send function
function sendEmail() {
        clearInterval(countdownInterval);
        $('#countdown-timer').hide();

        const emailBody = $('#email_html').val().trim();
        const isTestMode = testSendEnabled() && !autoSendEnabled();

        if (!emailBody) {
            alert("⚠️ Please fill out the email content before sending.");
            return;
        }

        const targetEmail = isTestMode ? testEmail : recipientEmail;

        if (!targetEmail) {
            alert("❌ No recipient available.");
            return;
        }

        if (isSending) return; // prevent duplicate calls
        isSending = true;

        console.log("🚀 Sending to:", targetEmail);

        // Show sending state
        $('#auto-send-button, #test-send-button').text("⏳ Sending...").prop('disabled', true);
        $('#email_to').val(targetEmail);

        $.ajax({
            url: "", // Same page
            type: "POST",
            data: {
                send_email: "1",
                email_to: targetEmail,
                email_html: emailBody,
                test_mode: isTestMode ? 1 : 0,
                subscriber_id: recipientId
            },
            success: function (resp) {
                let data = {};
                try { data = typeof resp === 'string' ? JSON.parse(resp) : resp; } catch (e) {}
                if (data.success) {
                    if (isTestMode) {
                        $('#test-send-button').text("✅ Sent!").prop('disabled', true);
                        console.log("✅ Server confirmed test send to:", targetEmail);
                        localStorage.removeItem('testSend');
                    } else {
                        $('#auto-send-button').text(`✅ Sent to ${recipientEmail}`);
                        console.log("✅ Server confirmed send to:", targetEmail);
                        // Chain to next
                        fetchNextRecipient(true); // fetch + auto-send next
                    }
                } else {
                    isSending = false;
                    handleSendError(data.message || 'Failed to send the email.');
                    return;
                }
                isSending = false;
            },
            error: function () {
                isSending = false;
                handleSendError('Failed to send the email.');
            }
        });
    }

    // 🔹 Form submission (manual trigger)
    $('#email-form').on('submit', function (e) {
        e.preventDefault();
        sendEmail();
    });

    // 🔹 Manual click trigger
    $('#test-send-button, #auto-send-button').on('click', function (e) {
        e.preventDefault();
        $('#email-form').trigger('submit');
    });

    // 🔹 Toggle listeners
    $('#test-email-toggle').on('change', function () {
        localStorage.setItem('testSend', $(this).is(':checked'));
        updateVisibleButton();
    });

    $('#test-email-settings').on('click', function () {
        $('#test-email-config').toggle();
    });

    $('#test-email-input').on('input change', function () {
        setTestEmail($(this).val());
    });

    $('#auto-send-toggle').on('change', function () {
        localStorage.setItem('autoSend', $(this).is(':checked'));
        updateVisibleButton();

        // If switched ON and a recipient is already loaded, auto-trigger
        if (autoSendEnabled() && recipientEmail) {
            startCountdownAndSend();
        }
    });

    $('#send-delay-slider').on('input change', function () {
        sendDelay = parseInt($(this).val());
        $('#delay-display').text(sendDelay);
        console.log(`⏲️ Delay set to ${sendDelay}s`);

    });

    // 🔹 Reset admin alerts
    $('#reset-alerts-button').on('click', function (e) {
        e.preventDefault();
        if (!confirm('Mark all admin alerts as addressed?')) {
            return;
        }
        $.ajax({
            url: '../emailing/reset_admin_alerts.php',
            type: 'POST',
            dataType: 'json',
            success: function (resp) {
                if (resp.success) {
                    alert('✅ Alerts have been reset.');
                    location.reload();
                } else {
                    alert('❌ Failed to reset alerts.');
                }
            },
            error: function () {
                alert('❌ Failed to reset alerts.');
            }
        });
    });

    // 🔹 Initial state from localStorage
    const savedAutoSend = localStorage.getItem('autoSend') === 'true';
    const savedTestSend = localStorage.getItem('testSend') === 'true';
    sendDelay = parseInt(localStorage.getItem('sendDelay')) || 1;
    $('#send-delay-slider').val(sendDelay);
    $('#delay-display').text(sendDelay);

    $('#auto-send-toggle').prop('checked', savedAutoSend);
    $('#test-email-toggle').prop('checked', savedTestSend);

    updateVisibleButton();

    if (hasAlerts) {
        alert("⚠️ Unaddressed Admin Alerts Exist! You cannot send emails until they are resolved.");
        $('#auto-send-button, #test-send-button').prop('disabled', true);
    } else {
        console.log("🚚 Fetching first recipient...");
        fetchNextRecipient(true); // Fetch on page load, and auto-send if toggled
    }
});


</script>







</body>
</html>