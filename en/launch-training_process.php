<?php
require_once '../earthenAuth_helper.php';
require_once '../auth/session_start.php';

header('Content-Type: application/json; charset=UTF-8');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
    exit();
}

$buwana_id = $_SESSION['buwana_id'];

require_once '../gobrikconn_env.php';
require_once '../buwanaconn_env.php';
include '../scripts/photo-functions.php';


/* ---------------------------------------------------------
   Helpers
--------------------------------------------------------- */

function normalizeDatetimeLocal(?string $value): ?string {
    $value = trim((string)$value);
    if ($value === '') {
        return null;
    }

    if (strlen($value) === 10) {
        $value .= 'T12:00';
    }

    $ts = strtotime($value);
    if ($ts === false) {
        return null;
    }

    return date("Y-m-d H:i:s", $ts);
}

function normalizeIntOrNull($value, int $min = null, int $max = null): ?int {
    if ($value === null || $value === '') {
        return null;
    }

    if (!is_numeric($value)) {
        return null;
    }

    $intVal = (int)$value;

    if ($min !== null && $intVal < $min) {
        return null;
    }
    if ($max !== null && $intVal > $max) {
        return null;
    }

    return $intVal;
}

function normalizeCheckbox(string $key): int {
    return isset($_POST[$key]) ? 1 : 0;
}

function normalizeEnum(string $value, array $allowed, string $fallback): string {
    return in_array($value, $allowed, true) ? $value : $fallback;
}

function refValues(array &$arr): array {
    $refs = [];
    foreach ($arr as $key => &$value) {
        $refs[$key] = &$value;
    }
    return $refs;
}

function executePrepared(mysqli $conn, string $sql, array $params = []): mysqli_stmt {
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception('Statement preparation failed: ' . $conn->error);
    }

    if (!empty($params)) {
        $types = str_repeat('s', count($params));
        $bindParams = array_merge([$types], $params);
        call_user_func_array([$stmt, 'bind_param'], refValues($bindParams));
    }

    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        throw new Exception('Statement execution failed: ' . $error);
    }

    return $stmt;
}

function buildLeadTrainerString(mysqli $conn, array $trainerIds, string $language): string {
    if (empty($trainerIds)) {
        return '';
    }

    $names = [];
    $stmt = $conn->prepare("SELECT full_name FROM tb_ecobrickers WHERE ecobricker_id = ?");

    if (!$stmt) {
        return '';
    }

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
    if ($count === 0) return '';
    if ($count === 1) return $names[0];
    if ($count === 2) return $names[0] . ' ' . $andWord . ' ' . $names[1];

    $last = array_pop($names);
    return implode(', ', $names) . ' ' . $andWord . ' ' . $last;
}


/* ---------------------------------------------------------
   Identify edit/create
--------------------------------------------------------- */

$training_id = isset($_POST['training_id']) ? (int)$_POST['training_id'] : 0;
if ($training_id <= 0 && isset($_GET['id'])) {
    $training_id = (int)$_GET['id'];
}
$editing = ($training_id > 0);


/* ---------------------------------------------------------
   Load existing long-text fields when editing
--------------------------------------------------------- */

$existing_summary = '';
$existing_success = '';
$existing_challenges = '';
$existing_lessons = '';

if ($editing) {
    $stmt_existing = $gobrik_conn->prepare("
        SELECT training_summary, training_success, training_challenges, training_lessons_learned
        FROM tb_trainings
        WHERE training_id = ?
    ");

    if (!$stmt_existing) {
        echo json_encode(['success' => false, 'error' => 'Could not load existing training.']);
        exit();
    }

    $stmt_existing->bind_param("i", $training_id);
    $stmt_existing->execute();
    $stmt_existing->bind_result(
        $existing_summary,
        $existing_success,
        $existing_challenges,
        $existing_lessons
    );
    $stmt_existing->fetch();
    $stmt_existing->close();
}


/* ---------------------------------------------------------
   Collect and normalize inputs
--------------------------------------------------------- */

$training_title = trim($_POST['training_title'] ?? '');
$training_subtitle = trim($_POST['training_subtitle'] ?? '');
$training_date = normalizeDatetimeLocal($_POST['training_date'] ?? null);
$training_time_txt = trim($_POST['training_time_txt'] ?? '');

$youtube_result_video = trim($_POST['youtube_result_video'] ?? '');
$moodle