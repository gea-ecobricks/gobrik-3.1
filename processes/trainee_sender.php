<?php
require_once '../earthenAuth_helper.php';
require '../vendor/autoload.php';
use GuzzleHttp\Client;

header('Content-Type: application/json');

if (!isset($_SESSION['buwana_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

$training_id = intval($_POST['training_id'] ?? 0);
$test = isset($_POST['test']) ? intval($_POST['test']) : 0;
$custom_message = trim($_POST['message'] ?? '');

if ($training_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid training']);
    exit();
}

require_once '../gobrikconn_env.php';

$sql = "SELECT training_title, training_time_txt, lead_trainer, trainer_contact_email, zoom_link_full FROM tb_trainings WHERE training_id = ?";
$stmt = $gobrik_conn->prepare($sql);
$stmt->bind_param('i', $training_id);
$stmt->execute();
$stmt->bind_result($training_title, $training_time_txt, $lead_trainer, $trainer_contact_email, $zoom_link_full);
$stmt->fetch();
$stmt->close();

$lead_trainer = $lead_trainer ?? '';
$trainer_contact_email = $trainer_contact_email ?? '';
$training_title = $training_title ?? '';
$training_time_txt = $training_time_txt ?? '';
$zoom_link_full = $zoom_link_full ?? '';

$client = new Client(['base_uri' => 'https://api.eu.mailgun.net/v3/']);
$mailgunApiKey = getenv('MAILGUN_API_KEY');
$mailgunDomain = 'mail.gobrik.com';

function sendMsg($to, $first_name, $vars, $override = '') {
    global $client, $mailgunApiKey, $mailgunDomain, $trainer_contact_email, $lead_trainer, $training_title;
    $body = <<<EOT
Hi there {$first_name},

Thank you again for registering for our {$vars['title']}!  

This is a reminder that today, at {$vars['time_txt']} the workshop begins!

The training is on Zoom.  Here's the full zoom link and invite you will need to access:

{$vars['zoom']}

We'll be opening up the meeting 15 minutes earlier to test systems and audio.  Feel free to join early for a meet and greet.

Meanwhile, we're also setting up a support chat for the week.  I don't know about you, but I've got a lot of plastic saved up and it needs packing.  So after the workshop we're going to use the group to let you (and us!) share our ecobricking progress and ask questions.

We do our best to avoid meta products in the same way we avoid single-use plastic products, so sorry no whatsapp.  We use Signal (a free, open-source, foundation-run equivalent).  Click the link to join the group now or after the workshop:

https://signal.group/#CjQKICIVvzmbBXqB7_9-5XyXd53zbdw7RLqVWKbQ8UzX2EkREhC0_jo3SCAr40xIO_jePrmT

Unlike some of our GEA workshops, no need to bring anything to this workshop except your curiousity.  It will be interactive, so be prepared to share and anwser questions via mic and via chat.

Alright, see you soon!

{$lead_trainer}
EOT;

    if ($override !== '') {
        $body = $override;
    }

    $html = nl2br($body);

    try {
        $response = $client->post("https://api.eu.mailgun.net/v3/{$mailgunDomain}/messages", [
            'auth' => ['api', $mailgunApiKey],
            'form_params' => [
                'from' => "$lead_trainer <{$trainer_contact_email}>",
                'to' => $to,
                'cc' => $trainer_contact_email,
                'bcc' => 'russmaier@gmail.com',
                'subject' => "Reminder: {$training_title} starts today",
                'html' => $html,
                'text' => $body,
                'h:Reply-To' => $trainer_contact_email,
                'o:stop-retrying' => 'yes',
                'o:deliverytime' => gmdate('D, d M Y H:i:s T', strtotime('-1 hour'))
            ]
        ]);
        return $response->getStatusCode() == 200;
    } catch (Exception $e) {
        error_log('TraineeSender error: ' . $e->getMessage());
        return false;
    }
}

$vars = ['title' => $training_title, 'time_txt' => $training_time_txt, 'zoom' => $zoom_link_full];

$messages = [];
$success = true;

if ($test) {
    $ok = sendMsg($trainer_contact_email, $lead_trainer, $vars, $custom_message);
    $success = $ok;
    $messages[] = $ok ? "Test sent to $trainer_contact_email" : "Failed to send test";
} else {
    $sql = "SELECT e.first_name, e.email_addr FROM tb_training_trainees t INNER JOIN tb_ecobrickers e ON t.ecobricker_id = e.ecobricker_id WHERE t.training_id = ?";
    $stmt = $gobrik_conn->prepare($sql);
    $stmt->bind_param('i', $training_id);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $ok = sendMsg($row['email_addr'], $row['first_name'], $vars, $custom_message);
        $messages[] = $ok ? "Sent to {$row['email_addr']}" : "Failed to {$row['email_addr']}";
        if (!$ok) $success = false;
    }
    $stmt->close();
}

$gobrik_conn->close();

error_log('[TraineeSender] '.implode(' | ', $messages));

echo json_encode(['success' => $success, 'message' => implode('\n', $messages)]);
