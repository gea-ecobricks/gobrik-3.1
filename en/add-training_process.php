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
$editing = ($training_id > 0);

$training_title = trim($_POST['training_title'] ?? '');
$lead_trainer = trim($_POST['lead_trainer'] ?? '');
$training_date = trim($_POST['training_date'] ?? '');
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
$training_summary = isset($_POST['training_summary']) ? trim($_POST['training_summary']) : null;
$training_agenda = isset($_POST['training_agenda']) ? trim($_POST['training_agenda']) : null;
$training_success = trim($_POST['training_success'] ?? '');
$training_challenges = trim($_POST['training_challenges'] ?? '');
$training_lessons_learned = trim($_POST['training_lessons_learned'] ?? '');
$training_location = trim($_POST['training_location'] ?? '');
$training_type = trim($_POST['training_type'] ?? '');

$no_participants = filter_var($_POST['no_participants'], FILTER_VALIDATE_INT) ?? 0;
$briks_made = filter_var($_POST['briks_made'], FILTER_VALIDATE_INT) ?? 0;
$avg_brik_weight = filter_var($_POST['avg_brik_weight'], FILTER_VALIDATE_INT) ?? 0;
$country_id = filter_var($_POST['country_id'], FILTER_VALIDATE_INT) ?? null;
$community_id = filter_var($_POST['community_id'], FILTER_VALIDATE_INT) ?? null;

if ($community_id !== null) {
    $stmt = $buwana_conn->prepare("SELECT community_id FROM communities_tb WHERE community_id = ?");
    $stmt->bind_param("i", $community_id);
    $stmt->execute();
    if ($stmt->get_result()->num_rows === 0) {
        $community_id = null;
    }
    $stmt->close();
}

// Preserve existing fields when not provided in the form
if ($editing) {
    $stmt = $gobrik_conn->prepare("SELECT training_summary, training_agenda FROM tb_trainings WHERE training_id = ?");
    $stmt->bind_param("i", $training_id);
    $stmt->execute();
    $stmt->bind_result($existing_summary, $existing_agenda);
    $stmt->fetch();
    $stmt->close();

    if ($training_summary === null) {
        $training_summary = $existing_summary;
    }
    if ($training_agenda === null) {
        $training_agenda = $existing_agenda;
    }
}

if ($editing) {
    $sql = "UPDATE tb_trainings SET
            training_title=?, lead_trainer=?, country_id=?, training_date=?,
            no_participants=?, training_type=?, briks_made=?, avg_brik_weight=?,
            location_lat=?, location_long=?, training_location=?, training_summary=?, training_agenda=?,
            training_success=?, training_challenges=?, training_lessons_learned=?,
            youtube_result_video=?, moodle_url=?, ready_to_show=?, featured_description=?, community_id=?
            WHERE training_id=?";
    $stmt = $gobrik_conn->prepare($sql);
    $stmt->bind_param("ssisisiiddssssssssisii",
        $training_title, $lead_trainer, $country_id, $training_date, $no_participants,
        $training_type, $briks_made, $avg_brik_weight, $latitude, $longitude, $training_location,
        $training_summary, $training_agenda, $training_success, $training_challenges,
        $training_lessons_learned, $youtube_result_video, $moodle_url, $ready_to_show,
        $featured_description, $community_id, $training_id
    );
    if ($stmt->execute()) {
        $new_training_id = $training_id;
    } else {
        echo json_encode(['success' => false, 'error' => 'Update failed.']);
        exit();
    }
} else {
    $sql = "INSERT INTO tb_trainings
            (training_title, lead_trainer, country_id, training_date, no_participants,
            training_type, briks_made, avg_brik_weight, location_lat, location_long,
            training_location, training_summary, training_agenda, training_success, training_challenges,
            training_lessons_learned, youtube_result_video, moodle_url, ready_to_show, featured_description, community_id)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $gobrik_conn->prepare($sql);
    $stmt->bind_param("ssisisiiddssssssssisi",
        $training_title, $lead_trainer, $country_id, $training_date, $no_participants,
        $training_type, $briks_made, $avg_brik_weight, $latitude, $longitude, $training_location,
        $training_summary, $training_agenda, $training_success, $training_challenges,
        $training_lessons_learned, $youtube_result_video, $moodle_url, $ready_to_show, $featured_description, $community_id
    );
    if ($stmt->execute()) {
        $new_training_id = $gobrik_conn->insert_id;
    } else {
        echo json_encode(['success' => false, 'error' => 'Insert failed.']);
        exit();
    }
}

echo json_encode(['success' => true, 'training_id' => $new_training_id]);

