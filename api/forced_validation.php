<?php
// forced_validation.php
// Purpose: Handle admin-forced validations initiated from en/validate-1.php.

session_start();
header('Content-Type: application/json');

// -----------------------------------------------------------------------------
// 1. Session & Request Validation
// -----------------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Invalid request method.'], JSON_UNESCAPED_UNICODE);
    exit;
}

require_once '../gobrikconn_env.php';

if (!isset($_SESSION['buwana_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Authentication required.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$buwana_id = intval($_SESSION['buwana_id']);

// -----------------------------------------------------------------------------
// 2. Admin Verification & Context
// -----------------------------------------------------------------------------
$admin_query = $gobrik_conn->prepare('SELECT ecobricker_id, full_name, user_roles FROM tb_ecobrickers WHERE buwana_id = ?');
if (!$admin_query) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Unable to prepare admin lookup.'], JSON_UNESCAPED_UNICODE);
    exit;
}
$admin_query->bind_param('i', $buwana_id);
$admin_query->execute();
$admin_query->bind_result($admin_ecobricker_id, $admin_name, $admin_roles);
if (!$admin_query->fetch()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Admin account not found.'], JSON_UNESCAPED_UNICODE);
    $admin_query->close();
    exit;
}
$admin_query->close();

if (stripos($admin_roles ?? '', 'admin') === false) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Admin privileges are required.'], JSON_UNESCAPED_UNICODE);
    exit;
}

// -----------------------------------------------------------------------------
// 3. Payload Extraction
// -----------------------------------------------------------------------------
$ecobrick_id    = isset($_POST['ecobrick_id']) ? intval($_POST['ecobrick_id']) : 0;
$status_input   = isset($_POST['status']) ? trim($_POST['status']) : '';
$star_rating    = isset($_POST['star_rating']) ? intval($_POST['star_rating']) : 0;
$feedback       = isset($_POST['validator_feedback']) ? trim($_POST['validator_feedback']) : '';

// -----------------------------------------------------------------------------
// 4. Input Validation
// -----------------------------------------------------------------------------
if ($ecobrick_id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing ecobrick identifier.'], JSON_UNESCAPED_UNICODE);
    exit;
}
if ($status_input === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'A status selection is required.'], JSON_UNESCAPED_UNICODE);
    exit;
}
if ($star_rating < 1 || $star_rating > 5) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Please provide a star rating between 1 and 5.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$status_normalized = strtolower($status_input);
if (!in_array($status_normalized, ['authenticated', 'rejected'], true)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid status option supplied.'], JSON_UNESCAPED_UNICODE);
    exit;
}
if ($status_normalized === 'rejected' && $feedback === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Please leave feedback when rejecting an ecobrick.'], JSON_UNESCAPED_UNICODE);
    exit;
}

// -----------------------------------------------------------------------------
// 5. Ecobrick Lookup & Preparation
// -----------------------------------------------------------------------------
$ecobrick_stmt = $gobrik_conn->prepare('SELECT serial_no, weight_g, maker_id, ecobricker_maker, ecobrick_brk_amt FROM tb_ecobricks WHERE ecobrick_unique_id = ?');
if (!$ecobrick_stmt) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Failed to prepare ecobrick lookup.'], JSON_UNESCAPED_UNICODE);
    exit;
}
$ecobrick_stmt->bind_param('i', $ecobrick_id);
$ecobrick_stmt->execute();
$ecobrick_stmt->bind_result($serial_no, $weight_g, $maker_id, $ecobricker_maker, $existing_brk_amt);
if (!$ecobrick_stmt->fetch()) {
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'Ecobrick not found.'], JSON_UNESCAPED_UNICODE);
    $ecobrick_stmt->close();
    exit;
}
$ecobrick_stmt->close();

$maker_id = $maker_id !== null ? trim((string) $maker_id) : '';
$existing_brk_amt = $existing_brk_amt !== null ? (float) $existing_brk_amt : 0.0;

$maker_ecobricker_id = null;
if ($maker_id !== '') {
    $maker_lookup = $gobrik_conn->prepare('SELECT ecobricker_id FROM tb_ecobrickers WHERE maker_id = ? LIMIT 1');
    if ($maker_lookup) {
        $maker_lookup->bind_param('s', $maker_id);
        $maker_lookup->execute();
        $maker_lookup->bind_result($matched_ecobricker_id);
        if ($maker_lookup->fetch()) {
            $maker_ecobricker_id = (int) $matched_ecobricker_id;
        }
        $maker_lookup->close();
    }
}
if ($maker_ecobricker_id === null && ctype_digit($maker_id)) {
    $maker_ecobricker_id = (int) $maker_id;
}

if ($serial_no === null || $serial_no === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Ecobrick serial is missing.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$recorded_weight = $weight_g !== null ? (float) $weight_g : 0.0;
$preset_brk_value = round($recorded_weight / 1000, 2);

$count_stmt = $gobrik_conn->prepare('SELECT COUNT(*) AS total FROM validations_tb WHERE recorded_serial = ?');
if (!$count_stmt) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Failed to prepare validation count.'], JSON_UNESCAPED_UNICODE);
    exit;
}
$count_stmt->bind_param('s', $serial_no);
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$validations_count = 1;
if ($count_result && ($row = $count_result->fetch_assoc())) {
    $validations_count = intval($row['total']) + 1;
}
$count_stmt->close();

$authenticator_version = '2.0';
$validation_status = 'forced';
$validation_note = 'Manual admin validation';

$status_label = ucfirst($status_normalized);

// -----------------------------------------------------------------------------
// 6. Record Validation Event
// -----------------------------------------------------------------------------
$insert_sql = 'INSERT INTO validations_tb (authenticator_version, created, recorded_serial, ecobricker_id, recorded_weight, preset_brk_value, star_rating, validations_count, validation_status, validator_comments, validation_note, admin_forced_status, revision_date, brk_trans_no) VALUES (?, NOW(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NULL, NULL)';
$insert_stmt = $gobrik_conn->prepare($insert_sql);
if (!$insert_stmt) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Failed to prepare validation insert.'], JSON_UNESCAPED_UNICODE);
    exit;
}
$insert_stmt->bind_param(
    'ssiddiissss',
    $authenticator_version,
    $serial_no,
    $admin_ecobricker_id,
    $recorded_weight,
    $preset_brk_value,
    $star_rating,
    $validations_count,
    $validation_status,
    $feedback,
    $validation_note,
    $status_normalized
);
if (!$insert_stmt->execute()) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Failed to save the validation record.'], JSON_UNESCAPED_UNICODE);
    $insert_stmt->close();
    exit;
}
$validation_id = $gobrik_conn->insert_id;
$insert_stmt->close();

$now_ts = date('Y-m-d H:i:s');
$today_d = date('Y-m-d');

// -----------------------------------------------------------------------------
// 7. Update Ecobrick Status & Metrics
// -----------------------------------------------------------------------------
if ($status_normalized === 'authenticated') {
    $update_sql = 'UPDATE tb_ecobricks SET validator_1 = ?, last_validation_ts = ?, status = ?, final_validation_score = 35, weight_authenticated_kg = ?, ecobrick_brk_amt = ? WHERE ecobrick_unique_id = ?';
    $weight_kg = $recorded_weight / 1000;
    $update_stmt = $gobrik_conn->prepare($update_sql);
    if (!$update_stmt) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Failed to prepare ecobrick update.'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    $update_stmt->bind_param('sssddi', $admin_name, $now_ts, $status_label, $weight_kg, $weight_kg, $ecobrick_id);
} else {
    $update_sql = 'UPDATE tb_ecobricks SET validator_1 = ?, last_validation_ts = ?, status = ?, final_validation_score = NULL, weight_authenticated_kg = NULL, ecobrick_brk_amt = 0 WHERE ecobrick_unique_id = ?';
    $update_stmt = $gobrik_conn->prepare($update_sql);
    if (!$update_stmt) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Failed to prepare ecobrick update.'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    $update_stmt->bind_param('sssi', $admin_name, $now_ts, $status_label, $ecobrick_id);
}
if (!$update_stmt->execute()) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Failed to update ecobrick status.'], JSON_UNESCAPED_UNICODE);
    $update_stmt->close();
    exit;
}
$update_stmt->close();

$brk_trans_no = null;
$brk_tran_legacy_id = null;
if ($status_normalized === 'authenticated') {
    // -------------------------------------------------------------------------
    // 8. Create Brikcoin Transaction for Authenticated Ecobricks
    // -------------------------------------------------------------------------
    $next_tran_id = 1;
    $tran_id_result = $gobrik_conn->query('SELECT MAX(tran_id) AS max_tran FROM tb_brk_transaction');
    if ($tran_id_result && ($tran_id_row = $tran_id_result->fetch_assoc())) {
        $max_tran = isset($tran_id_row['max_tran']) ? (int) $tran_id_row['max_tran'] : 0;
        $next_tran_id = $max_tran + 1;
    }
    if ($tran_id_result) {
        $tran_id_result->free();
    }

    $tran_sql = 'INSERT INTO tb_brk_transaction (tran_id, tran_name, individual_amt, status, send_ts, sender_ecobricker, block_tran_type, block_amt, sender, receiver_or_receivers, receiver_central_reserve, sender_central_reserve, ecobrick_serial_no, tran_sender_note, send_dt, authenticator_version) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
    $tran_stmt = $gobrik_conn->prepare($tran_sql);
    if (!$tran_stmt) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Failed to prepare brikcoin transaction.'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    $tran_name = 'BRK from admin validation';
    $status_text = 'Completed';
    $block_type = 'Coins issued for authentication';
    $sender_ecobricker = null;
    $sender = 'Brikcoin Generator';
    $receiver = 'GoBrik Central Reserve';
    $receiver_central_reserve = 'Central Reserve';
    $sender_central = 'Brikcoin Generator';
    $ecobrick_serial_no = intval($serial_no);
    $note = 'Beta testing';
    $auth_version = '2.0.1';

    $tran_stmt->bind_param(
        'isdssssdssssisss',
        $next_tran_id,
        $tran_name,
        $preset_brk_value,
        $status_text,
        $now_ts,
        $sender_ecobricker,
        $block_type,
        $preset_brk_value,
        $sender,
        $receiver,
        $receiver_central_reserve,
        $sender_central,
        $ecobrick_serial_no,
        $note,
        $today_d,
        $auth_version
    );

    if (!$tran_stmt->execute()) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Failed to create brikcoin transaction.'], JSON_UNESCAPED_UNICODE);
        $tran_stmt->close();
        exit;
    }
    $brk_trans_no = $gobrik_conn->insert_id;
    $brk_tran_legacy_id = $next_tran_id;
    $tran_stmt->close();

    $link_stmt = $gobrik_conn->prepare('UPDATE validations_tb SET brk_trans_no = ? WHERE validation_id = ?');
    if ($link_stmt) {
        $link_stmt->bind_param('ii', $brk_trans_no, $validation_id);
        $link_stmt->execute();
        $link_stmt->close();
    }
}

// -----------------------------------------------------------------------------
// 9. Response Payload
// -----------------------------------------------------------------------------
echo json_encode([
    'success' => true,
    'message' => 'Validation saved successfully.',
    'status' => $status_normalized,
    'status_label' => $status_label,
    'validation_id' => $validation_id,
    'brk_trans_no' => $brk_trans_no,
    'brk_legacy_tran_id' => $brk_tran_legacy_id,
    'serial_no' => $serial_no,
    'maker_id' => $maker_id,
    'maker_ecobricker_id' => $maker_ecobricker_id,
    'validator_name' => $admin_name,
    'authenticator_version' => $authenticator_version,
    'validator_comments' => $feedback,
    'validation_note' => $validation_note,
    'brk_value' => $status_normalized === 'authenticated' ? $preset_brk_value : 0.0,
    'existing_brk_amt' => $existing_brk_amt
], JSON_UNESCAPED_UNICODE);

$gobrik_conn->close();
exit;
