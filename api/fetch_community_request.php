<?php
require_once '../auth/session_start.php';
require_once '../earthenAuth_helper.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['ok' => false, 'error' => 'Not logged in']);
    exit();
}

$training_id = isset($_GET['training_id']) ? intval($_GET['training_id']) : 0;
if ($training_id <= 0) {
    echo json_encode(['ok' => false, 'error' => 'Invalid training_id']);
    exit();
}

require_once '../gobrikconn_env.php';
require_once '../buwanaconn_env.php';

// Fetch training details
$sql = "SELECT t.training_id, t.training_title, t.training_subtitle, t.training_date,
               t.training_time_txt, t.training_language, t.training_location,
               t.training_type, t.lead_trainer, t.trainer_contact_email,
               t.funding_goal_idr, t.min_participants_required, t.default_price_idr,
               t.threshold_status, t.community_id
        FROM tb_trainings t
        WHERE t.training_id = ? AND t.threshold_status = 'open_request'";
$stmt = $gobrik_conn->prepare($sql);
if (!$stmt) {
    echo json_encode(['ok' => false, 'error' => 'DB error']);
    exit();
}
$stmt->bind_param("i", $training_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    echo json_encode(['ok' => false, 'error' => 'Training not found or not an open request']);
    exit();
}
$training = $result->fetch_assoc();
$stmt->close();

// Fetch the requester (first reserved registration for this training)
$requester_name = '';
$requester_email = '';
$requester_buwana_id = null;
$stmt_reg = $gobrik_conn->prepare(
    "SELECT r.buwana_id, e.full_name, e.email_addr
     FROM training_registrations_tb r
     LEFT JOIN tb_ecobrickers e ON e.buwana_id = r.buwana_id
     WHERE r.training_id = ? AND r.status = 'reserved'
     ORDER BY r.registration_id ASC
     LIMIT 1"
);
if ($stmt_reg) {
    $stmt_reg->bind_param("i", $training_id);
    $stmt_reg->execute();
    $stmt_reg->bind_result($requester_buwana_id, $requester_name, $requester_email);
    $stmt_reg->fetch();
    $stmt_reg->close();
}

// Fetch community name
$community_name = '';
if (!empty($training['community_id'])) {
    $stmt_com = $buwana_conn->prepare("SELECT com_name FROM communities_tb WHERE community_id = ?");
    if ($stmt_com) {
        $stmt_com->bind_param("i", $training['community_id']);
        $stmt_com->execute();
        $stmt_com->bind_result($community_name);
        $stmt_com->fetch();
        $stmt_com->close();
    }
}

// Format date
$date_display = !empty($training['training_date'])
    ? date('F j, Y \a\t g:i A', strtotime($training['training_date']))
    : '—';

echo json_encode([
    'ok' => true,
    'training_id'       => (int)$training['training_id'],
    'training_title'    => $training['training_title'],
    'training_date'     => $date_display,
    'time_txt'          => $training['training_time_txt'] ?? '',
    'language'          => strtoupper($training['training_language'] ?? ''),
    'location'          => $training['training_location'] ?? '',
    'training_type'     => $training['training_type'] ?? '',
    'lead_trainer'      => $training['lead_trainer'] ?? '',
    'trainer_email'     => $training['trainer_contact_email'] ?? '',
    'funding_goal_idr'  => (int)$training['funding_goal_idr'],
    'min_participants'  => (int)$training['min_participants_required'],
    'community_name'    => $community_name,
    'requester_name'    => $requester_name ?: 'Unknown',
    'requester_email'   => $requester_email ?: '',
    'requester_buwana_id' => $requester_buwana_id,
]);
