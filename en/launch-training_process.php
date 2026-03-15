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
$moodle_url = trim($_POST['moodle_url'] ?? '');

$ready_to_show = normalizeCheckbox('ready_to_show');
$show_report = normalizeCheckbox('show_report');
$show_signup_count = normalizeCheckbox('show_signup_count');

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
$registration_scope = trim($_POST['registration_scope'] ?? 'anyone');
$trainer_contact_email = trim($_POST['trainer_contact_email'] ?? '');
$earthcal_event_url = trim($_POST['earthcal_event_url'] ?? '');

$no_participants = normalizeIntOrNull($_POST['no_participants'] ?? null, 0, 5000);
$briks_made = normalizeIntOrNull($_POST['briks_made'] ?? null, 0, 1000000);
$avg_brik_weight = normalizeIntOrNull($_POST['avg_brik_weight'] ?? null, 0, 1000000);

$country_id = normalizeIntOrNull($_POST['country_id'] ?? null, 1, 99999999);
$community_id = normalizeIntOrNull($_POST['community_id'] ?? null, 1, 99999999);

$trainers = isset($_POST['trainers']) && is_array($_POST['trainers'])
    ? array_values(array_unique(array_map('intval', $_POST['trainers'])))
    : [];

/* ---------------------------------------------------------
   3P / payment mode inputs
--------------------------------------------------------- */

// The form now uses a checkbox as the source of truth
$enable_3p = normalizeCheckbox('enable_3p');
$payment_mode = $enable_3p ? 'pledge_threshold' : 'free';

// Hard-coded now in the form
$base_currency = 'IDR';

// Hidden numeric fields posted by the form
$default_price_idr = normalizeIntOrNull($_POST['default_price_idr'] ?? null, 0, 2000000000);
$min_participants_required = normalizeIntOrNull($_POST['min_participants_required'] ?? null, 1, 5000);
$funding_goal_idr = normalizeIntOrNull($_POST['funding_goal_idr'] ?? null, 0, 2000000000);

$pledge_deadline = normalizeDatetimeLocal($_POST['pledge_deadline'] ?? null);
$payment_deadline = normalizeDatetimeLocal($_POST['payment_deadline'] ?? null);

// System fields still posted as hidden inputs
$threshold_status = trim($_POST['threshold_status'] ?? 'open');
if (!in_array($threshold_status, ['open', 'threshold_met', 'confirmed', 'failed', 'cancelled', 'completed'], true)) {
    $threshold_status = 'open';
}

$auto_confirm_threshold = normalizeCheckbox('auto_confirm_threshold');
$allow_overpledge = normalizeCheckbox('allow_overpledge');
$min_pledge_idr = normalizeIntOrNull($_POST['min_pledge_idr'] ?? 0, 0, 2000000000);
$max_pledge_idr = normalizeIntOrNull($_POST['max_pledge_idr'] ?? null, 0, 2000000000);

if ($min_pledge_idr === null) {
    $min_pledge_idr = 0;
}

// display_cost is currently commented out in the form, so derive it safely
$display_cost = trim($_POST['display_cost'] ?? '');

/* ---------------------------------------------------------
   Normalize by mode
--------------------------------------------------------- */

// Legacy compatibility fields
$cost = null;
$currency = null;

if ($payment_mode === 'free') {
    $default_price_idr = null;
    $min_participants_required = null;
    $funding_goal_idr = null;
    $pledge_deadline = null;
    $payment_deadline = null;
    $threshold_status = 'open';
    $auto_confirm_threshold = 1;
    $allow_overpledge = 1;
    $min_pledge_idr = 0;
    $max_pledge_idr = null;

    $display_cost = 'Free / Donation';

    $cost = null;
    $currency = null;
} else {
    // 3P mode
    if ($display_cost === '') {
        if ($min_pledge_idr > 0) {
            $display_cost = 'Pledge-based / from ' . number_format($min_pledge_idr) . ' IDR';
        } elseif ($default_price_idr !== null) {
            $display_cost = 'Pledge-based / suggested ' . number_format($default_price_idr) . ' IDR';
        } else {
            $display_cost = 'Pledge-based';
        }
    }

    $cost = $default_price_idr;
    $currency = $base_currency;
}

/* ---------------------------------------------------------
   Validate business rules
--------------------------------------------------------- */

if ($training_title === '') {
    echo json_encode(['success' => false, 'error' => 'Training title is required.']);
    exit();
}

if ($training_type === '') {
    echo json_encode(['success' => false, 'error' => 'Training type is required.']);
    exit();
}

if ($training_language === '') {
    $training_language = 'en';
}

if ($no_participants === null) {
    $no_participants = 0;
}

if ($payment_mode === 'pledge_threshold') {
    if ($default_price_idr === null || $default_price_idr < 0) {
        echo json_encode(['success' => false, 'error' => '3P courses require a valid suggested base price.']);
        exit();
    }

    if ($min_participants_required === null || $min_participants_required < 1) {
        echo json_encode(['success' => false, 'error' => '3P courses require a minimum registrants threshold.']);
        exit();
    }

    if ($funding_goal_idr === null || $funding_goal_idr <= 0) {
        echo json_encode(['success' => false, 'error' => '3P courses require a funding goal.']);
        exit();
    }

    if ($max_pledge_idr !== null && $max_pledge_idr < $min_pledge_idr) {
        echo json_encode(['success' => false, 'error' => 'Maximum pledge cannot be less than minimum pledge.']);
        exit();
    }
}

/* ---------------------------------------------------------
   Build lead trainer string
--------------------------------------------------------- */

$lead_trainer = buildLeadTrainerString($gobrik_conn, $trainers, $training_language);

/* ---------------------------------------------------------
   Validate community exists
--------------------------------------------------------- */

if ($community_id !== null) {
    $stmt = $buwana_conn->prepare("SELECT community_id FROM communities_tb WHERE community_id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $community_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if (!$result || $result->num_rows === 0) {
            $community_id = null;
        }
        $stmt->close();
    } else {
        $community_id = null;
    }
}

/* ---------------------------------------------------------
   Save training
--------------------------------------------------------- */

try {
    $trainingData = [
        'training_title' => $training_title,
        'training_subtitle' => $training_subtitle,
        'lead_trainer' => $lead_trainer,
        'country_id' => $country_id,
        'training_date' => $training_date,
        'training_time_txt' => $training_time_txt,
        'no_participants' => $no_participants,
        'training_type' => $training_type,
        'training_language' => $training_language,
        'briks_made' => $briks_made,
        'avg_brik_weight' => $avg_brik_weight,
        'training_location' => $training_location,
        'training_summary' => $training_summary,
        'training_agenda' => $training_agenda,
        'training_success' => $training_success,
        'training_challenges' => $training_challenges,
        'training_lessons_learned' => $training_lessons_learned,
        'youtube_result_video' => $youtube_result_video,
        'moodle_url' => $moodle_url,
        'ready_to_show' => $ready_to_show,
        'show_report' => $show_report,
        'show_signup_count' => $show_signup_count,
        'featured_description' => $featured_description,
        'community_id' => $community_id,
        'zoom_link' => $zoom_link,
        'zoom_link_full' => $zoom_link_full,
        'registration_scope' => $registration_scope,
        'trainer_contact_email' => $trainer_contact_email,
        'earthcal_event_url' => $earthcal_event_url,

        // legacy compatibility
        'Cost' => $cost,
        'Currency' => $currency,

        // current public display
        'display_cost' => $display_cost,

        // 3P fields
        'default_price_idr' => $default_price_idr,
        'base_currency' => $base_currency,
        'payment_mode' => $payment_mode,
        'min_participants_required' => $min_participants_required,
        'funding_goal_idr' => $funding_goal_idr,
        'pledge_deadline' => $pledge_deadline,
        'payment_deadline' => $payment_deadline,
        'threshold_status' => $threshold_status,
        'auto_confirm_threshold' => $auto_confirm_threshold,
        'allow_overpledge' => $allow_overpledge,
        'min_pledge_idr' => $min_pledge_idr,
        'max_pledge_idr' => $max_pledge_idr
    ];

    if ($editing) {
        $setParts = [];
        $params = [];

        foreach ($trainingData as $column => $value) {
            $setParts[] = "`$column` = ?";
            $params[] = $value;
        }

        $params[] = $training_id;

        $sql = "UPDATE tb_trainings SET " . implode(", ", $setParts) . " WHERE training_id = ?";
        $stmt = executePrepared($gobrik_conn, $sql, $params);
        $stmt->close();

        $new_training_id = $training_id;
    } else {
        $columns = array_keys($trainingData);
        $placeholders = implode(', ', array_fill(0, count($columns), '?'));
        $columnSql = '`' . implode('`, `', $columns) . '`';

        $sql = "INSERT INTO tb_trainings ($columnSql) VALUES ($placeholders)";
        $stmt = executePrepared($gobrik_conn, $sql, array_values($trainingData));
        $stmt->close();

        $new_training_id = (int)$gobrik_conn->insert_id;
    }

    /* ---------------------------------------------------------
       Feature photo URLs
    --------------------------------------------------------- */

    $fields = [];
    $values = [];

    for ($i = 1; $i <= 3; $i++) {
        $name = "feature_photo{$i}_main";
        if (!empty($_POST[$name] ?? '')) {
            $fields[] = "`$name` = ?";
            $values[] = trim($_POST[$name]);
        }
    }

    if (!empty($fields) && $new_training_id > 0) {
        $values[] = $new_training_id;
        $sql = "UPDATE tb_trainings SET " . implode(', ', $fields) . " WHERE training_id = ?";
        $up = executePrepared($gobrik_conn, $sql, $values);
        $up->close();
    }

    /* ---------------------------------------------------------
       Update trainers association
    --------------------------------------------------------- */

    if ($new_training_id > 0) {
        $del = executePrepared(
            $gobrik_conn,
            "DELETE FROM tb_training_trainers WHERE training_id = ?",
            [$new_training_id]
        );
        $del->close();

        if (!empty($trainers)) {
            $ins = $gobrik_conn->prepare("
                INSERT INTO tb_training_trainers (training_id, ecobricker_id)
                VALUES (?, ?)
            ");

            if (!$ins) {
                throw new Exception('Could not prepare trainer insert.');
            }

            foreach ($trainers as $trainer_id) {
                $trainer_id = (int)$trainer_id;
                $ins->bind_param("ii", $new_training_id, $trainer_id);
                if (!$ins->execute()) {
                    $error = $ins->error;
                    $ins->close();
                    throw new Exception('Failed to save trainer link: ' . $error);
                }
            }

            $ins->close();
        }
    }

    echo json_encode([
        'success' => true,
        'training_id' => $new_training_id,
        'payment_mode' => $payment_mode
    ]);

} catch (Throwable $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}