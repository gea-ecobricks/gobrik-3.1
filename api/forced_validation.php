<?php
// forced_validation.php
// Purpose: Admin-forced validation of an ecobrick. Inserts into validations_tb,
// updates tb_ecobricks, and (if Authenticated) issues BRK via tb_brk_transaction
// and links its tran_id back to validations_tb.brk_trans_no.

// -----------------------------------------------------------------------------
// 0) INITIAL SETUP & DB CONNECT
// -----------------------------------------------------------------------------
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../gobrikconn_env.php'; // provides $gobrik_conn (mysqli)

// -----------------------------------------------------------------------------
// 1) VALIDATE & SANITIZE INPUT
//    Required: ecobrick_serial, ecobricker_id, admin_forced_status
//    Optional: validator_comments, validation_note
// -----------------------------------------------------------------------------
if (
    !isset($_POST['ecobrick_serial'], $_POST['ecobricker_id'], $_POST['admin_forced_status'])
) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Missing required parameters.']);
    exit;
}

$ecobrick_serial       = $gobrik_conn->real_escape_string($_POST['ecobrick_serial']);
$ecobricker_id         = intval($_POST['ecobricker_id']);
$admin_forced_status   = $gobrik_conn->real_escape_string($_POST['admin_forced_status']);
$validator_comments    = isset($_POST['validator_comments']) ? $gobrik_conn->real_escape_string($_POST['validator_comments']) : null;
$validation_note       = isset($_POST['validation_note']) ? $gobrik_conn->real_escape_string($_POST['validation_note']) : null;
$authenticator_version = '2.0'; // per request

$current_dt = date('Y-m-d H:i:s'); // for NOW() equivalents
$current_d  = date('Y-m-d');       // date only

// -----------------------------------------------------------------------------
// 2) FETCH ECOBRICK DETAILS NEEDED FOR THE VALIDATION RECORD & UPDATES
//    We need: weight_g (-> recorded_weight), etc.
// -----------------------------------------------------------------------------
$ecobrick_sql = "
    SELECT bottom_colour, weight_g, ecobrick_brk_amt
    FROM tb_ecobricks
    WHERE serial_no = ?
";
$ecobrick_stmt = $gobrik_conn->prepare($ecobrick_sql);
$ecobrick_stmt->bind_param('s', $ecobrick_serial);
$ecobrick_stmt->execute();
$ebr = $ecobrick_stmt->get_result();
if ($ebr->num_rows === 0) {
    echo json_encode(['ok' => false, 'error' => 'Ecobrick not found.']);
    $ecobrick_stmt->close();
    $gobrik_conn->close();
    exit;
}
$ecobrick_row     = $ebr->fetch_assoc();
$recorded_weight  = isset($ecobrick_row['weight_g']) ? floatval($ecobrick_row['weight_g']) : 0.0;
$preset_brk_value = round($recorded_weight / 1000, 2); // BRK value derived from grams → kg
$ecobrick_stmt->close();

// -----------------------------------------------------------------------------
// 3) COUNT EXISTING VALIDATIONS FOR THIS ECOBRICK
// -----------------------------------------------------------------------------
$count_sql = "SELECT COUNT(*) AS total FROM validations_tb WHERE recorded_serial = ?";
$count_stmt = $gobrik_conn->prepare($count_sql);
$count_stmt->bind_param('s', $ecobrick_serial);
$count_stmt->execute();
$count_res = $count_stmt->get_result()->fetch_assoc();
$validations_count = intval($count_res['total']) + 1;
$count_stmt->close();

// -----------------------------------------------------------------------------
// 4) LOOK UP VALIDATOR NAME FROM tb_ecobrickers (BY ecobricker_id)
// -----------------------------------------------------------------------------
$name_sql = "SELECT full_name FROM tb_ecobrickers WHERE ecobricker_id = ?";
$name_stmt = $gobrik_conn->prepare($name_sql);
$name_stmt->bind_param('i', $ecobricker_id);
$name_stmt->execute();
$name_res = $name_stmt->get_result();
$validator_name = ($name_res->num_rows > 0) ? $name_res->fetch_assoc()['full_name'] : 'Unknown Validator';
$name_stmt->close();

// -----------------------------------------------------------------------------
// 5) INSERT INTO validations_tb (MATCHING OFFICIAL FIELD NAMES EXACTLY)
//    Fields per spec:
//    validation_id (auto), authenticator_version, created(NOW),
//    recorded_serial, ecobricker_id, recorded_weight, preset_brk_value,
//    star_rating(NULL here), validations_count, validation_status,
//    validator_comments, validation_note, admin_forced_status,
//    revision_date(NULL here), brk_trans_no(NULL here initially), last_updated(AUTO)
// -----------------------------------------------------------------------------
$insert_sql = "
    INSERT INTO validations_tb
    (authenticator_version, created, recorded_serial, ecobricker_id, recorded_weight,
     preset_brk_value, validations_count, validation_status, validator_comments,
     validation_note, admin_forced_status, revision_date, brk_trans_no)
    VALUES (?, NOW(), ?, ?, ?, ?, ?, ?, ?, ?, ?, NULL, NULL)
";
$insert_stmt = $gobrik_conn->prepare($insert_sql);
$validation_status = 'forced';
$insert_stmt->bind_param(
    'ssiddisssss',
    $authenticator_version,   // s
    $ecobrick_serial,         // s
    $ecobricker_id,           // i
    $recorded_weight,         // d
    $preset_brk_value,        // d
    $validations_count,       // i
    $validation_status,       // s
    $validator_comments,      // s (nullable ok)
    $validation_note,         // s (nullable ok)
    $admin_forced_status      // s
);
if (!$insert_stmt->execute()) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Failed to insert validation record.', 'detail' => $insert_stmt->error]);
    $insert_stmt->close();
    $gobrik_conn->close();
    exit;
}
$validation_id = $gobrik_conn->insert_id; // we’ll use this if we create a BRK transaction
$insert_stmt->close();

// -----------------------------------------------------------------------------
// 6) UPDATE tb_ecobricks WITH VALIDATION EFFECTS
//    - validator_1 := full_name
//    - last_validation_ts := NOW()
//    - status := admin_forced_status
//    - IF Authenticated:
//        final_validation_score := 35
//        weight_authenticated_kg := recorded_weight / 1000
//        ecobrick_brk_amt := recorded_weight / 1000
// -----------------------------------------------------------------------------
$update_sql = "UPDATE tb_ecobricks
               SET validator_1 = ?, last_validation_ts = ?, status = ?";
$is_authenticated = (strtolower($admin_forced_status) === 'authenticated');
if ($is_authenticated) {
    $update_sql .= ",
        final_validation_score = 35,
        weight_authenticated_kg = ?,
        ecobrick_brk_amt = ?";
}
$update_sql .= " WHERE serial_no = ?";

$update_stmt = $gobrik_conn->prepare($update_sql);
if ($is_authenticated) {
    $weight_kg = $recorded_weight / 1000;
    $update_stmt->bind_param('sssdds', $validator_name, $current_dt, $admin_forced_status, $weight_kg, $weight_kg, $ecobrick_serial);
} else {
    $update_stmt->bind_param('sss', $validator_name, $current_dt, $admin_forced_status, $ecobrick_serial);
}
if (!$update_stmt->execute()) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Failed to update ecobrick record.', 'detail' => $update_stmt->error]);
    $update_stmt->close();
    $gobrik_conn->close();
    exit;
}
$update_stmt->close();

// -----------------------------------------------------------------------------
// 7) IF AUTHENTICATED → CREATE tb_brk_transaction RECORD
//    Set fields exactly as specified, and then write its tran_id into
//    validations_tb.brk_trans_no for the just-created validation.
// -----------------------------------------------------------------------------
$brk_trans_no = null;
if ($is_authenticated) {
    $tran_sql = "
        INSERT INTO tb_brk_transaction
        (tran_name, individual_amt, status, send_ts, sender_ecobricker, block_tran_type,
         block_amt, sender, receiver_or_receivers, sender_central_reserve, ecobrick_serial_no,
         tran_sender_note, send_dt, authenticator_version)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ";
    $tran_stmt = $gobrik_conn->prepare($tran_sql);

    $tran_name             = "Brikcoins generated by an admin authentication";
    $individual_amt        = $preset_brk_value;               // decimal(20,2)
    $status_txt            = "Completed";
    $send_ts               = $current_dt;                     // datetime
    $sender_ecobricker     = $validator_name;                 // for traceability
    $block_tran_type       = "Coins issued for authentication";
    $block_amt             = $preset_brk_value;               // float in table, we send as string/number
    $sender                = "Brikcoin Generator";
    $receiver_list         = "GoBrik Central Reserve";
    $sender_central        = "Brikcoin Generator";
    // Note: tb_brk_transaction.ecobrick_serial_no is mediumint(9). Cast serial to int if numeric, else 0.
    $ecobrick_serial_no    = intval($ecobrick_serial);
    $tran_sender_note      = "Beta testing";
    $send_dt               = $current_d;                      // date
    $auth_version          = "2.0";

    $tran_stmt->bind_param(
        'sdssssssssisss',
        $tran_name,
        $individual_amt,
        $status_txt,
        $send_ts,
        $sender_ecobricker,
        $block_tran_type,
        $block_amt,
        $sender,
        $receiver_list,
        $sender_central,
        $ecobrick_serial_no,
        $tran_sender_note,
        $send_dt,
        $auth_version
    );

    if (!$tran_stmt->execute()) {
        echo json_encode(['ok' => false, 'error' => 'Failed to insert BRK transaction.', 'detail' => $tran_stmt->error]);
        $tran_stmt->close();
        $gobrik_conn->close();
        exit;
    }

    $brk_trans_no = $gobrik_conn->insert_id; // this is tb_brk_transaction.tran_id (PK)
    $tran_stmt->close();

    // Link transaction back to validations_tb.brk_trans_no
    $link_sql = "UPDATE validations_tb SET brk_trans_no = ? WHERE validation_id = ?";
    $link_stmt = $gobrik_conn->prepare($link_sql);
    $link_stmt->bind_param('ii', $brk_trans_no, $validation_id);
    if (!$link_stmt->execute()) {
        echo json_encode(['ok' => false, 'error' => 'Failed to link BRK transaction to validation.', 'detail' => $link_stmt->error]);
        $link_stmt->close();
        $gobrik_conn->close();
        exit;
    }
    $link_stmt->close();
}

// -----------------------------------------------------------------------------
// 8) SUCCESS RESPONSE
// -----------------------------------------------------------------------------
echo json_encode([
    'ok' => true,
    'message' => 'Validation saved; ecobrick updated.',
    'validation_id' => $validation_id,
    'validator' => $validator_name,
    'status' => $admin_forced_status,
    'validation_count' => $validations_count,
    'brk_trans_no' => $brk_trans_no
]);

$gobrik_conn->close();
