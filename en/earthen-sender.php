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
    $buwana_id = $_SESSION['buwana_id'] ?? '';
    $login_url = 'https://buwana.ecobricks.org/en/login.php?redirect=' . urlencode($redirect_target) . '&app=gbrk_f2c61a85a4cd4b8b89a7';
    if (!empty($buwana_id)) {
        $login_url .= '&id=' . urlencode($buwana_id);
    }

    header('Location: ' . $login_url);
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
$earthen_stats = getGhostMemberStats($ghoststats_conn);

if (!defined('EARTHEN_TOTAL_MEMBERS')) {
    define('EARTHEN_TOTAL_MEMBERS', $earthen_stats['total'] ?? 0);
}

// Default newsletter headers
$email_from = 'Earthen <earthen@ecobricks.org>';
$email_subject = 'Writing Earth Right';

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


$batch_label = 'earthen-' . date('Ymd-His');
$initial_member_batch = [];
$initialOrderDirection = (isset($_COOKIE['ghostBatchOrder']) && strtolower($_COOKIE['ghostBatchOrder']) === 'desc') ? 'DESC' : 'ASC';

try {
    $pending_members = fetchGhostPendingBatch($ghoststats_conn, 100, 0, $initialOrderDirection);

    if (!empty($pending_members)) {
        $insert_stmt = $gobrik_conn->prepare(
            "INSERT IGNORE INTO earthen_send_batch_tb (
                batch_label,
                ghost_member_id,
                ghost_email,
                ghost_name,
                ghost_newsletter_id,
                ghost_newsletter_slug,
                test_sent,
                email_open_rate
            ) VALUES (?, ?, ?, ?, NULL, NULL, 0, ?);"
        );

        if ($insert_stmt) {
            foreach ($pending_members as $member) {
                $ghost_id = $member['id'] ?? null;
                $ghost_email = $member['email'] ?? '';

                if (!$ghost_id || !$ghost_email) {
                    continue;
                }

                $ghost_name = $member['name'] ?? null;
                $open_rate = $member['email_open_rate'] ?? null;

                $insert_stmt->bind_param('sssss', $batch_label, $ghost_id, $ghost_email, $ghost_name, $open_rate);
                $insert_stmt->execute();

                try {
                    ensureMemberHasLabel($ghost_id, 'sent-001');
                } catch (Exception $labelException) {
                    error_log('[EARTHEN] ❌ Failed to apply sent label: ' . $labelException->getMessage());
                }
            }

            $insert_stmt->close();

            $fetch_stmt = $gobrik_conn->prepare(
                "SELECT id, ghost_member_id, ghost_email, ghost_name, email_open_rate, test_sent, test_sent_date_time
                 FROM earthen_send_batch_tb
                 WHERE batch_label = ?
                 ORDER BY id ASC"
            );

            if ($fetch_stmt) {
                $fetch_stmt->bind_param('s', $batch_label);
                $fetch_stmt->execute();
                $result = $fetch_stmt->get_result();
                $initial_member_batch = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
                $fetch_stmt->close();
            }
        }
    }
} catch (Exception $batchException) {
    error_log('[EARTHEN] ❌ Failed to prepare initial batch: ' . $batchException->getMessage());
}

$all_members = array_map(function ($member) {
    return [
        'id' => $member['ghost_member_id'] ?? null,
        'email' => $member['ghost_email'] ?? '',
        'name' => $member['ghost_name'] ?? '',
        'email_open_rate' => $member['email_open_rate'] ?? '0%',
        'test_sent' => (int) ($member['test_sent'] ?? 0),
        'test_sent_date_time' => $member['test_sent_date_time'] ?? 'N/A',
        'batch_row_id' => $member['id'] ?? null,
    ];
}, $initial_member_batch);

$first_recipient_email = $all_members[0]['email'] ?? '';
$first_recipient_id = $all_members[0]['id'] ?? '';

$total_members = $earthen_stats['total'] ?? (defined('EARTHEN_TOTAL_MEMBERS') ? EARTHEN_TOTAL_MEMBERS : 0);
$sent_count = $earthen_stats['sent'] ?? 0;
$sent_percentage = $earthen_stats['percentage'] ?? 0;
$pending_count = max(0, $total_members - $sent_count);

// Mailgun event breakdown for processing chart
$mailgun_status_counts = [];
$mailgun_total_events = 0;
$mailgun_status_query = "SELECT COALESCE(event_type, 'unknown') AS status, COUNT(*) AS count FROM earthen_mailgun_events_tb WHERE LOWER(COALESCE(event_type, '')) <> 'accepted' GROUP BY status ORDER BY status";
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


function logFailedEmail(string $email, string $reason): void
{
    global $gobrik_conn;

    if (!isset($gobrik_conn)) {
        error_log('[EARTHEN] No DB connection available to log failed email.');
        return;
    }

    $member_id = null;

    try {
        $member_id = getMemberIdByEmail($email);
    } catch (Exception $e) {
        error_log('[EARTHEN] Failed to look up Ghost member ID: ' . $e->getMessage());
    }

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

<style>
    .toggle-switch .slider.order-oldest {
        background-color: #4caf50;
    }

    .toggle-switch .slider.order-newest {
        background-color: #ff9800;
    }

    #order-toggle-wrapper {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-top: 12px;
    }

    #order-toggle-wrapper strong {
        min-width: 120px;
        display: inline-block;
    }
</style>




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
            <h2 id="greeting">Earthen Manual Sender</h2>
            <p id="subgreeting">Our tool for sending our newsletter our email by email to our Earthen Database.</p>
        </div>



<p id="overall-stats">Total Members: <strong id="total-members"><?php echo number_format($total_members); ?></strong>  |  Total Sent <strong id="sent-count"><?php echo $sent_count; ?></strong> (<span id="sent-percentage"><?php echo number_format($sent_percentage, 3); ?></span>%)</p>
<p id="batch-stats">Current Batch: <strong id="batch-size">0</strong>  |  Batch Sent: <strong id="batch-sent">0</strong> (<span id="batch-percentage">0.00</span>%)</p>



<!-- Auto-send toggle -->
<div class="form-row" style="display:flex;flex-flow:row;background-color:var(--lighter);padding:20px;border:grey 1px solid;border-radius:12px;margin-top:20px;">
    <div id="left-colum" style="width: 100%;">
        <label>✉️ Auto Send Emails</label>
        <p class="form-caption" style="margin-top:10px;">Uncheck to prevent the email from sending automatically after countdown.</p>

        <label style="display:block;margin-top:15px;">🔁 Auto-load batches</label>
        <p class="form-caption" style="margin-top:10px;">When enabled, the next 100 members will load automatically after the current batch finishes and sending will continue.</p>

        <div id="order-toggle-wrapper">
            <strong>🧭 Member order</strong>
            <label class="toggle-switch" style="margin:0;">
                <input type="checkbox" id="order-toggle" value="1">
                <span class="slider"></span>
            </label>
            <span id="order-label" class="form-caption">Oldest first</span>
        </div>

        <label for="send-delay-slider" style="display:block;margin-top:20px;margin-bottom: 5px;">⏱️ Send Delay</label>
        <input type="range" id="send-delay-slider" min="1" max="10" value="5" step="1" style="width:90%;accent-color:var(--emblem-green);">
                <p class="form-caption" style="margin-top:5px;">Adjust sending delay from 1 to 10 seconds.</p>

    </div>


    <div id="right-column" style="width:100px; justify-content:center;">
        <label class="toggle-switch">
            <input type="checkbox" id="auto-send-toggle" value="1">
            <span class="slider"></span>
        </label>
        <div style="margin-top:10px;">
            <label class="toggle-switch">
                <input type="checkbox" id="auto-load-toggle" value="1">
                <span class="slider"></span>
            </label>
        </div>
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
    <input type="hidden" id="subscriber_id" name="subscriber_id" value="<?php echo htmlspecialchars($first_recipient_id); ?>">

    <!-- Hidden field for recipient email
        <input type="hidden" id="email_to" name="email_to" value="<?php echo htmlspecialchars($recipient_email); ?>">
    -->
<input type="hidden" id="email_to" name="email_to" value="<?php echo htmlspecialchars($first_recipient_email); ?>">
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
    let recipientQueue = [];
    let queueIndex = 0;
    let autoSendStarted = false;
    const batchSize = 100;
    let batchSent = 0;
    let currentBatchSize = 0;
    let batchOffset = 0;
    let batchOrder = 'ASC';
    let isLoadingBatch = false;
    const initialBatch = <?php echo json_encode($all_members); ?>;
    const initialStats = <?php echo json_encode($earthen_stats); ?>;
    let queueStats = {
        total: <?php echo (int) ($total_members ?? 0); ?>,
        sent: <?php echo (int) ($sent_count ?? 0); ?>,
        percentage: <?php echo (float) ($sent_percentage ?? 0); ?>
    };
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

    function logError(message) {
        console.error(message);
    }

    function autoLoadEnabled() {
        return $('#auto-load-toggle').is(':checked');
    }

    function setBatchOrder(order, reload = true) {
        batchOrder = order === 'DESC' ? 'DESC' : 'ASC';
        localStorage.setItem('ghostBatchOrder', batchOrder);
        document.cookie = `ghostBatchOrder=${batchOrder};path=/;max-age=${60 * 60 * 24 * 30}`;
        updateOrderToggleLabel();

        if (reload) {
            resetBatchState();
            fetchNextBatch(true);
        }
    }

    function updateOrderToggleLabel() {
        const isNewest = batchOrder === 'DESC';
        const slider = $('#order-toggle').siblings('.slider');

        $('#order-toggle').prop('checked', isNewest);
        slider.toggleClass('order-newest', isNewest);
        slider.toggleClass('order-oldest', !isNewest);
        $('#order-label').text(isNewest ? 'Newest first' : 'Oldest first');
    }

    function resetBatchState() {
        queueIndex = 0;
        recipientQueue = [];
        currentBatchSize = 0;
        batchSent = 0;
        batchOffset = 0;
        renderStatusTable();
        setActiveRecipientFromQueue(-1);
        updateBatchDisplay();
    }

    function renderStatusTable() {
        if (recipientQueue.length) {
            const rows = recipientQueue.map((m) => {
                const isSent = m.test_sent === 1 || m.status === 'sent';
                const statusIcon = isSent ? '✅ Sent' : (m.status === 'failed' ? '⚠️ Failed' : '⏳ Pending');
                const sentDate = m.test_sent_date_time && m.test_sent_date_time !== 'N/A'
                    ? m.test_sent_date_time
                    : (m.sent_at ? new Date(m.sent_at).toLocaleString() : 'Queued');

                return [
                    m.name || '',
                    m.email || '',
                    m.email_open_rate || '0%',
                    sentDate,
                    statusIcon,
                ];
            });

            statusTable.clear().rows.add(rows).draw(false);
            return;
        }

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

    renderStatusTable();

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
        if (!autoSendEnabled() || !autoSendStarted || !recipientEmail) {
            return;
        }

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

    function updateBatchDisplay() {
        const percent = currentBatchSize > 0 ? Math.min(100, (batchSent / currentBatchSize) * 100) : 0;
        $('#batch-size').text(currentBatchSize);
        $('#batch-sent').text(batchSent);
        $('#batch-percentage').text(percent.toFixed(2));
    }

    function updateStatsDisplay(stats) {
        if (!stats) return;
        queueStats = {
            total: stats.total ?? 0,
            sent: stats.sent ?? 0,
            percentage: stats.percentage ?? 0,
        };

        $('#total-members').text(Number(queueStats.total).toLocaleString());
        $('#sent-count').text(queueStats.sent);
        $('#sent-percentage').text(Number(queueStats.percentage).toFixed(3));
    }

    function setBatchFromData(batch) {
        recipientQueue = batch;
        currentBatchSize = recipientQueue.length;
        batchOffset += currentBatchSize;
        batchSent = recipientQueue.filter(m => m.test_sent === 1).length;

        const firstPendingIndex = recipientQueue.findIndex(m => m.test_sent !== 1);
        queueIndex = firstPendingIndex >= 0 ? firstPendingIndex : recipientQueue.length;

        renderStatusTable();

        if (!setActiveRecipientFromQueue(queueIndex)) {
            handleBatchCompletion();
        }
    }

    function loadInitialBatch() {
        updateStatsDisplay(initialStats);

        if (batchOrder === 'DESC') {
            resetBatchState();
            fetchNextBatch(true);
            return;
        }

        if (Array.isArray(initialBatch) && initialBatch.length) {
            setBatchFromData(initialBatch);
        } else {
            handleBatchCompletion();
        }

        updateBatchDisplay();
    }

    function incrementSentStats() {
        queueStats.sent = (queueStats.sent || 0) + 1;
        queueStats.percentage = queueStats.total > 0
            ? Math.min(100, (queueStats.sent / queueStats.total) * 100)
            : 0;

        updateStatsDisplay(queueStats);
        batchSent += 1;
        updateBatchDisplay();
    }

    function handleBatchCompletion() {
        queueIndex = 0;
        recipientQueue = [];
        currentBatchSize = 0;
        batchSent = 0;
        renderStatusTable();
        setActiveRecipientFromQueue(-1);
        updateBatchDisplay();

        if (autoLoadEnabled()) {
            fetchNextBatch();
            return;
        }

        $('#auto-send-button').text("✅ All batch emails sent!").prop('disabled', true);
    }

    function fetchNextBatch(showLoadingText = false) {
        if (isLoadingBatch) return;

        isLoadingBatch = true;
        if (showLoadingText) {
            $('#auto-send-button').text('⏳ Loading batch...').prop('disabled', true);
        } else {
            $('#auto-send-button').text('⏳ Loading next batch...').prop('disabled', true);
        }

        $.ajax({
            url: '../emailing/get_recipient_batch.php',
            method: 'GET',
            dataType: 'json',
            data: { limit: batchSize, offset: batchOffset, order: batchOrder.toLowerCase() },
            success: function (resp) {
                isLoadingBatch = false;

                if (!resp || !resp.success) {
                    $('#auto-send-button').text('❌ Failed to load batch').prop('disabled', false);
                    return;
                }

                if (resp.stats) {
                    updateStatsDisplay(resp.stats);
                }

                const batch = Array.isArray(resp.batch) ? resp.batch : [];

                if (!batch.length) {
                    $('#auto-send-button').text('✅ All batch emails sent!').prop('disabled', true);
                    return;
                }

                const normalizedBatch = batch.map((m) => ({
                    id: m.id ?? null,
                    email: m.email ?? '',
                    name: m.name ?? '',
                    email_open_rate: m.email_open_rate ?? '0%',
                    test_sent: parseInt(m.test_sent ?? 0),
                    test_sent_date_time: m.test_sent_date_time ?? 'N/A',
                    batch_row_id: m.batch_row_id ?? null,
                    status: m.status ?? 'pending',
                }));

                setBatchFromData(normalizedBatch);
                updateBatchDisplay();

                $('#auto-send-button').text('📨 Send').prop('disabled', false);

                if (autoSendEnabled() && autoSendStarted && recipientEmail) {
                    startCountdownAndSend();
                }
            },
            error: function () {
                isLoadingBatch = false;
                $('#auto-send-button').text('❌ Failed to load batch').prop('disabled', false);
            }
        });
    }

    function setActiveRecipientFromQueue(index) {
        const sub = recipientQueue[index];

        if (!sub) {
            recipientEmail = '';
            recipientId = null;
            $('#email_to').val('');
            $('#subscriber_id').val('');
            updateVisibleButton();
            return false;
        }

        recipientEmail = sub.email || '';
        recipientName = sub.name || '';
        recipientId = sub.id || null;

        $('#subscriber_id').val(recipientId);
        $('#email_to').val(recipientEmail);

        console.log('📋 Next recipient:', recipientEmail);

        updateVisibleButton();
        renderStatusTable();

        if (autoSendEnabled()) {
            startCountdownAndSend();
        }

        return true;
    }

    function markCurrentRecipient(status) {
        const current = recipientQueue[queueIndex];
        if (!current) return;

        current.status = status;
        if (status === 'sent') {
            current.sent_at = new Date().toISOString();
            current.test_sent = 1;
            current.test_sent_date_time = current.sent_at;
        }

        renderStatusTable();
    }

    function handleSendError(msg) {
        sendFailCount++;
        logError(`${msg} Attempt ${sendFailCount} for ${recipientEmail}`);
        markCurrentRecipient('failed');
        isSending = false;
        sendFailCount = 0;

        if (autoSendEnabled()) {
            queueIndex++;
            if (!setActiveRecipientFromQueue(queueIndex)) {
                handleBatchCompletion();
            }
        }
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
            url: '../emailing/earthen_send_email.php',
            type: 'POST',
            data: {
                send_email: '1',
                email_to: targetEmail,
                email_html: emailBody,
                test_mode: isTestMode ? 1 : 0,
                subscriber_id: recipientId,
                batch_row_id: recipientQueue[queueIndex] ? recipientQueue[queueIndex].batch_row_id : null
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
                        markCurrentRecipient('sent');
                        incrementSentStats();
                        queueIndex++;
                        if (!setActiveRecipientFromQueue(queueIndex)) {
                            handleBatchCompletion();
                        }
                    }
                } else {
                    handleSendError(data.message || 'Failed to send the email.');
                    return;
                }
                isSending = false;

                // Continue auto sending on the configured timer after a successful send
                if (!isTestMode && autoSendEnabled() && autoSendStarted) {
                    startCountdownAndSend();
                }
            },
            error: function () {
                handleSendError('Failed to send the email.');
            }
        });
    }

    // 🔹 Form submission (manual trigger)
    $('#email-form').on('submit', function (e) {
        e.preventDefault();

        if (autoSendEnabled()) {
            autoSendStarted = true;
            startCountdownAndSend();
            return;
        }

        sendEmail();
    });

    // 🔹 Manual click trigger
    $('#test-send-button, #auto-send-button').on('click', function (e) {
        e.preventDefault();
        if (autoSendEnabled()) {
            autoSendStarted = true;
        }
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

        if (!$(this).is(':checked')) {
            autoSendStarted = false;
            clearInterval(countdownInterval);
            $('#countdown-timer').hide();
            return;
        }

        // If switched ON and a recipient is already loaded, auto-trigger
        if (autoSendEnabled() && recipientEmail) {
            startCountdownAndSend();
        }
    });

    $('#auto-load-toggle').on('change', function () {
        localStorage.setItem('autoLoad', $(this).is(':checked'));

        if (!$(this).is(':checked')) {
            $('#auto-send-button').text("✅ All batch emails sent!").prop('disabled', recipientQueue.length === 0);
        }
    });

    $('#order-toggle').on('change', function () {
        const isNewest = $(this).is(':checked');
        setBatchOrder(isNewest ? 'DESC' : 'ASC');
    });

    $('#send-delay-slider').on('input change', function () {
        sendDelay = parseInt($(this).val());
        localStorage.setItem('sendDelay', sendDelay);
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
    const savedAutoLoad = localStorage.getItem('autoLoad') === 'true';
    const savedOrder = localStorage.getItem('ghostBatchOrder') === 'DESC' ? 'DESC' : 'ASC';
    sendDelay = parseInt(localStorage.getItem('sendDelay')) || 1;
    $('#send-delay-slider').val(sendDelay);
    $('#delay-display').text(sendDelay);

    setBatchOrder(savedOrder, false);
    $('#auto-send-toggle').prop('checked', savedAutoSend);
    $('#test-email-toggle').prop('checked', savedTestSend);
    $('#auto-load-toggle').prop('checked', savedAutoLoad);

    loadInitialBatch();
    updateVisibleButton();

    if (hasAlerts) {
        alert("⚠️ Unaddressed Admin Alerts Exist! You cannot send emails until they are resolved.");
        $('#auto-send-button, #test-send-button').prop('disabled', true);
    } else {
        if (autoSendEnabled() && recipientEmail) {
            startCountdownAndSend();
        }
    }
});


</script>







</body>
</html>