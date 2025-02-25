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

// Check if user is admin
$query = "SELECT user_roles FROM tb_ecobrickers WHERE buwana_id = ?";
if ($stmt = $gobrik_conn->prepare($query)) {
    $stmt->bind_param("i", $buwana_id);
    $stmt->execute();
    $stmt->bind_result($user_roles);
    if ($stmt->fetch() && stripos($user_roles, 'admin') === false) {
        exit("<script>alert('Only admins can access this page.'); window.location.href='dashboard.php';</script>");
    }
    $stmt->close();
}

// Connect to buwana database
require_once '../buwanaconn_env.php';

// Fetch next recipient (starting with russ@ecobricks.org)
$query = "SELECT id, email, name FROM ghost_test_email_tb WHERE test_sent = 0 ORDER BY id ASC LIMIT 1";
$result = $buwana_conn->query($query);
$subscriber = $result->fetch_assoc();

// Default to russ@ecobricks.org for the first test
$email = $subscriber ? $subscriber['email'] : "russ@ecobricks.org";
$name = $subscriber ? $subscriber['name'] : "Russell Maier";
$subscriber_id = $subscriber ? $subscriber['id'] : "test_id";

// Newsletter ID (use a fixed value for now, can be dynamic later)
$newsletter_id = "18bce2af-ca5b-4a10-bff3-f79d32479f09";

// Function to generate unsubscribe link
function generateUnsubscribeLink($subscriber_id, $newsletter_id) {
    $secret_key = 'your-secret-key'; // Keep this private
    $base_url = "https://earthen.io/";
    $hash = hash_hmac('sha256', $subscriber_id . $newsletter_id, $secret_key);
    return "{$base_url}?uuid={$subscriber_id}&newsletter={$newsletter_id}&key={$hash}&action=unsubscribe";
}

// Generate unsubscribe link
$unsubscribe_link = generateUnsubscribeLink($subscriber_id, $newsletter_id);

// Email content with unsubscribe link
$email_html = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>We've moved on from the US Dollar</title>
</head>
<body>
    <p>Hello $name,</p>
    <p>Today, we're announcing our transition from US dollars in our finances and accounting...</p>
    <p><a href="https://ecobricks.org/en/open-books.php">Explore Open Books</a></p>
    <p>If you no longer wish to receive these emails, <a href="$unsubscribe_link">unsubscribe here</a>.</p>
</body>
</html>
HTML;

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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_email'])) {
    if (sendEmail($email, $email_html)) {
        // Update the database to mark email as sent
        $updateQuery = "UPDATE ghost_test_email_tb SET test_sent = 1, test_sent_date_time = NOW() WHERE email = ?";
        $stmt = $buwana_conn->prepare($updateQuery);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->close();

        echo "<script>alert('Email sent to $email'); window.location.reload();</script>";
    } else {
        echo "<script>alert('Failed to send email.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Ghost Newsletter Emailer</title>
</head>
<body>
    <h2>Ghost Newsletter Emailer</h2>
    <p>Sending to: <strong><?php echo htmlspecialchars($email); ?></strong></p>

    <form method="POST">
        <label for="email_html">Newsletter HTML:</label>
        <textarea name="email_html" id="email_html" rows="10" style="width:100%;"><?php echo htmlspecialchars($email_html); ?></textarea>
        <br><br>
        <button type="submit" name="send_email">Send Email</button>
    </form>
</body>
</html>
