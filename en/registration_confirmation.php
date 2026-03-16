<?php
require_once '../earthenAuth_helper.php';
require_once '../auth/session_start.php';
require '../vendor/autoload.php';

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

ob_start();

if (!isLoggedIn()) {
    header("Location: login.php?redirect=register.php");
    exit();
}

require_once '../gobrikconn_env.php';

$buwana_id = $_SESSION['buwana_id'];

$training_id   = isset($_GET['id']) ? intval($_GET['id']) : 0;
$ecobricker_id = isset($_GET['ecobricker_id']) ? intval($_GET['ecobricker_id']) : 0;
$mode          = trim($_GET['mode'] ?? 'free');

$pledged_amount_idr = isset($_GET['pledged_amount_idr']) ? intval($_GET['pledged_amount_idr']) : 0;
$display_currency   = trim($_GET['display_currency'] ?? 'IDR');
$display_amount     = isset($_GET['display_amount']) ? trim((string)$_GET['display_amount']) : null;

if ($training_id <= 0 || $ecobricker_id <= 0) {
    header("Location: register.php?error=invalid");
    exit();
}

/* ---------------------------------------------------------
   Validate logged-in user matches ecobricker record
--------------------------------------------------------- */
$sql_user = "SELECT ecobricker_id, first_name, email_addr, buwana_id, full_name
             FROM tb_ecobrickers
             WHERE ecobricker_id = ?";
$stmt_user = $gobrik_conn->prepare($sql_user);
$stmt_user->bind_param("i", $ecobricker_id);
$stmt_user->execute();
$stmt_user->bind_result($db_ecobricker_id, $first_name, $email_addr, $user_buwana_id, $full_name);
$stmt_user->fetch();
$stmt_user->close();

if (!$db_ecobricker_id || (int)$user_buwana_id !== (int)$buwana_id) {
    header("Location: register.php?id=$training_id&error=unauthorized");
    exit();
}

/* ---------------------------------------------------------
   Load training
--------------------------------------------------------- */
$sql_training = "SELECT
    training_title,
    training_date,
    zoom_link,
    training_time_txt,
    training_type,
    feature_photo1_tmb,
    training_agenda,
    agenda_url,
    lead_trainer,
    trainer_contact_email,
    zoom_link_full,
    payment_mode,
    default_price_idr,
    funding_goal_idr,
    min_participants_required,
    threshold_status,
    pledge_deadline,
    payment_deadline,
    display_cost
FROM tb_trainings
WHERE training_id = ?";

$stmt_training = $gobrik_conn->prepare($sql_training);
$stmt_training->bind_param("i", $training_id);
$stmt_training->execute();
$stmt_training->bind_result(
    $training_title,
    $training_date,
    $zoom_link,
    $training_time_txt,
    $training_type,
    $feature_photo1_tmb,
    $training_agenda,
    $agenda_url,
    $lead_trainer,
    $trainer_contact_email,
    $zoom_link_full,
    $payment_mode,
    $default_price_idr,
    $funding_goal_idr,
    $min_participants_required,
    $threshold_status,
    $pledge_deadline,
    $payment_deadline,
    $display_cost
);
$stmt_training->fetch();
$stmt_training->close();

if (empty($training_title)) {
    header("Location: register.php?error=invalid");
    exit();
}

// Ensure optional fields are safe strings
$feature_photo1_tmb    = $feature_photo1_tmb ?? '';
$agenda_url            = $agenda_url ?? '';
$lead_trainer          = $lead_trainer ?? '';
$trainer_contact_email = $trainer_contact_email ?? '';
$zoom_link_full        = $zoom_link_full ?? '';
$zoom_link             = $zoom_link ?? '';
$payment_mode          = $payment_mode ?? 'free';
$display_cost          = $display_cost ?? 'Free / Donation';

if (empty($zoom_link_full)) {
    $zoom_link_full = "No additional Zoom details available.";
}

$training_date_formatted = !empty($training_date)
    ? date("F j, Y", strtotime($training_date))
    : '';

/* ---------------------------------------------------------
   Normalize mode from training, not user input
--------------------------------------------------------- */
$effective_mode = ($payment_mode === 'pledge_threshold') ? 'pledge_threshold' : 'free';

if ($effective_mode === 'free') {
    $mode = 'free';
} else {
    $mode = 'pledge_threshold';
}

/* ---------------------------------------------------------
   Prevent duplicates in new registration system
--------------------------------------------------------- */
$sql_check_reg = "SELECT registration_id, status
                  FROM training_registrations_tb
                  WHERE training_id = ? AND buwana_id = ?
                  LIMIT 1";
$stmt_check_reg = $gobrik_conn->prepare($sql_check_reg);
$stmt_check_reg->bind_param("ii", $training_id, $buwana_id);
$stmt_check_reg->execute();
$stmt_check_reg->bind_result($existing_registration_id, $existing_registration_status);
$already_has_registration = $stmt_check_reg->fetch();
$stmt_check_reg->close();

if ($already_has_registration) {
    if ($effective_mode === 'pledge_threshold') {
        $redirectUrl = "register.php?id=$training_id&pledged=1";
        if ($pledged_amount_idr > 0) {
            $redirectUrl .= "&pledged_amount_idr=" . urlencode((string)$pledged_amount_idr);
        }
        if (!empty($display_currency)) {
            $redirectUrl .= "&display_currency=" . urlencode($display_currency);
        }
        if ($display_amount !== null && $display_amount !== '') {
            $redirectUrl .= "&display_amount=" . urlencode($display_amount);
        }
        header("Location: $redirectUrl");
    } else {
        header("Location: register.php?id=$training_id&registered=1");
    }
    exit();
}

/* ---------------------------------------------------------
   Begin transaction
--------------------------------------------------------- */
$gobrik_conn->begin_transaction();

try {
    $new_registration_id = null;
    $new_pledge_id = null;

    if ($effective_mode === 'free') {
        /* -------------------------------------------------
           FREE COURSE FLOW
        ------------------------------------------------- */

        $registration_status = 'confirmed';
        $confirmed_at = date('Y-m-d H:i:s');

        $sql_insert_reg = "INSERT INTO training_registrations_tb
            (training_id, buwana_id, status, created_at, confirmed_at, attendee_name, attendee_email)
            VALUES (?, ?, ?, NOW(), ?, ?, ?)";
        $stmt_insert_reg = $gobrik_conn->prepare($sql_insert_reg);
        if (!$stmt_insert_reg) {
            throw new Exception("Could not prepare free registration insert.");
        }

        $stmt_insert_reg->bind_param(
            "iissss",
            $training_id,
            $buwana_id,
            $registration_status,
            $confirmed_at,
            $full_name,
            $email_addr
        );

        if (!$stmt_insert_reg->execute()) {
            throw new Exception("Could not create free registration.");
        }

        $new_registration_id = $gobrik_conn->insert_id;
        $stmt_insert_reg->close();

        // Legacy sync: keep old table alive for now
        $sql_check_legacy = "SELECT id FROM tb_training_trainees WHERE training_id = ? AND ecobricker_id = ?";
        $stmt_check_legacy = $gobrik_conn->prepare($sql_check_legacy);
        $stmt_check_legacy->bind_param("ii", $training_id, $ecobricker_id);
        $stmt_check_legacy->execute();
        $stmt_check_legacy->store_result();

        if ($stmt_check_legacy->num_rows === 0) {
            $stmt_check_legacy->close();

            $sql_insert_legacy = "INSERT INTO tb_training_trainees (training_id, ecobricker_id, rsvp_status)
                                  VALUES (?, ?, 'confirmed')";
            $stmt_insert_legacy = $gobrik_conn->prepare($sql_insert_legacy);
            if (!$stmt_insert_legacy) {
                throw new Exception("Could not prepare legacy trainee insert.");
            }
            $stmt_insert_legacy->bind_param("ii", $training_id, $ecobricker_id);

            if (!$stmt_insert_legacy->execute()) {
                throw new Exception("Could not insert legacy trainee row.");
            }
            $stmt_insert_legacy->close();
        } else {
            $stmt_check_legacy->close();
        }

        $gobrik_conn->commit();

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

    /* -----------------------------------------------------
       3P COURSE FLOW
    ----------------------------------------------------- */

    if ($pledged_amount_idr < 0) {
        throw new Exception("Invalid pledge amount.");
    }

    if ($display_currency === '') {
        $display_currency = 'IDR';
    }

    // If display amount was not provided, fall back to pledge amount
    if ($display_amount === null || $display_amount === '') {
        $display_amount = (string)$pledged_amount_idr;
    }

    $registration_status = 'pledged';

    $sql_insert_reg = "INSERT INTO training_registrations_tb
        (training_id, buwana_id, status, created_at, attendee_name, attendee_email)
        VALUES (?, ?, ?, NOW(), ?, ?)";
    $stmt_insert_reg = $gobrik_conn->prepare($sql_insert_reg);
    if (!$stmt_insert_reg) {
        throw new Exception("Could not prepare 3P registration insert.");
    }

    $stmt_insert_reg->bind_param(
        "iisss",
        $training_id,
        $buwana_id,
        $registration_status,
        $full_name,
        $email_addr
    );

    if (!$stmt_insert_reg->execute()) {
        throw new Exception("Could not create 3P registration.");
    }

    $new_registration_id = $gobrik_conn->insert_id;
    $stmt_insert_reg->close();

    // Snapshot the suggested amount at the moment of pledge
    $suggested_amount_idr = (int)($default_price_idr ?? 0);

    $pledge_status = 'active';

    $sql_insert_pledge = "INSERT INTO training_pledges_tb
        (
            training_id,
            buwana_id,
            pledge_currency,
            pledged_amount_idr,
            display_currency,
            display_amount,
            suggested_amount_idr,
            pledge_status,
            confirmed_at,
            created_at,
            updated_at
        )
        VALUES (?, ?, 'IDR', ?, ?, ?, ?, ?, NOW(), NOW(), NOW())";

    $stmt_insert_pledge = $gobrik_conn->prepare($sql_insert_pledge);
    if (!$stmt_insert_pledge) {
        throw new Exception("Could not prepare pledge insert.");
    }

    $stmt_insert_pledge->bind_param(
        "iiisis",
        $training_id,
        $buwana_id,
        $pledged_amount_idr,
        $display_currency,
        $display_amount,
        $suggested_amount_idr,
        $pledge_status
    );

    if (!$stmt_insert_pledge->execute()) {
        throw new Exception("Could not create pledge row.");
    }

    $new_pledge_id = $gobrik_conn->insert_id;
    $stmt_insert_pledge->close();

    // Link pledge to registration
    $sql_link_reg = "UPDATE training_registrations_tb SET pledge_id = ? WHERE registration_id = ?";
    $stmt_link_reg = $gobrik_conn->prepare($sql_link_reg);
    if (!$stmt_link_reg) {
        throw new Exception("Could not prepare registration pledge linkage.");
    }
    $stmt_link_reg->bind_param("ii", $new_pledge_id, $new_registration_id);

    if (!$stmt_link_reg->execute()) {
        throw new Exception("Could not link pledge to registration.");
    }
    $stmt_link_reg->close();

    $gobrik_conn->commit();

    sendTrainingPledgeReceivedEmail(
        $first_name,
        $email_addr,
        $training_title,
        $training_date_formatted,
        $training_time_txt,
        $training_type,
        $feature_photo1_tmb,
        $lead_trainer,
        $trainer_contact_email,
        $pledged_amount_idr,
        $display_currency,
        $display_amount,
        $display_cost,
        $payment_deadline
    );

    $redirectUrl = "register.php?id=$training_id&pledged=1";
    $redirectUrl .= "&pledged_amount_idr=" . urlencode((string)$pledged_amount_idr);
    $redirectUrl .= "&display_currency=" . urlencode($display_currency);
    $redirectUrl .= "&display_amount=" . urlencode((string)$display_amount);

    header("Location: $redirectUrl");
    exit();

} catch (Throwable $e) {
    $gobrik_conn->rollback();
    error_log("registration_confirmation.php error: " . $e->getMessage());
    header("Location: register.php?id=$training_id&error=failed");
    exit();
}

$gobrik_conn->close();


/* =========================================================
   EMAIL FUNCTIONS
========================================================= */

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

        return $response->getStatusCode() === 200;
    } catch (RequestException $e) {
        error_log("Mailgun API Exception (confirmation): " . $e->getMessage());
        return false;
    }
}

function sendTrainingPledgeReceivedEmail(
    string $first_name,
    string $email_addr,
    string $training_title,
    string $training_date_formatted,
    string $training_time_txt,
    string $training_type,
    string $feature_photo1_tmb,
    string $lead_trainer,
    string $trainer_contact_email,
    int $pledged_amount_idr,
    string $display_currency,
    string $display_amount,
    string $display_cost,
    ?string $payment_deadline
) {
    $client = new Client(['base_uri' => 'https://api.eu.mailgun.net/v3/']);
    $mailgunApiKey = getenv('MAILGUN_API_KEY');
    $mailgunDomain = 'mail.gobrik.com';

    $subject = "Your pledge for $training_title has been recorded";

    $payment_deadline_text = '';
    if (!empty($payment_deadline)) {
        $payment_deadline_text = date("F j, Y g:i A", strtotime($payment_deadline));
    }

    $html_body = "
        <div style='text-align: left; font-family: Arial, sans-serif;'>
            <img src='$feature_photo1_tmb' alt='Training Banner' style='max-width: 700px; max-height: 600px; border-radius: 8px; margin-bottom: 20px;'>
            <h2>Hi there, $first_name!</h2>

            <p>Your pledge for <strong>$training_title</strong> has been recorded.</p>

            <p>This course uses <strong>Pledge, Proceed and Pay</strong>. That means your pledge helps the course reach the minimum participation and funding threshold needed for it to go ahead.</p>

            <p><strong>Your pledge:</strong> $display_amount $display_currency</p>
            <p><strong>Pledge recorded in system currency:</strong> " . number_format($pledged_amount_idr) . " IDR</p>
            <p><strong>Course pricing display:</strong> $display_cost</p>

            <p>You do <strong>not</strong> need to make payment yet. If the course reaches its threshold, we will email you with the next step to complete payment.</p>
            " . (!empty($payment_deadline_text) ? "<p><strong>If the course proceeds, payment deadline:</strong> $payment_deadline_text</p>" : "") . "

            <p>Training date: <strong>$training_date_formatted</strong><br>
            Time: <strong>$training_time_txt</strong><br>
            Type: <strong>$training_type</strong></p>

            <p>Thank you for helping make this course possible.</p>

            <br><br>
            <p><strong>$lead_trainer</strong></p>
            <p>Contact: <a href='mailto:$trainer_contact_email'>$trainer_contact_email</a></p>
        </div>
    ";

    $text_body = <<<EOT
Hi there, $first_name!

Your pledge for "$training_title" has been recorded.

This course uses Pledge, Proceed and Pay. Your pledge helps the course reach the minimum participation and funding threshold needed for it to go ahead.

Your pledge: $display_amount $display_currency
Pledge recorded in system currency: {$pledged_amount_idr} IDR
Course pricing display: $display_cost

You do not need to make payment yet. If the course reaches its threshold, we will email you with the next step to complete payment.
EOT;

    if (!empty($payment_deadline_text)) {
        $text_body .= "\n\nIf the course proceeds, payment deadline: $payment_deadline_text";
    }

    $text_body .= <<<EOT

Training date: $training_date_formatted
Time: $training_time_txt
Type: $training_type

Thank you for helping make this course possible.

$lead_trainer
Contact: $trainer_contact_email
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

        return $response->getStatusCode() === 200;
    } catch (RequestException $e) {
        error_log("Mailgun API Exception (pledge): " . $e->getMessage());
        return false;
    }
}
?>