<?php
ob_start(); // Start output buffering

require_once '../earthenAuth_helper.php';
require_once '../gobrikconn_env.php';
require_once '../buwanaconn_env.php';
require_once '../scripts/earthen_subscribe_functions.php';

header('Content-Type: text/html; charset=UTF-8');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Get email from query string
$email_addr = $_GET['email'] ?? '';

// Validate email
if (empty($email_addr) || !filter_var($email_addr, FILTER_VALIDATE_EMAIL)) {
    die("<p style='color: red;'>Invalid email address. Please try again.</p>");
}

// If form is submitted, process unsubscription
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_unsubscribe'])) {
    echo "<div id='progress'><p>Checking database for accounts linked to: <strong>$email_addr</strong>...</p></div>";
    ob_flush();
    flush();
    sleep(1); // Simulate loading time

    try {
        // Fetch user details from tb_ecobrickers
        $sql_fetch_details = "SELECT ecobricker_id, buwana_id FROM tb_ecobrickers WHERE email_addr = ?";
        $stmt_fetch_details = $gobrik_conn->prepare($sql_fetch_details);
        $stmt_fetch_details->bind_param('s', $email_addr);
        $stmt_fetch_details->execute();
        $stmt_fetch_details->bind_result($ecobricker_id, $buwana_id);
        $stmt_fetch_details->fetch();
        $stmt_fetch_details->close();

        if (empty($ecobricker_id)) {
            echo "<script>document.getElementById('progress').innerHTML += '<p>No account found in GoBrik.</p>';</script>";
            ob_flush();
            flush();
        } else {
            echo "<script>document.getElementById('progress').innerHTML += '<p>Unsubscribing from GoBrik...</p>';</script>";
            ob_flush();
            flush();
            sleep(1);

            // Mark as unsubscribed
            $sql_unsubscribe_ecobricker = "UPDATE tb_ecobrickers SET subscribed_to_emails = 0 WHERE ecobricker_id = ?";
            $stmt_unsubscribe_ecobricker = $gobrik_conn->prepare($sql_unsubscribe_ecobricker);
            $stmt_unsubscribe_ecobricker->bind_param('i', $ecobricker_id);
            $stmt_unsubscribe_ecobricker->execute();
            $stmt_unsubscribe_ecobricker->close();
        }

        // Unsubscribe in users_tb
        if (!empty($buwana_id)) {
            echo "<script>document.getElementById('progress').innerHTML += '<p>Checking Buwana account...</p>';</script>";
            ob_flush();
            flush();
            sleep(1);

            $sql_unsubscribe_user = "UPDATE users_tb SET subscribed_to_emails = 0 WHERE buwana_id = ?";
            $stmt_unsubscribe_user = $buwana_conn->prepare($sql_unsubscribe_user);
            $stmt_unsubscribe_user->bind_param('i', $buwana_id);
            $stmt_unsubscribe_user->execute();
            $stmt_unsubscribe_user->close();

            $sql_unsubscribe_credentials = "UPDATE credentials_tb SET subscribed_to_emails = 0 WHERE buwana_id = ?";
            $stmt_unsubscribe_credentials = $buwana_conn->prepare($sql_unsubscribe_credentials);
            $stmt_unsubscribe_credentials->bind_param('i', $buwana_id);
            $stmt_unsubscribe_credentials->execute();
            $stmt_unsubscribe_credentials->close();

            echo "<script>document.getElementById('progress').innerHTML += '<p>Buwana account unsubscribed.</p>';</script>";
            ob_flush();
            flush();
        } else {
            echo "<script>document.getElementById('progress').innerHTML += '<p>No linked Buwana account found.</p>';</script>";
            ob_flush();
            flush();
        }

        // Call external unsubscribe function
        echo "<script>document.getElementById('progress').innerHTML += '<p>Unsubscribing from Earthen newsletter...</p>';</script>";
        ob_flush();
        flush();
        sleep(1);
        earthenUnsubscribe($email_addr);

        echo "<script>document.getElementById('progress').innerHTML += '<p><strong>✅ Unsubscribe complete.</strong></p>';</script>";

    } catch (Exception $e) {
        echo "<script>document.getElementById('progress').innerHTML += '<p style=\"color: red;\">Error: " . $e->getMessage() . "</p>';</script>";
    }

    ob_end_flush();
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unsubscribe Confirmation</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            padding: 20px;
        }
        .container {
            max-width: 500px;
            margin: auto;
            background: #f9f9f9;
            padding: 20px;
            border-radius: 10px;
        }
        button {
            background-color: #d9534f;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background-color: #c9302c;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Unsubscribe from Earthen Newsletter</h2>
    <p>We've received your request to remove <strong><?php echo htmlspecialchars($email_addr); ?></strong> from our Earthen newsletter system.</p>
    <p>This will also check and delete your Buwana account on <a href="https://gobrik.com">GoBrik.com</a> (if one exists).</p>

    <form method="POST">
        <button type="submit" name="confirm_unsubscribe">Confirm Unsubscribe and Account Deletion</button>
    </form>
</div>

</body>
</html>