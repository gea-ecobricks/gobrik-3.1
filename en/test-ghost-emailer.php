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

// LOGIN & ADMIN CHECK:
if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

$buwana_id = $_SESSION['buwana_id'];
require_once '../gobrikconn_env.php';

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
$query = "SELECT COUNT(*) AS total_members, SUM(CASE WHEN test_sent = 1 THEN 1 ELSE 0 END) AS sent_count FROM ghost_test_email_tb";
$result = $buwana_conn->query($query);
$row = $result->fetch_assoc();

$total_members = intval($row['total_members'] ?? 0);
$sent_count = intval($row['sent_count'] ?? 0);
$sent_percentage = ($total_members > 0) ? round(($sent_count / $total_members) * 100, 2) : 0;

// Fetch the 3 most recently sent emails
$query_sent = "SELECT id, email, name, test_sent, test_sent_date_time FROM ghost_test_email_tb WHERE test_sent = 1 ORDER BY test_sent_date_time DESC LIMIT 3";
$sent_result = $buwana_conn->query($query_sent);
$sent_members = $sent_result->fetch_all(MYSQLI_ASSOC);

// Fetch the next 7 pending emails
$query_pending = "SELECT id, email, name, test_sent, test_sent_date_time FROM ghost_test_email_tb WHERE test_sent = 0 ORDER BY id ASC LIMIT 7";
$pending_result = $buwana_conn->query($query_pending);
$pending_members = $pending_result->fetch_all(MYSQLI_ASSOC);

// Merge sent and pending for display
$all_members = array_merge($sent_members, $pending_members);

// Get the next recipient who hasn't received the test email and is NOT using @outlook, comcast or hotmail
$query = "SELECT id, email, name FROM ghost_test_email_tb
          WHERE test_sent = 0
--           AND email NOT LIKE '%@outlook.%'
--           AND email NOT LIKE '%@live.%'
          AND email NOT LIKE '%@hotmail.%'
          AND email NOT LIKE '%@comcast%'
          ORDER BY id ASC LIMIT 1";
$result = $buwana_conn->query($query);
$subscriber = $result->fetch_assoc();

// Initialize variables
$subscriber_id = null;
$recipient_email = null;

if ($subscriber) {
    $recipient_email = $subscriber['email']; // Use actual recipient from the database
    $subscriber_id = $subscriber['id'];
}

// Ensure there is always an email to send, otherwise stop the process
if (!$recipient_email) {
    die("No pending recipients found. Email sending process stopped.");
}

//Validate again before sending to avoid errors in form submission
//strpos($recipient_email, '@live.') !== false || strpos($recipient_email, '@outlook.') !== false ||
if ( strpos($recipient_email, '@hotmail.') !== false || strpos($recipient_email, '@comcast.') !== false) {
    die("Skipping @outlook & @hotmail emails. No valid recipient found.");
}


// Generate unsubscribe link
$unsubscribe_link = "https://gobrik.com/emailing/unsubscribe.php?email=" . urlencode($recipient_email);



// Default email HTML with dynamic unsubscribe link
$email_template = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Equinox Recap: Spring update</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #15212A;
            line-height: 1.5;
            background-color: #ffffff;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: auto;
            padding: 20px;
            background-color: #ffffff;
        }
        .header {
            text-align: center;
            padding-bottom: 20px;
        }
        .header img {
            max-width: 100%;
        }
        .post-title {
            font-size: 26px;
            font-weight: bold;
            text-align: center;
            margin-bottom: 10px;
        }
        .post-meta {
            font-size: 14px;
            color: #738a94;
            text-align: center;
            margin-bottom: 30px;
        }
        .image-caption {
            font-size: 13px;
            color: #738a94;
            text-align: center;
            margin-top: 5px;
        }
        .content p {
            margin-bottom: 16px;
        }
        .bookmark-card {
            border: 1px solid #e0e7eb;
            padding: 16px;
            margin-bottom: 24px;
            background-color: #f9f9f9;
        }
        .bookmark-title {
            font-size: 16px;
            font-weight: bold;
            color: #15212A;
        }
        .bookmark-description {
            font-size: 14px;
            color: #73818c;
            margin-top: 8px;
        }
        .footer {
            text-align: center;
            color: #73818c;
            font-size: 12px;
            margin-top: 30px;
        }
        .footer a {
            color: #73818c;
            text-decoration: underline;
        }
    </style>
</head>
<body>
<div class="container">

    <div class="header">
        <a href="https://earthen.io/">
            <img src="https://earthen.io/content/images/size/w1200/2022/08/email-cover-1200px.jpg" alt="Earthen">
        </a>
    </div>

    <div class="post-title">
        Equinox Recap: Spring update
    </div>
    <div class="post-meta">
        By GEA Center Circle • 8 Apr 2025
        <br>
        <a href="https://earthen.io/p/99c9ba31-badb-4ba5-ba45-25c00a207c7f/" style="color: #738a94; text-decoration: underline;">View in browser</a>
    </div>

    <div class="header">
        <img src="https://earthen.io/content/images/size/w1200/2025/03/Screenshot-From-2025-03-21-09-29-34.png" alt="Feature Image">
    </div>
    <div class="image-caption">
        Earth has now completed a quarter of its solar cycle since the December 21st Solstice (And Earth and Venus are in opposition too!) — Image: <a href="https://cycles.earthen.io/" style="color: #4B8501;">Earthcal</a>
    </div>

    <div class="content">
        <p>Whether it's the onset of Fall or Spring for you, we've passed the 2025 Equinox, the Eid holiday and... it's time for our GEA Earthen quarterly update!</p>

        <p>We're on to new ways of connecting ecobricks with bamboo to make geometric lattices! See the blog from Russell below...</p>

        <p>As always, the medium is the most important message — and that's the case with this very newsletter!</p>

        <p>The GEA has proudly completely ditched its reliance on corporate technologies platforms for 2025. This Earthen update is being sent using the open-source <a href="https://ghost.org/" style="color: #4B8501;">Ghost</a> newsletter platform hosted on our own server.</p>

        <p>We've rebuilt GoBrik from the ground up to get off <s>Amazon</s> servers (we're now on <a href="https://dewaweb.com" style="color: #4B8501;">Dewaweb.com</a>). Our team ditched <s>WhatsApp</s> for Signal, <s>Dropbox/Google</s> for Nextcloud, and built the open-source Buwana account system — so you don't need <s>Facebook</s> or <s>Google</s> logins!</p>

        <div class="bookmark-card">
            <div class="bookmark-title"><a href="https://earthen.io/gobrik-2025-accounts-ditching-big-tech-2/" style="color: #15212A;">We’ve ditched big-tech for 2025</a></div>
            <div class="bookmark-description">It’s 2025! And the urgency to get off our reliance on big tech has never been greater...</div>
        </div>

        <p>We've also fully disengaged our finances from the US dollar. Billions of dollars are used to fund the genocide and ecocide in occupied Palestine. Staying true to our principles, we’ve stopped using USD in any way — we no longer accept, hold, or use it for accounting.</p>

        <div class="bookmark-card">
            <div class="bookmark-title"><a href="https://earthen.io/were-ditching-the-us-dollar/" style="color: #15212A;">We’ve moved on from the US Dollar</a></div>
            <div class="bookmark-description">Today we’re announcing our transition from US dollars in our finances and accounting.</div>
        </div>

        <p>Finally, a breakthrough! Russell Maier and our engineering team have discovered exciting ways to connect ecobricks using bamboo. The experiments show great promise.</p>

        <div class="bookmark-card">
            <div class="bookmark-title"><a href="https://earthen.io/a-new-way-to-connect-ecobricks-2/" style="color: #15212A;">New ways to connect ecobricks</a></div>
            <div class="bookmark-description">How can used plastic be put to good use without relying on capital and industry?</div>
        </div>

        <p>Stay tuned. This year with GoBrik and Earthen updates, we’ll be sharing more regenerative news, events and stories in the plastic transition and Earthen movement.</p>
    </div>

    <div class="footer">
        <p><em>Together we can be the transition to ever increasing harmony with the cycles of life.</em></p>
        <p>Earthen © 2025 • <a href="$unsubscribe_link">Unsubscribe</a></p>
        <p>Powered by <a href="https://ghost.org/">Ghost</a></p>
    </div>
</div>
</body>
</html>
HTML;


// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_email']) && !$has_alerts) {
    $email_html = $_POST['email_html'] ?? '';  // ✅ Use correct field name
    $recipient_email = $_POST['email_to'] ?? '';    // ✅ Use correct variable

    if (!empty($email_html) && !empty($recipient_email)) {
        if (sendEmail($recipient_email, $email_html)) {
            // ✅ Mark email as sent in the database
            $updateQuery = "UPDATE ghost_test_email_tb SET test_sent = 1, test_sent_date_time = NOW() WHERE email = ?";
            $stmt = $buwana_conn->prepare($updateQuery);
            $stmt->bind_param("s", $recipient_email);
            $stmt->execute();
            $stmt->close();

            // ✅ Redirect to refresh and get the next recipient
            header("Location: test-ghost-emailer.php");
            exit();
        } else {
            echo "<script>alert('❌ Email failed to send! Check logs.');</script>";
        }
    }
}

// ✅ Handle case where no more recipients exist
if (!$recipient_email) {
    echo "<script>alert('✅ All emails have been sent! No more pending recipients.');</script>";
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
            'from' => '[Earthen] GEA Center Circle <earthen@ecobricks.org>',
            'to' => $to,
            'subject' => 'We\'ve moved on from the US dollar',
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
?>

<?php require_once("../includes/admin-panel-inc.php"); ?>

<div class="form-container">
    <h2>Ghost Newsletter Emailer</h2>

   <!-- Auto-send toggle -->
<div style="margin: 10px 0;">
    <label for="auto-send-toggle" style="font-weight: bold; font-size: 16px;">
        <input type="checkbox" id="auto-send-toggle" style="transform: scale(1.2); margin-right: 8px;">
        Enable Auto-Send
    </label>
    <p style="font-size: 13px; color: #666;">Uncheck this to prevent the email from sending automatically after countdown.</p>
</div>

<!-- Send one test email (hidden unless auto-send is off) -->
<div id="test-email-container" style="margin: 10px 0; display: none;">
    <label for="test-email-toggle" style="font-weight: bold; font-size: 16px;">
        <input type="checkbox" id="test-email-toggle" style="transform: scale(1.2); margin-right: 8px;">
        Send one test email
    </label>
    <p style="font-size: 13px; color: #666;">Will send this email once to russmaier@gmail.com</p>
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
        </div>
    <?php endif; ?>

    <p>Total Members: <strong><?php echo $total_members; ?></strong></p>
    <p>Emails Sent: <strong><?php echo $sent_count; ?></strong> (<?php echo $sent_percentage; ?>%)</p>

   <form id="email-form" method="POST">
    <label for="email_html">Newsletter HTML:</label>
    <textarea name="email_html" id="email_html" rows="10" style="width:100%;"><?php echo htmlspecialchars($email_template); ?></textarea>

    <!-- Hidden field for recipient email -->
    <input type="hidden" id="email_to" name="email_to" value="<?php echo htmlspecialchars($recipient_email); ?>">

    <br><br>
<!-- Auto-send Button (hidden by default unless auto-send is enabled) -->
<button id="auto-send-button" style="display:none" type="submit" name="send_email" class="confirm-button enabled" <?php echo $has_alerts ? 'disabled' : ''; ?>>
    📨 Send to <?php echo htmlspecialchars($recipient_email); ?>
</button>

<!-- Test Send Button (visible by default when auto-send is off) -->
<button id="test-send-button" type="submit" name="send_email" class="confirm-button enabled" <?php echo $has_alerts ? 'disabled' : ''; ?>>
    📨 Send to russmaier@gmail.com
</button>


</form>

<div id="countdown-timer" style="margin-top: 10px; display: none; text-align:center; width:100%;">
    <p>Email will send in <span id="countdown">2</span> seconds...</p>
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

    // 🔹 PART ONE: Config & Setup
    const hasAlerts = <?php echo $has_alerts ? 'true' : 'false'; ?>;
    let countdownTimer;
    let countdown = 2;
    const recipientEmail = $('#email_to').val().trim();

    const autoSendEnabled = () => $('#auto-send-toggle').is(':checked');
    const testSendEnabled = () => $('#test-email-toggle').is(':checked');

    function updateVisibleButton() {
        if (autoSendEnabled()) {
            $('#test-send-button').hide();
            $('#auto-send-button').show();
            $('#auto-send-button').html(`📨 Send to ${recipientEmail || 'recipient'}`);
        } else if (testSendEnabled()) {
            $('#auto-send-button').hide();
            $('#test-send-button').show();
            $('#test-send-button').html("📨 Send to russmaier@gmail.com");
        } else {
            $('#auto-send-button').hide();
            $('#test-send-button').show();
            $('#test-send-button').html("📨 Send (no recipient selected)");
        }
    }

    // 🔹 PART TWO: Load from localStorage
    const savedAutoSend = localStorage.getItem('autoSend') === 'true';
    const savedTestSend = localStorage.getItem('testSend') === 'true';

    $('#auto-send-toggle').prop('checked', savedAutoSend);
    $('#test-email-toggle').prop('checked', savedTestSend);
    toggleTestCheckbox();
    updateVisibleButton();

    if (hasAlerts) {
        alert("⚠️ Unaddressed Admin Alerts Exist! You cannot send emails until they are resolved.");
        $('#auto-send-button, #test-send-button').prop('disabled', true);
        return;
    }

    if (savedAutoSend) {
        startCountdown();
    } else {
        $('#countdown-timer').hide();
    }

    // 🔹 PART THREE: Toggle Watchers
    $('#auto-send-toggle').on('change', function () {
        const isChecked = $(this).is(':checked');
        localStorage.setItem('autoSend', isChecked);
        toggleTestCheckbox();
        updateVisibleButton();

        if (isChecked) {
            startCountdown();
        } else {
            clearInterval(countdownTimer);
            $('#countdown-timer').hide();
        }
    });

    $('#test-email-toggle').on('change', function () {
        const isChecked = $(this).is(':checked');
        localStorage.setItem('testSend', isChecked);
        updateVisibleButton();
    });

    // 🔹 PART FOUR: Manual Button Clicks
    $('#test-send-button, #auto-send-button').on('click', function (event) {
        event.preventDefault();
        $('#email-form').trigger('submit');
    });

    // 🔹 PART FIVE: Submit Handler
    $('#email-form').on('submit', function (event) {
        event.preventDefault();

        const emailBody = $('#email_html').val().trim();

        if (!emailBody) {
            alert("⚠️ Please fill out the email content before sending.");
            return;
        }

        const isTestMode = testSendEnabled() && !autoSendEnabled();

        if (isTestMode) {
            $.ajax({
                url: "",
                type: "POST",
                data: {
                    send_email: "1",
                    email_to: "russmaier@gmail.com",
                    email_html: emailBody
                },
                success: function () {
                    $('#test-send-button').html(`✅ Sent to russmaier@gmail.com!`).prop('disabled', true);
                    localStorage.removeItem('autoSend');
                    localStorage.removeItem('testSend');
                    setTimeout(() => location.reload(), 2000);
                },
                error: function () {
                    alert("❌ Failed to send the test email.");
                }
            });
            return;
        }

        // ✅ Real recipient send
        if (!recipientEmail) {
            alert("⚠️ No recipient found for regular sending.");
            return;
        }

        $.ajax({
            url: "",
            type: "POST",
            data: {
                send_email: "1",
                email_to: recipientEmail,
                email_html: emailBody
            },
            success: function () {
                $('#auto-send-button').html(`✅ Sent to ${recipientEmail}!`).prop('disabled', true);
                localStorage.removeItem('autoSend');
                localStorage.removeItem('testSend');
                setTimeout(() => location.reload(), 1000);
            },
            error: function () {
                alert("❌ Failed to send the email.");
            }
        });
    });

    // 🔹 PART SIX: Countdown
    function startCountdown() {
        $('#countdown-timer').show();
        $('#stop-timer-btn').show();
        updateCountdownText();

        countdownTimer = setInterval(() => {
            countdown--;
            updateCountdownText();

            if (countdown <= 0) {
                clearInterval(countdownTimer);
                if (autoSendEnabled()) {
                    $('#email-form').trigger('submit');
                } else {
                    $('#countdown-timer').hide();
                }
            }
        }, 1000);
    }

    function updateCountdownText() {
        $('#countdown').text(countdown);
    }

    // 🔹 PART SEVEN: Stop Button
    $('#stop-timer-btn').on('click', function () {
        clearInterval(countdownTimer);
        $('#countdown-timer').hide();
        $(this).hide();
    });

    // 🔹 PART EIGHT: Toggle Test Checkbox Visibility
    function toggleTestCheckbox() {
        if (!autoSendEnabled()) {
            $('#test-email-container').show();
        } else {
            $('#test-email-container').hide();
            $('#test-email-toggle').prop('checked', false);
        }
    }
});
</script>







<!--FOOTER STARTS HERE-->
<?php require_once ("../footer-2024.php"); ?>



</body>
</html>