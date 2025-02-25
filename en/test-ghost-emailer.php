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
            header("Location: dashboard.php");
            exit();
        }
    } else {
        header("Location: dashboard.php");
        exit();
    }
    $stmt->close();
} else {
    header("Location: dashboard.php");
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

// Fetch the 3 most recently updated (sent) accounts
$query_sent = "SELECT id, email, name, test_sent, test_sent_date_time
               FROM ghost_test_email_tb
               WHERE test_sent = 1
               ORDER BY test_sent_date_time DESC
               LIMIT 3";
$sent_result = $buwana_conn->query($query_sent);
$sent_members = $sent_result->fetch_all(MYSQLI_ASSOC);

// Fetch the next 7 pending (unsent) accounts
$query_pending = "SELECT id, email, name, test_sent, test_sent_date_time
                  FROM ghost_test_email_tb
                  WHERE test_sent = 0
                  ORDER BY id ASC
                  LIMIT 7";
$pending_result = $buwana_conn->query($query_pending);
$pending_members = $pending_result->fetch_all(MYSQLI_ASSOC);

// Merge sent and pending for display
$all_members = array_merge($sent_members, $pending_members);

// Handle form submission (send email)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_email'])) {
    $email_html = $_POST['email_html'];

    // Get the next recipient (start with russmaier@gmail.com, then pick from DB)
    $query = "SELECT id, email, name FROM ghost_test_email_tb WHERE test_sent = 0 ORDER BY id ASC LIMIT 1";
    $result = $buwana_conn->query($query);
    $subscriber = $result->fetch_assoc();

    $test_email = "russmaier@gmail.com"; // Start with test email

    if (!$subscriber) {
        $test_email = null; // Stop if no more unsent users
    } else {
        $test_email = $subscriber['email'];
        $subscriber_id = $subscriber['id'];
    }

    if (!empty($email_html) && $test_email) {
        // Generate unsubscribe link
        $unsubscribe_link = "<p>Earthen © 2025 • <a href='https://mail2.earthen.io/unsubscribe/$test_email'>Unsubscribe</a></p>";
        $email_html .= $unsubscribe_link;

        if (sendEmail($test_email, $email_html)) {
            // Mark as sent in database
            $updateQuery = "UPDATE ghost_test_email_tb SET test_sent = 1, test_sent_date_time = NOW() WHERE id = ?";
            $stmt = $buwana_conn->prepare($updateQuery);
            $stmt->bind_param("s", $subscriber_id);
            $stmt->execute();
            $stmt->close();

            // Refresh the page without an alert
            header("Location: test-ghost-emailer.php");
            exit();
        }
    }
}

// Email sending function
function sendEmail($to, $htmlBody) {
    $client = new Client(['base_uri' => 'https://api.eu.mailgun.net/v3/']);
    $mailgunApiKey = getenv('MAILGUN_API_KEY');
    $mailgunDomain = 'mail2.earthen.io';

    try {
        $response = $client->post("https://api.eu.mailgun.net/v3/{$mailgunDomain}/messages", [
            'auth' => ['api', $mailgunApiKey],
            'form_params' => [
                'from' => 'GEA Team <gea@mail2.earthen.io>',
                'to' => $to,
                'subject' => 'GoBrik Newsletter',
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

<div class="splash-title-block"></div>
<div id="splash-bar"></div>

<div id="form-submission-box" class="landing-page-form">
    <div class="form-container">
        <h2>Ghost Newsletter Emailer</h2>
        <p>Total Members: <strong><?php echo $total_members; ?></strong></p>
        <p>Emails Sent: <strong><?php echo $sent_count; ?></strong> (<?php echo $sent_percentage; ?>%)</p>

        <form method="POST">
            <label for="email_html">Newsletter HTML:</label>
            <textarea name="email_html" id="email_html" rows="6" style="width:100%;"></textarea>
            <br><br>
            <button type="submit" name="send_email">Send Next Email</button>
        </form>

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
</div>

</body>
</html>
