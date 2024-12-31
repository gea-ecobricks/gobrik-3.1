<?php
require_once '../earthenAuth_helper.php';
require_once '../gobrikconn_env.php';
require_once '../smtp_mailer.php'; // Assuming this is your SMTP setup file

header('Content-Type: application/json');
$response = [];

try {
    // Fetch the next ecobricker to send an email
    $sql_fetch_ecobricker = "
        SELECT ecobricker_id, first_name, date_registered, email_addr
        FROM tb_ecobrickers
        WHERE test_email_status = 'unsent'
          AND buwana_activated = 1
        ORDER BY ecobricker_id ASC
        LIMIT 1";
    $stmt_fetch_ecobricker = $gobrik_conn->prepare($sql_fetch_ecobricker);
    if (!$stmt_fetch_ecobricker) {
        throw new Exception('Error preparing fetch statement: ' . $gobrik_conn->error);
    }
    $stmt_fetch_ecobricker->execute();
    $stmt_fetch_ecobricker->bind_result($ecobricker_id, $first_name, $date_registered, $email_addr);
    $stmt_fetch_ecobricker->fetch();
    $stmt_fetch_ecobricker->close();

    if (empty($ecobricker_id)) {
        throw new Exception('No more emails to send. All eligible ecobrickers have been notified.');
    }

    // Compose the email
    $subject = "New Year... new GoBrik! Fully regenerated to be corporate code-free.";
    $body = "
        Happy New Year $first_name!

        In celebration of 2025, we're excited to launch the new GoBrik 3.0.
        While on $date_registered, you signed up for GoBrik, today 1/1/2025 we invite you to regenerate your account on our fully revamped system to preserve ownership of all your logged ecobricks, brikcoin, and validation credit balances.

        Please login to activate your account: https://gobrik.com

        Together we can keep plastic out of industry and out of the biosphere.

        Russell and the GoBrik, GEA team.
    ";
    $from = "no-reply@gobrik.com";

    // Send the email
    $mail_sent = smtp_mail($email_addr, $subject, $body, $from);
    if (!$mail_sent) {
        throw new Exception('Failed to send the email.');
    }

    // Update test_email_status in the database
    $sql_update_status = "UPDATE tb_ecobrickers SET test_email_status = 'sent' WHERE ecobricker_id = ?";
    $stmt_update_status = $gobrik_conn->prepare($sql_update_status);
    if (!$stmt_update_status) {
        throw new Exception('Error preparing update statement: ' . $gobrik_conn->error);
    }
    $stmt_update_status->bind_param('i', $ecobricker_id);
    $stmt_update_status->execute();
    $stmt_update_status->close();

    $response = [
        'success' => true,
        'message' => "Email sent successfully to $email_addr.",
    ];
} catch (Exception $e) {
    $response = [
        'success' => false,
        'error' => $e->getMessage(),
    ];
}

echo json_encode($response);
exit();
?>
