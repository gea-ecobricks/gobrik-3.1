<?php
require_once '../earthenAuth_helper.php'; // Authentication helper
require '../vendor/autoload.php'; // Composer autoload

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

// Set page variables
$lang = basename(dirname($_SERVER['SCRIPT_NAME']));
$version = '0.53';
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
        window.location.href = "login.php?redirect=' . urlencode($page) . '.php";
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
$query = "SELECT COUNT(*) AS total_members, SUM(CASE WHEN test_sent = 1 THEN 1 ELSE 0 END) AS sent_count FROM earthen_members_tb";
$result = $buwana_conn->query($query);
$row = $result->fetch_assoc();

$total_members = intval($row['total_members'] ?? 0);
$sent_count = intval($row['sent_count'] ?? 0);
$sent_percentage = ($total_members > 0) ? round(($sent_count / $total_members) * 100, 2) : 0;

// Fetch the 3 most recently sent emails
$query_sent = "SELECT id, email, name, test_sent, test_sent_date_time FROM earthen_members_tb WHERE test_sent = 1 ORDER BY test_sent_date_time DESC LIMIT 3";
$sent_result = $buwana_conn->query($query_sent);
$sent_members = $sent_result->fetch_all(MYSQLI_ASSOC);

// Fetch the next 7 pending emails
$query_pending = "SELECT id, email, name, test_sent, test_sent_date_time FROM earthen_members_tb WHERE test_sent = 0 ORDER BY id ASC LIMIT 7";
$pending_result = $buwana_conn->query($query_pending);
$pending_members = $pending_result->fetch_all(MYSQLI_ASSOC);

// Merge sent and pending for display
$all_members = array_merge($sent_members, $pending_members);


require_once 'live-newsletter.php';  //the newsletter html

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_email']) && !$has_alerts) {
    $email_html = $_POST['email_html'] ?? '';
    $recipient_email = $_POST['email_to'] ?? '';
    $subscriber_id = $_SESSION['locked_subscriber_id'] ?? null;

    if (!empty($email_html) && !empty($recipient_email) && $subscriber_id) {
        try {
            if (sendEmail($recipient_email, $email_html)) {
                // ✅ Mark as sent
                $stmt = $buwana_conn->prepare("UPDATE earthen_members_tb SET test_sent = 1, test_sent_date_time = NOW() WHERE id = ? AND test_sent = 0");

                $stmt->bind_param("i", $subscriber_id);
                $stmt->execute();
                $stmt->close();

                error_log("[EARTHEN] ✅ COMMITTED: {$recipient_email} by " . session_id());

                unset($_SESSION['locked_subscriber_id']); // Clean up
                echo json_encode(['success' => true]);
                exit();
            } else {
                error_log("[EARTHEN] ❌ Failed to send: {$recipient_email} by " . session_id());
                echo json_encode(['success' => false, 'message' => 'Sending failed']);
                exit();
            }
        } catch (Exception $e) {
            error_log("[EARTHEN] ❌ Exception: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Server error']);
            exit();
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Missing recipient or content']);
        exit();
    }
}


// Email sending function
function sendEmail($to, $htmlBody) {
    $client = new Client(['base_uri' => 'https://api.eu.mailgun.net/v3/']);
    $mailgunApiKey = getenv('EARTHEN_MAILGUN_SENDING_KEY');
    $mailgunDomain = 'earthen.ecobricks.org';

    try {
         $response = $client->post("https://api.eu.mailgun.net/v3/{$mailgunDomain}/messages", [
        'auth' => ['api', $mailgunApiKey],
        'form_params' => [
            'from' => 'Earthen <earthen@ecobricks.org>',
            'to' => $to,
            'subject' => 'Spring Earthen Update',
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



<div class="splash-title-block"></div>
<div id="splash-bar"></div>
<div id="top-page-image" class="message-birded top-page-image"></div>

<!-- SENDER FORM CONTENT -->
    <div class="form-container">

        <?php if ($has_alerts): ?>
        <div style="background: #ffdddd; padding: 15px; border-left: 5px solid red; margin-bottom: 20px;">
            <h3 style="color: red;">⚠️ Admin Alerts Found!</h3>
            <ul>
                <?php foreach ($alerts as $alert): ?>
                    <li><strong><?php echo htmlspecialchars($alert['alert_title']); ?>:</strong> <?php echo htmlspecialchars($alert['alert_message']); ?></li>
                <?php endforeach; ?>
            </ul>
            <p>Please resolve these alerts before proceeding.</p>
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
<div style="margin: 10px 0;">
  <label for="auto-send-toggle" style="font-weight: bold; font-size: 16px;display:flex;">
    <input type="checkbox" id="auto-send-toggle" style="transform: scale(1.2); margin-right: 8px;">
  <p>Uncheck this to prevent the email from sending automatically after countdown.
  </p>
</div>


        <!-- Send one test email (hidden unless auto-send is off) -->
        <div id="test-email-container" style="margin: 10px 0; display: none;">
            <label for="test-email-toggle" style="font-weight: bold; font-size: 16px;">
                <input type="checkbox" id="test-email-toggle" style="transform: scale(1.2); margin-right: 8px;">
                Send one test email
            </label>
            <p style="font-size: 13px; color: #666;">Will send this email once to russmaier@gmail.com</p>
        </div>




   <form id="email-form" method="POST">
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
    <p>Email will send in <span id="countdown">1</span> seconds...</p>
    <button type="button" id="stop-timer-btn" class="confirm-button delete">🛑 Stop Timer</button>
</div>





    <h3>Email Sending Status:</h3>
    <table border="1" width="100%">
        <thead>
            <tr>
                <th>ID</th>
                <th>Email</th>
                <th>Name</th>
                <th>Sent</th>
                <th>Sent Date</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($all_members as $member): ?>
                <tr>
                    <td><?php echo $member['id']; ?></td>
                    <td><?php echo $member['email']; ?></td>
                    <td><?php echo $member['name']; ?></td>
                    <td><?php echo $member['test_sent'] ? '✅' : '❌'; ?></td>
                    <td><?php echo $member['test_sent_date_time'] ?? 'N/A'; ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<script>
$(document).ready(function () {
    let recipientEmail = '';
    let recipientName = '';
    let recipientId = null;

    const hasAlerts = <?php echo $has_alerts ? 'true' : 'false'; ?>;

    const autoSendEnabled = () => $('#auto-send-toggle').is(':checked');
    const testSendEnabled = () => $('#test-email-toggle').is(':checked');

    function updateVisibleButton() {
        if (testSendEnabled() && !autoSendEnabled()) {
            $('#test-email-container').show();
            $('#test-send-button').show().html("📨 Send to russmaier@gmail.com");
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

    // 🟢 Fetch next recipient via AJAX
  function fetchNextRecipient() {
    $.ajax({
        url: '../scripts/get_next_recipient.php',
        type: 'GET',
        dataType: 'json',
        success: function (response) {
            if (response.success) {
                // 🎯 Set recipient
                const sub = response.subscriber;
                recipientEmail = sub?.email || '';
                recipientName = sub?.name || '';
                recipientId = sub?.id || null;

                $('#email_to').val(recipientEmail);

                // 📊 Update stats
                if (response.stats) {
                    $('#total-members').text(response.stats.total);
                    $('#sent-count').text(response.stats.sent);
                    $('#sent-percentage').text(response.stats.percentage.toFixed(2));
                }

                updateVisibleButton();

                // 🟢 Auto-send the next email if enabled
                if ($('#auto-send-toggle').is(':checked')) {
                    setTimeout(() => {
                        sendEmail();
                    }, 200); // small buffer for DOM update
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
            alert("❌ Failed to fetch next recipient.");
        }
    });
}



    // 🟢 Shared send function
    function sendEmail() {
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

        // Show sending state
        $('#auto-send-button, #test-send-button').text("⏳ Sending...").prop('disabled', true);

        $.ajax({
            url: "", // Same page
            type: "POST",
            data: {
                send_email: "1",
                email_to: targetEmail,
                email_html: emailBody
            },
            success: function () {
                if (isTestMode) {
                    $('#test-send-button').text("✅ Sent!").prop('disabled', true);
                    localStorage.removeItem('testSend');
                } else {
                    $('#auto-send-button').text(`✅ Sent to ${recipientEmail}`);
                    console.log("📫 Sent to:", recipientEmail);

                    // Chain to next
                    fetchNextRecipient(true); // fetch + auto-send next
                }
            },
            error: function () {
                alert("❌ Failed to send the email.");
                updateVisibleButton();
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
            sendEmail();
        }
    });

    // 🔹 Initial state from localStorage
    const savedAutoSend = localStorage.getItem('autoSend') === 'true';
    const savedTestSend = localStorage.getItem('testSend') === 'true';

    $('#auto-send-toggle').prop('checked', savedAutoSend);
    $('#test-email-toggle').prop('checked', savedTestSend);

    updateVisibleButton();

    if (hasAlerts) {
        alert("⚠️ Unaddressed Admin Alerts Exist! You cannot send emails until they are resolved.");
        $('#auto-send-button, #test-send-button').prop('disabled', true);
    } else {
        fetchNextRecipient(true); // Fetch on page load, and auto-send if toggled
    }
});


</script>











<!--FOOTER STARTS HERE-->
<?php require_once ("../footer-2024.php"); ?>



</body>
</html>