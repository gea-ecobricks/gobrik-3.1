<?php
require_once '../earthenAuth_helper.php';
startSecureSession();

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

$buwana_id = $_SESSION['buwana_id'];
require_once '../gobrikconn_env.php';
require_once '../buwanaconn_env.php';
include '../scripts/photo-functions.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
    exit();
}

$training_id = isset($_POST['training_id']) ? intval($_POST['training_id']) : 0;
if ($training_id <= 0 && isset($_GET['id'])) {
    $training_id = intval($_GET['id']);
}
$editing = ($training_id > 0);

$training_title = trim($_POST['training_title'] ?? '');
$training_subtitle = trim($_POST['training_subtitle'] ?? '');
$training_date = trim($_POST['training_date'] ?? '');
$training_time_txt = trim($_POST['training_time_txt'] ?? '');
if (!empty($training_date)) {
    if (strlen($training_date) == 10) {
        $training_date .= "T12:00";
    }
    $training_date = date("Y-m-d H:i:s", strtotime($training_date));
} else {
    $training_date = null;
}
$youtube_result_video = trim($_POST['youtube_result_video'] ?? '');
$moodle_url = trim($_POST['moodle_url'] ?? '');
$ready_to_show = isset($_POST['ready_to_show']) ? 1 : 0;
$featured_description = trim($_POST['featured_description'] ?? '');
$training_summary = trim($_POST['training_summary'] ?? '');
$training_agenda = trim($_POST['training_agenda'] ?? '');
$training_success = trim($_POST['training_success'] ?? '');
$training_challenges = trim($_POST['training_challenges'] ?? '');
$training_lessons_learned = trim($_POST['training_lessons_learned'] ?? '');
$training_location = trim($_POST['training_location'] ?? '');
$training_type = trim($_POST['training_type'] ?? '');
$training_language = trim($_POST['training_language'] ?? 'en');
$zoom_link = trim($_POST['zoom_link'] ?? '');
$zoom_link_full = trim($_POST['zoom_link_full'] ?? '');
$registration_scope = trim($_POST['registration_scope'] ?? '');
$trainer_contact_email = trim($_POST['trainer_contact_email'] ?? '');

$no_participants = filter_var($_POST['no_participants'], FILTER_VALIDATE_INT) ?? 0;
$briks_made = filter_var($_POST['briks_made'], FILTER_VALIDATE_INT) ?? 0;
$avg_brik_weight = filter_var($_POST['avg_brik_weight'], FILTER_VALIDATE_INT) ?? 0;
$country_id = filter_var($_POST['country_id'], FILTER_VALIDATE_INT) ?? null;
$community_id = filter_var($_POST['community_id'], FILTER_VALIDATE_INT) ?? null;
$trainers = isset($_POST['trainers']) && is_array($_POST['trainers']) ? array_map('intval', $_POST['trainers']) : [];

function buildLeadTrainerString($conn, $trainerIds, $language) {
    if (empty($trainerIds)) return '';

    $names = [];
    $stmt = $conn->prepare("SELECT full_name FROM tb_ecobrickers WHERE ecobricker_id = ?");
    foreach ($trainerIds as $id) {
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->bind_result($name);
        if ($stmt->fetch()) {
            $names[] = $name;
        }
        $stmt->reset();
    }
    $stmt->close();

    $andWord = 'and';
    if ($language === 'fr') {
        $andWord = 'et';
    } elseif ($language === 'es') {
        $andWord = 'y';
    } elseif ($language === 'id') {
        $andWord = 'dan';
    }

    $count = count($names);
    if ($count === 1) return $names[0];
    if ($count === 2) return $names[0] . ' ' . $andWord . ' ' . $names[1];

    $last = array_pop($names);
    return implode(', ', $names) . ' ' . $andWord . ' ' . $last;
}

$lead_trainer = buildLeadTrainerString($gobrik_conn, $trainers, $training_language);

if ($community_id !== null) {
    $stmt = $buwana_conn->prepare("SELECT community_id FROM communities_tb WHERE community_id = ?");
    $stmt->bind_param("i", $community_id);
    $stmt->execute();
    if ($stmt->get_result()->num_rows === 0) {
        $community_id = null;
    }
    $stmt->close();
}

if ($editing) {
    $sql = "UPDATE tb_trainings SET
            training_title=?, training_subtitle=?, lead_trainer=?, country_id=?, training_date=?, training_time_txt=?,
            no_participants=?, training_type=?, training_language=?, briks_made=?, avg_brik_weight=?,
            location_lat=?, location_long=?, training_location=?, training_summary=?, training_agenda=?,
            training_success=?, training_challenges=?, training_lessons_learned=?,
            youtube_result_video=?, moodle_url=?, ready_to_show=?, featured_description=?, community_id=?,
            zoom_link=?, zoom_link_full=?, registration_scope=?, trainer_contact_email=?
            WHERE training_id=?";
    $stmt = $gobrik_conn->prepare($sql);
    $stmt->bind_param("sssississiiddssssssssisissssi",
        $training_title, $training_subtitle, $lead_trainer, $country_id, $training_date, $training_time_txt, $no_participants,
        $training_type, $training_language, $briks_made, $avg_brik_weight, $latitude, $longitude, $training_location,
        $training_summary, $training_agenda, $training_success, $training_challenges,
        $training_lessons_learned, $youtube_result_video, $moodle_url, $ready_to_show,
        $featured_description, $community_id, $zoom_link, $zoom_link_full, $registration_scope, $trainer_contact_email,
        $training_id
    );
    if ($stmt->execute()) {
        $new_training_id = $training_id;
    } else {
        echo json_encode(['success' => false, 'error' => 'Update failed.']);
        exit();
    }
    $stmt->close();

    $fields = [];
    $values = [];
    $types = '';
    for ($i = 1; $i <= 3; $i++) {
        $name = "feature_photo{$i}_main";
        if (!empty($_POST[$name])) {
            $fields[] = "$name=?";
            $values[] = trim($_POST[$name]);
            $types .= 's';
        }
    }
    if (!empty($fields)) {
        $fields_sql = implode(',', $fields);
        $values[] = $new_training_id;
        $types .= 'i';
        $up = $gobrik_conn->prepare("UPDATE tb_trainings SET $fields_sql WHERE training_id=?");
        $up->bind_param($types, ...$values);
        $up->execute();
        $up->close();
    }
} else {
    $sql = "INSERT INTO tb_trainings
            (training_title, training_subtitle, lead_trainer, country_id, training_date, training_time_txt, no_participants,
            training_type, training_language, briks_made, avg_brik_weight, location_lat, location_long,
            training_location, training_summary, training_agenda, training_success, training_challenges,
            training_lessons_learned, youtube_result_video, moodle_url, ready_to_show, featured_description, community_id,
            zoom_link, zoom_link_full, registration_scope, trainer_contact_email)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $gobrik_conn->prepare($sql);
    $stmt->bind_param("sssississiiddssssssssisissss",
        $training_title, $training_subtitle, $lead_trainer, $country_id, $training_date, $training_time_txt, $no_participants,
        $training_type, $training_language, $briks_made, $avg_brik_weight, $latitude, $longitude, $training_location,
        $training_summary, $training_agenda, $training_success, $training_challenges,
        $training_lessons_learned, $youtube_result_video, $moodle_url, $ready_to_show, $featured_description, $community_id,
        $zoom_link, $zoom_link_full, $registration_scope, $trainer_contact_email
    );
    if ($stmt->execute()) {
        $new_training_id = $gobrik_conn->insert_id;
    } else {
        echo json_encode(['success' => false, 'error' => 'Insert failed.']);
        exit();
    }
    $stmt->close();

    $fields = [];
    $values = [];
    $types = '';
    for ($i = 1; $i <= 3; $i++) {
        $name = "feature_photo{$i}_main";
        if (!empty($_POST[$name])) {
            $fields[] = "$name=?";
            $values[] = trim($_POST[$name]);
            $types .= 's';
        }
    }
    if (!empty($fields)) {
        $fields_sql = implode(',', $fields);
        $values[] = $new_training_id;
        $types .= 'i';
        $up = $gobrik_conn->prepare("UPDATE tb_trainings SET $fields_sql WHERE training_id=?");
        $up->bind_param($types, ...$values);
        $up->execute();
        $up->close();
    }
}

// ✅ Update trainers association
if ($new_training_id && !empty($trainers)) {
    $del = $gobrik_conn->prepare("DELETE FROM tb_training_trainers WHERE training_id = ?");
    $del->bind_param("i", $new_training_id);
    $del->execute();
    $del->close();

    $ins = $gobrik_conn->prepare("INSERT INTO tb_training_trainers (training_id, ecobricker_id) VALUES (?, ?)");
    if (!$ins) {
        die("Prepare failed: " . $gobrik_conn->error);
    }


    // avoid undefined variable notice that can corrupt JSON output
    $tr_id = null;

    $ins->bind_param("ii", $new_training_id, $tr_id);

    foreach ($trainers as $tr_id) {
        if (!$ins->execute()) {
            error_log("Insert error for ecobricker $tr_id: " . $ins->error);
        }
    }

    $ins->close();
}

echo json_encode(['success' => true, 'training_id' => $new_training_id]);

