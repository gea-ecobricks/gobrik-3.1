<?php
require_once '../earthenAuth_helper.php';
require_once '../auth/session_start.php';

if (!isLoggedIn()) {
    header('Location: /en/login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /en/dashboard.php');
    exit();
}

require_once '../gobrikconn_env.php';

$project_id = isset($_POST['project_id']) ? (int)$_POST['project_id'] : null;
if (empty($project_id)) {
    header('Location: /en/dashboard.php');
    exit();
}

$buwana_id = (int)($_SESSION['buwana_id'] ?? 0);

// ── Look up the logged-in user's ecobricker_id ────────────────────────────────
$stmt_user = $gobrik_conn->prepare("SELECT ecobricker_id FROM tb_ecobrickers WHERE buwana_id = ?");
if (!$stmt_user) {
    error_log('edit_project: prepare ecobricker lookup failed: ' . $gobrik_conn->error);
    header('Location: /en/dashboard.php?error=db');
    exit();
}
$stmt_user->bind_param("i", $buwana_id);
$stmt_user->execute();
$stmt_user->bind_result($ecobricker_id);
$stmt_user->fetch();
$stmt_user->close();

if (empty($ecobricker_id)) {
    header('Location: /en/dashboard.php?error=no-account');
    exit();
}

// ── Verify user is an admin of this project ───────────────────────────────────
$stmt_auth = $gobrik_conn->prepare("SELECT project_admins FROM tb_projects WHERE project_id = ?");
if (!$stmt_auth) {
    error_log('edit_project: prepare auth check failed: ' . $gobrik_conn->error);
    header('Location: /en/dashboard.php?error=db');
    exit();
}
$stmt_auth->bind_param("i", $project_id);
$stmt_auth->execute();
$stmt_auth->bind_result($project_admins_raw);
$stmt_auth->fetch();
$stmt_auth->close();

if (empty($project_admins_raw)) {
    header('Location: /en/dashboard.php?error=not-found');
    exit();
}

$admin_ids = array_map('trim', explode(',', $project_admins_raw));
if (!in_array((string)$ecobricker_id, $admin_ids)) {
    header('Location: /en/dashboard.php?error=unauthorized');
    exit();
}

// ── Handle delete action ──────────────────────────────────────────────────────
if (isset($_POST['action']) && $_POST['action'] === 'delete_project') {
    include '../scripts/photo-functions.php';
    $deleteResult = deleteProject($project_id, $gobrik_conn);
    if ($deleteResult === true) {
        $gobrik_conn->close();
        header('Location: /en/dashboard.php?deleted=1');
    } else {
        error_log('edit_project: delete failed: ' . $deleteResult);
        header('Location: /en/edit-project.php?id=' . $project_id . '&error=delete');
    }
    exit();
}

// ── Collect and sanitize text fields ─────────────────────────────────────────
$project_name        = trim($_POST['project_name']        ?? '');
$description_short   = trim($_POST['description_short']   ?? '');
$description_long    = trim($_POST['description_long']    ?? '');
$location_full       = trim($_POST['location_address']    ?? '');
$project_type        = trim($_POST['project_type']        ?? '');
$construction_type   = trim($_POST['construction_type']   ?? '');
$project_sort        = trim($_POST['project_sort']        ?? '');
$community           = trim($_POST['community']           ?? '');
$connected_ecobricks = trim($_POST['connected_ecobricks'] ?? '');
$start_dt            = trim($_POST['start_dt']            ?? '');
$end_dt              = trim($_POST['end_dt']              ?? '');
$briks_used          = (int)($_POST['briks_used']         ?? 0);
$est_avg_brik_weight = (int)($_POST['est_avg_brik_weight'] ?? 0);
$latitude            = (isset($_POST['latitude'])  && $_POST['latitude']  !== '') ? (float)$_POST['latitude']  : null;
$longitude           = (isset($_POST['longitude']) && $_POST['longitude'] !== '') ? (float)$_POST['longitude'] : null;

// Build project_admins from tag-based admin_ids[] inputs
$admin_post_ids = $_POST['admin_ids'] ?? [];
if (!empty($admin_post_ids)) {
    $project_admins = implode(',', array_map('intval', $admin_post_ids));
} else {
    $project_admins = $project_admins_raw; // keep existing if none submitted
}

$project_end    = !empty($end_dt) ? $end_dt : $start_dt;
$briks_required = max(0, $briks_used);

// ── Update text fields ────────────────────────────────────────────────────────
$update_sql = "UPDATE tb_projects SET
    project_name = ?, description_short = ?, description_long = ?,
    start_dt = ?, end_dt = ?, project_end = ?,
    briks_required = ?, briks_used = ?, est_avg_brik_weight = ?,
    project_type = ?, construction_type = ?, project_sort = ?,
    community = ?, project_admins = ?, location_full = ?,
    location_lat = ?, location_long = ?,
    connected_ecobricks = ?
WHERE project_id = ?";

$stmt = $gobrik_conn->prepare($update_sql);
if (!$stmt) {
    error_log('edit_project: prepare update failed: ' . $gobrik_conn->error);
    header('Location: /en/edit-project.php?id=' . $project_id . '&error=db');
    exit();
}
$stmt->bind_param(
    'ssssssiiissssssddsi',
    $project_name, $description_short, $description_long,
    $start_dt, $end_dt, $project_end,
    $briks_required, $briks_used, $est_avg_brik_weight,
    $project_type, $construction_type, $project_sort,
    $community, $project_admins, $location_full,
    $latitude, $longitude,
    $connected_ecobricks,
    $project_id
);
if (!$stmt->execute()) {
    error_log('edit_project: update execute failed: ' . $stmt->error);
    $stmt->close();
    header('Location: /en/edit-project.php?id=' . $project_id . '&error=save');
    exit();
}
$stmt->close();

// Update derived est_total_weight
$est_total_weight = ($briks_used * $est_avg_brik_weight) / 1000.0;
$wt_stmt = $gobrik_conn->prepare("UPDATE tb_projects SET est_total_weight = ? WHERE project_id = ?");
if ($wt_stmt) {
    $wt_stmt->bind_param('di', $est_total_weight, $project_id);
    $wt_stmt->execute();
    $wt_stmt->close();
}

// ── Process photo uploads ─────────────────────────────────────────────────────
include '../scripts/photo-functions.php';

$upload_dir    = '../projects/photos/';
$thumbnail_dir = '../projects/tmbs/';
$photo_fields  = [];
$photo_values  = [];
$photo_types   = '';
$photo_error   = '';

// Fetch existing photo DB values so we can read and increment their ?v= numbers
$existing_photos = [];
$stmt_photos = $gobrik_conn->prepare(
    "SELECT photo1_main, photo1_tmb, photo2_main, photo2_tmb,
            photo3_main, photo3_tmb, photo4_main, photo4_tmb,
            photo5_main, photo5_tmb, photo6_main, photo6_tmb
     FROM tb_projects WHERE project_id = ?"
);
if ($stmt_photos) {
    $stmt_photos->bind_param('i', $project_id);
    $stmt_photos->execute();
    $stmt_photos->bind_result(
        $ep1m, $ep1t, $ep2m, $ep2t,
        $ep3m, $ep3t, $ep4m, $ep4t,
        $ep5m, $ep5t, $ep6m, $ep6t
    );
    $stmt_photos->fetch();
    $stmt_photos->close();
    $existing_photos = [
        1 => ['main' => $ep1m, 'tmb' => $ep1t],
        2 => ['main' => $ep2m, 'tmb' => $ep2t],
        3 => ['main' => $ep3m, 'tmb' => $ep3t],
        4 => ['main' => $ep4m, 'tmb' => $ep4t],
        5 => ['main' => $ep5m, 'tmb' => $ep5t],
        6 => ['main' => $ep6m, 'tmb' => $ep6t],
    ];
}

// Returns the next cache-bust version for a DB-stored path.
// e.g. "../projects/photos/project-5-1.webp"     → 2
//      "../projects/photos/project-5-1.webp?v=3"  → 4
function nextPhotoVersion(string $existing_db_path): int {
    if (preg_match('/\?v=(\d+)$/', $existing_db_path, $m)) {
        return (int)$m[1] + 1;
    }
    return 2; // first edit of an unversioned original
}

for ($i = 1; $i <= 6; $i++) {
    $input_name = "photo{$i}_main";
    if (!isset($_FILES[$input_name]) || $_FILES[$input_name]['error'] !== UPLOAD_ERR_OK) {
        continue; // no file uploaded for this slot — keep existing
    }

    $filename    = 'project-' . $project_id . '-' . $i . '.webp';
    $target_path = $upload_dir . $filename;
    $tmb_path    = $thumbnail_dir . $filename;

    if (resizeAndConvertToWebP($_FILES[$input_name]['tmp_name'], $target_path, 1000, 88)) {
        createThumbnail($target_path, $tmb_path, 250, 250, 77);

        // Increment version based on whatever is currently stored in the DB
        $v           = nextPhotoVersion($existing_photos[$i]['main'] ?? '');
        $main_db_val = $target_path . '?v=' . $v;
        $tmb_db_val  = $tmb_path   . '?v=' . $v;

        array_push($photo_fields, "photo{$i}_main", "photo{$i}_tmb");
        array_push($photo_values, $main_db_val, $tmb_db_val);
        $photo_types .= 'ss';
    } else {
        $photo_error .= "Error processing photo {$i}. ";
        error_log('edit_project: photo processing failed for slot ' . $i);
    }
}

if (!empty($photo_fields) && empty($photo_error)) {
    // Also mark the project as ready to show and update its timestamp
    array_push($photo_fields, 'ready_to_show', 'logged_ts');
    array_push($photo_values, 1, date('Y-m-d H:i:s'));
    $photo_types .= 'is';

    $fields_clause = implode(', ', array_map(fn($f) => "{$f} = ?", $photo_fields));
    $photo_sql     = "UPDATE tb_projects SET {$fields_clause} WHERE project_id = ?";
    $photo_values[] = $project_id;
    $photo_types   .= 'i';

    $photo_stmt = $gobrik_conn->prepare($photo_sql);
    if ($photo_stmt) {
        $photo_stmt->bind_param($photo_types, ...$photo_values);
        if (!$photo_stmt->execute()) {
            error_log('edit_project: photo DB update failed: ' . $photo_stmt->error);
        }
        $photo_stmt->close();
    }
}

$gobrik_conn->close();
header('Location: /en/edit-project.php?id=' . $project_id . '&saved=1');
exit();
?>
