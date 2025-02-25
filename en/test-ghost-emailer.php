<?php
require_once '../earthenAuth_helper.php'; // Authentication helper
require '../vendor/autoload.php'; // Composer dependencies

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

// Page setup
$lang = basename(dirname($_SERVER['SCRIPT_NAME']));
$version = '1.0';
$page = 'admin-panel';
$lastModified = date("Y-m-d\TH:i:s\Z", filemtime(__FILE__));

// LOGIN & ADMIN CHECK using gobrik database
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

require_once '../buwanaconn_env.php';

// Fetch email stats from buwana db
$query = "
    SELECT COUNT(*) AS total_members,
           SUM(CASE WHEN test_sent = 1 THEN 1 ELSE 0 END) AS sent_count
    FROM ghost_test_email_tb";
$result = $buwana_conn->query($query);
$row = $result->fetch_assoc();

$total_members = intval($row['total_members'] ?? 0);
$sent_count = intval($row['sent_count'] ?? 0);
$sent_percentage = ($total_members > 0) ? round(($sent_count / $total_members) * 100, 2) : 0;

// Get next 10 unsent members
$query = "SELECT id, email, name, test_sent, test_sent_date_time
          FROM ghost_test_email_tb
          WHERE test_sent = 0
          ORDER BY id ASC
          LIMIT 10";
$members_result = $buwana_conn->query($query);
$next_members = $members_result->fetch_all(MYSQLI_ASSOC);

// Handle form submission (send email)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_email'])) {
    $email_html = $_POST['email_html'];
    $test_email = 'russ@ecobricks.org';  // Hardcoded recipient

    if (empty($email_html)) {
        echo "<script>alert('Please provide email HTML content.');</script>";
    } else {
        if (sendEmail($test_email, $email_html)) {
            echo "<script>alert('Test email sent to $test_email');</script>";
        } else {
            echo "<script>alert('Failed to send test email.');</script>";
        }
    }
}

// Email sending function
function sendEmail($to, $htmlBody) {
    $client = new Client(['base_uri' => 'https://api.eu.mailgun.net/v3/']);
    $mailgunApiKey = getenv('MAILGUN_API_KEY');
    $mailgunDomain = 'mail.gobrik.com';

    try {
        $response = $client->post("https://api.eu.mailgun.net/v3/{$mailgunDomain}/messages", [
            'auth' => ['api', $mailgunApiKey],
            'form_params' => [
                'from' => 'GoBrik Team <no-reply@mail.gobrik.com>',
                'to' => $to,
                'subject' => 'GoBrik Newsletter Test',
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

<?php require_once ("../includes/admin-panel-inc.php"); ?>
<div class="splash-title-block"></div>
<div id="splash-bar"></div>

<!-- PAGE CONTENT -->
<div id="top-page-image" class="message-birded top-page-image"></div>

<div id="form-submission-box" class="landing-page-form">
    <div class="form-container">
        <h2>Ghost Newsletter Emailer (Test Mode)</h2>
        <p>This test will send an email to: <strong>russ@ecobricks.org</strong></p>

        <form method="POST">
            <label for="email_html">Newsletter HTML:</label>
            <textarea name="email_html" id="email_html" rows="6" style="width:100%;"></textarea>
            <br><br>
            <button type="submit" name="send_email">Send Test Email</button>
        </form>

        <h3>Next 10 Subscribers:</h3>
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
                <?php foreach ($next_members as $member): ?>
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
</div>

</body>
</html>
