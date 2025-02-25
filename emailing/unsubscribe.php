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
$email_addr = trim($_GET['email'] ?? ''); // Trim to remove any extra spaces

// Validate email
if (empty($email_addr) || !filter_var($email_addr, FILTER_VALIDATE_EMAIL)) {
    die("<p style='color: red;'>Invalid email address. Please try again.</p>");
}

// Debug log file
$log_file = '../logs/unsubscribe.log';

// Function to log debug messages
function log_debug($message) {
    global $log_file;
    file_put_contents($log_file, date("[Y-m-d H:i:s]") . " " . $message . "\n", FILE_APPEND);
}

// Log email received
log_debug("Received unsubscribe request for: $email_addr");

// If form is submitted, process unsubscription
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_unsubscribe'])) {
    echo "<div id='progress'><p>Checking database for accounts linked to: <strong>$email_addr</strong>...</p></div>";
    ob_flush();
    flush();
    sleep(1); // Simulate loading time

    try {
        // 🔹 Fetch user details from tb_ecobrickers
        $sql_fetch_details = "SELECT ecobricker_id, buwana_id FROM tb_ecobrickers WHERE LOWER(email_addr) = LOWER(?)";
        $stmt_fetch_details = $gobrik_conn->prepare($sql_fetch_details);
        $stmt_fetch_details->bind_param('s', $email_addr);
        $stmt_fetch_details->execute();
        $stmt_fetch_details->bind_result($ecobricker_id, $buwana_id);
        $stmt_fetch_details->fetch();
        $stmt_fetch_details->close();

        log_debug("Fetched from tb_ecobrickers - Ecobricker ID: " . ($ecobricker_id ?? 'NULL') . ", Buwana ID: " . ($buwana_id ?? 'NULL'));

        // If no matching account is found
        if (empty($ecobricker_id)) {
            log_debug("No matching tb_ecobrickers record found for email: $email_addr");
            echo "<script>document.getElementById('progress').innerHTML += '<p>No account found in GoBrik.</p>';</script>";
            ob_flush();
            flush();
        } else {
            echo "<script>document.getElementById('progress').innerHTML += '<p>Unsubscribing from GoBrik...</p>';</script>";
            ob_flush();
            flush();
            sleep(1);

            // 🔹 Mark as unsubscribed (ADD ERROR CHECKING)
            $sql_unsubscribe_ecobricker = "UPDATE tb_ecobrickers SET subscribed_to_emails = 0 WHERE ecobricker_id = ?";
            $stmt_unsubscribe_ecobricker = $gobrik_conn->prepare($sql_unsubscribe_ecobricker);
            if (!$stmt_unsubscribe_ecobricker) {
                log_debug("Error preparing unsubscribe statement: " . $gobrik_conn->error);
                throw new Exception("Database error: Unable to prepare unsubscribe query.");
            }

            $stmt_unsubscribe_ecobricker->bind_param('i', $ecobricker_id);
            $stmt_unsubscribe_ecobricker->execute();

            if ($stmt_unsubscribe_ecobricker->affected_rows === 0) {
                log_debug("No rows updated in tb_ecobrickers for ecobricker_id: $ecobricker_id");
            } else {
                log_debug("Successfully unsubscribed ecobricker_id: $ecobricker_id");
            }

            $stmt_unsubscribe_ecobricker->close();
        }

        // 🔹 Unsubscribe in users_tb
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

            log_debug("Buwana account unsubscribed for buwana_id: $buwana_id");

            echo "<script>document.getElementById('progress').innerHTML += '<p>Buwana account unsubscribed.</p>';</script>";
            ob_flush();
            flush();
        } else {
            log_debug("No linked Buwana account found for email: $email_addr");
            echo "<script>document.getElementById('progress').innerHTML += '<p>No linked Buwana account found.</p>';</script>";
            ob_flush();
            flush();
        }

        // 🔹 Call external unsubscribe function
        echo "<script>document.getElementById('progress').innerHTML += '<p>Unsubscribing from Earthen newsletter...</p>';</script>";
        ob_flush();
        flush();
        sleep(1);
        earthenUnsubscribe($email_addr);
        log_debug("Earthen unsubscribe API called for: $email_addr");

        echo "<script>document.getElementById('progress').innerHTML += '<p><strong>✅ Unsubscribe complete.</strong></p>';</script>";

    } catch (Exception $e) {
        log_debug("ERROR: " . $e->getMessage());
        echo "<script>document.getElementById('progress').innerHTML += '<p style=\"color: red;\">Error: " . $e->getMessage() . "</p>';</script>";
    }

    ob_end_flush();
    exit();
}
?>
