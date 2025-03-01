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

// Get the next recipient who hasn't received the test email and is NOT using @outlook
$query = "SELECT id, email, name FROM ghost_test_email_tb
          WHERE test_sent = 0
          AND email NOT LIKE '%@outlook.%'
          AND email NOT LIKE '%@hotmail.%'
          AND email NOT LIKE '%@comcast.%'
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

// Validate again before sending to avoid errors in form submission
if (strpos($recipient_email, '@outlook.') !== false || strpos($recipient_email, '@hotmail.') !== false) {
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
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>We've moved on from the US Dollar</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #15212A;
            line-height: 1.5;
            background-color: #fff;
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
            height: auto;
        }
        .post-title {
            font-size: 24px;
            font-weight: bold;
            text-align: center;
            margin-top: 20px;
        }
        .post-meta {
            font-size: 14px;
            color: #738a94;
            text-align: center;
            margin-bottom: 20px;
        }
        .content {
            margin-top: 20px;
        }
        .content p {
            margin-bottom: 15px;
        }
        .image-caption {
            font-size: 14px;
            color: #738a94;
            text-align: center;
            margin-top: 5px;
        }
        .footer {
            font-size: 12px;
            text-align: center;
            color: #738a94;
            margin-top: 20px;
        }
        .footer a {
            color: #4B8501;
            text-decoration: none;
        }
        .button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #4B8501;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-size: 16px;
            text-align: center;
        }
        .button:hover {
            background-color: #3A6A00;
        }
    </style>
</head>
<body>
    <div class="container">

        <!-- Header -->
        <div class="header">
            <a href="https://earthen.io/">
                <img src="https://earthen.io/content/images/size/w1200/2022/08/email-cover-1200px.jpg" alt="Earthen">
            </a>
        </div>

        <!-- View in Browser Link -->
        <div class="post-meta">
            <a href="https://earthen.io/p/f021c1cc-d488-4564-86aa-65c095a8a2c2/" style="color: #738a94; text-decoration: underline;">
                View in Browser
            </a>
        </div>

        <!-- Title -->
        <div class="post-title">
            <a href="https://earthen.io/p/f021c1cc-d488-4564-86aa-65c095a8a2c2/" style="text-decoration: none; color: #15212A;">
                We've moved on from the US Dollar
            </a>
        </div>

        <!-- Meta Information -->
        <div class="post-meta">
            By GEA Center Circle • 25 Feb 2025
        </div>

        <!-- Feature Image -->
        <div class="header">
            <img src="https://earthen.io/content/images/size/w1200/2025/02/Nasser-Ishtayeh---Nablus-Olive-Tree-cut-down-by-IDF.jpg" alt="Feature Image">
        </div>
        <div class="image-caption">
            A Palestinian woman despairs as her olive trees are destroyed by occupying forces funded in USD - Nablus, Palestine, 2005 - Nasser Ishtayeh
        </div>

        <!-- Email Content -->
        <div class="content">
            <p>Today, we're announcing our transition from US dollars in our finances and accounting.</p>
            <p>Over the last year, we've been appalled by the dramatic escalation of ecocide and genocide in Palestine. With thousands of American-made and funded bombs being dropped on Gaza, the correlation between our own finances and the occupation became all too clear.</p>
            <p>As a global organization, we receive funds and pay bills in various countries and currencies. We also have a principle of making our books publicly available. Seven years ago, when we set up our Open Books system on ecobricks.org, choosing USD seemed like a harmless and universal way to present our finances in a way that could be well understood by folks around the world.</p>
            <p><strong>No more.</strong></p>
            <p>Gaza has been a shocking reminder of the correlation between Global North Currency and global oppression. US dollars are the currency by which both global petroleum is bought and the bombs in Gaza have been funded.</p>
            <p>It may be a drop in the ocean given the meager size of our accounts. However, there is no other way to stay true to our regenerative principles than to stop using the Global North currency most connected with the military-industrial complex and petro-capital economy behind the onslaught of Gaza.</p>
            <p>After all, when we use a currency, we play a part in validating and energizing the system behind it.</p>

            <p>It hasn't been easy. It has taken us a year of extricating ourselves from various accounts, systems, and services (and revamping our in-house accounting database!). However, as of now, we have ceased using any US-based financial platforms and accounts.</p>

            <p>From now on, our <a href="https://ecobricks.org/en/open-books.php"open book finances</a> will be denominated in Indonesian Rupiahs.</p>

            <p>To regeneration and beyond,</p>
            <p><strong>Russell, Ani, Lucie, Paula, Fran, Setiadji, Rere & Aang</strong></p>
            <p>GEA Center Circle</p>
            <p><a href="https://ecobricks.org/en/open-books.php">Ecobricks.org/openbooks</a></p>

            <p>P.S. And also... Directly connected to this, in 2024 <a href="https://earthen.io/gobrik-3-launch/">we let go of our use of corporate digital services based in the Global North.</a></p>
        </div>
        <br><br>

        <!-- Footer -->
        <div class="footer">
            <p>Together we can be the transition to ever increasing harmony with the cycles of life.</p>
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
    <!-- Updated button text with recipient email -->
    <button type="submit" id="send-email-btn" name="send_email" class="confirm-button enabled" <?php echo $has_alerts ? 'disabled' : ''; ?>>
        📨 Send to <?php echo htmlspecialchars($recipient_email); ?>
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
    const hasAlerts = <?php echo $has_alerts ? 'true' : 'false'; ?>;
    let countdownTimer;
    let countdown = 1; // Start countdown from 3 seconds

    // 🚨 Stop countdown if there are alerts & disable sending 🚨
    if (hasAlerts) {
        alert("⚠️ Unaddressed Admin Alerts Exist! You cannot send emails until they are resolved.");
        $('#send-email-btn').prop('disabled', true);
        return; // Stop execution
    }

    // ✅ Start countdown only if no alerts
    startCountdown();

    // 📨 Handle the email submission when button is clicked
    $('#send-email-btn').on('click', function (event) {
        event.preventDefault(); // Prevent default form submission
        $('#email-form').trigger('submit'); // ✅ Now submits correctly
    });

    // ✅ Handle form submission properly
    $('#email-form').on('submit', function (event) {
        event.preventDefault(); // Prevent page reload

        const emailTo = $('#email_to').val().trim();
        const emailBody = $('#email_html').val().trim();

        if (!emailTo || !emailBody) {
            alert("⚠️ Please fill out all fields before sending the email.");
            return;
        }

        // ✅ Send email via AJAX
        $.ajax({
            url: "", // ✅ Use empty string to submit to the same PHP file
            type: "POST",
            data: {
                send_email: "1",
                email_to: emailTo,
                email_html: emailBody
            },
            success: function (response) {
                $('#send-email-btn').html(`✅ Sent to ${emailTo}!`).prop('disabled', true);
                setTimeout(() => location.reload(), 1000);
            },
            error: function () {
                alert("❌ Failed to send the email. Please try again.");
            }
        });
    });

    // ⏳ Countdown function
    function startCountdown() {
        $('#countdown-timer').show();
        $('#stop-timer-btn').show();
        updateCountdownText();

        countdownTimer = setInterval(() => {
            countdown--;
            updateCountdownText();

            if (countdown <= 0) {
                clearInterval(countdownTimer);
                $('#email-form').trigger('submit'); // ✅ Automatically submits form
            }
        }, 1000);
    }

    // 🔄 Update countdown text
    function updateCountdownText() {
        $('#countdown').text(countdown);
    }

    // 🛑 Stop countdown
    $('#stop-timer-btn').on('click', function () {
        clearInterval(countdownTimer);
        $('#countdown-timer').hide();
        $(this).hide();
    });
});




</script>



<!--FOOTER STARTS HERE-->
<?php require_once ("../footer-2024.php"); ?>



</body>
</html>