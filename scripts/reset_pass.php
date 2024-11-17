<?php
require '../vendor/autoload.php'; // Include Composer's autoloader
require '../buwanaconn_env.php'; // Database connection information

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

// Turn on error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set language and validate the email input
$lang = isset($_POST['lang']) ? filter_var($_POST['lang'], FILTER_SANITIZE_STRING) : 'en';
$email = isset($_POST['email']) ? filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL) : '';

if ($email) {
    try {
        // Check if email exists in the database
        $stmt = $buwana_conn->prepare("SELECT email, first_name FROM users_tb WHERE email = ?");
        if (!$stmt) {
            throw new Exception("Prepare statement failed: " . $buwana_conn->error);
        }
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->bind_result($result_email, $first_name);
        $stmt->fetch();
        $stmt->close();

        if ($result_email) {
            // Generate a unique token
            $password_reset_token = bin2hex(random_bytes(16));
            $password_reset_expires = date('Y-m-d H:i:s', strtotime('+10 minutes'));

            // Update the user's password reset token and expiry in the database
            $stmt = $buwana_conn->prepare("UPDATE users_tb SET password_reset_token = ?, password_reset_expires = ? WHERE email = ?");
            if (!$stmt) {
                throw new Exception("Prepare statement failed: " . $buwana_conn->error);
            }
            $stmt->bind_param("sss", $password_reset_token, $password_reset_expires, $email);
            $stmt->execute();
            $stmt->close();

            // Language-specific email content
            $subjects = [
                'en' => 'Reset your GoBrik password',
                'fr' => 'Réinitialisez votre mot de passe GoBrik',
                'es' => 'Restablezca su contraseña de GoBrik',
                'id' => 'Atur Ulang Kata Sandi GoBrik Anda'
            ];

            $bodies = [
                'en' => "Hello $first_name,<br><br>
                    A password reset was requested at " . date('Y-m-d H:i:s') . " on GoBrik.com for your Buwana account. If you didn't request this, please disregard!<br><br>
                    To reset your password, please click the following link:<br><br>
                    <a href='https://gobrik.com/{$lang}/password-reset.php?token={$password_reset_token}'>Reset Password</a><br><br>
                    The GoBrik Team",
                // Additional language bodies as above...
            ];

            // Send email using Mailgun API
            $client = new Client(['base_uri' => 'https://api.eu.mailgun.net/v3/']);
            $mailgunApiKey = getenv('MAILGUN_API_KEY');
$mailgunDomain = getenv('MAILGUN_DOMAIN') ?: 'mail.gobrik.com';

            try {
                $response = $client->post("{$mailgunDomain}/messages", [
                    'auth' => ['api', $mailgunApiKey],
                    'form_params' => [
                        'from' => 'GoBrik Team <no-reply@mail.gobrik.com>', // Use your Mailgun verified domain email
                        'to' => $email,
                        'subject' => $subjects[$lang] ?? $subjects['en'],
                        'html' => $bodies[$lang] ?? $bodies['en'],
                        'text' => strip_tags($bodies[$lang] ?? $bodies['en']), // Plain text fallback
                    ]
                ]);

                if ($response->getStatusCode() == 200) {
                    $confirmation_code = uniqid("email_", true);
                    error_log("Mailgun: Email sent successfully to $email. Confirmation Code: " . $confirmation_code);
                    echo '<script>alert("An email with a link to reset your GoBrik Buwana password has been sent!"); window.location.href = "../' . $lang . '/login.php";</script>';
                } else {
                    error_log("Mailgun: Failed to send email to $email. Status: " . $response->getStatusCode());
                    echo '<script>alert("Failed to send email. Please try again later."); window.location.href = "../' . $lang . '/login.php";</script>';
                }

            } catch (RequestException $e) {
                error_log("Mailgun API Exception: " . $e->getMessage());
                echo '<script>alert("Message could not be sent... Please try again later."); window.location.href = "../' . $lang . '/login.php";</script>';
            }
        } else {
            header('Location: ../' . $lang . '/login.php?email_not_found&email=' . urlencode($email));
            exit();
        }
    } catch (Exception $e) {
        error_log("Database Exception: " . $e->getMessage());
        echo "<script>console.error('Error: " . htmlspecialchars($e->getMessage()) . "');</script>";
    }
} else {
    echo '<script>alert("Please enter a valid email address."); window.location.href = "../' . $lang . '/login.php";</script>';
}

// Close the database connection
$buwana_conn->close();
?>
