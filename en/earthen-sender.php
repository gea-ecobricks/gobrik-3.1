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

// Get the next recipient who hasn't received the test email and is NOT using @outlook, comcast or hotmail
$query = "SELECT id, email, name FROM earthen_members_tb
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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Equinox Recap: Spring update</title>
    <style>
        body {
            background-color: #ffffff;
            font-family: -apple-system, BlinkMacSystemFont, Roboto, Helvetica, Arial, sans-serif;
            font-size: 18px;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            color: #15212A;
        }

        a {
            color: #4B8501;
            text-decoration: none;
        }

        img {
            max-width: 100%;
            height: auto;
            border: 0;
            display: block;
            margin: 0 auto;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }

        .site-title {
            text-align: center;
            text-transform: uppercase;
            font-weight: bold;
            padding-bottom: 10px;
            font-size: 16px;
        }

        .post-title {
            font-size: 36px;
            font-weight: 700;
            text-align: center;
            margin: 30px 0 10px;
        }

        .post-meta {
            text-align: center;
            font-size: 13px;
            color: #73818c;
        }

        .post-content p {
            margin-bottom: 1.5em;
        }

        .feature-image-caption,
        .kg-card-figcaption {
            text-align: center;
            color: #73818c;
            font-size: 13px;
            margin-top: 5px;
        }

        .bookmark {
            border: 1px solid #e0e7eb;
            border-radius: 3px;
            margin: 20px 0;
            overflow: hidden;
        }

        .bookmark a {
            display: block;
            color: #15212A;
            text-decoration: none;
        }

        .bookmark .meta {
            font-size: 13px;
            color: #73818c;
        }

        .footer {
            text-align: center;
            font-size: 13px;
            color: #73818c;
            margin-top: 30px;
            border-top: 1px solid #e0e7eb;
            padding-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="https://earthen.io/">
            <img src="https://earthen.io/content/images/size/w1200/2022/08/email-cover-1200px.jpg" alt="Earthen Cover">
        </a>

        <div class="site-title">
            <img src="https://earthen.io/content/images/2022/07/page-logo.png" alt="Earthen Logo" width="44" height="44"><br>
            <a href="https://earthen.io/">Earthen</a>
        </div>

        <div class="post-title">
            <a href="https://earthen.io/p/99c9ba31-badb-4ba5-ba45-25c00a207c7f/">Earthen Spring Recap</a>
        </div>

        <div class="post-meta">
            By the Global Ecobrick Alliance • 22 Apr 2025<br>
            <a href="https://earthen.io/p/99c9ba31-badb-4ba5-ba45-25c00a207c7f/">View in browser</a>
        </div>

        <div class="post-content">
            <img src="https://earthen.io/content/images/size/w2000/2025/04/easter-spring-update.png" alt="Earthcal">
            <p class="feature-image-caption">
                And the planets align! This week Earth, Venus, Mercury, Jupiter and Neptune align - Image: <a href="https://cycles.earthen.io/">Earthcal</a>
            </p>

            <p>Happy Earth Day!  Whether it's the onset of Fall or Spring for you, with the passing of the Equinox, Eid and the arrival of Easter... it's time for our GEA Earthen quarterly update!</p>

            <p>In celebration of our planet, its crucial to remember, that the way something is said has more meaning than what is said.  And that's the case with this very newsletter! The GEA has proudly completely ditched its reliance on corporate technology platforms for 2025.</p>

            <p>This Earthen update is being sent to using the opensource Ghost.org newsletter platform that we run on our own server. We've rebuilt our GoBrik app from the ground up to get off amazon servers. Our center circle has ditched whatsapp for Signal. We're using Nextcloud instead of dropbox/google for our docs. And our dev team has built our own open source Buwana account system so you don't have to sign into our services with an apple or facebook account!</p>

            <div class="bookmark">
                <a href="https://earthen.io/gobrik-2025-accounts-ditching-big-tech-2/">

                    <div style="padding: 10px;">
                        <strong>We’ve ditched big-tech for 2025</strong><br>
                        <span class="meta">Earthen • GEA Center Circle</span>
                    </div>
                </a>
            </div>

            <br><br>
             <img src="https://earthen.io/content/images/2025/04/image.png" alt="UN condemns systematic destruction">

            <p class="kg-card-figcaption">UN rights expert condemns ‘systematic’ war-time mass destruction of homes.  <a href="https://news.un.org/en/story/2024/03/1147272">UN.org</a></p>

            <p>Second, we’re now USD free. Over a year ago, our GEA team, observing the direct connection between the US support of the ecocide and genocide in Gaza, we decided to no longer accept, hold, or denominate our accounting in USD.</p>

            <p>As the occupation forces continue to wage destruction of homes and biomes in Gaza and the West Bank, we grieve and are deeply saddened.  This goes against all our  ecological and humanitarian principles.  The GEA stands by our decision to disengage from the petro-captical currency that is behind the social oppression and ecological depletion in Palestine (and other places around the world). We've thus moved our accounting to Indonesian Rupiahs instead.</p>

            <div class="bookmark">
                <a href="https://earthen.io/were-ditching-the-us-dollar/">
                    <div style="padding: 10px;">
                        <strong>We’ve moved on from the US Dollar</strong><br>
                        <span class="meta">Earthen • GEA Center Circle</span>
                    </div>
                </a>
            </div>

            <br><br>

<img src="https://earthen.io/content/images/size/w1600/2025/04/image-1.png" alt="Bamboo build">
            <p>Finally, we're excited to share a breakthrough by Russell Maier, one of our founders and a lead developer on the GEA team. Russell and his engineering team has been working on bamboo to connect ecobricks in all sorts of new geometric ways! Their initial experiments are very promising! We're really excited to see where this goes.</p>


            <div class="bookmark">
                <a href="https://earthen.io/a-new-way-to-connect-ecobricks-2/">
                    <div style="padding: 10px;">
                        <strong>New ways to connect ecobricks</strong><br>
                        <span class="meta">Earthen • Russell Maier</span>
                    </div>
                </a>
            </div>

            <p>Stay tuned. As we get rolling with our new GoBrik and Earthen newsletter this year, we'll share more great regenerative news, events and developments in the plastic transition and earthen movement.</p>
        </div>

        <div class="footer">
            <p><em>Together we can be the transition to ever increasing harmony with the cycles of life.</em></p>
            <p>Earthen © 2025 – <a href="https://earthen.io/unsubscribe/?uuid=611f7d90-e87c-4c43-ab51-0772a7883703&key=c8c3faf87323b6ad7a8b96bcc9f9d742316e82dc604c69de46e524bcb11e3104&newsletter=7bbd5ff6-f69e-4ff0-a9d3-67963d85410b">Unsubscribe</a></p>
            <p style="margin-top: 20px;"><a href="https://ghost.org/?via=pbg-newsletter"><img src="https://static.ghost.org/v4.0.0/images/powered.png" width="142" height="30" alt="Powered by Ghost"></a></p>
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
            $updateQuery = "UPDATE earthen_members_tb SET test_sent = 1, test_sent_date_time = NOW() WHERE email = ?";
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

        <p>Total Members: <strong><?php echo $total_members; ?></strong></p>
    <p>Emails Sent: <strong><?php echo $sent_count; ?></strong> (<?php echo $sent_percentage; ?>%)</p>


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
    // Declare globally once
    let countdownTimer;
    let countdown = 2;

    const hasAlerts = <?php echo $has_alerts ? 'true' : 'false'; ?>;
    const recipientEmail = $('#email_to').val().trim();

    const autoSendEnabled = () => $('#auto-send-toggle').is(':checked');
    const testSendEnabled = () => $('#test-email-toggle').is(':checked');

    // 🟢 Button display handler
    function updateVisibleButton() {
        if (autoSendEnabled()) {
            $('#test-send-button').hide();
            $('#auto-send-button').show().html(`📨 Send to ${recipientEmail || 'recipient'}`);
        } else if (testSendEnabled()) {
            $('#auto-send-button').hide();
            $('#test-send-button').show().html("📨 Send to russmaier@gmail.com");
        } else {
            $('#auto-send-button').hide();
            $('#test-send-button').show().html("📨 Send (no recipient selected)");
        }
    }

    // 🔹 Load localStorage
    const savedAutoSend = localStorage.getItem('autoSend') === 'true';
    const savedTestSend = localStorage.getItem('testSend') === 'true';

    $('#auto-send-toggle').prop('checked', savedAutoSend);
    $('#test-email-toggle').prop('checked', savedTestSend);
    toggleTestCheckbox();
    updateVisibleButton();

    // 🔹 Prevent send if alerts
    if (hasAlerts) {
        alert("⚠️ Unaddressed Admin Alerts Exist! You cannot send emails until they are resolved.");
        $('#auto-send-button, #test-send-button').prop('disabled', true);
        return;
    }

    // ✅ Restart countdown if saved autoSend
    if (savedAutoSend) {
        countdown = 2;
        startCountdown();
    } else {
        $('#countdown-timer').hide();
    }

    // 🔹 Toggle listeners
    $('#auto-send-toggle').on('change', function () {
        const isChecked = $(this).is(':checked');
        localStorage.setItem('autoSend', isChecked);
        toggleTestCheckbox();
        updateVisibleButton();

        if (isChecked) {
            countdown = 2;
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

    // 🔹 Manual Send
    $('#test-send-button, #auto-send-button').on('click', function (event) {
        event.preventDefault();
        $('#email-form').trigger('submit');
    });

    // 🔹 Submit handler
    $('#email-form').on('submit', function (event) {
        event.preventDefault();

        const emailBody = $('#email_html').val().trim();
        const isTestMode = testSendEnabled() && !autoSendEnabled();

        if (!emailBody) {
            alert("⚠️ Please fill out the email content before sending.");
            return;
        }

        // Visual sending state
        $('#auto-send-button, #test-send-button').text("⏳ Sending...").prop('disabled', true);

        // Prevent duplicate trigger
        $('#email-form').off('submit');

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
                    $('#test-send-button').text("✅ Sent!").prop('disabled', true);
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

        // Normal Send
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
                $('#auto-send-button').text(`✅ Sent to ${recipientEmail}!`).prop('disabled', true);
                localStorage.removeItem('autoSend');
                localStorage.removeItem('testSend');
                setTimeout(() => location.reload(), 1000);
            },
            error: function () {
                alert("❌ Failed to send the email.");
            }
        });
    });

    // 🔹 Countdown timer logic
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

    // 🔹 Stop button
    $('#stop-timer-btn').on('click', function () {
        clearInterval(countdownTimer);
        $('#countdown-timer').hide();
        $(this).hide();
    });

    // 🔹 Toggle test checkbox visibility
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