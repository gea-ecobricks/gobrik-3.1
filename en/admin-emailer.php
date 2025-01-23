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
// Check if the user is logged in, if not send them to login.
if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

// User is logged in, proceed to check admin status
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
//END LOGIN AND ROLE CHECK

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
            return $result->fetch_assoc(); // Return the first record
        }
    }
    return null; // Return null if no matching record found
}

// Fetch the next ecobricker candidate on page load
$nextEcobricker = getNextEcobricker($gobrik_conn);


    require_once '../buwanaconn_env.php';

 // Fetch the user's location data
    $user_continent_icon = getUserContinent($buwana_conn, $buwana_id);
    $user_location_watershed = getWatershedName($buwana_conn, $buwana_id);
    $user_location_full = getUserFullLocation($buwana_conn, $buwana_id);
    $gea_status = getGEA_status($buwana_id);
    $user_community_name = getCommunityName($buwana_conn, $buwana_id);
    $ecobrick_unique_id = '';
    $first_name = getFirstName($buwana_conn, $buwana_id);

// Initialize variables for the email form
$email_addr = $nextEcobricker['email_addr'] ?? '';
$first_name = $nextEcobricker['first_name'] ?? '';
$date_registered = $nextEcobricker['date_registered'] ?? '';
$brk_balance = $nextEcobricker['brk_balance'] ?? 0;
$ecobricks_made = $nextEcobricker['ecobricks_made'] ?? 0;
$city_txt = $nextEcobricker['city_txt'] ?? '';
$region_txt = $nextEcobricker['region_txt'] ?? '';
$country_txt = $nextEcobricker['country_txt'] ?? '';
$subject = "Please activate your 2025 GoBrik account";




// Compose the email body with dynamic user data
$body = "
Hi there $first_name,<br><br>
GoBrik has been totally revamped for 2025! <br><br>
We've removed our reliance on Google, Facebook and Amazon services and need you to re-activate your new account with our new Buwana authentication protocol.<br><br>
You've been with us since you registered on $date_registered.<br><br>
In your old account, we have your $ecobricks_made ecobricks and $brk_balance Brikcoins.<br><br>
Your work there was a great service to your local $city_txt ecology and your $region_txt bioregion of $country_txt Earth.<br><br>
We'd like to encourage you to activate your account and try logging your latest ecobricks.<br><br>
Please do so by logging in with your email ($email_addr) at <a href='https://gobrik.com'>https://gobrik.com</a><br><br>
See you on the app!<br><br>
GEA Dev Team
";



function sendAccountActivationEmail($to, $subject, $body_html, $body_text) {
    // Set up the Mailgun API client
    $client = new Client(['base_uri' => 'https://api.eu.mailgun.net/v3/']); // EU endpoint for Mailgun
    $mailgunApiKey = getenv('MAILGUN_API_KEY'); // Get Mailgun API key from environment variables
    $mailgunDomain = 'mail.gobrik.com'; // Verified Mailgun domain

    try {
        // Log the email sending attempt
        error_log("Attempting to send email to $to with subject: $subject");

        // Send the email using Mailgun's API
        $response = $client->post("https://api.eu.mailgun.net/v3/{$mailgunDomain}/messages", [
            'auth' => ['api', $mailgunApiKey],
            'form_params' => [
                'from' => 'GoBrik Team <no-reply@mail.gobrik.com>', // Verified sender email
                'to' => $to,
                'subject' => $subject,
                'html' => $body_html,
                'text' => $body_text, // Plain text fallback
            ]
        ]);

        // Check response status
        if ($response->getStatusCode() == 200) {
            error_log("Mailgun: Email sent successfully to $to");
            return true;
        } else {
            error_log("Mailgun: Failed to send email to $to. Status Code: " . $response->getStatusCode());
            error_log("Mailgun Response: " . (string)$response->getBody());
            return false;
        }

    } catch (RequestException $e) {
        // Log exception message
        error_log("Mailgun API Request Exception: " . $e->getMessage());

        // Log response body if available
        if ($e->hasResponse()) {
            error_log("Mailgun API Error Response: " . (string) $e->getResponse()->getBody());
        }

        return false;
    } catch (Exception $e) {
        // Log any other exceptions
        error_log("Unexpected Exception: " . $e->getMessage());
        return false;
    }
}




// PART 4: Handle form submission
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
        // Call the email sending function
        $success = sendAccountActivationEmail($to, $subject, $body_html, $body_text);

        if ($success) {
            echo "<script>alert('Email sent successfully to $to');</script>";

            // Update the emailing_status to 'delivered'
            $updateQuery = "UPDATE tb_ecobrickers SET emailing_status = 'delivered' WHERE email_addr = ?";
            $stmt = $gobrik_conn->prepare($updateQuery);
            $stmt->bind_param("s", $to);
            $stmt->execute();
            $stmt->close();
        } else {
            echo "<script>alert('Failed to send the email to $to. Check the logs for details.');</script>";
        }
    }
}




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

<div style="text-align:center;">
    <h2>Send Activation Emails to Unactivated Users</h2>
    <p>Here are the user's that haven't yet activated their buwana account</p>

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

    <form method="post" style="text-align:left;">
        <label for="email_to">Sending to:</label><br>
        <input type="email" id="email_to" name="email_to" value="<?php echo htmlspecialchars($email_addr); ?>" style="width: 80%;"><br><br>

        <label for="email_subject">Subject:</label><br>
        <input type="text" id="email_subject" name="email_subject" value="<?php echo htmlspecialchars($subject); ?>" style="width: 80%;"><br><br>

        <label for="email_body">Body:</label><br>
        <textarea id="email_body" name="email_body" rows="10" style="width: 80%;"><?php echo htmlspecialchars($body); ?></textarea><br><br>

        <button type="submit" name="send_email" class="confirm-button enabled">📨 Send Email</button>
    </form>
</div>

</div>

</div>


</div> <!--Closes main-->


<!--FOOTER STARTS HERE-->
<?php require_once ("../footer-2024.php"); ?>



<script>
document.querySelector('form').addEventListener('submit', function (e) {
    const emailTo = document.getElementById('email_to').value.trim();
    const emailSubject = document.getElementById('email_subject').value.trim();
    const emailBody = document.getElementById('email_body').value.trim();

    if (!emailTo || !emailSubject || !emailBody) {
        e.preventDefault();
        alert("Please fill out all fields before sending the email.");
    }
});



$(document).ready(function () {
    $('#next-ecobrickers').DataTable({
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

    // Populate email form with the first record
    $('#next-ecobrickers').on('xhr.dt', function (e, settings, json) {
        if (json.data.length > 0) {
            const firstRecord = json.data[0];
            $('#email_to').val(firstRecord.email_addr);
            $('#email_subject').val("Please activate your 2025 GoBrik account");
            $('#email_body').val(`
Hi there ${firstRecord.first_name},

GoBrik has been totally revamped for 2025!

We've removed our reliance on Google, Facebook, and Amazon services and need you to re-activate your new account with our new Buwana authentication protocol.

Please do so by logging in with your email (${firstRecord.email_addr}) at https://gobrik.com

Best regards,
GEA Dev Team
            `);
        }
    });
});
</script>


</body>
</html>