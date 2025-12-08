<?php
require_once '../earthenAuth_helper.php'; // 🌿 Optional helper functions

// 🌍 Set up page environment
$lang = basename(dirname($_SERVER['SCRIPT_NAME']));
$version = '7.5';
$page = 'dashboard';
$lastModified = date("Y-m-d\TH:i:s\Z", filemtime(__FILE__));

// 🔐 Start session and verify Buwana JWT (auto-redirects if not logged in)
require_once '../auth/session_start.php';

// 🆔 Retrieve the authenticated user's Buwana ID
$buwana_id = $_SESSION['buwana_id'] ?? '';

// 🧭 Buwana app registration check
// --------------------------------------------------
// Even though the user is logged in with Buwana, there is still a chance they
// have not connected their Buwana account to this specific client app yet.
// We call the shared Buwana API to confirm the "registered" connection and
// redirect them to the app-connect flow if the relationship is missing.
$client_id = 'gbrk_f2c61a85a4cd4b8b89a7';
if (!empty($buwana_id)) {
    $api_endpoint = 'https://buwana.ecobricks.org/api/check_user_app_connection.php';
    $query = http_build_query([
        'buwana_id' => $buwana_id,
        'client_id' => $client_id,
        'lang' => $lang ?? 'en'
    ]);

    $ch = curl_init("{$api_endpoint}?{$query}");
    if ($ch) {
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $api_response = curl_exec($ch);
        $curl_error = curl_error($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($api_response !== false && $http_code === 200) {
            $connection_status = json_decode($api_response, true);
            if (json_last_error() === JSON_ERROR_NONE && isset($connection_status['connected']) && !$connection_status['connected']) {
                $redirect_url = $connection_status['app_login_url'] ?? '';
                if (!empty($redirect_url)) {
                    header("Location: {$redirect_url}");
                    exit();
                }
            }
        } else {
            error_log('Buwana connection check failed: ' . ($curl_error ?: 'Unexpected HTTP ' . $http_code));
        }
    }
}

// 🔗 Establish DB connections to GoBrik and Buwana
require_once '../gobrikconn_env.php';
require_once '../buwanaconn_env.php';

// 🌎 Fetch user meta from Buwana database
$user_continent_icon = getUserContinent($buwana_conn, $buwana_id);
$earthling_emoji = getUserEarthlingEmoji($buwana_conn, $buwana_id);
$user_location_watershed = getWatershedName($buwana_conn, $buwana_id);
$user_location_full = getUserFullLocation($buwana_conn, $buwana_id);
$gea_status = getGEA_status($buwana_id);
$user_roles = getUser_Role($buwana_id);
$user_community_name = getCommunityName($buwana_conn, $buwana_id);

// 👤 Look up user's GoBrik account info
$sql_lookup_user = "SELECT first_name, ecobricks_made, ecobricker_id, location_full_txt, user_capabilities FROM tb_ecobrickers WHERE buwana_id = ?";
$stmt_lookup_user = $gobrik_conn->prepare($sql_lookup_user);
if ($stmt_lookup_user) {
    $stmt_lookup_user->bind_param("i", $buwana_id);
    $stmt_lookup_user->execute();
    $stmt_lookup_user->bind_result($first_name, $ecobricks_made, $ecobricker_id, $location_full_txt, $user_capabilities_raw);
    $stmt_lookup_user->fetch();
    $stmt_lookup_user->close();
} else {
    die("Error preparing statement for tb_ecobrickers: " . $gobrik_conn->error);
}

$user_capabilities_raw = $user_capabilities_raw ?? '';
$user_capabilities_list = array_filter(array_map('trim', explode(',', $user_capabilities_raw)));
$normalized_capabilities = array_map('strtolower', $user_capabilities_list);
$has_review_capability = in_array('review ecobricks', $normalized_capabilities, true);
$is_admin = strpos(strtolower($user_roles ?? ''), 'admin') !== false;
$has_validation_access = $is_admin || $has_review_capability;

$awaiting_validation_count = 0;
$authenticated_today_count = 0;
$rejected_today_count = 0;

if ($has_validation_access) {
    $sql_awaiting_validation = "SELECT COUNT(*) FROM tb_ecobricks WHERE status = 'Awaiting validation'";
    $stmt_awaiting_validation = $gobrik_conn->prepare($sql_awaiting_validation);
    if ($stmt_awaiting_validation) {
        $stmt_awaiting_validation->execute();
        $stmt_awaiting_validation->bind_result($awaiting_validation_count);
        $stmt_awaiting_validation->fetch();
        $stmt_awaiting_validation->close();
    }

    $validation_window_start = date('Y-m-d H:i:s', time() - 86400);
    $sql_recent_validations = "SELECT status, COUNT(*) AS record_count FROM tb_ecobricks WHERE status IN ('Authenticated', 'Rejected') AND last_validation_ts >= ? GROUP BY status";
    $stmt_recent_validations = $gobrik_conn->prepare($sql_recent_validations);
    if ($stmt_recent_validations) {
        $stmt_recent_validations->bind_param("s", $validation_window_start);
        if ($stmt_recent_validations->execute()) {
            $stmt_recent_validations->bind_result($status_value, $status_count);
            while ($stmt_recent_validations->fetch()) {
                if (strcasecmp($status_value, 'Authenticated') === 0) {
                    $authenticated_today_count = (int) $status_count;
                } elseif (strcasecmp($status_value, 'Rejected') === 0) {
                    $rejected_today_count = (int) $status_count;
                }
            }
        }
        $stmt_recent_validations->close();
    }
}

// 🪪 Set maaker_id for further lookups
// $maaker_id = $ecobricker_id;

// 📦 Fetch summary of ecobricks logged by user
$sql_summary = "SELECT COUNT(*) as total_ecobricks, SUM(weight_g) / 1000 as total_weight_kg, SUM(volume_ml) as total_volume_ml
                FROM tb_ecobricks
                WHERE ecobricker_id = ? AND status != 'not ready'";
$stmt_summary = $gobrik_conn->prepare($sql_summary);
if ($stmt_summary) {
    $stmt_summary->bind_param("s", $ecobricker_id);
    $stmt_summary->execute();
    $stmt_summary->bind_result($total_ecobricks, $total_weight_kg, $total_volume_ml);
    $stmt_summary->fetch();
    $stmt_summary->close();
} else {
    die("Error preparing statement for ecobricks summary: " . $gobrik_conn->error);
}

// ⚖️ Calculate net density (g/ml)
$net_density = $total_volume_ml > 0 ? ($total_weight_kg * 1000) / $total_volume_ml : 0;

$total_ecobricks_formatted = number_format((float) ($total_ecobricks ?? 0));
$total_weight_formatted = number_format((float) ($total_weight_kg ?? 0), 1);
$net_density_formatted = number_format((float) $net_density, 2);

$dashboard_summary_text = "So far you've logged {$total_ecobricks_formatted} ecobricks. In total you've logged {$total_weight_formatted} kg with a net density of {$net_density_formatted} g/ml.";

// 📍 Process user location
$location_full_txt = $location_full_txt ?? '';
$location_parts = array_map('trim', explode(',', $location_full_txt));
$location_last = $location_parts[count($location_parts) - 1] ?? '';
$location_third_last = $location_parts[count($location_parts) - 3] ?? '';
$locationFullTxt = $location_third_last . ', ' . $location_last;

// 🎓 Fetch trainings where user is trainer, ordered by most recent first
$trainings = [];
$sql_trainings = "SELECT t.training_id, t.training_title, t.training_date, t.training_location, t.training_type, t.ready_to_show, t.show_report,
                         (SELECT COUNT(*) FROM tb_training_trainees WHERE training_id = t.training_id) AS trainee_count
                  FROM tb_trainings t
                  INNER JOIN tb_training_trainers tt ON t.training_id = tt.training_id
                  WHERE tt.ecobricker_id = ?
                  ORDER BY t.training_date DESC";
$stmt_trainings = $gobrik_conn->prepare($sql_trainings);
if ($stmt_trainings) {
    $stmt_trainings->bind_param("i", $ecobricker_id);
    $stmt_trainings->execute();
    $result_trainings = $stmt_trainings->get_result();
    while ($row = $result_trainings->fetch_assoc()) {
        $trainings[] = $row;
    }
    $stmt_trainings->close();
} else {
    die("Error preparing statement for trainer trainings: " . $gobrik_conn->error);
}

// 📋 Fetch trainings where user is a registered trainee
$registered_trainings = [];
$sql_registered_trainings = "SELECT t.training_id, t.training_title, t.training_date, t.training_location,
                                    t.training_country, t.training_type, t.zoom_link, t.zoom_link_full
                             FROM tb_trainings t
                             INNER JOIN tb_training_trainees tt ON t.training_id = tt.training_id
                             WHERE tt.ecobricker_id = ?";
$stmt_registered_trainings = $gobrik_conn->prepare($sql_registered_trainings);
if ($stmt_registered_trainings) {
    $stmt_registered_trainings->bind_param("i", $ecobricker_id);
    $stmt_registered_trainings->execute();
    $result_registered_trainings = $stmt_registered_trainings->get_result();
    while ($row = $result_registered_trainings->fetch_assoc()) {
        $registered_trainings[] = $row;
    }
    $stmt_registered_trainings->close();
} else {
    die("Error preparing statement for registered trainings: " . $gobrik_conn->error);
}

// 🧱 Fetch featured ecobricks for homepage slider and grid
function formatLocationTail(?string $location_full): string {
    $location_parts = array_filter(array_map('trim', explode(',', $location_full ?? '')));
    return implode(', ', array_slice($location_parts, -2));
}

function fetchFeaturedEcobricks(mysqli $conn, int $limit = 9, int $offset = 0): array {
    $sql_featured = "SELECT selfie_photo_url, selfie_thumb_url, serial_no, photo_version, weight_g, ecobricker_maker, location_full, vision, date_logged_ts, status
                     FROM tb_ecobricks
                     WHERE selfie_photo_url IS NOT NULL
                       AND selfie_photo_url != ''
                       AND ((feature = 1 AND status != 'not ready') OR LOWER(status) = 'authenticated')
                     ORDER BY feature DESC, date_logged_ts DESC
                     LIMIT ? OFFSET ?";

    $featured_ecobricks = [];

    $stmt_featured = $conn->prepare($sql_featured);
    if ($stmt_featured) {
        $stmt_featured->bind_param('ii', $limit, $offset);
        $stmt_featured->execute();
        $result_featured = $stmt_featured->get_result();
        while ($row = $result_featured->fetch_assoc()) {
            $featured_ecobricks[] = [
                'selfie_photo_url' => $row['selfie_photo_url'] ?? '',
                'selfie_thumb_url' => $row['selfie_thumb_url'] ?? '',
                'serial_no' => $row['serial_no'] ?? '',
                'photo_version' => $row['photo_version'] ?? '',
                'weight_g' => $row['weight_g'] ?? '',
                'ecobricker_maker' => $row['ecobricker_maker'] ?? '',
                'location_display' => formatLocationTail($row['location_full'] ?? ''),
                'vision' => $row['vision'] ?? '',
                'date_logged_ts' => $row['date_logged_ts'] ?? '',
                'status' => $row['status'] ?? '',
            ];
        }
        $stmt_featured->close();
    } else {
        die("Error preparing statement for featured ecobricks: " . $conn->error);
    }

    return $featured_ecobricks;
}

$featured_ecobricks = fetchFeaturedEcobricks($gobrik_conn, 9, 0);
$featured_ecobricks_json = json_encode($featured_ecobricks, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES);

// 📣 Fetch the latest dashboard notice for display and admin controls
function fetchLatestDashNotice($conn) {
    $latest_notice = null;
    $sql_notice = "SELECT notice_id, message_body, message_emoji, featured_url, featured_text, status
                   FROM dash_notices_tb
                   ORDER BY date_created DESC, notice_id DESC
                   LIMIT 1";

    $stmt_notice = $conn->prepare($sql_notice);
    if ($stmt_notice) {
        if ($stmt_notice->execute()) {
            $stmt_notice->bind_result($notice_id, $message_body, $message_emoji, $featured_url, $featured_text, $status);
            if ($stmt_notice->fetch()) {
                $latest_notice = [
                    'notice_id' => $notice_id,
                    'message_body' => $message_body,
                    'message_emoji' => $message_emoji,
                    'featured_url' => $featured_url,
                    'featured_text' => $featured_text,
                    'status' => $status
                ];
            }
        }
        $stmt_notice->close();
    }

    return $latest_notice;
}

$latest_notice = fetchLatestDashNotice($gobrik_conn);
$active_notice = null;
if ($latest_notice && (!isset($latest_notice['status']) || strtolower($latest_notice['status']) === 'active')) {
    $active_notice = $latest_notice;
}

$default_notice_text = 'Updated: Free October 20th Ecobrick Intro course.';
$default_featured_text = 'Register';
$default_featured_url = 'https://gobrik.com/en/courses.php';

if ($active_notice) {
    $notice_icon = $active_notice['message_emoji'] ?? '👉';
    $notice_text = $active_notice['message_body'] ?? $default_notice_text;
    $notice_featured_text = $active_notice['featured_text'] ?? '';
    $notice_featured_url = $active_notice['featured_url'] ?? '';
} else {
    $notice_icon = '👉';
    $notice_text = $default_notice_text;
    $notice_featured_text = $default_featured_text;
    $notice_featured_url = $default_featured_url;
}

$ghost_member_stats = [
    'total' => 0,
    'sent_001' => 0,
    'sent_002' => 0,
    'remaining' => 0,
];

if ($is_admin) {
    require_once '../emailing/earthen_helpers.php';

    $ghoststats_conn = loadGhostStatsConnection();

    if ($ghoststats_conn) {
        $sent001_stats = getGhostMemberStats($ghoststats_conn, 'sent-001');
        $sent002_stats = getGhostMemberStats($ghoststats_conn, 'sent-002');

        $ghost_total = max($sent001_stats['total'] ?? 0, $sent002_stats['total'] ?? 0);
        $ghost_remaining = max(0, $ghost_total - ($sent001_stats['sent'] ?? 0) - ($sent002_stats['sent'] ?? 0));

        $ghost_member_stats = [
            'total' => $ghost_total,
            'sent_001' => $sent001_stats['sent'] ?? 0,
            'sent_002' => $sent002_stats['sent'] ?? 0,
            'remaining' => $ghost_remaining,
        ];
    }

    if ($ghoststats_conn instanceof mysqli) {
        $ghoststats_conn->close();
    }
}

// 🔒 Clean exit: close DB connections
$buwana_conn->close();
$gobrik_conn->close();
$dashboard_header_version = "../header-2026.php";
?>





<!--
GoBrik.com site version 3.1
Developed and made open source by the Global Ecobrick Alliance
See our git hub repository for the full code and to help out:
https://github.com/gea-ecobricks/gobrik-3.0/tree/main/en-->

<?php require_once("../includes/dashboard-inc.php"); ?>
<link rel="stylesheet" href="../styles/dashboard-v2-styles.css?v11.1">

<style>
    #header.top-menu {
        position: relative !important;
        top: auto !important;
    }

    .dashboard-v2-panel {
        position: relative;
    }

    .vertical-toggle {
        position: relative;
        width: 32px;
        height: 70px;
        border: 1px solid #d0d0d0;
        border-radius: 18px;
        background: linear-gradient(180deg, #ffffff 0%, #f1f1f1 100%);
        padding: 6px 4px;
        display: flex;
        align-items: flex-start;
        justify-content: center;
        cursor: pointer;
        transition: background 0.2s ease, box-shadow 0.2s ease, align-items 0.2s ease;
        box-shadow: inset 0 1px 2px rgba(0,0,0,0.08);
    }

    .vertical-toggle:focus-visible {
        outline: 2px solid #1976d2;
        outline-offset: 3px;
    }

    .vertical-toggle.down {
        align-items: flex-end;
        background: linear-gradient(180deg, #e8f0ff 0%, #d0e0ff 100%);
        box-shadow: inset 0 1px 2px rgba(25, 118, 210, 0.25);
    }

    .vertical-toggle .toggle-knob {
        width: 18px;
        height: 26px;
        background: #1976d2;
        border-radius: 10px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.25);
        transition: background 0.2s ease;
    }

    .vertical-toggle.down .toggle-knob {
        background: #0d47a1;
    }
</style>

<div class="dashboard-wrapper">
<div id="dashboard-v2-grid" class="dashboard-grid">
    <div class="dashboard-column column-narrow">
        <div id="registered-notice-panel" class="dashboard-v2-panel notice-panel">
            <div id="registered-notice" class="top-container-notice">
                <span id="notice-icon" class="notice-icon">
                    <?php echo htmlspecialchars($notice_icon ?: '👉', ENT_QUOTES, 'UTF-8'); ?>
                </span>
                <span id="notice-text" class="notice-text"><?php echo nl2br(htmlspecialchars($notice_text, ENT_QUOTES, 'UTF-8')); ?>
                        <a href="<?php echo htmlspecialchars($notice_featured_url, ENT_QUOTES, 'UTF-8'); ?>">
                            <?php echo htmlspecialchars($notice_featured_text, ENT_QUOTES, 'UTF-8'); ?>
                        </a>
                    </span>

                <button class="notice-close" aria-label="Close">&times;</button>
            </div>
        </div>

        <div id="welcome-greeting-panel" class="dashboard-v2-panel">
            <span class="dashboard-status-pill logged-in-pill">Logged in <?php echo htmlspecialchars($earthling_emoji ?? '🟢', ENT_QUOTES, 'UTF-8'); ?></span>
            <h2 id="greeting">Hello <?php echo htmlspecialchars($first_name); ?>!</h2>
            <p id="subgreeting">Welcome to your new 2026 GoBrik dashboard. We've revamped it for the new year.</p>
            <p class="dashboard-summary-text"><?php echo htmlspecialchars($dashboard_summary_text, ENT_QUOTES, 'UTF-8'); ?></p>
            <div class="dashboard-actions">
                <a href="log.php" class="button" id="log-ecobrick-button" data-lang-id="001-log-an-ecobrick">➕ Log an Ecobrick</a>
                <a href="add-project.php" class="button ghost" data-lang-id="001c-register-project">🏗️ Register Project</a>
                <button id="take-gobrik-tour" class="button secondary" data-lang-id="001b-take-gobrik-tour" aria-label="Tour" onclick="startTour()">🛳️ GoBrik Tour</button>
            </div>
        </div>

        <div id="registrations-panel" class="dashboard-v2-panel">
            <h3 data-lang-id="002-my-registrations">My Registrations</h3>
            <p>Trainings that you've registered for.</p>

            <table id="trainee-trainings" class="display responsive nowrap" style="width:100%;">
                <thead>
                    <tr>
                        <th>Training</th>
                        <th>Location</th>
                        <th>Date</th>
                        <th>Country</th>
                        <th>Type</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($registered_trainings as $training): ?>
                        <?php $training_date = date('Y-m-d', strtotime($training['training_date'])); ?>
                        <tr>
                            <td><?php echo htmlspecialchars($training['training_title']); ?></td>
                            <td style="text-align:center;">
                                <a href="javascript:void(0);"
                                   style="text-decoration:underline; font-weight:bold;"
                                   onclick="openRegisteredTrainingsModal(<?php echo $training['training_id']; ?>,
                                                                      '<?php echo htmlspecialchars($training['training_location'], ENT_QUOTES, 'UTF-8'); ?>')">
                                    <?php echo htmlspecialchars($training['training_location']); ?> 🔎
                                </a>
                            </td>
                            <td><?php echo htmlspecialchars($training_date); ?></td>
                            <td><?php echo htmlspecialchars($training['training_country']); ?></td>
                            <td><?php echo htmlspecialchars($training['training_type']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div id="support-chats-panel" class="dashboard-v2-panel">
            <div class="support-chats-header">
                <div>
                    <h3>Support Chats</h3>
                    <p>Message our developers for support.</p>
                </div>
                <button id="support-chat-toggle" class="vertical-toggle" aria-expanded="false" aria-label="Toggle support chat form">
                    <span class="toggle-knob"></span>
                </button>
            </div>
            <div class="menu-buttons-row" style="justify-content:center;">
                <a href="https://buwana.ecobricks.org/en/feedback.php?buwana=<?php echo urlencode($buwana_id); ?>&app=<?php echo urlencode($client_id); ?>" class="page-button">Message Developers</a>
            </div>
            <form class="support-chat-form" id="support-chat-form">
                <div>
                    <label for="support-subject">Subject</label>
                    <input type="text" id="support-subject" name="subject" placeholder="Give your chat a subject" />
                </div>
                <div>
                    <label for="support-priority">Priority</label>
                    <select id="support-priority" name="priority">
                        <option value="low">Low</option>
                        <option value="medium">Medium</option>
                        <option value="high">High</option>
                    </select>
                </div>
                <div>
                    <label for="support-description">Issue</label>
                    <textarea id="support-description" name="description" placeholder="Describe your issue..."></textarea>
                </div>
                <div class="support-chat-actions">
                    <button type="button" class="page-button">Start Chat</button>
                    <button type="button" class="page-button secondary">Your Support Chats</button>
                </div>
            </form>
        </div>

        <?php if ($is_admin): ?>
            <div id="admin-support-chats" class="dashboard-v2-panel">
                <span class="panel-pill admin-pill">Admin</span>
                <h4 class="panel-title" style="text-align:center;">Admin Support Chats</h4>
                <div class="menu-buttons-row" style="justify-content:center;">
                    <a href="https://buwana.ecobricks.org/en/cs-chats.php?buwana=<?php echo urlencode($buwana_id); ?>&app=gbrk_f2c61a85a4cd4b8b89a7" class="page-button">💬 Support Chats</a>
                </div>
            </div>

            <div id="dash-notice-control" class="dashboard-v2-panel">
                <span class="panel-pill admin-pill">Admin</span>
                <h4 class="panel-title" style="margin-bottom:6px;text-align:center;">Update Dashboard Notice</h4>
                <div style="display:flex;align-items:center;justify-content:space-between;gap:10px;">
                    <p style="margin:0;font-size:0.95em;">Admins use this to feature special news. The message will be featured at the top of everyone's dashboard.</p>
                    <button id="dash-notice-toggle" class="vertical-toggle" aria-expanded="false" aria-label="Toggle dashboard notice form">
                        <span class="toggle-knob"></span>
                    </button>
                </div>
                <form id="dash-notice-form" action="../api/add_new_dash_notice.php" method="post" class="dash-notice-form" style="display:none;margin-top:12px;font-size:0.92em;">
                    <div class="form-field" style="margin-bottom:10px;">
                        <label for="notice-message-body" style="display:block;margin-bottom:4px;">Message</label>
                        <textarea id="notice-message-body" name="message_body" rows="2" required style="width:100%;padding:8px;"><?php echo htmlspecialchars($latest_notice['message_body'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                    </div>
                    <div class="form-field" style="margin-bottom:10px;">
                        <label for="notice-featured-text" style="display:block;margin-bottom:4px;">Featured Text</label>
                        <input type="text" id="notice-featured-text" name="featured_text" maxlength="100" style="width:100%;padding:8px;"
                               value="<?php echo htmlspecialchars($latest_notice['featured_text'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    </div>
                    <div class="form-field" style="margin-bottom:10px;">
                        <label for="notice-featured-url" style="display:block;margin-bottom:4px;">Featured URL</label>
                        <input type="url" id="notice-featured-url" name="featured_url" style="width:100%;padding:8px;"
                               value="<?php echo htmlspecialchars($latest_notice['featured_url'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    </div>
                    <div class="form-field" style="margin-bottom:10px;">
                        <label for="notice-message-emoji" style="display:block;margin-bottom:4px;">Message Emoji</label>
                        <input type="text" id="notice-message-emoji" name="message_emoji" maxlength="10" style="width:100%;padding:8px;"
                               value="<?php echo htmlspecialchars($latest_notice['message_emoji'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    </div>
                    <button type="submit" class="button secondary" style="width:100%;">Save Notice</button>
                </form>
            </div>
        <?php endif; ?>

        <?php if (strpos(strtolower($gea_status), 'trainer') !== false): ?>
            <div id="gea-trainer-menu" class="dashboard-v2-panel">
                <span class="panel-pill trainer-pill">Trainer</span>
                <h4 class="panel-title">GEA Trainer Menu</h4>
                <div class="menu-buttons-row">
                    <a href="https://nextcloud.ecobricks.org/index.php/s/wCC2BwBwkW7GzTA" target="_blank" class="page-button">Trainer File Kit</a>
                    <a href="https://learning.ecobricks.org" target="_blank" class="page-button">GEA Courses</a>
                    <a href="https://ecobricks.org/<?php echo htmlspecialchars($lang); ?>/media.php" target="_blank" class="page-button">Ecobricks Media Kit</a>
                    <a href="admin-review.php" class="page-button">Validate Ecobricks</a>
                    <a href="bug-report.php" class="page-button">Report a Bug</a>
                    <a href="accounting.php" class="page-button">GEA Accounting</a>
                    <a href="finalizer.php" class="page-button" id="event-register-button" data-lang-id="005-totem-training">+ Set Buwana Totem</a>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <div class="dashboard-column column-wide">
        <div id="latest-ecobricks-panel" class="dashboard-v2-panel">
            <h4 style="margin:0 0 12px 0;">Latest featured ecobricks...</h4>
            <div id="ecobrick-slider" class="ecobrick-mobile-slider" aria-label="Latest ecobrick selfies slider">
                <?php if (!empty($featured_ecobricks)): ?>
                    <?php foreach ($featured_ecobricks as $index => $brick): ?>
                        <div class="slide<?php echo $index === 0 ? ' active' : ''; ?>">
                            <img src="<?php echo htmlspecialchars($brick['selfie_photo_url']); ?>?v=<?php echo htmlspecialchars($brick['photo_version']); ?>"
                                 alt="Ecobrick selfie for serial <?php echo htmlspecialchars($brick['serial_no']); ?>">
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <div class="ecobrick-grid" aria-label="Latest ecobrick selfies grid">
                <?php if (!empty($featured_ecobricks)): ?>
                    <?php foreach ($featured_ecobricks as $index => $brick): ?>
                        <button class="ecobrick-grid-item" type="button" data-grid-index="<?php echo (int) $index; ?>" title="<?php echo htmlspecialchars($brick['vision'] ?? $brick['location_display'] ?? 'View featured ecobrick'); ?>">
                            <img src="<?php echo htmlspecialchars($brick['selfie_photo_url']); ?>?v=<?php echo htmlspecialchars($brick['photo_version']); ?>"
                                 alt="Ecobrick selfie for serial <?php echo htmlspecialchars($brick['serial_no']); ?>">
                        </button>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <div class="ecobrick-grid-actions">
                <div class="ecobrick-grid-action-row" id="featured-grid-action-row">
                    <button id="previous-ecobricks" class="page-button tertiary previous-ecobricks" style="display:none;">Previous</button>
                    <button id="load-next-ecobricks" class="page-button tertiary load-next-ecobricks">Next Selfies</button>
                </div>
                <small class="ecobrick-grid-note">Load more of the latest authenticated ecobricks with selfies</small>
            </div>
            <p id="featured-ecobricks-empty" class="ecobrick-empty-message" <?php echo !empty($featured_ecobricks) ? 'style="display:none;"' : ''; ?>>No featured ecobricks to display right now.</p>
        </div>

        <div id="my-ecobricks-panel" class="dashboard-v2-panel">
            <h3 data-lang-id="002-my-ecobricks">My Ecobricks</h3>
            <table id="latest-ecobricks" class="display responsive nowrap" style="width:100%">
                <thead>
                    <tr>
                        <th data-lang-id="1103-brik">Brik</th>
                        <th data-lang-id="1104-weight">Weight</th>
                        <th data-lang-id="1108-volume">Volume</th>
                        <th data-lang-id="1109-density">Density</th>
                        <th data-lang-id="1110-date-logged">Logged</th>
                        <th data-lang-id="1106-status">Status</th>
                        <th data-lang-id="1107-serial">Serial</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- DataTables will populate this via AJAX -->
                </tbody>
            </table>
        </div>

        <?php if ($is_admin): ?>
            <div id="admin-menu" class="dashboard-v2-panel">
                <span class="panel-pill admin-pill">Admin</span>
                <h4 class="panel-title">Earthen Manual Mailer</h4>
                <div class="earthen-mailer-layout">
                    <div class="earthen-mailer-actions">
                        <a href="earthen-sender.php" class="page-button">Earthen Mailer</a>
                        <a href="https://earthen.io/ghost" class="page-button">Ghost Login</a>
                        <a href="admin-panel.php" class="page-button">Member Management</a>
                        <a href="../emailing/mailgun-logs.php" class="page-button">Mailgun logs</a>
                    </div>
                    <div class="earthen-mailer-chart">
                        <div class="earthen-mailer-chart-inner">
                            <canvas id="earthen-mailer-donut" aria-label="Earthen member send status" role="img"></canvas>
                        </div>
                        <div style="display:flex;flex-wrap:wrap;justify-content:center;gap:15px;margin-top:12px;font-size:0.95em;">
                            <div><strong>Total members:</strong> <?php echo number_format((int) $ghost_member_stats['total']); ?></div>
                            <div><strong>sent-001:</strong> <?php echo number_format((int) $ghost_member_stats['sent_001']); ?></div>
                            <div><strong>sent-002:</strong> <?php echo number_format((int) $ghost_member_stats['sent_002']); ?></div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if (strpos(strtolower($gea_status), 'trainer') !== false): ?>
            <div id="my-trainings-panel" class="dashboard-v2-panel">
                <span class="panel-pill trainer-pill">Trainer</span>
                <h3 data-lang-id="002-my-trainings">My Trainings</h3>
                <div class="menu-buttons-row">
                    <a href="launch-training.php" class="page-button">🚀 New Training</a>
                    <a href="training-report.php" class="page-button" id="event-register-button" data-lang-id="004-log-training">📝Log Report</a>
                </div>

                <table id="trainer-trainings" class="display" style="width:100%;">
                    <thead>
                        <tr>
                            <th>Training</th>
                            <th>Date</th>
                            <th>Signups</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $pendingReport = null; foreach ($trainings as $training): ?>
                            <?php
                                $training_date_ts = strtotime($training['training_date']);
                                $is_listed = $training['ready_to_show'] == 1;
                                $show_report = $training['show_report'] == 1;

                                if (!$is_listed) {
                                    $circle = '⚪';
                                } elseif ($training_date_ts > time()) {
                                    $circle = '🟢';
                                } elseif ($show_report && $is_listed) {
                                    $circle = '✅';
                                } else {
                                    $circle = '🔴';
                                    if (!isset($pendingReport)) {
                                        $pendingReport = [
                                            'id' => $training['training_id'],
                                            'title' => $training['training_title'],
                                            'date' => date('Y-m-d', $training_date_ts)
                                        ];
                                    }
                                }
                            ?>
                            <tr>
                                <td style="white-space:normal;"><?php echo $circle . ' ' . htmlspecialchars($training['training_title']); ?></td>
                                <td><?php echo date("Y-m-d", strtotime($training['training_date'])); ?></td>
                                <td style="text-align:center;padding:10px;">
                                    <a href="javascript:void(0);" class="log-report-btn signup-btn" onclick="openTraineesModal(<?php echo $training['training_id']; ?>, '<?php echo htmlspecialchars($training['training_title'], ENT_QUOTES, 'UTF-8'); ?>')" style="display:inline-block;">
                                        <span class="signup-count"><?php echo (int) $training['trainee_count']; ?></span>
                                        <span class="hover-emoji">👥</span>
                                    </a>
                                </td>
                                <td style="text-align:center;">
                                    <button class="serial-button settings-button" data-show-report="<?php echo $training['show_report']; ?>" data-ready-to-show="<?php echo $training['ready_to_show']; ?>" onclick="actionsTrainingModal(this, <?php echo $training['training_id']; ?>)">
                                        <span class="default-emoji">✏️</span><span class="hover-emoji">⚙️</span>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <?php if ($has_validation_access): ?>
            <div id="validation-panel" class="dashboard-v2-panel">
                <span class="panel-pill validator-pill">Validator</span>
                <div style="display:flex;flex-wrap:wrap;justify-content:center;gap:35px;margin:10px 0 25px 0;">
                    <div style="text-align:center;">
                        <div style="font-size:1.9em;font-weight:600;color:#f57c00;line-height:1;">
                            ⏲️ <?php echo number_format((int) $awaiting_validation_count); ?>
                        </div>
                        <div style="font-size:1em;margin-top: 6px;">Awaiting Review</div>
                    </div>
                    <div style="text-align:center;">
                        <div style="font-size:1.9em;font-weight:600;color:#2e7d32;line-height:1;">
                            ✅<?php echo number_format((int) $authenticated_today_count); ?>
                        </div>
                        <div style="font-size:1em;margin-top: 6px;">Authenticated Today</div>
                    </div>
                    <div style="text-align:center;">
                        <div style="font-size:1.9em;font-weight:600;color:#c62828;line-height:1;">
                             ⛔<?php echo number_format((int) $rejected_today_count); ?>
                        </div>
                        <div style="font-size:1em;margin-top: 6px;">Rejected Today</div>
                    </div>
                </div>
                <a href="admin-review.php" class="page-button" style="display:block;width:100%;max-width:420px;margin:0 auto;">Admin Validation</a>
            </div>
        <?php endif; ?>
    </div>
</div><!-- closes main container -->

</div><!--closes main and starry background-->

</div>

<!-- FOOTER STARTS HERE -->
<?php require_once("../footer-2026.php"); ?>



<script>
    //GET ECOBRICKER'S ECOBRICKS

    const featuredGridBricks = <?php echo $featured_ecobricks_json ?? '[]'; ?> || [];
    const FEATURED_LIMIT = 9;
    let featuredOffset = 0;
    let currentFeaturedBricks = Array.isArray(featuredGridBricks) ? featuredGridBricks : [];

    const sliderElement = document.getElementById('ecobrick-slider');
    const previousButton = document.getElementById('previous-ecobricks');
    const gridActionRow = document.getElementById('featured-grid-action-row');
    const loadNextButton = document.getElementById('load-next-ecobricks');
    const mobileSliderQuery = window.matchMedia('(max-width: 768px)');
    const welcomePanel = document.getElementById('welcome-greeting-panel');
    const latestPanel = document.getElementById('latest-ecobricks-panel');
    const narrowColumn = document.querySelector('.dashboard-column.column-narrow');
    const wideColumn = document.querySelector('.dashboard-column.column-wide');
    const latestOriginalNextSibling = latestPanel?.nextElementSibling ?? null;
    let sliderCurrentIndex = 0;
    let sliderIntervalId = null;
    let sliderTouchStartX = 0;

    function updateFeaturedControls() {
        if (!loadNextButton) return;

        const hasPrevious = featuredOffset > 0;

        if (previousButton) {
            previousButton.style.display = hasPrevious ? 'inline-flex' : 'none';
        }

        if (gridActionRow) {
            gridActionRow.classList.toggle('showing-previous', hasPrevious);
        }

        loadNextButton.classList.toggle('dual-width', hasPrevious);
    }

    function getSliderSlides() {
        return sliderElement ? Array.from(sliderElement.querySelectorAll('.slide')) : [];
    }

    function setActiveSlide(index) {
        const slides = getSliderSlides();
        if (!slides.length) return;

        sliderCurrentIndex = (index + slides.length) % slides.length;
        slides.forEach((slide, idx) => slide.classList.toggle('active', idx === sliderCurrentIndex));
    }

    function startSliderInterval() {
        const slides = getSliderSlides();
        if (!sliderElement || sliderIntervalId || slides.length <= 1 || !mobileSliderQuery.matches) return;

        sliderIntervalId = setInterval(() => setActiveSlide(sliderCurrentIndex + 1), 3000);
    }

    function stopSliderInterval() {
        if (sliderIntervalId) {
            clearInterval(sliderIntervalId);
            sliderIntervalId = null;
        }
    }

    function resetSliderState() {
        stopSliderInterval();
        sliderCurrentIndex = 0;
        setActiveSlide(0);
        startSliderInterval();
    }

    function handleSliderTouchStart(e) {
        if (!mobileSliderQuery.matches) return;
        sliderTouchStartX = e.touches[0].clientX;
        stopSliderInterval();
    }

    function handleSliderTouchEnd(e) {
        if (!mobileSliderQuery.matches) return;
        const diff = e.changedTouches[0].clientX - sliderTouchStartX;
        if (diff < -50) {
            setActiveSlide(sliderCurrentIndex + 1);
        } else if (diff > 50) {
            setActiveSlide(sliderCurrentIndex - 1);
        }
        startSliderInterval();
    }

    function handleSliderQueryChange(event) {
        if (event.matches) {
            startSliderInterval();
        } else {
            stopSliderInterval();
            sliderCurrentIndex = 0;
            setActiveSlide(0);
        }
    }

    function toggleFeaturedEmptyState(hasData) {
        const emptyMessage = document.getElementById('featured-ecobricks-empty');
        const grid = document.querySelector('.ecobrick-grid');
        const slider = document.getElementById('ecobrick-slider');

        if (emptyMessage) {
            emptyMessage.style.display = hasData ? 'none' : 'block';
        }
        if (grid) {
            grid.style.display = hasData ? '' : 'none';
        }
        if (slider) {
            slider.style.display = hasData ? '' : 'none';
        }
    }

    function preloadFeaturedEcobricks(bricks) {
        if (!Array.isArray(bricks)) return;

        bricks.slice(0, FEATURED_LIMIT).forEach((brick) => {
            if (!brick || !brick.selfie_photo_url) return;
            const preloadImg = new Image();
            preloadImg.src = `${brick.selfie_photo_url}?v=${brick.photo_version ?? ''}`;
        });
    }

    function renderFeaturedEcobricks(bricks) {
        const slider = document.getElementById('ecobrick-slider');
        const grid = document.querySelector('.ecobrick-grid');

        if (!slider || !grid) return;

        slider.innerHTML = '';
        grid.innerHTML = '';

        if (!Array.isArray(bricks) || !bricks.length) {
            toggleFeaturedEmptyState(false);
            stopSliderInterval();
            return;
        }

        bricks.slice(0, FEATURED_LIMIT).forEach((brick, index) => {
            const slide = document.createElement('div');
            slide.className = `slide${index === 0 ? ' active' : ''}`;

            const slideImg = document.createElement('img');
            slideImg.src = `${brick.selfie_photo_url}?v=${brick.photo_version ?? ''}`;
            slideImg.alt = `Ecobrick selfie for serial ${brick.serial_no || ''}`;
            slide.appendChild(slideImg);
            slider.appendChild(slide);

            const gridButton = document.createElement('button');
            gridButton.className = 'ecobrick-grid-item';
            gridButton.type = 'button';
            gridButton.title = brick.vision || brick.location_display || 'View featured ecobrick';

            const gridImg = document.createElement('img');
            gridImg.src = `${brick.selfie_photo_url}?v=${brick.photo_version ?? ''}`;
            gridImg.alt = `Ecobrick selfie for serial ${brick.serial_no || ''}`;
            gridButton.appendChild(gridImg);

            gridButton.addEventListener('click', () => openViewEcobricV2(brick));
            grid.appendChild(gridButton);
        });

        toggleFeaturedEmptyState(true);
        resetSliderState();
    }

    function updateFeaturedBricks(bricks) {
        currentFeaturedBricks = Array.isArray(bricks) ? bricks : [];
        renderFeaturedEcobricks(currentFeaturedBricks);
        preloadFeaturedEcobricks(currentFeaturedBricks);
        updateFeaturedControls();
    }

    function loadFeaturedBatch(offset) {
        fetch(`../api/fetch_featured_ecobricks.php?offset=${offset}&limit=${FEATURED_LIMIT}`)
            .then((response) => response.json())
            .then((data) => {
                if (!data?.success) {
                    throw new Error(data?.error || 'Unable to load ecobricks');
                }

                if (!Array.isArray(data.data) || !data.data.length) {
                    featuredOffset = Math.max(0, featuredOffset - FEATURED_LIMIT);
                    toggleFeaturedEmptyState(false);
                    updateFeaturedControls();
                    return;
                }

                updateFeaturedBricks(data.data);
            })
            .catch((error) => {
                console.error('Error fetching featured ecobricks:', error);
                featuredOffset = Math.max(0, featuredOffset - FEATURED_LIMIT);
                updateFeaturedControls();
            });
    }

    function repositionLatestPanel() {
        if (!latestPanel || !welcomePanel || !narrowColumn || !wideColumn) return;

        if (mobileSliderQuery.matches) {
            if (latestPanel.parentElement !== narrowColumn || latestPanel.previousElementSibling !== welcomePanel) {
                welcomePanel.insertAdjacentElement('afterend', latestPanel);
            }
        } else if (latestPanel.parentElement !== wideColumn) {
            if (latestOriginalNextSibling && latestOriginalNextSibling.parentElement === wideColumn) {
                wideColumn.insertBefore(latestPanel, latestOriginalNextSibling);
            } else {
                wideColumn.insertBefore(latestPanel, wideColumn.firstChild);
            }
        }
    }

    function closeInfoModalV2() {
        const modal = document.getElementById('form-modal-message-v2');
        if (!modal) return;

        modal.classList.add('modal-hidden');
        modal.classList.remove('modal-shown');
        document.body.classList.remove('modal-open');
        document.getElementById('page-content')?.classList.remove('blurred');
        document.getElementById('footer-full')?.classList.remove('blurred');

        modal.querySelector('.modal-photo-v2')?.replaceChildren();
        modal.querySelector('.modal-message-v2')?.replaceChildren();

        const modalStatusPill = modal.querySelector('.modal-status-pill');
        const modalViewButton = modal.querySelector('.modal-view-button');
        if (modalStatusPill) {
            modalStatusPill.textContent = '';
            modalStatusPill.className = 'modal-status-pill status-pill status-default';
            modalStatusPill.style.display = 'none';
        }

        if (modalViewButton) {
            modalViewButton.href = '#';
            modalViewButton.style.display = 'none';
        }
    }

    function getStatusClassName(statusText = '') {
        const normalized = statusText.toLowerCase();

        if (normalized.includes('auth')) return 'status-authenticated';
        if (normalized.includes('await')) return 'status-awaiting';
        if (normalized.includes('reject')) return 'status-rejected';

        return 'status-default';
    }

    function applyStatusPill(pillElement, statusText) {
        if (!pillElement) return;

        const baseClass = pillElement.classList.contains('ecobrick-status-pill')
            ? 'ecobrick-status-pill status-pill'
            : 'modal-status-pill status-pill';
        const statusClass = getStatusClassName(statusText);

        pillElement.className = `${baseClass} ${statusClass}`;
        pillElement.textContent = statusText || 'Status unknown';
        pillElement.style.display = 'inline-flex';
    }

    function openViewEcobricV2(brickData) {
        if (!brickData) return;

        const modal = document.getElementById('form-modal-message-v2');
        const photoContainer = modal?.querySelector('.modal-photo-v2');
        const messageContainer = modal?.querySelector('.modal-message-v2');
        const modalStatusPill = modal?.querySelector('.modal-status-pill');
        const modalViewButton = modal?.querySelector('.modal-view-button');

        if (!modal || !photoContainer || !messageContainer) return;

        photoContainer.replaceChildren();
        messageContainer.replaceChildren();

        const photoWrapper = document.createElement('div');
        photoWrapper.className = 'ecobrick-photo-wrapper';

        const img = document.createElement('img');
        img.src = `${brickData.selfie_photo_url}?v=${brickData.photo_version ?? ''}`;
        img.alt = `Ecobrick selfie for serial ${brickData.serial_no || ''}`;

        photoWrapper.appendChild(img);
        photoContainer.appendChild(photoWrapper);

        const metaWrapper = document.createElement('div');
        metaWrapper.className = 'ecobrick-meta-v2';

        const details = document.createElement('p');
        const weightTxt = brickData.weight_g ? `${Number(brickData.weight_g).toLocaleString()} gram` : 'an unknown weight';
        const makerTxt = brickData.ecobricker_maker || 'an unknown maker';
        const locationTxt = brickData.location_display || 'an undisclosed location';
        const serialTxt = brickData.serial_no || 'an unlisted serial';
        details.textContent = `This ${weightTxt} ecobrick ${serialTxt} was made by ${makerTxt} in ${locationTxt}.`;
        metaWrapper.appendChild(details);

        photoContainer.appendChild(metaWrapper);

        const viewHref = `brik.php?serial_no=${encodeURIComponent(brickData.serial_no || '')}`;
        if (modalViewButton) {
            modalViewButton.href = viewHref;
            modalViewButton.setAttribute('aria-label', `Open ecobrick ${brickData.serial_no || ''} details`);
            modalViewButton.style.display = 'inline-flex';
        }

        applyStatusPill(modalStatusPill, brickData.status);

        modal.classList.remove('modal-hidden');
        modal.classList.add('modal-shown');

        document.getElementById('page-content')?.classList.add('blurred');
        document.getElementById('footer-full')?.classList.add('blurred');
        document.body.classList.add('modal-open');
    }

    document.addEventListener('DOMContentLoaded', () => {
        updateFeaturedBricks(currentFeaturedBricks);

        loadNextButton?.addEventListener('click', () => {
            featuredOffset += FEATURED_LIMIT;
            updateFeaturedControls();
            loadFeaturedBatch(featuredOffset);
        });

        previousButton?.addEventListener('click', () => {
            featuredOffset = Math.max(0, featuredOffset - FEATURED_LIMIT);
            updateFeaturedControls();
            loadFeaturedBatch(featuredOffset);
        });

        sliderElement?.addEventListener('touchstart', handleSliderTouchStart);
        sliderElement?.addEventListener('touchend', handleSliderTouchEnd);
        mobileSliderQuery.addEventListener('change', handleSliderQueryChange);
        mobileSliderQuery.addEventListener('change', repositionLatestPanel);

        setActiveSlide(0);
        startSliderInterval();
        repositionLatestPanel();
    });


    $(document).ready(function() {
        var ecobrickerId = "<?php echo htmlspecialchars($ecobricker_id); ?>"; // Get the logged-in user's ecobricker_id
        var userLang = "<?php echo htmlspecialchars($lang); ?>"; // Get the user's language

        $("#latest-ecobricks").DataTable({
            "responsive": true,
            "serverSide": true,
            "processing": true,
            "ajax": {
                "url": "../api/fetch_my_briks.php",
                "type": "POST",
                "data": function(d) {
                    d.ecobricker_id = ecobrickerId; // Pass the ecobricker_id to filter the results to the user's ecobricks
                }
            },
            "pageLength": 7, // Show 7 briks per page
            "language": {
                "emptyTable": "It looks like you haven't logged any ecobricks yet!",
                "lengthMenu": "Show _MENU_ briks",
                "search": "",
                "info": "Showing _START_ to _END_ of _TOTAL_ ecobricks",
                "infoEmpty": "No ecobricks available",
                "loadingRecords": "Loading ecobricks...",
                "processing": "Processing...",
                "paginate": {
                    "first": "First",
                    "last": "Last",
                    "next": "Next",
                    "previous": "Previous"
                }
            },
            "columns": [
                { "data": "ecobrick_thumb_photo_url", "orderable": false },
                { "data": "weight_g" },
                { "data": "volume_ml" },
                { "data": "density" },
                { "data": "date_logged_ts" },
                { "data": "status", "orderable": false },
                {
                    "data": "serial_no",
                    "render": function(data, type, row) {
                        if (type === 'display') {
                            return `<button class="serial-button" data-serial-no="${data}" data-status="${row.status}" title="View Ecobrick Details">${data}</button>`;
                        }
                        return data;
                    },
                    "orderable": false
                }
            ],
            "columnDefs": [
                { "className": "all", "targets": [0, 1, 6] }, // Ensure Brik (thumbnail), Weight, and Serial always display
                { "className": "min-tablet", "targets": [2, 3, 4] } // These fields can be hidden first on smaller screens
            ],
            "initComplete": function() {
                var searchBox = $("div.dataTables_filter input");
                searchBox.attr("placeholder", "Search your briks...");

                // Add event listener for clicks on the serial number buttons
                $('#latest-ecobricks tbody').on('click', '.serial-button', function() {
                    var serialNo = $(this).data('serial-no');
                    var status = $(this).data('status');
                    viewEcobrickActions(serialNo, status, userLang);
                });
            }
        });
    });
 
document.addEventListener('DOMContentLoaded', () => {
    const notice = document.getElementById('registered-notice');
    const panel = document.getElementById('registered-notice-panel');
    const closeBtn = notice?.querySelector('.notice-close');

    closeBtn?.addEventListener('click', () => {
        if (panel) {
            panel.style.display = 'none';
        } else if (notice) {
            notice.style.display = 'none';
        }
    });
});


// REGISTRATION (TRAININGS)
$(document).ready(function() {
    let table = $("#trainee-trainings").DataTable({
        "pageLength": 7,
        "searching": false,
        "lengthChange": false,
        "responsive": {
            "details": {
                "type": "column",
                "target": "tr"
            }
        },
        "language": {
            "emptyTable": "You haven't registered for any trainings yet.",
            "info": "Showing _START_ to _END_ of _TOTAL_ trainings",
            "infoEmpty": "No trainings available",
            "loadingRecords": "Loading trainings...",
            "processing": "Processing...",
            "paginate": {
                "first": "First",
                "last": "Last",
                "next": "Next",
                "previous": "Previous"
            }
        },
        "columnDefs": [
            { "responsivePriority": 1, "targets": 0 },
            { "responsivePriority": 2, "targets": 1 },
            { "className": "none", "targets": [2, 3, 4] },
            { "orderable": false, "targets": [3, 4] }
        ]
    });
});


function openRegisteredTrainingsModal(trainingId, trainingLocation) {
    const modal = document.getElementById('form-modal-message');
    const modalBox = document.getElementById('modal-content-box');

    // Show the modal
    modal.style.display = 'flex';
    modalBox.style.flexFlow = 'column';
    modalBox.style.alignItems = 'center'; // Center content
    modalBox.style.textAlign = 'center';

    // Lock scrolling for the body and blur background
    document.getElementById('page-content')?.classList.add('blurred');
    document.getElementById('footer-full')?.classList.add('blurred');
    document.body.classList.add('modal-open');

    // Escape function to prevent XSS
    function escapeHTML(str) {
        return str.replace(/</g, "&lt;").replace(/>/g, "&gt;");
    }

    // Set up initial loading structure
    modalBox.innerHTML = `
        <p>Loading training details...</p>
    `;

    // Fetch training details via AJAX
    fetch(`../api/fetch_registered_training.php?training_id=${trainingId}`)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                modalBox.innerHTML = `<p style="color:red;">${escapeHTML(data.error)}</p>`;
                return;
            }

            // Build modal content
            modalBox.innerHTML = `
                <img src="${escapeHTML(data.feature_photo1_tmb)}" alt="Training Image"
                    style="width: 500px; max-width: 100%; border-radius: 8px; margin-bottom: 20px;">

                <h3>${escapeHTML(data.training_title)}</h3>

                <p>${escapeHTML(data.training_type)} is being led by ${escapeHTML(data.lead_trainer)}
                   on ${escapeHTML(data.training_date)} at ${escapeHTML(data.training_location)} at ${escapeHTML(data.training_time_txt)}.</p>

                <button onclick="window.open('${escapeHTML(data.zoom_link)}', '_blank')"
                    style="margin: 10px; padding: 10px 20px; font-size: 16px; background-color: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer;">
                    Launch Zoom
                </button>

                <button onclick="window.open('${escapeHTML(data.moodle_url)}', '_blank')"
                    style="margin: 10px; padding: 10px 20px; font-size: 16px; background-color: #28a745; color: white; border: none; border-radius: 5px; cursor: pointer;">
                    Launch Moodle Agenda
                </button>

                <br><br>
                <p style="font-size:0.9em">${escapeHTML(data.zoom_link_full)}</p>
            `;
        })
        .catch(error => {
            modalBox.innerHTML = `<p style="color:red;">Error loading training details: ${escapeHTML(error.message)}</p>`;
        });

    // Show the modal
    modal.classList.remove('modal-hidden');
}

function openTraineeSender(trainingId) {
    const modal = document.getElementById('form-modal-message');
    const modalBox = document.getElementById('modal-content-box');

    modal.style.display = 'flex';
    modalBox.style.flexFlow = 'column';

    document.getElementById('page-content')?.classList.add('blurred');
    document.getElementById('footer-full')?.classList.add('blurred');
    document.body.classList.add('modal-open');

    modalBox.innerHTML = `<p>Loading message...</p>`;

    fetch(`../api/fetch_registered_training.php?training_id=${trainingId}`)
        .then(r => r.json())
        .then(data => {
            if (data.error) {
                modalBox.innerHTML = `<p style="color:red;">${escapeHTML(data.error)}</p>`;
                return;
            }

            const msg = `Hi there $first_name,\n\nThank you again for registering for our ${escapeHTML(data.training_title)} event!  \n\nThis is a reminder that today, at ${escapeHTML(data.training_time_txt)} the ${escapeHTML(data.training_type)} begins!\n\nThe training is on Zoom.  Here's the full zoom link and invite you will need to access:\n\n------------------------\n\n${escapeHTML(data.zoom_link_full)}\n\n------------------------\n\nWe'll be opening up the meeting 15 minutes earlier to test systems and audio.  Feel free to join early for a meet and greet.\n\nMeanwhile, we're also setting up a support chat for the week.  I don't know about you, but I've got a lot of plastic saved up and it needs packing.  So after the workshop we're going to use the group to let you (and us!) share our ecobricking progress and ask questions.\n\nWe do our best to avoid meta products in the same way we avoid single-use plastic products, so sorry no whatsapp.  We use Signal (a free, open-source, foundation-run equivalent).  Click the link to join the group now or after the workshop:\n\nhttps://signal.group/#CjQKICIVvzmbBXqB7_9-5XyXd53zbdw7RLqVWKbQ8UzX2EkREhC0_jo3SCAr40xIO_jePrmT\n\nUnlike some of our GEA workshops, no need to bring anything to this workshop except your curiousity.  It will be interactive, so be prepared to share and anwser questions via mic and via chat.\n\nAlright, see you soon!\n\n${escapeHTML(data.lead_trainer)}\n${escapeHTML(data.trainer_contact_email)}`;

            const defaultTitle = `Reminder: ${escapeHTML(data.training_title)} starts today`;

            modalBox.innerHTML = `
                <h4 style="text-align:center;">Send a message to Participants</h4>
                <p style="text-align:center;">Use this quick tool and default message to send a message to everyone who has signed up for the training</p>
                <input id="trainee-title" type="text" style="width:100%;margin-bottom:10px;" value="${defaultTitle}" />
                <textarea id="trainee-message" style="white-space:pre-wrap;text-align:left;width:100%;height:260px;">${msg}</textarea>
                <button id="trainee-test-send" class="confirm-button enabled" style="min-width:360px;margin-top:10px;">Test to: ${escapeHTML(data.trainer_contact_email)}</button>
                <button id="trainee-all-send" class="confirm-button enabled" style="min-width:360px;margin-top:10px;">Send Email to All</button>
                <div id="trainee-send-status" style="margin-top:10px;text-align:center;"></div>
            `;

            document.getElementById('trainee-test-send').addEventListener('click', () => sendTraineeEmails(trainingId, true));
            document.getElementById('trainee-all-send').addEventListener('click', () => sendTraineeEmails(trainingId, false));
        })
        .catch(err => {
            modalBox.innerHTML = `<p style="color:red;">Error loading training: ${escapeHTML(err.message)}</p>`;
        });

    modal.classList.remove('modal-hidden');
}

function sendTraineeEmails(trainingId, isTest) {
    const statusDiv = document.getElementById('trainee-send-status');
    const btn = isTest ? document.getElementById('trainee-test-send') : document.getElementById('trainee-all-send');
    btn.innerHTML = '<div class="spinner-photo-loading"></div>';

    fetch('../processes/trainee_sender.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({
            training_id: trainingId,
            test: isTest ? 1 : 0,
            title: document.getElementById('trainee-title').value,
            message: document.getElementById('trainee-message').value
        })
    })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                btn.textContent = '✅ Message sent!';
                btn.style.background = 'green';
            } else {
                btn.textContent = '🙄Message failed to send';
                btn.style.background = 'red';
            }
            if (data.message) {
                statusDiv.innerHTML += `<p>${escapeHTML(data.message)}</p>`;
            }
        })
        .catch(err => {
            btn.textContent = '🙄Message failed to send';
            btn.style.background = 'red';
            statusDiv.innerHTML += `<p>${escapeHTML(err.message)}</p>`;
        });
}


$(document).ready(function() {
    const noticeToggle = document.getElementById('dash-notice-toggle');
    const noticeForm = document.getElementById('dash-notice-form');
    const supportToggle = document.getElementById('support-chat-toggle');
    const supportForm = document.getElementById('support-chat-form');

    if (noticeToggle && noticeForm) {
        const setNoticeState = (expanded) => {
            noticeToggle.setAttribute('aria-expanded', expanded ? 'true' : 'false');
            noticeToggle.classList.toggle('down', expanded);
            noticeForm.style.display = expanded ? 'block' : 'none';
        };

        setNoticeState(false);

        noticeToggle.addEventListener('click', () => {
            const isExpanded = noticeToggle.getAttribute('aria-expanded') === 'true';
            setNoticeState(!isExpanded);
        });
    }

    if (supportToggle && supportForm) {
        const setSupportState = (expanded) => {
            supportToggle.setAttribute('aria-expanded', expanded ? 'true' : 'false');
            supportToggle.classList.toggle('down', expanded);
            supportForm.style.display = expanded ? 'flex' : 'none';
        };

        setSupportState(false);

        supportToggle.addEventListener('click', () => {
            const isExpanded = supportToggle.getAttribute('aria-expanded') === 'true';
            setSupportState(!isExpanded);
        });
    }

    $("#trainer-trainings").DataTable({
        "pageLength": 5,
        "searching": false,
        "lengthChange": false,
        "order": [[1, "desc"]],
        "language": {
            "emptyTable": "You are not a trainer for any trainings yet.",
            "lengthMenu": "Show _MENU_ trainings",
            "info": "Showing _START_ to _END_ of _TOTAL_ trainings",
            "infoEmpty": "No trainings available",
            "loadingRecords": "Loading trainings...",
            "processing": "Processing...",
            "paginate": {
                "first": "First",
                "last": "Last",
                "next": "Next",
                "previous": "Previous"
            }
        },
        "columnDefs": [
            { "type": "date", "targets": 1 },
            { "orderable": false, "targets": [2, 3] }
        ]
    });
});





function openTraineesModal(trainingId, trainingTitle) {
    const modal = document.getElementById('form-modal-message');
    const modalBox = document.getElementById('modal-content-box');

    // Show the modal
    modal.style.display = 'flex';
    modalBox.style.flexFlow = 'column';

    // Lock scrolling for the body and blur background
    document.getElementById('page-content')?.classList.add('blurred');
    document.getElementById('footer-full')?.classList.add('blurred');
    document.body.classList.add('modal-open');

    // Set up modal structure
    modalBox.innerHTML = `
        <h4 style="text-align:center;">Registered Trainees for <br> ${escapeHTML(trainingTitle)}</h4>
        <div id="trainee-table-container" style="max-height: 100%; overflow-y: auto; margin-bottom: 20px;"></div>
        <button id="message-participants-btn" class="confirm-button enabled" style="margin-top:10px;">Message Participants...</button>
    `;

    document.getElementById('message-participants-btn').addEventListener('click', () => {
        closeInfoModal();
        openTraineeSender(trainingId);
    });

    // Fetch trainees via AJAX
    fetch(`../api/fetch_training_trainees.php?training_id=${trainingId}`)
        .then(response => response.json())
        .then(data => {
            if (!data || data.length === 0) {
                document.getElementById('trainee-table-container').innerHTML = `<p style="text-align:center;">No trainees registered yet.</p>`;
                return;
            }

            if (data.error) {
                document.getElementById('trainee-table-container').innerHTML = `<p style="text-align:center; color: red;">${escapeHTML(data.error)}</p>`;
                return;
            }

            // Build the DataTable HTML
            let tableHTML = `
                <table id="trainees-table" class="display" style="width:100%;">
                    <thead>
                        <tr>
                            <th>First Name</th>
                            <th>Email</th>
                            <th>GEA Status</th>
                            <th>RSVP Status</th>
                            <th>Date Registered</th>
                        </tr>
                    </thead>
                    <tbody>
            `;

            data.forEach(trainee => {
                tableHTML += `
                    <tr>
                        <td>${escapeHTML(trainee.first_name || '-')}</td>
                        <td>${escapeHTML(trainee.email_addr || '-')}</td>
                        <td>${escapeHTML(trainee.gea_status || '-')}</td>
                        <td>${escapeHTML(trainee.rsvp_status || '-')}</td>
                        <td>${escapeHTML(trainee.date_registered || '-')}</td>
                    </tr>
                `;
            });

            tableHTML += '</tbody></table>';

            // Insert the table into the modal
            document.getElementById('trainee-table-container').innerHTML = tableHTML;

            // Initialize the DataTable with scrollability
            $('#trainees-table').DataTable({
                paging: true,
                searching: true,
                info: true,
                scrollX: true,
                scrollY: "100%", // Makes it scrollable within the modal
                scrollCollapse: true
            });
        })
        .catch(error => {
            document.getElementById('trainee-table-container').innerHTML = `<p style="text-align:center; color: red;">Error loading trainees: ${escapeHTML(error.message)}</p>`;
        });

    // Show the modal
    modal.classList.remove('modal-hidden');
}



</script>







<script type="text/javascript">

// JavaScript to determine the user's time of day and display an appropriate greeting
window.onload = function() {
    var now = new Date();
    var hours = now.getHours();
    var greeting;
    var lang = "<?php echo htmlspecialchars($lang); ?>"; // Get the language from PHP

    // Determine greeting based on the time of day
    if (hours < 12) {
        switch (lang) {
            case 'fr':
                greeting = "Bonjour";
                break;
            case 'es':
                greeting = "Buenos días";
                break;
            case 'id':
                greeting = "Selamat pagi";
                break;
            case 'en':
            default:
                greeting = "Good morning";
                break;
        }
    } else if (hours < 18) {
        switch (lang) {
            case 'fr':
                greeting = "Bon après-midi";
                break;
            case 'es':
                greeting = "Buenas tardes";
                break;
            case 'id':
                greeting = "Selamat siang";
                break;
            case 'en':
            default:
                greeting = "Good afternoon";
                break;
        }
    } else {
        switch (lang) {
            case 'fr':
                greeting = "Bonsoir";
                break;
            case 'es':
                greeting = "Buenas noches";
                break;
            case 'id':
                greeting = "Selamat malam";
                break;
            case 'en':
            default:
                greeting = "Good evening";
                break;
        }
    }

    document.getElementById("greeting").innerHTML = greeting + " <?php echo htmlspecialchars($first_name); ?>!";
}



// Main greeting function to determine the user's time of day and display an appropriate greeting
function mainGreeting() {
    var now = new Date();
    var hours = now.getHours();
    var greeting;
    var lang = "<?php echo htmlspecialchars($lang); ?>"; // Get the language from PHP

    // Determine greeting based on the time of day
    if (hours < 12) {
        switch (lang) {
            case 'fr':
                greeting = "Bonjour";
                break;
            case 'es':
                greeting = "Buenos días";
                break;
            case 'id':
                greeting = "Selamat pagi";
                break;
            case 'en':
            default:
                greeting = "Good morning";
                break;
        }
    } else if (hours < 18) {
        switch (lang) {
            case 'fr':
                greeting = "Bon après-midi";
                break;
            case 'es':
                greeting = "Buenas tardes";
                break;
            case 'id':
                greeting = "Selamat siang";
                break;
            case 'en':
            default:
                greeting = "Good afternoon";
                break;
        }
    } else {
        switch (lang) {
            case 'fr':
                greeting = "Bonsoir";
                break;
            case 'es':
                greeting = "Buenas noches";
                break;
            case 'id':
                greeting = "Selamat malam";
                break;
            case 'en':
            default:
                greeting = "Good evening";
                break;
        }
    }

    document.getElementById("greeting").innerHTML = greeting + " <?php echo htmlspecialchars($first_name); ?>!";
}


// Secondary greeting function to provide additional dynamic content
function secondaryGreeting() {
    // Retrieve the language setting from the server-side PHP variable
    const lang = '<?php echo htmlspecialchars($lang ?? '', ENT_QUOTES, 'UTF-8'); ?>';
    const ecobricksMade = <?php echo (int)($ecobricks_made ?? 0); ?>;
    const locationFullTxt = '<?php echo htmlspecialchars($user_location_full ?? '', ENT_QUOTES, 'UTF-8'); ?>';
    const totalWeight = '<?php echo number_format($total_weight_kg ?? 0, 1); ?>';
    const netDensity = '<?php echo number_format($net_density ?? 0, 2); ?>';

    // Determine the appropriate language object based on the current language setting
    let translations;
    switch (lang) {
        case 'fr':
            translations = fr_Page_Translations;
            break;
        case 'es':
            translations = es_Page_Translations;
            break;
        case 'id':
            translations = id_Page_Translations;
            break;
        default:
            translations = en_Page_Translations; // Default to English if no match is found
    }

    // Determine the message to display based on the number of ecobricks made
    let message;
    if (ecobricksMade < 1) {
        message = translations.welcomeBeta;
    } else {
        // Replace placeholders with dynamic values
        message = translations.loggedEcobricks
            .replace('{ecobricksMade}', ecobricksMade)
            .replace('{locationFullTxt}', locationFullTxt)
            .replace('{totalWeight}', totalWeight)
            .replace('{netDensity}', netDensity);
    }

    // Set the inner HTML of the subgreeting paragraph
    document.getElementById('subgreeting').innerHTML = message;
}




//GET MESSAGE COUNT FOR NOTIFICATION
const userId = "<?php echo $buwana_id; ?>";

function setMessageCountNotification() {
    $.ajax({
        url: '../messenger/check_message_stats.php',
        method: 'GET',
        data: { user_id: userId }, // Assuming userId is globally defined
        success: function(response) {
            if (response.status === 'success') {
                const unreadMessages = response.unread_messages;

                // Update the messenger button with the unread message count
                const messengerButton = $('#messenger-button');
                if (unreadMessages > 0) {
                    messengerButton.html(`Messenger BETA <span style="color:red">+${unreadMessages}</span>`);
                } else {
                    messengerButton.text('Messenger BETA'); // Reset text if no unread messages
                }
            } else {
                console.error('Error retrieving message count:', response.message);
            }
        },
        error: function(error) {
            console.error('Error in AJAX request:', error);
        }
    });
}

// Pass PHP variable to JavaScript
const geaStatus = "<?php echo $gea_status; ?>";

window.onload = function() {
    mainGreeting();
    secondaryGreeting();

    // Only call setMessageCountNotification if geaStatus contains "admin"
    if (geaStatus.toLowerCase().includes("admin")) {
        setMessageCountNotification();
    }
};

</script>



<script>
function viewEcobrickActions(serial_no, status, lang) {
    console.log("Button clicked with serial number:", serial_no);
    const modal = document.getElementById('form-modal-message');
    const modalBox = document.getElementById('modal-content-box');
    let messageContainer = document.querySelector('.modal-message');
    if (!messageContainer) {
        messageContainer = document.createElement('div');
        messageContainer.className = 'modal-message';
        modalBox.appendChild(messageContainer);
    }

    // Clear existing content in the modal
    messageContainer.innerHTML = '';

    // Determine the appropriate language object
    let translations;
    switch (lang) {
        case 'fr':
            translations = fr_Translations;
            break;
        case 'es':
            translations = es_Translations;
            break;
        case 'id':
            translations = id_Translations;
            break;
        default:
            translations = en_Translations; // Default to English
    }

    // Properly encode serial number for URL safety
    let encodedSerialNo = encodeURIComponent(serial_no);
    let ecobrickURL = `https://gobrik.com/en/brik.php?serial_no=${encodedSerialNo}`;

   // Construct the content (stack of buttons) using string concatenation to avoid issues
let content = '';

content += '<a class="ecobrick-action-button" href="brik.php?serial_no=' + encodedSerialNo + '" data-lang-id="013-view-ecobrick-post">';
content += '🔍 ' + translations['013-view-ecobrick-post'];
content += '</a>';

// Conditionally display the "Edit Ecobrick" button if the status is not authenticated
if (status !== "authenticated") {
    content += '<a class="ecobrick-action-button" href="log.php?retry=' + encodedSerialNo + '" data-lang-id="015-edit-ecobrick">';
    content += '✏️ ' + translations['015-edit-ecobrick'];
    content += '</a>';
}

// Add the "Share Ecobrick" button
content += '<a class="ecobrick-action-button" href="javascript:void(0);" onclick="copyEcobrickLink(\'' + ecobrickURL + '\', this)" data-lang-id="016-share-ecobrick">';
content += '🔗 ' + (translations['016-share-ecobrick'] || 'Share Ecobrick');
content += '</a>';

// Add the "Delete Ecobrick" button
content += '<a class="ecobrick-action-button deleter-button" href="javascript:void(0);" onclick="deleteEcobrick(\'' + encodedSerialNo + '\')" data-lang-id="014-delete-ecobrick">';
content += '❌ ' + translations['014-delete-ecobrick'];
content += '</a>';

// Insert the content into the message container
messageContainer.innerHTML = content;


    // Display the modal
    modal.style.display = 'flex';
    modalBox.style.background = 'none';
    document.getElementById('page-content').classList.add('blurred');
    document.getElementById('footer-full').classList.add('blurred');
    document.body.classList.add('modal-open');
}

// Function to copy the Ecobrick URL to clipboard and change the button text
function copyEcobrickLink(url, button) {
    // Use the modern clipboard API, if available
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(url)
            .then(() => {
                // Change the button text to "URL Copied!"
                button.innerHTML = 'URL Copied!';
                // After 1 second, close the modal
                setTimeout(closeInfoModal, 1000);
            })
            .catch(err => {
                console.error('Failed to copy: ', err);
                alert('Error copying URL. Please try again.');
            });
    } else {
        // Fallback for older browsers
        const tempInput = document.createElement('input');
        tempInput.value = url;
        document.body.appendChild(tempInput);
        tempInput.select();
        document.execCommand('copy');
        document.body.removeChild(tempInput);

        // Change the button text to "URL Copied!"
        button.innerHTML = '🤩 URL Copied!';

        // After 1 second, close the modal
        setTimeout(closeInfoModal, 1000);
    }
}



// Function to delete an ecobrick

function deleteEcobrick(serial_no) {
    // Ask the user for confirmation
    if (confirm('Are you sure you want to delete this ecobrick from the database? This cannot be undone.')) {
        // Send the delete request via fetch
        fetch('delete-ecobrick.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                'serial_no': serial_no, // Send serial_no
                'action': 'delete_ecobrick' // Include action for clarity
            })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok.');
            }
            return response.json(); // Expecting JSON from the server
        })
        .then(data => {
            if (data.success) {
                alert('Your ecobrick has been successfully deleted.');
                window.location.href = 'dashboard.php'; // Redirect after deletion
            } else {
                alert('There was an error deleting the ecobrick: ' + data.error);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('There was an error processing your request.');
        });
    }
}

function actionsTrainingModal(buttonElem, trainingId) {
    const modal = document.getElementById('form-modal-message');
    const modalBox = document.getElementById('modal-content-box');
    modalBox.innerHTML = '';
    const messageContainer = document.createElement('div');
    messageContainer.className = 'modal-message';
    modalBox.appendChild(messageContainer);

    let url = `register.php?id=${trainingId}`;
    let content = '';
    content += `<a class="ecobrick-action-button" href="${url}" target="_blank">🔍 View Course Listing</a>`;
    content += `<a class="ecobrick-action-button" href="launch-training.php?id=${trainingId}">✏️ Edit Course</a>`;
    content += `<a class="ecobrick-action-button" href="training-report.php?training_id=${trainingId}">📝 Edit Report</a>`;
    content += `<a class="ecobrick-action-button" href="javascript:void(0);" onclick="copyCourseListingURL(${trainingId}, this)">🔗 Copy Course Listing URL</a>`;

    const showReport = buttonElem.getAttribute('data-show-report') === '1';
    const readyToShow = buttonElem.getAttribute('data-ready-to-show') === '1';
    const reportChecked = showReport ? 'checked' : '';
    const listingChecked = readyToShow ? 'checked' : '';

    content += `<div class="training-toggle-row">
                    <span class="training-toggle-title">Publish report on ecobricks.org:</span>
                    <label class="toggle-switch">
                        <input type="checkbox" class="training-report-toggle" data-training-id="${trainingId}" ${reportChecked}>
                        <span class="slider"></span>
                    </label>
                </div>`;
    content += `<div class="training-toggle-row">
                    <span class="training-toggle-title">List on GoBrik courses:</span>
                    <label class="toggle-switch">
                        <input type="checkbox" class="training-listing-toggle" data-training-id="${trainingId}" ${listingChecked}>
                        <span class="slider"></span>
                    </label>
                </div>`;

    content += `<a class="ecobrick-action-button deleter-button" href="javascript:void(0);" onclick="deleteTraining(${trainingId})">❌ Delete Training</a>`;

    messageContainer.innerHTML = content;
    const newReportToggle = messageContainer.querySelector('.training-report-toggle');
    const newListingToggle = messageContainer.querySelector('.training-listing-toggle');
    if (newReportToggle) addReportToggleListener(newReportToggle, buttonElem);
    if (newListingToggle) addListingToggleListener(newListingToggle, buttonElem);
    modal.style.display = 'flex';
    modalBox.style.background = 'none';
    document.getElementById('page-content').classList.add('blurred');
    document.getElementById('footer-full').classList.add('blurred');
    document.body.classList.add('modal-open');
}

function copyCourseListingURL(trainingId, button) {
    const url = `https://gobrik.com/en/register.php?id=${trainingId}`;
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(url).then(() => {
            button.innerHTML = 'URL Copied!';
            setTimeout(closeInfoModal, 1000);
        }).catch(err => {
            console.error('Failed to copy: ', err);
            alert('Error copying URL. Please try again.');
        });
    } else {
        const tempInput = document.createElement('input');
        tempInput.value = url;
        document.body.appendChild(tempInput);
        tempInput.select();
        document.execCommand('copy');
        document.body.removeChild(tempInput);
        button.innerHTML = 'URL Copied!';
        setTimeout(closeInfoModal, 1000);
    }
}

function deleteTraining(trainingId) {
    if (confirm('Are you sure you want to delete this training? This cannot be undone.')) {
        fetch('../processes/delete_training.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                'training_id': trainingId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Training deleted successfully.');
                window.location.reload();
            } else {
                alert('Error deleting training: ' + data.error);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('There was an error processing your request.');
        });
    }
}

function addReportToggleListener(toggle, buttonElem) {
    toggle.addEventListener('change', function() {
        const trainingId = this.dataset.trainingId;
        const showReport = this.checked ? 1 : 0;
        fetch('../api/toggle_training_report.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                'training_id': trainingId,
                'show_report': showReport
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                buttonElem.setAttribute('data-show-report', showReport);
            } else {
                alert('Error updating status: ' + data.error);
                toggle.checked = !toggle.checked; // revert on failure
            }
        })
        .catch(() => {
            alert('There was an error processing your request.');
            toggle.checked = !toggle.checked;
        });
    });
}

function addListingToggleListener(toggle, buttonElem) {
    toggle.addEventListener('change', function() {
        const trainingId = this.dataset.trainingId;
        const readyToShow = this.checked ? 1 : 0;
        fetch('../api/toggle_training_listing.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                'training_id': trainingId,
                'ready_to_show': readyToShow
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                buttonElem.setAttribute('data-ready-to-show', readyToShow);
            } else {
                alert('Error updating listing status: ' + data.error);
                toggle.checked = !toggle.checked; // revert on failure
            }
        })
        .catch(() => {
            alert('There was an error processing your request.');
            toggle.checked = !toggle.checked;
        });
    });
}






</script>

<?php if ($is_admin): ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const chartEl = document.getElementById('earthen-mailer-donut');
    if (!chartEl || typeof Chart === 'undefined') {
        return;
    }

    const stats = <?php echo json_encode($ghost_member_stats, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
    const totals = {
        total: Number(stats.total) || 0,
        sent001: Number(stats.sent_001) || 0,
        sent002: Number(stats.sent_002) || 0,
    };
    const remaining = Math.max(totals.total - totals.sent001 - totals.sent002, 0);

    new Chart(chartEl, {
        type: 'doughnut',
        data: {
            labels: ['sent-001', 'sent-002', 'Unsent'],
            datasets: [{
                data: [totals.sent001, totals.sent002, remaining],
                backgroundColor: ['#4caf50', '#2196f3', '#e0e0e0'],
                borderWidth: 0
            }]
        },
        options: {
            plugins: {
                legend: {
                    position: 'bottom'
                }
            },
            cutout: '55%',
            maintainAspectRatio: false
        }
    });
});
</script>
<?php endif; ?>


<script>
    // Check if the tour has been taken
    document.addEventListener('DOMContentLoaded', function() {
        const tourButton = document.getElementById('take-gobrik-tour');
        if (!tourButton) return;

        if (localStorage.getItem('gobrikTourTaken') === 'true') {
            tourButton.style.display = 'none';
        }
    });

    // Function to start the guided tour and set localStorage
    function startTour() {
        guidedTour();  // Call your guided tour function

        // Record in localStorage that the tour has been taken
        localStorage.setItem('gobrikTourTaken', 'true');

        // Hide the button after it is clicked
        const tourButton = document.getElementById('take-gobrik-tour');
        if (tourButton) {
            tourButton.style.display = 'none';
        }
    }

    // Example function for guided tour (replace with your actual guidedTour function)
    function guidedTour() {
        // Your guided tour logic here
        alert("Starting the GoBrik guided tour!");
    }
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const slider = document.getElementById('ecobrick-slider');
    if (!slider) return;

    const slides = slider.querySelectorAll('.slide');
    if (!slides.length) return;

    const mobileQuery = window.matchMedia('(max-width: 768px)');
    let currentSlide = 0;
    let intervalId = null;
    let startX = 0;

    function setActiveSlide(index) {
        slides[currentSlide].classList.remove('active');
        currentSlide = (index + slides.length) % slides.length;
        slides[currentSlide].classList.add('active');
    }

    function startInterval() {
        if (intervalId || slides.length <= 1 || !mobileQuery.matches) return;
        intervalId = setInterval(() => setActiveSlide(currentSlide + 1), 3000);
    }

    function stopInterval() {
        if (intervalId) {
            clearInterval(intervalId);
            intervalId = null;
        }
    }

    mobileQuery.addEventListener('change', (event) => {
        if (event.matches) {
            startInterval();
        } else {
            stopInterval();
            currentSlide = 0;
            slides.forEach((slide, idx) => slide.classList.toggle('active', idx === 0));
        }
    });

    slider.addEventListener('touchstart', e => {
        if (!mobileQuery.matches) return;
        startX = e.touches[0].clientX;
        stopInterval();
    });

    slider.addEventListener('touchend', e => {
        if (!mobileQuery.matches) return;
        const diff = e.changedTouches[0].clientX - startX;
        if (diff < -50) {
            setActiveSlide(currentSlide + 1);
        } else if (diff > 50) {
            setActiveSlide(currentSlide - 1);
        }
        startInterval();
    });

    slides.forEach((slide, idx) => slide.classList.toggle('active', idx === 0));
    startInterval();
});
</script>

<script>
function trainerReportAlert(firstName, trainingName, trainingDate, geaStatus, trainingId) {
    const notice = document.getElementById('registered-notice');
    if (!notice) return;
    const icon = document.getElementById('notice-icon');
    const textSpan = document.getElementById('notice-text');
    const closeBtn = notice.querySelector('.notice-close');

    if (!notice.dataset.originalBg) {
        notice.dataset.originalBg = notice.style.backgroundColor || window.getComputedStyle(notice).backgroundColor;
        notice.dataset.originalIcon = icon.textContent;
        notice.dataset.originalText = textSpan.innerHTML;
    }

    notice.style.backgroundColor = 'orange';
    icon.textContent = '⚠️';
    textSpan.innerHTML = `${firstName}! It looks like your course ${trainingName} is complete as of ${trainingDate}! As a GEA ${geaStatus} its important to complete and publish your Training Report. ` +
        `<a href="training-report.php?training_id=${trainingId}" style="margin-left:5px;text-decoration:underline;color:white;font-weight:bold;">Complete Report</a>`;

    closeBtn.addEventListener('click', function(e) {
        e.stopImmediatePropagation();
        notice.style.display = 'flex';
        notice.style.backgroundColor = notice.dataset.originalBg;
        icon.textContent = notice.dataset.originalIcon;
        textSpan.innerHTML = notice.dataset.originalText;
    }, { once: true });
}
</script>

<?php if (isset($pendingReport)): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    trainerReportAlert(
        <?php echo json_encode($first_name); ?>,
        <?php echo json_encode($pendingReport['title']); ?>,
        <?php echo json_encode($pendingReport['date']); ?>,
        <?php echo json_encode($gea_status); ?>,
        <?php echo json_encode($pendingReport['id']); ?>
    );
});
</script>
<?php endif; ?>

</body>
</html>
