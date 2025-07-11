<?php
require_once '../earthenAuth_helper.php';
require_once '../auth/session_start.php';

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

// Default to existing values for certain fields when not posted
$existing_summary = $existing_success = $existing_challenges = $existing_lessons = '';
if ($editing) {
    $stmt_existing = $gobrik_conn->prepare("SELECT training_summary, training_success, training_challenges, training_lessons_learned FROM tb_trainings WHERE training_id=?");
    $stmt_existing->bind_param("i", $training_id);
    $stmt_existing->execute();
    $stmt_existing->bind_result($existing_summary, $existing_success, $existing_challenges, $existing_lessons);
    $stmt_existing->fetch();
    $stmt_existing->close();
}

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
$show_report = isset($_POST['show_report']) ? 1 : 0;
$show_signup_count = isset($_POST['show_signup_count']) ? 1 : 0;
$featured_description = trim($_POST['featured_description'] ?? '');
$training_summary = isset($_POST['training_summary']) ? trim($_POST['training_summary']) : $existing_summary;
$training_agenda = trim($_POST['training_agenda'] ?? '');
$training_success = isset($_POST['training_success']) ? trim($_POST['training_success']) : $existing_success;
$training_challenges = isset($_POST['training_challenges']) ? trim($_POST['training_challenges']) : $existing_challenges;
$training_lessons_learned = isset($_POST['training_lessons_learned']) ? trim($_POST['training_lessons_learned']) : $existing_lessons;
$training_location = trim($_POST['training_location'] ?? '');
$training_type = trim($_POST['training_type'] ?? '');
$training_language = trim($_POST['training_language'] ?? 'en');
$zoom_link = trim($_POST['zoom_link'] ?? '');
$zoom_link_full = trim($_POST['zoom_link_full'] ?? '');
$registration_scope = trim($_POST['registration_scope'] ?? '');
$trainer_contact_email = trim($_POST['trainer_contact_email'] ?? '');

$cost = isset($_POST["cost"]) ? intval($_POST["cost"]) : null;
$currency = trim($_POST["currency"] ?? "");
$display_cost = trim($_POST["display_cost"] ?? "Free / by Donation");
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
            training_location=?, training_summary=?, training_agenda=?,
            training_success=?, training_challenges=?, training_lessons_learned=?,
            youtube_result_video=?, moodle_url=?, ready_to_show=?, show_report=?, show_signup_count=?, featured_description=?, community_id=?,
            zoom_link=?, zoom_link_full=?, registration_scope=?, trainer_contact_email=?, cost=?, currency=?, display_cost=?
            WHERE training_id=?";
    $stmt = $gobrik_conn->prepare($sql);
    if (!$stmt) {
        echo json_encode(['success' => false, 'error' => 'Statement preparation failed.']);
        exit();
    }
    $stmt->bind_param("sssississiissssssssiiisissssissi",
        $training_title, $training_subtitle, $lead_trainer, $country_id, $training_date, $training_time_txt, $no_participants,
        $training_type, $training_language, $briks_made, $avg_brik_weight, $training_location,
        $training_summary, $training_agenda, $training_success, $training_challenges,
        $training_lessons_learned, $youtube_result_video, $moodle_url, $ready_to_show, $show_report, $show_signup_count,
        $featured_description, $community_id, $zoom_link, $zoom_link_full, $registration_scope, $trainer_contact_email, $cost, $currency, $display_cost,
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
            training_type, training_language, briks_made, avg_brik_weight, training_location, training_summary, training_agenda, training_success, training_challenges,
            training_lessons_learned, youtube_result_video, moodle_url, ready_to_show, show_report, show_signup_count, featured_description, community_id,
            zoom_link, zoom_link_full, registration_scope, trainer_contact_email, cost, currency, display_cost)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $gobrik_conn->prepare($sql);
    if (!$stmt) {
        echo json_encode(['success' => false, 'error' => 'Statement preparation failed.']);
        exit();
    }
    $stmt->bind_param("sssississiissssssssiiisissssiss",
        $training_title, $training_subtitle, $lead_trainer, $country_id, $training_date, $training_time_txt, $no_participants,
        $training_type, $training_language, $briks_made, $avg_brik_weight, $training_location,
        $training_summary, $training_agenda, $training_success, $training_challenges,
        $training_lessons_learned, $youtube_result_video, $moodle_url, $ready_to_show, $show_report, $show_signup_count, $featured_description, $community_id,
        $zoom_link, $zoom_link_full, $registration_scope, $trainer_contact_email, $cost, $currency, $display_cost
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
if ($new_training_id) {
    // Remove any existing trainer links for this training
    $del = $gobrik_conn->prepare("DELETE FROM tb_training_trainers WHERE training_id = ?");
    $del->bind_param("i", $new_training_id);
    $del->execute();
    $del->close();

    if (!empty($trainers)) {
        // Insert the currently selected trainers
        $ins = $gobrik_conn->prepare("INSERT INTO tb_training_trainers (training_id, ecobricker_id) VALUES (?, ?)");
        $ins->bind_param("ii", $new_training_id, $trainer_id);
        foreach ($trainers as $trainer_id) {
            $ins->execute();
        }
        $ins->close();
    }
}

echo json_encode(['success' => true, 'training_id' => $new_training_id]);

