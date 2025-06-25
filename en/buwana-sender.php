<?php
require_once '../earthenAuth_helper.php'; // Authentication helper
require '../vendor/autoload.php'; // Composer autoload

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

// Set page variables
$lang = basename(dirname($_SERVER['SCRIPT_NAME']));
$version = '0.54';
$page = 'admin-panel';
$lastModified = date("Y-m-d\TH:i:s\Z", filemtime(__FILE__));


startSecureSession(); // Start a secure session with regeneration to prevent session fixation


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
    // Redirect to login page with the redirect parameter set to the current page
    echo '<script>
        alert("Please login before viewing this page.");
        window.location.href = "login.php?redirect=' . urlencode('buwana-sender') . '.php";
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

// Default newsletter headers
$email_from = 'Earthen <earthen@ecobricks.org>';
$email_subject = 'Solstice, Ayyew & Earthen';

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

// Fetch email stats
$query = "SELECT COUNT(*) AS total_members, SUM(CASE WHEN test_sent = 1 THEN 1 ELSE 0 END) AS sent_count FROM users_tb";
$result = $buwana_conn->query($query);
$row = $result->fetch_assoc();

$total_members = intval($row['total_members'] ?? 0);
$sent_count = intval($row['sent_count'] ?? 0);
$sent_percentage = ($total_members > 0) ? round(($sent_count / $total_members) * 100, 2) : 0;

// Fetch the last four sent members and the remaining pending ones
$status_limit = 20; // total rows to display in the status table
$sent_limit = 4;    // number of most recent sent entries

$query_sent = "SELECT buwana_id AS id, email, full_name AS name, bot_score, test_sent, test_sent_date_time
               FROM users_tb
               WHERE test_sent = 1
               ORDER BY test_sent_date_time DESC
               LIMIT {$sent_limit}";
$sent_result = $buwana_conn->query($query_sent);
$sent_members = $sent_result ? $sent_result->fetch_all(MYSQLI_ASSOC) : [];
$sent_count = count($sent_members);

$pending_limit = $status_limit - $sent_count;

$query_pending = "SELECT buwana_id AS id, email, full_name AS name, bot_score, test_sent, test_sent_date_time
                 FROM users_tb
                 WHERE test_sent = 0 AND processing IS NULL
                 ORDER BY created_at ASC
                 LIMIT {$pending_limit}";
$pending_result = $buwana_conn->query($query_pending);
$pending_members = $pending_result ? $pending_result->fetch_all(MYSQLI_ASSOC) : [];

$all_members = array_merge($sent_members, $pending_members);

// Processing stats for Chart.js
$processing_query = "SELECT
    SUM(CASE WHEN processing IS NULL THEN 1 ELSE 0 END) AS null_count,
    SUM(CASE WHEN processing = 0 THEN 1 ELSE 0 END) AS delivered_count,
    SUM(CASE WHEN processing = 1 THEN 1 ELSE 0 END) AS sending_count,
    SUM(CASE WHEN processing = 2 THEN 1 ELSE 0 END) AS failed_immediate_count,
    SUM(CASE WHEN processing = 3 THEN 1 ELSE 0 END) AS failed_later_count
    FROM users_tb";
$proc_result = $buwana_conn->query($processing_query);
$proc_row = $proc_result->fetch_assoc();
$processing_counts = [
    'unsent' => intval($proc_row['null_count'] ?? 0),
    'delivered' => intval($proc_row['delivered_count'] ?? 0),
    'sending' => intval($proc_row['sending_count'] ?? 0),
    'failed_immediate' => intval($proc_row['failed_immediate_count'] ?? 0),
    'failed_later' => intval($proc_row['failed_later_count'] ?? 0)
];

$processing_percentages = [];
foreach ($processing_counts as $key => $count) {
    $processing_percentages[$key] = ($total_members > 0) ? round(($count / $total_members) * 100, 2) : 0;
}
$unsent_percentage = $processing_percentages['unsent'];

require_once 'live-newsletter.php';  //the newsletter html

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_email']) && !$has_alerts) {
    $email_html = $_POST['email_html'] ?? '';
    $recipient_email = $_POST['email_to'] ?? '';
    $subscriber_id = $_SESSION['locked_subscriber_id'] ?? null;
    $is_test_mode = isset($_POST['test_mode']) && $_POST['test_mode'] == '1';

    if (!empty($email_html) && !empty($recipient_email) && ($subscriber_id || $is_test_mode)) {
        // The webhook previously confirmed sends, now we update immediately
        try {

            error_log("[BUWANA] → Sending " . ($is_test_mode ? 'TEST ' : '') . "{$recipient_email} by " . session_id());
            $send_ok = sendEmail($recipient_email, $email_html);

            if ($subscriber_id && !$is_test_mode) {
                if ($send_ok) {
                    // Mark as sent and processing (sending)
                    $stmt = $buwana_conn->prepare(
                        "UPDATE users_tb SET test_sent = 1, test_sent_date_time = NOW(), processing = 1 WHERE buwana_id = ?"
                    );
                } else {
                    // Mark attempt but leave processing NULL
                    $stmt = $buwana_conn->prepare(
                        "UPDATE users_tb SET test_sent = 1, test_sent_date_time = NOW() WHERE buwana_id = ?"
                    );
                }

                if ($stmt) {
                    $stmt->bind_param('i', $subscriber_id);
                    $stmt->execute();
                    $stmt->close();
                }
            }

            if ($send_ok) {
                error_log("[BUWANA] ✅ Mailgun accepted " . ($is_test_mode ? 'TEST ' : '') . "{$recipient_email} by " . session_id());
            } else {
                error_log("[BUWANA] ❌ Failed to send " . ($is_test_mode ? 'TEST ' : '') . "{$recipient_email} by " . session_id());
            }

            unset($_SESSION['locked_subscriber_id']);

            echo json_encode(['success' => $send_ok, 'message' => $send_ok ? '' : 'Sending failed']);
            exit();
        } catch (Exception $e) {
            if (!$is_test_mode && $subscriber_id) {
                $stmt = $buwana_conn->prepare(
                    "UPDATE users_tb SET test_sent = 1, test_sent_date_time = NOW() WHERE buwana_id = ?"
                );
                if ($stmt) {
                    $stmt->bind_param('i', $subscriber_id);
                    $stmt->execute();
                    $stmt->close();
                }
            }
            unset($_SESSION['locked_subscriber_id']);
            error_log("[BUWANA] ❌ Exception: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Server error']);
            exit();
        }
    } else {
        error_log("[BUWANA] ❌ Missing recipient or content");
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
            <h2 id="greeting">Buwana Newsletter Sender</h2>
            <p id="subgreeting">Our tool for sending our newsletter our email by email to our Buwana users.</p>
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
        <div id="test-email-container" class="form-row" style="display:none;background-color:var(--lighter);padding:20px;border:grey 1px solid;border-radius:12px;margin-top:20px;">
            <div id="left-colum" style="width: 100%;">
                <label for="test-email-toggle">📨 Send One Test Email</label>
                <p class="form-caption" style="margin-top:10px;">Will send this email once to russmaier@gmail.com</p>
            </div>
            <div id="right-column" style="width:100px;justify-content:center;">
                <label class="toggle-switch">
                    <input type="checkbox" id="test-email-toggle" value="1">
                    <span class="slider"></span>
                </label>
            </div>
        </div>





<div id="send-controls" style="height:500px;">
<form id="email-form" method="POST" style="margin-top: 50px;">
    <p><strong>From:</strong> <?php echo htmlspecialchars($email_from, ENT_QUOTES, 'UTF-8'); ?></p>
    <p><strong>Subject:</strong> <?php echo htmlspecialchars($email_subject, ENT_QUOTES, 'UTF-8'); ?></p>
    <label for="email_html">Newsletter HTML:</label>
    <textarea name="email_html" id="email_html" rows="10" style="width:100%;"><?php echo htmlspecialchars($email_template); ?></textarea>

    <!-- Hidden field for recipient email
        <input type="hidden" id="email_to" name="email_to" value="<?php echo htmlspecialchars($recipient_email); ?>">
-->
<input type="hidden" id="email_to" name="email_to" value="">
    <br><br>
<!-- Auto-send Button (hidden by default unless auto-send is enabled) -->
<button id="auto-send-button" style="display:none" type="submit" name="send_email" class="confirm-button enabled" <?php echo $has_alerts ? 'disabled' : ''; ?>>
    📨 Send
</button>


<!-- Test Send Button (visible by default when auto-send is off) -->
<button id="test-send-button" type="submit" name="send_email" class="confirm-button enabled" <?php echo $has_alerts ? 'disabled' : ''; ?>>
    📨 Send to russmaier@gmail.com
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
                <th>🤖</th>
                <th>Sent Date</th>
                <th>Sent</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($all_members as $member): ?>
                <tr>
                    <td><?php echo htmlspecialchars($member['name']); ?></td>
                    <td><?php echo htmlspecialchars($member['email']); ?></td>
                    <td><?php echo $member['bot_score'] ?? '0'; ?></td>
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
var processingData = <?php echo json_encode($processing_percentages); ?>;
var unsentPercentage = <?php echo json_encode($unsent_percentage); ?>;
</script>
<script>
$(document).ready(function () {
    let recipientEmail = '';
    let recipientName = '';
    let recipientId = null;
    let countdownInterval = null;
    let isSending = false; // prevent duplicate sends
    let sendDelay = 5;
    let sendFailCount = 0;


    const hasAlerts = <?php echo $has_alerts ? 'true' : 'false'; ?>;

    // Initialize doughnut chart
    const chartCtx = document.getElementById('processingChart').getContext('2d');
    new Chart(chartCtx, {
        type: 'doughnut',
        data: {
            labels: ['Unsent', 'Delivered', 'Sending', 'Failed Immediately', 'Failed Later'],
            datasets: [{
                data: [
                    processingData.unsent,
                    processingData.delivered,
                    processingData.sending,
                    processingData.failed_immediate,
                    processingData.failed_later
                ],
                backgroundColor: ['yellow', 'limegreen', '#90ee90', 'red', '#8b0000'],
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
    $('#unsent-percentage-label').text(unsentPercentage + '% Unsent');

    // Initialize DataTable for status overview
    const statusTable = $('#email-status-table').DataTable({
        order: [],
        pageLength: 10,
        responsive: true,
        autoWidth: false
    });
    $("div.dataTables_filter input").attr('placeholder', 'Search emails...');

    function refreshStatusTable() {
        $.getJSON('../scripts/get_buwana_email_status.php', function(resp) {
            if (resp.success) {
                const rows = resp.members.map(m => [
                    m.name,
                    m.email,
                    m.bot_score || '0',
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

    function updateVisibleButton() {
        if (!autoSendEnabled()) {
            $('#test-email-container').show();
            if (testSendEnabled()) {
                $('#test-send-button').show().html("📨 Send to russmaier@gmail.com");
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
        sendDelay = parseInt($('#send-delay-slider').val()) || 5;
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
        url: '../scripts/get_next_buwana_recipient.php',
        type: 'GET',
        dataType: 'json',
        success: function (response) {
            if (response.success) {
                // 🎯 Set recipient
                const sub = response.subscriber;
                recipientEmail = sub?.email || '';
                recipientName = sub?.name || '';
                recipientId = sub?.id || null;

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
        $.post('../scripts/log_sender_error.php', {message});
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

        const targetEmail = isTestMode ? "russmaier@gmail.com" : recipientEmail;

        if (!targetEmail) {
            alert("❌ No recipient available.");
            return;
        }

        if (isSending) return; // prevent duplicate calls
        isSending = true;

        console.log("🚀 Sending to:", targetEmail);

        // Show sending state
        $('#auto-send-button, #test-send-button').text("⏳ Sending...").prop('disabled', true);

        $.ajax({
            url: "", // Same page
            type: "POST",
            data: {
                send_email: "1",
                email_to: targetEmail,
                email_html: emailBody,
                test_mode: isTestMode ? 1 : 0
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
            url: '../scripts/reset_admin_alerts.php',
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
    sendDelay = parseInt(localStorage.getItem('sendDelay')) || 5;
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
