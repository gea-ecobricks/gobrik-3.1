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

// Get next recipient (default to test email first)
$query = "SELECT id, email, name FROM ghost_test_email_tb WHERE test_sent = 0 ORDER BY id ASC LIMIT 1";
$result = $buwana_conn->query($query);
$subscriber = $result->fetch_assoc();

$test_email = "russ@ecobricks.org"; // Default test email
$subscriber_id = null;

if ($subscriber) {
    $test_email = $subscriber['email']; // Use actual recipient if available
    $subscriber_id = $subscriber['id'];
}

// Generate unsubscribe link
$unsubscribe_link = "https://mail2.earthen.io/unsubscribe/" . urlencode($test_email);

// Default email HTML with dynamic unsubscribe link
$email_template = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>We've moved on from the US Dollar</title>
</head>
<body style="font-family: Arial, sans-serif; color: #15212A; line-height: 1.5;">
    <div style="max-width: 600px; margin: auto; padding: 20px; background-color: #ffffff;">
        <h2 style="text-align: center;">We've moved on from the US Dollar</h2>
        <p>Today, we're announcing our transition from US dollars in our finances and accounting.</p>
        <p>Over the last year, we've been appalled by the dramatic escalation of ecocide and genocide in Palestine...</p>

        <p>To regeneration and beyond,</p>
        <p><strong>Russell, Ani, Lucie, Paula, Fran, Setiadji, Rere & Aang</strong></p>

        <p style="text-align: center; font-size: 12px; color: #738a94;">
            Earthen © 2025 • <a href="$unsubscribe_link" style="color: #4B8501; text-decoration: none;">Unsubscribe</a>
        </p>
    </div>
</body>
</html>
HTML;

// Handle form submission (send email)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_email'])) {
    $email_html = $_POST['email_html'];

    if (!empty($email_html) && $test_email) {
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
    $mailgunDomain = 'mail.gobrik.com';

    try {
        $response = $client->post("https://api.eu.mailgun.net/v3/{$mailgunDomain}/messages", [
            'auth' => ['api', $mailgunApiKey],
            'form_params' => [
                'from' => 'GoBrik Team <gea@mail2.earthen.io>',
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

<div class="form-container">
    <h2>Ghost Newsletter Emailer</h2>
    <p>Total Members: <strong><?php echo $total_members; ?></strong></p>
    <p>Emails Sent: <strong><?php echo $sent_count; ?></strong> (<?php echo $sent_percentage; ?>%)</p>

    <form method="POST">
        <label for="email_html">Newsletter HTML:</label>
        <textarea name="email_html" id="email_html" rows="10" style="width:100%;"><?php echo htmlspecialchars($email_template); ?></textarea>
        <br><br>
        <button type="submit" name="send_email">Send Next Email</button>
    </form>
</div>

</body>
</html>
