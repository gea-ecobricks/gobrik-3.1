<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php'; // Include Composer's autoloader for PHPMailer

/**
 * Function to send an email using PHPMailer and SMTP.
 *
 * @param string $to      Recipient email address.
 * @param string $subject Email subject.
 * @param string $body    Email body (HTML format).
 * @param string $from    Sender email address.
 * @return bool           True if the email is sent successfully, otherwise false.
 */
function smtp_mail($to, $subject, $body, $from = 'no-reply@gobrik.com') {
    try {
        $mail = new PHPMailer(true);

        // Server settings
        $mail->isSMTP();
        $mail->Host = 'mail.ecobricks.org'; // SMTP server
        $mail->SMTPAuth = true;
        $mail->Username = getenv('SMTP_USERNAME'); // SMTP username (use environment variable)
        $mail->Password = getenv('SMTP_PASSWORD'); // SMTP password (use environment variable)
        $mail->SMTPSecure = false; // Disable TLS/SSL if unnecessary
        $mail->Port = 26; // SMTP port

        // Recipients
        $mail->setFrom($from, 'GoBrik System');
        $mail->addAddress($to); // Add a recipient

        // Content
        $mail->isHTML(true); // Set email format to HTML
        $mail->Subject = $subject;
        $mail->Body = $body;

        // Send the email
        if ($mail->send()) {
            error_log("Email sent successfully to $to with subject '$subject'.");
            return true;
        } else {
            error_log("Failed to send email to $to. Error: " . $mail->ErrorInfo);
            return false;
        }
    } catch (Exception $e) {
        error_log("Exception: Could not send email to $to. Error: " . $e->getMessage());
        return false;
    }
}
?>
