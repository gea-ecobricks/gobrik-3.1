<?php
require_once '../earthenAuth_helper.php';
require_once '../auth/session_start.php';

if (!isLoggedIn()) {
    header('Location: /en/login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /en/add-project.php');
    exit();
}

require_once '../gobrikconn_env.php';

$project_id        = isset($_POST['project_id']) ? (int)$_POST['project_id'] : null;
$location_full     = trim($_POST['location_address'] ?? '');
$project_name      = trim($_POST['project_name'] ?? '');
$description_short = trim($_POST['description_short'] ?? '');
$description_long  = trim($_POST['description_long'] ?? '');
$project_type      = trim($_POST['project_type'] ?? '');
$construction_type = trim($_POST['construction_type'] ?? '');
$community         = trim($_POST['community'] ?? '');
$start_dt          = trim($_POST['start_dt'] ?? '');
$end_dt            = trim($_POST['end_dt'] ?? '');
$project_sort      = trim($_POST['project_sort'] ?? '');
$briks_used        = (int)($_POST['briks_used'] ?? 0);
$est_avg_brik_weight = (int)($_POST['est_avg_brik_weight'] ?? 0);
$latitude          = isset($_POST['latitude']) ? (float)$_POST['latitude'] : null;
$longitude         = isset($_POST['longitude']) ? (float)$_POST['longitude'] : null;
$connected_ecobricks = trim($_POST['connected_ecobricks'] ?? '');

// Build project_admins from tag-based admin_ids[] inputs
$admin_ids = $_POST['admin_ids'] ?? [];
if (!empty($admin_ids)) {
    $project_admins = implode(',', array_map('intval', $admin_ids));
} else {
    $project_admins = trim($_POST['project_admins'] ?? '');
}

$project_end   = !empty($end_dt) ? $end_dt : $start_dt;
$briks_required = $briks_used > 0 ? $briks_used : 0;
$logged_ts     = date('Y-m-d H:i:s');

if (!empty($project_id)) {
    $update_sql = "UPDATE tb_projects SET project_name = ?, description_short = ?, description_long = ?, start_dt = ?, end_dt = ?, project_end = ?, briks_required = ?, briks_used = ?, est_avg_brik_weight = ?, project_type = ?, construction_type = ?, project_sort = ?, community = ?, project_admins = ?, location_full = ?, location_lat = ?, location_long = ?, connected_ecobricks = ? WHERE project_id = ?";
    $stmt = $gobrik_conn->prepare($update_sql);
    if ($stmt) {
        $stmt->bind_param(
            'ssssssiiissssssddsi',
            $project_name,
            $description_short,
            $description_long,
            $start_dt,
            $end_dt,
            $project_end,
            $briks_required,
            $briks_used,
            $est_avg_brik_weight,
            $project_type,
            $construction_type,
            $project_sort,
            $community,
            $project_admins,
            $location_full,
            $latitude,
            $longitude,
            $connected_ecobricks,
            $project_id
        );
    } else {
        error_log('add_project_process: prepare failed (update): ' . $gobrik_conn->error);
        header('Location: /en/add-project.php?error=db');
        exit();
    }
} else {
    $insert_sql = "INSERT INTO tb_projects (project_name, description_short, description_long, start_dt, end_dt, project_end, briks_required, briks_used, est_avg_brik_weight, project_type, construction_type, project_sort, community, project_admins, location_full, location_lat, location_long, connected_ecobricks, logged_ts) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $gobrik_conn->prepare($insert_sql);
    if ($stmt) {
        $stmt->bind_param(
            'ssssssiiissssssddss',
            $project_name,
            $description_short,
            $description_long,
            $start_dt,
            $end_dt,
            $project_end,
            $briks_required,
            $briks_used,
            $est_avg_brik_weight,
            $project_type,
            $construction_type,
            $project_sort,
            $community,
            $project_admins,
            $location_full,
            $latitude,
            $longitude,
            $connected_ecobricks,
            $logged_ts
        );
    } else {
        error_log('add_project_process: prepare failed (insert): ' . $gobrik_conn->error);
        header('Location: /en/add-project.php?error=db');
        exit();
    }
}

if ($stmt) {
    if ($stmt->execute()) {
        if (empty($project_id)) {
            $project_id = $gobrik_conn->insert_id;
        }

        $est_total_weight = ($briks_used * $est_avg_brik_weight) / 1000;
        $update_weight_stmt = $gobrik_conn->prepare("UPDATE tb_projects SET est_total_weight = ? WHERE project_id = ?");
        if ($update_weight_stmt) {
            $update_weight_stmt->bind_param('di', $est_total_weight, $project_id);
            $update_weight_stmt->execute();
            $update_weight_stmt->close();
        }

        $project_url = "https://ecobricks.org/en/project.php?id=" . $project_id;
        $update_url_stmt = $gobrik_conn->prepare("UPDATE tb_projects SET project_url = ? WHERE project_id = ?");
        if ($update_url_stmt) {
            $update_url_stmt->bind_param('si', $project_url, $project_id);
            $update_url_stmt->execute();
            $update_url_stmt->close();
        }

        $stmt->close();
        $gobrik_conn->close();
        header('Location: /en/add-project-images.php?project_id=' . $project_id);
        exit();
    } else {
        error_log('add_project_process: execute failed: ' . $stmt->error);
        $stmt->close();
        $gobrik_conn->close();
        header('Location: /en/add-project.php?error=save');
        exit();
    }
}

$gobrik_conn->close();
header('Location: /en/add-project.php?error=unknown');
exit();
?>
