<?php
ob_start(); // Start output buffering

require_once '../earthenAuth_helper.php';
require_once '../scripts/earthen_subscribe_functions.php'; // Contains `earthenUnsubscribe()`

header('Content-Type: text/html; charset=UTF-8');
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error_log'); // Log errors to 'error_log' file in the same directory

// Get email from query string
$email_addr = $_GET['email'] ?? '';

// Validate email
if (empty($email_addr) || !filter_var($email_addr, FILTER_VALIDATE_EMAIL)) {
    die("<p style='color: red;'>Invalid email address. Please try again.</p>");
}

// If form is submitted, process unsubscription
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_unsubscribe'])) {
    echo "<div id='progress'><p>Processing your unsubscribe request for: <strong>$email_addr</strong>...</p></div>";
    ob_flush();
    flush();
    sleep(1); // Simulate loading time

    try {
        echo "<script>document.getElementById('progress').innerHTML += '<p>Unsubscribing from Earthen newsletter...</p>';</script>";
        ob_flush();
        flush();
        sleep(1);

        // Call Earthen unsubscribe function
        earthenUnsubscribe($email_addr);

        echo "<script>document.getElementById('progress').innerHTML += '<p><strong>✅ Successfully unsubscribed from the Earthen newsletter.</strong></p>';</script>";

    } catch (Exception $e) {
        error_log("Unsubscribe error: " . $e->getMessage());
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
    <title>Unsubscribe from Earthen Newsletter</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            padding: 20px;
            background-color: #f9f9f9;
        }
        .container {
            max-width: 500px;
            margin: auto;
            background: #ffffff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
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
    <p>We have received your request to remove <strong><?php echo htmlspecialchars($email_addr); ?></strong> from our Earthen newsletter system.</p>

    <form method="POST">
        <button type="submit" name="confirm_unsubscribe">Confirm Unsubscribe</button>
    </form>
</div>

</body>
</html>
