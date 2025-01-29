<?php
require_once '../earthenAuth_helper.php'; // Include the authentication helper functions
require '../vendor/autoload.php'; // Path to Composer's autoloader

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

// Set page variables
$lang = basename(dirname($_SERVER['SCRIPT_NAME']));
$version = '0.53';
$page = 'admin-panel';
$lastModified = date("Y-m-d\TH:i:s\Z", filemtime(__FILE__));

// LOGIN AND ROLE CHECK:
if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

$buwana_id = $_SESSION['buwana_id'];
require_once '../gobrikconn_env.php';

$query = "SELECT user_roles FROM tb_ecobrickers WHERE buwana_id = ?";
if ($stmt = $gobrik_conn->prepare($query)) {
    $stmt->bind_param("i", $buwana_id);
    $stmt->execute();
    $stmt->bind_result($user_roles);

    if ($stmt->fetch()) {
        if (stripos($user_roles, 'admin') === false) {
            echo "<script>
                alert('Sorry, only admins can see this page.');
                window.location.href = 'dashboard.php';
            </script>";
            exit();
        }
    } else {
        echo "<script>
            alert('User record not found.');
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

// PART 2: Function to grab the next ecobricker record
function getNextEcobricker($conn) {
    $query = "
        SELECT ecobricker_id, email_addr, first_name, date_registered, brk_balance, ecobrick_density_avg, ecobricks_made, city_txt, region_txt, country_txt
        FROM tb_ecobrickers
        WHERE ecobricker_id > 3
          AND (buwana_id IS NULL OR buwana_id = 150)
          AND emailing_status IN ('unsent', 'resend')
        ORDER BY ecobricker_id ASC
        LIMIT 1";

    $stmt = $conn->prepare($query);
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        }
    }
    return null;
}

$nextEcobricker = getNextEcobricker($gobrik_conn);

require_once '../buwanaconn_env.php';

// Fetch the user's location data
$user_continent_icon = getUserContinent($buwana_conn, $buwana_id);
$user_location_watershed = getWatershedName($buwana_conn, $buwana_id);
$user_location_full = getUserFullLocation($buwana_conn, $buwana_id);
$gea_status = getGEA_status($buwana_id);
$user_community_name = getCommunityName($buwana_conn, $buwana_id);
$ecobrick_unique_id = '';


// Initialize variables for the email form
$email_addr = $nextEcobricker['email_addr'] ?? '';
$first_name = $nextEcobricker['first_name'] ?? '';
$date_registered = $nextEcobricker['date_registered'] ?? '';
$brk_balance = $nextEcobricker['brk_balance'] ?? 0;
$ecobricks_made = $nextEcobricker['ecobricks_made'] ?? 0;
$city_txt = $nextEcobricker['city_txt'] ?? '';
$region_txt = $nextEcobricker['region_txt'] ?? '';
$country_txt = $nextEcobricker['country_txt'] ?? '';
$subject = "Ditch big-tech with us: Activate your GoBrik 2025 account.";

// Format the date to exclude the time
if (!empty($date_registered)) {
    $date_registered = date('Y-m-d', strtotime($date_registered));
}

// Initialize the email body
// Initialize the email body
$body = "
Hi there $first_name,<br><br>";
$body .= "GoBrik has been totally revamped for January, 2025! <br><br>";
$body .= "You've been with us since you registered on $date_registered.<br><br>";
$body .= "In your account, we have your $ecobricks_made ecobricks and the $brk_balance Brikcoins you earned.<br><br>";

$body .= "However, we need you to upgrade your account!<br><br>
To align with our ecological principles, we've removed our reliance on Google, Facebook, and Amazon services.
Not only are we up and running on our own servers, but we have also developed our own, fully open-source account system.  On it we are deploying an array of regenerative Earthen newsletters (also using an opensource, not-for-profit platform, of course).<br><br>
However, first we now need you to activate your new GoBrik account with our new Buwana authentication protocol — which will soon work for other regenerative apps!
<br><br>(You can read more about our transition off big tech <a href=\"https://earthen.io/gobrik-3-launch/\">here</a>.)<br><br>";

$body .= "
Your ecobricking was a great service to your local $city_txt ecology, your $region_txt bioregion of $country_txt and Earth's biosphere.<br><br>
To keep going with us, please use your email ($email_addr) at <a href=\"https://gobrik.com\">https://gobrik.com</a> to login and activate your account.<br><br>
To regeneration and beyond!<br><br>
GEA Dev Team<br><br>

P.S. If you'd like to delete your account with us, you can do that too during the activation process.
";


// Send email function
function sendAccountActivationEmail($to, $subject, $body_html, $body_text) {
    $client = new Client(['base_uri' => 'https://api.eu.mailgun.net/v3/']);
    $mailgunApiKey = getenv('MAILGUN_API_KEY');
    $mailgunDomain = 'mail.gobrik.com';

    try {
        $response = $client->post("https://api.eu.mailgun.net/v3/{$mailgunDomain}/messages", [
            'auth' => ['api', $mailgunApiKey],
            'form_params' => [
                'from' => 'GoBrik Team <no-reply@mail.gobrik.com>',
                'to' => $to,
                'subject' => $subject,
                'html' => $body_html,
                'text' => $body_text,
            ]
        ]);

        return $response->getStatusCode() == 200;
    } catch (Exception $e) {
        error_log("Error sending email: " . $e->getMessage());
        return false;
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_email'])) {
    // Get the form data
    $to = $_POST['email_to'];
    $subject = $_POST['email_subject'];
    $body_html = $_POST['email_body'];
    $body_text = strip_tags($body_html); // Generate plain text fallback

    // Validate required fields
    if (empty($to) || empty($subject) || empty($body_html)) {
        echo "<script>alert('Please fill in all the required fields.');</script>";
    } else {
        // Call the email-sending function
        $success = sendAccountActivationEmail($to, $subject, $body_html, $body_text);

        if ($success) {
            echo "<script>alert('Email sent successfully to $to');</script>";

            // Update the emailing_status to 'delivered'
            $updateQuery = "
                UPDATE tb_ecobrickers
                SET emailing_status = 'delivered'
                WHERE email_addr = ?";
            $stmt = $gobrik_conn->prepare($updateQuery);
            $stmt->bind_param("s", $to);
            $stmt->execute();
            $stmt->close();

            // Reload the page after successful email sending
            echo "<script>window.location.href = 'admin-emailer.php';</script>";
            exit();
        } else {
            echo "<script>alert('Failed to send the email to $to. Check the logs for details.');</script>";
        }
    }
}

// Fetch overall stats
require_once '../gobrikconn_env.php';

// Initialize variables
$total_ecobrickers = 0;
$total_with_buwana_id = 0;
$unsent = 0;
$delivered = 0;
$failed = 0;

// Fetch counts
$sql = "SELECT
            COUNT(*) as total_ecobrickers,
            SUM(CASE WHEN buwana_id IS NOT NULL AND buwana_id != '' THEN 1 ELSE 0 END) as total_with_buwana_id,
            SUM(CASE WHEN emailing_status = 'unsent' THEN 1 ELSE 0 END) as unsent,
            SUM(CASE WHEN emailing_status = 'delivered' THEN 1 ELSE 0 END) as delivered,
            SUM(CASE WHEN emailing_status = 'failed' THEN 1 ELSE 0 END) as failed
        FROM tb_ecobrickers";

$result = $gobrik_conn->query($sql);

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $total_ecobrickers = intval($row['total_ecobrickers'] ?? 0);
    $total_with_buwana_id = intval($row['total_with_buwana_id'] ?? 0);
    $unsent = intval($row['unsent'] ?? 0);
    $delivered = intval($row['delivered'] ?? 0);
    $failed = intval($row['failed'] ?? 0);
}

// Calculate percentage of users with Buwana accounts
$percent_with_buwana = $total_ecobrickers > 0 ? round(($total_with_buwana_id / $total_ecobrickers) * 100, 2) : 0;

$gobrik_conn->close();

?>





<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
<meta charset="UTF-8">
<title>Admin Send Email Check</title>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<?php require_once ("../includes/admin-panel-inc.php");?>

    <div class="splash-title-block"></div>
    <div id="splash-bar"></div>

    <!-- PAGE CONTENT -->
    <div id="top-page-image" class="message-birded top-page-image"></div>

    <div id="form-submission-box" class="landing-page-form">
        <div class="form-container">

           <!-- Email confirmation form -->

    <div id="content-to-refresh" style="text-align:center;">
    <h2>Send Activation Emails to Unactivated Users</h2>

     <p id="admin-welcome-stats">
    So far we have <?php echo number_format($total_ecobrickers); ?> ecobrickers on GoBrik.
    <?php echo $percent_with_buwana; ?>% have an active Buwana account.
    Of these, <?php echo number_format($unsent); ?> have not received the test email,
    <?php echo number_format($delivered); ?> have received it, and
    <?php echo number_format($failed); ?> account emails failed to receive it.</p>


    <p>Here are the users that haven't yet activated their Buwana account:</p>
    <div id="table-container" style="overflow-x: auto; width: 100%;">
        <table id="next-ecobrickers" class="display responsive nowrap" style="width:100%">
            <thead>
                <tr>
                    <th>Ecobricker ID</th>
                    <th>Email</th>
                    <th>Notes</th>
                    <th>First Name</th>
                    <th>Roles</th>
                    <th>Briks</th>
                    <th>Logins</th>
                    <th>Email Status</th>
                    <th>Full Name</th>
                    <th>Location</th>
                </tr>
            </thead>
        </table>
    </div>
    <p>Use this form to send an email to remind users to activate their account.</p>
    <form id="email-form" method="post" style="text-align:center;">
    <label for="email_to" style="text-align:left;">Sending to:</label><br>
    <input type="email" id="email_to" name="email_to" value="<?php echo htmlspecialchars($email_addr); ?>" style="width: 80%;"><br><br>

    <label for="email_subject" style="text-align:left;">Subject:</label><br>
    <input type="text" id="email_subject" name="email_subject" value="<?php echo htmlspecialchars($subject); ?>" style="width: 80%;"><br><br>

    <label for="email_body" style="text-align:left;">Body:</label><br>
    <textarea id="email_body" name="email_body" rows="10" style="width: 80%;"><?php echo htmlspecialchars($body); ?></textarea><br><br>

    <button type="button" id="send-email-btn" class="confirm-button enabled">📨 Send Email</button>

    <div id="countdown-timer" style="margin-top: 10px; display: none;text-align:center;width:100%;">
        <p>Email will send in refresh in <span id="countdown">7</span> seconds...</p>
        <button type="button" id="stop-timer-btn" style="display: none;" class="confirm-button delete">🛑 Stop Timer</button>
    </div>
</form>



</div>

</div>


</div> <!--Closes main-->



<script>
$(document).ready(function () {
    // Initialize the DataTable
    const dataTable = $('#next-ecobrickers').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "../api/fetch_next_ecobrickers.php",
            "type": "POST"
        },
        "columns": [
            { "data": "ecobricker_id" },
            { "data": "email_addr" },
            { "data": "account_notes" },
            { "data": "first_name" },
            { "data": "user_roles" },
            { "data": "ecobricks_made" },
            { "data": "login_count" },
            { "data": "emailing_status" },
            { "data": "full_name" },
            { "data": "location_full" }
        ]
    });

    let countdownTimer; // Reference for the countdown timer
    let countdown = 10; // Initial countdown value

    // Start the countdown timer on page load
    startCountdown();

    // Handle the email submission
    $('#send-email-btn').on('click', function () {
        const emailTo = $('#email_to').val().trim();
        const emailSubject = $('#email_subject').val().trim();
        const emailBody = $('#email_body').val().trim();

        if (!emailTo || !emailSubject || !emailBody) {
            alert("Please fill out all fields before sending the email.");
            return;
        }

        $.ajax({
            url: 'admin-emailer.php',
            type: 'POST',
            data: {
                send_email: true,
                email_to: emailTo,
                email_subject: emailSubject,
                email_body: emailBody
            },
            success: function () {
                // Update the button text to indicate success
                $('#send-email-btn').html(`✅ Sent to ${emailTo}!`).prop('disabled', true);

                // Reload the page after the form submission
                setTimeout(function () {
                    location.reload();
                }, 1000);
            },
            error: function () {
                alert("Failed to send the email. Please try again.");
            }
        });
    });

    // Countdown timer function
    function startCountdown() {
        $('#countdown-timer').show(); // Show the timer
        $('#stop-timer-btn').show(); // Show the stop button
        updateCountdownText();

        countdownTimer = setInterval(function () {
            countdown--;
            updateCountdownText();

            if (countdown <= 0) {
                clearInterval(countdownTimer);
                $('#send-email-btn').trigger('click'); // Submit the form when countdown reaches 0
            }
        }, 1000);
    }

    // Update the countdown text
    function updateCountdownText() {
        $('#countdown').text(countdown);
    }

    // Stop the countdown timer
    $('#stop-timer-btn').on('click', function () {
        clearInterval(countdownTimer);
        $('#countdown-timer').hide(); // Hide the timer
        $(this).hide(); // Hide the stop button
    });

    // Update the email field dynamically when new data is loaded into the DataTable
    $('#next-ecobrickers').on('xhr.dt', function (e, settings, json) {
        if (json.data.length > 0) {
            const firstRecord = json.data[0];
            const currentEmail = $('#email_to').val();
            if (!currentEmail || currentEmail !== firstRecord.email_addr) {
                $('#email_to').val(firstRecord.email_addr);
            }
        }
    });
});
</script>








<!--FOOTER STARTS HERE-->
<?php require_once ("../footer-2024.php"); ?>





</body>
</html>