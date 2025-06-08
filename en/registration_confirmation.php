<?php
require_once '../earthenAuth_helper.php';
require '../vendor/autoload.php';

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

ob_start();

if (!isLoggedIn()) {
    header("Location: login.php?redirect=register.php");
    exit();
}

require_once '../gobrikconn_env.php';

$training_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$ecobricker_id = isset($_GET['ecobricker_id']) ? intval($_GET['ecobricker_id']) : 0;

if ($training_id <= 0 || $ecobricker_id <= 0) {
    header("Location: register.php?error=invalid");
    exit();
}

$sql_user = "SELECT first_name, email_addr FROM tb_ecobrickers WHERE ecobricker_id = ?";
$stmt_user = $gobrik_conn->prepare($sql_user);
$stmt_user->bind_param("i", $ecobricker_id);
$stmt_user->execute();
$stmt_user->bind_result($first_name, $email_addr);
$stmt_user->fetch();
$stmt_user->close();

$sql_training = "SELECT training_title, training_date, zoom_link, training_time_txt, training_type, feature_photo1_tmb, training_agenda, agenda_url, lead_trainer, trainer_contact_email, zoom_link_full FROM tb_trainings WHERE training_id = ?";
$stmt_training = $gobrik_conn->prepare($sql_training);
$stmt_training->bind_param("i", $training_id);
$stmt_training->execute();
$stmt_training->bind_result($training_title, $training_date, $zoom_link, $training_time_txt, $training_type, $feature_photo1_tmb, $training_agenda, $agenda_url, $lead_trainer, $trainer_contact_email, $zoom_link_full);
$stmt_training->fetch();
$stmt_training->close();

if (!isset($zoom_link_full)) {
    $zoom_link_full = "No additional Zoom details available.";
}

$training_date_formatted = date("F j, Y", strtotime($training_date));

$sql_check = "SELECT id FROM tb_training_trainees WHERE training_id = ? AND ecobricker_id = ?";
$stmt_check = $gobrik_conn->prepare($sql_check);
$stmt_check->bind_param("ii", $training_id, $ecobricker_id);
$stmt_check->execute();
$stmt_check->store_result();

if ($stmt_check->num_rows > 0) {
    $stmt_check->close();
    $gobrik_conn->close();
    header("Location: register.php?id=$training_id&registered=1");
    exit();
}
$stmt_check->close();

$sql_insert = "INSERT INTO tb_training_trainees (training_id, ecobricker_id, rsvp_status) VALUES (?, ?, 'confirmed')";
$stmt_insert = $gobrik_conn->prepare($sql_insert);
$stmt_insert->bind_param("ii", $training_id, $ecobricker_id);

if ($stmt_insert->execute()) {
    $stmt_insert->close();

    sendTrainingConfirmationEmail(
        $first_name,
        $email_addr,
        $training_title,
        $training_date_formatted,
        $zoom_link,
        $zoom_link_full,
        $training_time_txt,
        $training_type,
        $feature_photo1_tmb,
        $agenda_url,
        $lead_trainer,
        $trainer_contact_email
    );

    header("Location: register.php?id=$training_id&registered=1");
    exit();
}

header("Location: register.php?id=$training_id&error=failed");
exit();

$gobrik_conn->close();


/* ✅ Proper Function Definition */
function sendTrainingConfirmationEmail(
    string $first_name,
    string $email_addr,
    string $training_title,
    string $training_date_formatted,
    string $zoom_link,
    string $zoom_link_full,
    string $training_time_txt,
    string $training_type,
    string $feature_photo1_tmb,
    string $agenda_url,
    string $lead_trainer,
    string $trainer_contact_email
) {
    $client = new Client(['base_uri' => 'https://api.eu.mailgun.net/v3/']);
    $mailgunApiKey = getenv('MAILGUN_API_KEY');
    $mailgunDomain = 'mail.gobrik.com';
    $zoom_full_html = nl2br($zoom_link_full);

    $subject = "Your registration to $training_title is confirmed!";

    $html_body = "
        <div style='text-align: left; font-family: Arial, sans-serif;'>
            <img src='$feature_photo1_tmb' alt='Training Banner' style='max-width: 700px; max-height: 600px; border-radius: 8px; margin-bottom: 20px;'>
            <h2>Hi there, $first_name!</h2>
            <p>Alright! 🎉 You're confirmed for our <strong>$training_type</strong> on <strong>$training_date_formatted</strong>.</p>

            <p>You can join <strong>$training_title</strong> on <strong>$training_date_formatted</strong> (that's $training_time_txt) using the following link:</p>
            <p style='font-size: 1.2em;'><a href='$zoom_link' target='_blank' style='color: #0073e6; font-weight: bold;'>🔗 Join Zoom Meeting</a></p>
            <p>The event will open 15 minutes beforehand for a meet & greet.</p>

            <p>View the agenda: <a href='$agenda_url' target='_blank'>$agenda_url</a></p>

            <br>
            <p>Thank you, and see you then!</p>
            <br><br>
            <p><strong>$lead_trainer</strong></p>
            <p>Contact: <a href='mailto:$trainer_contact_email'>$trainer_contact_email</a></p>
            <br><br><br>
            <hr>
            <p><strong>Full Zoom Invitation:</strong></p>
            <p>$zoom_full_html</p>
        </div>
    ";

    $text_body = <<<EOT
Hi there, $first_name!

You're confirmed for our $training_type on $training_date_formatted.

You can join "$training_title" on $training_date_formatted (that's $training_time_txt) using this link:
$zoom_link

The event will open 15 minutes beforehand for a meet & greet.

Agenda: $agenda_url

Thank you, and see you then!

$lead_trainer
Contact: $trainer_contact_email

-----
Full Zoom Invitation:
$zoom_link_full
EOT;

    try {
        $response = $client->post("$mailgunDomain/messages", [
            'auth' => ['api', $mailgunApiKey],
            'form_params' => [
                'from' => 'GoBrik Team <no-reply@mail.gobrik.com>',
                'to' => $email_addr,
                'subject' => $subject,
                'html' => $html_body,
                'text' => $text_body,
                'h:Reply-To' => $trainer_contact_email,
            ]
        ]);

        if ($response->getStatusCode() === 200) {
            error_log("Mailgun: Training confirmation email sent successfully to $email_addr");
            return true;
        } else {
            error_log("Mailgun: Failed to send confirmation email to $email_addr.");
            return false;
        }

    } catch (RequestException $e) {
        error_log("Mailgun API Exception: " . $e->getMessage());
        return false;
    }
}
?>
