<?php
require_once '../earthenAuth_helper.php';

// 🌍 Set up page environment
$lang = basename(dirname($_SERVER['SCRIPT_NAME']));
$version = '3.0';
$page = 'edit-project';
$lastModified = date("Y-m-d\TH:i:s\Z", filemtime(__FILE__));

// 🔐 Start session and verify Buwana JWT (auto-redirects if not logged in)
require_once '../auth/session_start.php';

// 🆔 Retrieve the authenticated user's Buwana ID
$buwana_id = $_SESSION['buwana_id'] ?? '';

// 📋 Require a project_id — this is always an edit, never a create
$project_id = isset($_GET['id']) ? (int)$_GET['id'] : null;
if (empty($project_id)) {
    header('Location: /en/dashboard.php');
    exit();
}

// 🧭 Buwana app registration check
$client_id = 'gbrk_f2c61a85a4cd4b8b89a7';
if (!empty($buwana_id)) {
    $api_endpoint = 'https://buwana.ecobricks.org/api/check_user_app_connection.php';
    $query = http_build_query([
        'buwana_id' => $buwana_id,
        'client_id' => $client_id,
        'lang'      => $lang ?? 'en'
    ]);
    $ch = curl_init("{$api_endpoint}?{$query}");
    if ($ch) {
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $api_response = curl_exec($ch);
        $curl_error   = curl_error($ch);
        $http_code    = curl_getinfo($ch, CURLINFO_HTTP_CODE);
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

// 🔗 Establish DB connections
require_once '../gobrikconn_env.php';
require_once '../buwanaconn_env.php';

// 🌎 Fetch user meta from Buwana
$user_continent_icon    = getUserContinent($buwana_conn, $buwana_id);
$earthling_emoji        = getUserEarthlingEmoji($buwana_conn, $buwana_id);
$user_location_watershed = getWatershedName($buwana_conn, $buwana_id);
$user_location_full     = getUserFullLocation($buwana_conn, $buwana_id);
$gea_status             = getGEA_status($buwana_id);
$user_roles             = getUser_Role($buwana_id);
$user_community_name    = getCommunityName($buwana_conn, $buwana_id);

// 👤 Look up user's GoBrik account
$sql_lookup_user = "SELECT first_name, ecobricks_made, ecobricker_id, location_full_txt, user_capabilities, full_name FROM tb_ecobrickers WHERE buwana_id = ?";
$stmt_lookup_user = $gobrik_conn->prepare($sql_lookup_user);
if ($stmt_lookup_user) {
    $stmt_lookup_user->bind_param("i", $buwana_id);
    $stmt_lookup_user->execute();
    $stmt_lookup_user->bind_result($first_name, $ecobricks_made, $ecobricker_id, $location_full_txt, $user_capabilities_raw, $full_name);
    $stmt_lookup_user->fetch();
    $stmt_lookup_user->close();
} else {
    die("Error preparing statement for tb_ecobrickers: " . $gobrik_conn->error);
}

// 📦 Fetch the project record (including all photo fields)
$project_lookup_sql = "SELECT
    project_name, description_short, description_long,
    briks_used, est_avg_brik_weight,
    project_type, construction_type,
    location_full, project_sort,
    community, project_admins, connected_ecobricks,
    start_dt, end_dt, location_lat, location_long,
    photo1_main, photo1_tmb,
    photo2_main, photo2_tmb,
    photo3_main, photo3_tmb,
    photo4_main, photo4_tmb,
    photo5_main, photo5_tmb,
    photo6_main, photo6_tmb
FROM tb_projects WHERE project_id = ?";

$project_lookup_stmt = $gobrik_conn->prepare($project_lookup_sql);
if ($project_lookup_stmt) {
    $project_lookup_stmt->bind_param("i", $project_id);
    $project_lookup_stmt->execute();
    $project_lookup_stmt->bind_result(
        $project_name_value, $description_short_value, $description_long_value,
        $briks_used_value, $est_avg_brik_weight_value,
        $project_type_value, $construction_type_value,
        $location_full_value, $project_sort_value,
        $community_value, $project_admins_value, $connected_ecobricks_value,
        $start_dt_value, $end_dt_value, $latitude_value, $longitude_value,
        $photo1_main, $photo1_tmb,
        $photo2_main, $photo2_tmb,
        $photo3_main, $photo3_tmb,
        $photo4_main, $photo4_tmb,
        $photo5_main, $photo5_tmb,
        $photo6_main, $photo6_tmb
    );
    $project_lookup_stmt->fetch();
    $project_lookup_stmt->close();
} else {
    die("Error preparing statement for tb_projects: " . $gobrik_conn->error);
}

// Redirect if project not found
if (empty($project_name_value)) {
    header('Location: /en/dashboard.php?error=not-found');
    exit();
}

// 🔒 Authorization: user must be in the project_admins list
$admin_ids_list = array_map('trim', explode(',', $project_admins_value ?? ''));
if (!in_array((string)$ecobricker_id, $admin_ids_list)) {
    header('Location: /en/dashboard.php?error=unauthorized');
    exit();
}

// 👥 Fetch existing project admins' names for the tag UI
$existing_admins = [];
$raw_admin_ids = array_filter(array_map('intval', $admin_ids_list));
if (!empty($raw_admin_ids)) {
    $placeholders = implode(',', array_fill(0, count($raw_admin_ids), '?'));
    $stmt_admin_names = $gobrik_conn->prepare("SELECT ecobricker_id, full_name FROM tb_ecobrickers WHERE ecobricker_id IN ({$placeholders})");
    if ($stmt_admin_names) {
        $types = str_repeat('i', count($raw_admin_ids));
        $stmt_admin_names->bind_param($types, ...$raw_admin_ids);
        $stmt_admin_names->execute();
        $res_admins = $stmt_admin_names->get_result();
        while ($row = $res_admins->fetch_assoc()) {
            $existing_admins[] = $row;
        }
        $stmt_admin_names->close();
    }
}

$saved = isset($_GET['saved']) && $_GET['saved'] === '1';
?>




<!DOCTYPE html>
<HTML lang="en">
<HEAD>
<META charset="UTF-8">
<?php $lang='en';?>
<?php $version='3.0';?>
<?php $page='edit-project';?>

<?php require_once ("../includes/edit-project-inc.php");?>


 <!-- PAGE CONTENT-->

<div id="form-submission-box" class="form-container-v2 landing-page-form" style="height:auto !important;">
    <div class="form-container">

        <?php if ($saved): ?>
        <div class="save-success-notice">
            Changes saved successfully! &nbsp; <a href="project.php?id=<?php echo (int)$project_id; ?>">View your project &rarr;</a>
        </div>
        <?php endif; ?>

        <div class="splash-form-content-block">
            <div class="splash-box">
                <div class="splash-heading" data-lang-id="001-splash-title">Edit Project</div>
            </div>
            <div class="splash-image">
                <img src="../svgs/building-methods.svg" style="width:65%" alt="There are many ways to build with ecobricks">
            </div>
        </div>

        <div class="lead-page-paragraph">
            <p data-lang-id="002-form-description">Update your project details and photos below. Changes are saved immediately when you click Save Changes.</p>
        </div>

        <form id="submit-form" method="post" action="../processes/edit_project.php" enctype="multipart/form-data" novalidate>

            <input type="hidden" name="project_id" value="<?php echo (int)$project_id; ?>">

            <!-- PROJECT NAME -->
            <div class="form-item" style="margin-top: 25px;">
                <label for="project_name" data-lang-id="005-project-name">Project Name:</label><br>
                <input type="text" id="project_name" name="project_name" aria-label="Project Name" title="Required. Max 255 characters." required value="<?php echo htmlspecialchars($project_name_value, ENT_QUOTES, 'UTF-8'); ?>">
                <p class="form-caption" data-lang-id="005b-project-name-caption">Give a name or title to your project post. Avoid special characters.</p>
                <div id="name-error-required" class="form-field-error" data-lang-id="000-field-required-error">This field is required.</div>
                <div id="name-error-long" class="form-field-error" data-lang-id="000-name-field-too-long-error">Your project name is too long. Max 50 characters.</div>
                <div id="name-error-invalid" class="form-field-error" data-lang-id="005b-project-name-error">Your entry contains invalid characters. Avoid quotes, slashes, and greater-than signs please.</div>
            </div>

            <!-- ADMIN SEARCH -->
            <div class="form-item">
                <label for="admin_search" data-lang-id="014-project-admins">Who are the project admins?</label><br>
                <input type="text" id="admin_search" placeholder="Type to search ecobrickers..." autocomplete="off">
                <div id="admin_results" class="autocomplete-results"></div>
                <div id="selected_admins" class="trainer-tag-container"></div>
                <p class="form-caption" data-lang-id="014b-project-admins-caption">Select the ecobrickers who will admin this project.</p>
                <div id="admins-error-required" class="form-field-error" data-lang-id="000-field-required-error">At least one project admin is required.</div>
            </div>

            <!-- SHORT DESCRIPTION -->
            <div class="form-item">
                <label for="description_short" data-lang-id="004-short-project-desc">Short project description:</label><br>
                <textarea id="description_short" name="description_short" aria-label="Project Description" title="Required. Max 150 words" required><?php echo htmlspecialchars($description_short_value, ENT_QUOTES, 'UTF-8'); ?></textarea>
                <p class="form-caption" data-lang-id="004-short-project-desc-caption">One sentence description. Max 150 words. Avoid special characters.</p>
                <div id="description-error-required" class="form-field-error" data-lang-id="000-field-required-error">This field is required.</div>
                <div id="description-error-long" class="form-field-error" data-lang-id="000-field-description-too-long-error">Your project description is too long. Max 255 characters.</div>
                <div id="description-error-invalid" class="form-field-error" data-lang-id="000-field-invalid-characters">Your entry contains invalid characters. Avoid quotes, slashes, and greater-than signs.</div>
            </div>

            <!-- LONG DESCRIPTION -->
            <div class="form-item">
                <label for="description_long" data-lang-id="005-long-project-desc">Full project description:</label><br>
                <textarea id="description_long" name="description_long" aria-label="Full Project Description" title="Optional. Max 1000 words"><?php echo htmlspecialchars($description_long_value, ENT_QUOTES, 'UTF-8'); ?></textarea>
                <p class="form-caption" data-lang-id="005-long-project-desc-caption">Optional. Share the full details of your project. Max 1000 words.</p>
                <div id="description2-error-long" class="form-field-error" data-lang-id="000-long-field-too-long-error">Your long project description is too long. Maximum 2000 characters.</div>
            </div>

            <!-- ECOBRICKS USED -->
            <div class="form-item">
                <label for="briks_used" data-lang-id="009-bricks-used">How many ecobricks does your project use?</label><br>
                <input type="number" id="briks_used" name="briks_used" aria-label="Bricks Used" min="1" max="5000" required value="<?php echo htmlspecialchars($briks_used_value, ENT_QUOTES, 'UTF-8'); ?>">
                <p class="form-caption" data-lang-id="009-bricks-used-caption">A number of ecobricks between 1–5000.</p>
                <div id="briks-error-range" class="form-field-error" data-lang-id="000-field-brik-number-error">Just a number (between 1 and 5000)</div>
            </div>

            <!-- AVERAGE BRIK WEIGHT -->
            <div class="form-item">
                <label for="est_avg_brik_weight" data-lang-id="010-est-avg-weight">Estimated average weight of ecobricks used (grams):</label><br>
                <input type="number" id="est_avg_brik_weight" name="est_avg_brik_weight" aria-label="Estimate Brik Weight" min="100" max="2000" required value="<?php echo htmlspecialchars($est_avg_brik_weight_value, ENT_QUOTES, 'UTF-8'); ?>">
                <p class="form-caption" data-lang-id="010-est-avg-weight-range">A number between 100 and 2000.</p>
                <div id="weight-error-range" class="form-field-error" data-lang-id="000-field-required-error">Average weight in grams must be between 100 and 2000.</div>
            </div>

            <!-- PROJECT TYPE -->
            <div class="form-item">
                <label for="project_type" data-lang-id="011-project-type">What type of project is this?</label><br>
                <select id="project_type" name="project_type" aria-label="Project Type" required>
                    <option value="" disabled <?php echo empty($project_type_value) ? 'selected' : ''; ?> data-lang-id="011-select">Select project type...</option>
                    <option value="single module" <?php echo $project_type_value === 'single module' ? 'selected' : ''; ?> data-lang-id="011-single-module">Single Module</option>
                    <option value="furniture"     <?php echo $project_type_value === 'furniture'     ? 'selected' : ''; ?> data-lang-id="011-modular-furniture">Furniture</option>
                    <option value="garden"        <?php echo $project_type_value === 'garden'        ? 'selected' : ''; ?> data-lang-id="011-outdoor-garden">Outdoor Garden</option>
                    <option value="structure"     <?php echo $project_type_value === 'structure'     ? 'selected' : ''; ?> data-lang-id="011-structure">Structure</option>
                    <option value="art"           <?php echo $project_type_value === 'art'           ? 'selected' : ''; ?> data-lang-id="011-art">Art</option>
                    <option value="other"         <?php echo $project_type_value === 'other'         ? 'selected' : ''; ?> data-lang-id="011-other">Other</option>
                </select>
                <br><br>
                <div id="project-type-error-required" class="form-field-error" data-lang-id="000-field-required-error">This field is required.</div>
            </div>

            <!-- CONSTRUCTION TYPE -->
            <div class="form-item">
                <label for="construction_type" data-lang-id="012-construction-type">What type of construction is this?</label><br>
                <select id="construction_type" name="construction_type" aria-label="Construction Type" required>
                    <option value="" disabled <?php echo empty($construction_type_value) ? 'selected' : ''; ?> data-lang-id="012-select">Select construction type...</option>
                    <option value="silicone"     <?php echo $construction_type_value === 'silicone'     ? 'selected' : ''; ?> data-lang-id="012-construction-silicone">Silicone</option>
                    <option value="banding"      <?php echo $construction_type_value === 'banding'      ? 'selected' : ''; ?> data-lang-id="012-construction-tire-banding">Tire Banding</option>
                    <option value="ecojoiner"    <?php echo $construction_type_value === 'ecojoiner'    ? 'selected' : ''; ?> data-lang-id="012-construction-ecojoiner">Ecojoiner</option>
                    <option value="earth"        <?php echo $construction_type_value === 'earth'        ? 'selected' : ''; ?> data-lang-id="012-construction-earth">Earth/Cob</option>
                    <option value="installation" <?php echo $construction_type_value === 'installation' ? 'selected' : ''; ?> data-lang-id="012-construction-installation">Installation</option>
                    <option value="other"        <?php echo $construction_type_value === 'other'        ? 'selected' : ''; ?> data-lang-id="012-other">Other</option>
                </select>
                <br><br>
                <div id="construction-type-error-required" class="form-field-error" data-lang-id="000-field-required-error">This field is required.</div>
            </div>

            <!-- LOCATION -->
            <div class="form-item">
                <label for="location_address" data-lang-id="015-location">Where is the project located?</label><br>
                <div class="input-container">
                    <input type="text" id="location_address" name="location_address" aria-label="Project Location" placeholder="Start typing your town..." required value="<?php echo htmlspecialchars($location_full_value, ENT_QUOTES, 'UTF-8'); ?>">
                    <div id="loading-spinner" class="spinner" style="display: none;"></div>
                </div>
                <p class="form-caption" data-lang-id="016-location-caption">For privacy, please don't use your exact address. Choose your general neighbourhood or town.</p>
                <div id="location-error-required" class="form-field-error" data-lang-id="000-field-required-error">This field is required.</div>
            </div>

            <!-- ADVANCED OPTIONS (closed by default) -->
            <div class="advanced-box" role="region" aria-labelledby="advancedBoxLabel-1">
                <div class="advanced-box-header" id="advancedBoxLabel-1">
                    <div class="advanced-title" data-lang-id="012-block-1-advanced">Advanced Options</div>
                    <div class="advanced-open-icon">+</div>
                </div>
                <div class="advanced-box-content">

                    <div class="form-item">
                        <label for="project_sort" data-lang-id="012b-project-sort">What sort of project is this?</label><br>
                        <select id="project_sort" name="project_sort" aria-label="Community or Personal Project">
                            <option value="" disabled <?php echo empty($project_sort_value) ? 'selected' : ''; ?> data-lang-id="012b-select">Select sort...</option>
                            <option value="community" <?php echo $project_sort_value === 'community' ? 'selected' : ''; ?> data-lang-id="012b-community-project">Community Project</option>
                            <option value="personal"  <?php echo $project_sort_value === 'personal'  ? 'selected' : ''; ?> data-lang-id="012b-personal-project">Personal Project</option>
                        </select>
                    </div>

                    <div class="form-item">
                        <label for="community" data-lang-id="013-community">What community is responsible for this project?</label><br>
                        <input type="text" id="community" name="community" aria-label="Community (optional)" value="<?php echo htmlspecialchars($community_value, ENT_QUOTES, 'UTF-8'); ?>">
                        <p class="form-caption" data-lang-id="013b-optional">Optional</p>
                        <div id="community-error-long" class="form-field-error" data-lang-id="000-field-too-long-error">Entry is too long.</div>
                    </div>

                    <div id="other-advanced-fields">

                        <div class="form-item">
                            <label for="connected_ecobricks">The serials of ecobricks used in your project:</label><br>
                            <input type="text" id="connected_ecobricks" name="connected_ecobricks" aria-label="Connected Ecobricks" placeholder="Enter serials..." value="<?php echo htmlspecialchars($connected_ecobricks_value, ENT_QUOTES, 'UTF-8'); ?>">
                            <div id="serial-select"><ul id="autocomplete-results"></ul></div>
                            <p class="form-caption">Optional: Enter the serial numbers of ecobricks connected to this project. Separate multiple serial numbers with commas.</p>
                        </div>

                        <div class="form-item">
                            <p data-lang-id="007-project-duration">Project Duration</p>
                            <label for="start_dt" data-lang-id="007-start-date">Start Date:</label><br>
                            <input type="date" id="start_dt" name="start_dt" aria-label="Start Date" value="<?php echo htmlspecialchars(substr($start_dt_value ?? '', 0, 10), ENT_QUOTES, 'UTF-8'); ?>">
                            <p class="form-caption" data-lang-id="008-start-date-caption">When did this project begin?</p>
                            <div id="start-error-reasonable" class="form-field-error" data-lang-id="000-field-reasonable-date">A reasonable date is required. Must be after 2000 and before today.</div>

                            <label for="end_dt" data-lang-id="007b-end-date">End Date:</label><br>
                            <input type="date" id="end_dt" name="end_dt" aria-label="End Date" value="<?php echo htmlspecialchars(substr($end_dt_value ?? '', 0, 10), ENT_QUOTES, 'UTF-8'); ?>">
                            <p class="form-caption" data-lang-id="008b-end-date-caption">When did this project end?</p>
                        </div>

                    </div><!--closes other-advanced-fields-->

                </div>
            </div><!--close advanced box-->

            <input type="hidden" id="lat" name="latitude"  value="<?php echo htmlspecialchars($latitude_value ?? '', ENT_QUOTES, 'UTF-8'); ?>">
            <input type="hidden" id="lon" name="longitude" value="<?php echo htmlspecialchars($longitude_value ?? '', ENT_QUOTES, 'UTF-8'); ?>">

            <!-- ═══════════════════════════════════════════════
                 PROJECT PHOTOS
            ════════════════════════════════════════════════ -->

            <h2 class="photos-section-header">Project Photos</h2>
            <p class="photos-section-intro">Upload new photos to replace existing ones. Leave a field empty to keep the current photo. Photos are stored as WebP and resized to 1000 px max.</p>

            <div class="photos-grid">

            <?php
            $photo_slots = [
                1 => ['tmb' => $photo1_tmb ?? '', 'label' => 'Feature Image',  'required' => true],
                2 => ['tmb' => $photo2_tmb ?? '', 'label' => 'Photo 2'],
                3 => ['tmb' => $photo3_tmb ?? '', 'label' => 'Photo 3'],
                4 => ['tmb' => $photo4_tmb ?? '', 'label' => 'Photo 4'],
                5 => ['tmb' => $photo5_tmb ?? '', 'label' => 'Photo 5'],
                6 => ['tmb' => $photo6_tmb ?? '', 'label' => 'Photo 6'],
            ];
            foreach ($photo_slots as $i => $slot):
            ?>
            <div class="form-item photo-slot">
                <label for="photo<?php echo $i; ?>_main"><?php echo htmlspecialchars($slot['label']); ?>:</label>
                <div class="photo-preview-box">
                    <?php if (!empty($slot['tmb'])): ?>
                        <img src="<?php echo htmlspecialchars($slot['tmb'], ENT_QUOTES, 'UTF-8'); ?>"
                             alt="Current <?php echo htmlspecialchars($slot['label']); ?>"
                             onerror="this.outerHTML='<div class=\'photo-no-image\'>No photo yet</div>'">
                    <?php else: ?>
                        <div class="photo-no-image">No photo yet</div>
                    <?php endif; ?>
                </div>
                <input type="file" id="photo<?php echo $i; ?>_main" name="photo<?php echo $i; ?>_main" accept="image/jpeg,image/png,image/webp">
                <p class="form-caption">
                    <?php echo ($i === 1) ? 'Replace the feature photo. Required if no photo exists.' : 'Optional. Upload to replace current photo.'; ?>
                </p>
            </div>
            <?php endforeach; ?>

            </div><!-- /photos-grid -->

            <!-- SUBMIT -->
            <div style="margin-top: 30px;" data-lang-id="017-submit-button">
                <input type="submit" value="Save Changes" aria-label="Save project changes">
            </div>

            <!-- VIEW PROJECT -->
            <div style="margin-top: 12px;">
                <a class="confirm-button" style="background: #5b9bd5;" href="project.php?id=<?php echo (int)$project_id; ?>">View Project &rarr;</a>
            </div>

        </form>

        <!-- DELETE PROJECT -->
        <form id="deleteForm" method="post" action="../processes/edit_project.php" style="margin-top: 40px; padding-top: 20px; border-top: 1px solid var(--divider-line);">
            <input type="hidden" name="project_id" value="<?php echo (int)$project_id; ?>">
            <input type="hidden" name="action" value="delete_project">
            <button type="button" id="deleteButton" class="delete-project-btn">&#10060; Delete This Project</button>
        </form>

    </div>
</div>




<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
<link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">

<script>
// PHP-rendered constants
const CURRENT_ECOBRICKER_ID = <?php echo (int)$ecobricker_id; ?>;
const CURRENT_FULL_NAME     = <?php echo json_encode($full_name ?? $first_name ?? '', JSON_HEX_TAG) ?: '""'; ?>;
const EXISTING_ADMINS       = <?php echo json_encode($existing_admins, JSON_HEX_TAG) ?: '[]'; ?>;
const PROJECT_ID            = <?php echo (int)$project_id; ?>;


// ── ADVANCED OPTIONS TOGGLE ──────────────────────────────────────────────────

function toggleAdvancedBox(event) {
    let currentAdvancedBox = event.currentTarget.parentElement;
    let header  = currentAdvancedBox.querySelector('.advanced-box-header');
    let content = currentAdvancedBox.querySelector('.advanced-box-content');
    let icon    = currentAdvancedBox.querySelector('.advanced-open-icon');
    let isExpanded = header.getAttribute('aria-expanded') === 'true';

    if (!isExpanded) {
        content.style.maxHeight = content.scrollHeight + 'px';
        icon.textContent = '−';
        header.setAttribute('aria-expanded', 'true');
    } else {
        content.style.maxHeight = '0px';
        icon.textContent = '+';
        header.setAttribute('aria-expanded', 'false');
    }
}

document.addEventListener("DOMContentLoaded", function() {
    document.querySelectorAll('.advanced-box-header').forEach(function(header) {
        header.addEventListener('click', toggleAdvancedBox);
    });
});


// ── ADVANCED FIELDS: SHOW/HIDE BY PROJECT SORT ───────────────────────────────

document.addEventListener("DOMContentLoaded", function() {
    const communityField          = document.getElementById("community").parentNode;
    const connectedEcoBricksField = document.getElementById("connected_ecobricks").parentNode;
    const projectDurationField    = document.getElementById("start_dt").parentNode.parentNode;

    communityField.style.display          = 'none';
    connectedEcoBricksField.style.display = 'none';
    projectDurationField.style.display    = 'none';

    function toggleFields() {
        var projectSort = document.getElementById("project_sort").value;

        communityField.style.display          = 'none';
        connectedEcoBricksField.style.display = 'none';
        projectDurationField.style.display    = 'none';

        if (projectSort === "community") {
            communityField.style.display = '';
        }
        if (projectSort === "community" || projectSort === "personal") {
            connectedEcoBricksField.style.display = '';
            projectDurationField.style.display    = '';
        }

        // Recalculate max-height only if the advanced box is already open
        const advancedHeader = document.querySelector('.advanced-box-header');
        const advancedBoxContent = document.querySelector('.advanced-box-content');
        if (advancedHeader && advancedHeader.getAttribute('aria-expanded') === 'true') {
            advancedBoxContent.style.maxHeight = advancedBoxContent.scrollHeight + "px";
        }
    }

    document.getElementById("project_sort").addEventListener("change", toggleFields);
    toggleFields();
});


// ── LOCATION AUTOCOMPLETE ─────────────────────────────────────────────────────

$(function() {
    let debounceTimer;
    $("#location_address").autocomplete({
        source: function(request, response) {
            $("#loading-spinner").show();
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                $.ajax({
                    url: "https://nominatim.openstreetmap.org/search",
                    dataType: "json",
                    headers: { 'User-Agent': 'gobrik.com' },
                    data: { q: request.term, format: "json" },
                    success: function(data) {
                        $("#loading-spinner").hide();
                        response($.map(data, function(item) {
                            return { label: item.display_name, value: item.display_name, lat: item.lat, lon: item.lon };
                        }));
                    },
                    error: function() {
                        $("#loading-spinner").hide();
                        response([]);
                    }
                });
            }, 300);
        },
        select: function(event, ui) {
            $('#lat').val(ui.item.lat);
            $('#lon').val(ui.item.lon);
        },
        minLength: 3
    });
});


// ── SERIAL AUTOCOMPLETE ───────────────────────────────────────────────────────

$(document).ready(function() {
    var $serialInput = $('#connected_ecobricks');
    var $autocompleteResults = $('#autocomplete-results');
    var $serialSelect = $('#serial-select');

    function performSearch(inputVal) {
        if (inputVal.length >= 4) {
            $.ajax({
                url: '../get-serials.php',
                type: 'GET',
                data: { search: inputVal },
                success: function(data) {
                    $autocompleteResults.empty();
                    if (data.length) {
                        data.forEach(function(item) {
                            $autocompleteResults.append($('<li>').text(item.serial_no));
                        });
                        $serialSelect.show();
                    } else {
                        $autocompleteResults.append($('<li>').text("No results found"));
                        $serialSelect.hide();
                    }
                }
            });
        } else {
            $autocompleteResults.empty();
            $serialSelect.hide();
        }
    }

    $serialInput.on('input', function() {
        var lastTerm = $(this).val().split(',').pop().trim();
        performSearch(lastTerm);
    });

    $autocompleteResults.on('click', 'li', function() {
        var selectedSerial = $(this).text();
        var currentInput   = $serialInput.val();
        var lastCommaIndex = currentInput.lastIndexOf(',');
        if (lastCommaIndex === -1) {
            $serialInput.val(selectedSerial + ', ');
        } else {
            $serialInput.val(currentInput.substring(0, lastCommaIndex + 1) + ' ' + selectedSerial + ', ');
        }
        $autocompleteResults.empty();
        $serialInput.focus();
        $serialSelect.hide();
    });

    $serialInput.blur(function() {
        setTimeout(function() { $serialSelect.hide(); }, 200);
    });
});


// ── ADMIN SEARCH ──────────────────────────────────────────────────────────────

document.addEventListener('DOMContentLoaded', function() {
    const adminInput     = document.getElementById('admin_search');
    const adminResults   = document.getElementById('admin_results');
    const adminContainer = document.getElementById('selected_admins');

    function fetchAdmins(query) {
        if (query.length >= 3) {
            fetch('../api/search_ecobrickers.php?query=' + encodeURIComponent(query))
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    adminResults.innerHTML = '';
                    if (data.length === 0) {
                        adminResults.innerHTML = "<div class='autocomplete-item' style='color:gray;'>No results found</div>";
                    } else {
                        data.forEach(function(person) {
                            var div = document.createElement('div');
                            div.textContent = person.full_name;
                            div.dataset.id = person.ecobricker_id;
                            div.classList.add('autocomplete-item');
                            div.addEventListener('mousedown', function(e) {
                                e.preventDefault();
                                addAdmin(person.ecobricker_id, person.full_name);
                                adminInput.value = '';
                                adminResults.innerHTML = '';
                            });
                            adminResults.appendChild(div);
                        });
                    }
                });
        } else {
            adminResults.innerHTML = '';
        }
    }

    function addAdmin(id, name) {
        if (document.getElementById('admin-hidden-' + id)) return;
        var box = document.createElement('div');
        box.className = 'trainer-tag-box';
        box.dataset.id = id;

        var remove = document.createElement('span');
        remove.className = 'remove-trainer';
        remove.textContent = '\u00D7';
        remove.addEventListener('click', function() { box.remove(); });

        var text   = document.createElement('span');
        text.textContent = name;

        var hidden = document.createElement('input');
        hidden.type  = 'hidden';
        hidden.name  = 'admin_ids[]';
        hidden.value = id;
        hidden.id    = 'admin-hidden-' + id;

        box.appendChild(remove);
        box.appendChild(text);
        box.appendChild(hidden);
        adminContainer.appendChild(box);
    }

    // Pre-populate existing admins from DB
    EXISTING_ADMINS.forEach(function(admin) {
        addAdmin(admin.ecobricker_id, admin.full_name);
    });

    adminInput.addEventListener('input', function() {
        fetchAdmins(adminInput.value.trim());
    });

    document.addEventListener('click', function(e) {
        if (!adminInput.contains(e.target) && !adminResults.contains(e.target)) {
            adminResults.innerHTML = '';
        }
    });

    window.addAdmin = addAdmin;
});


// ── FORM VALIDATION ───────────────────────────────────────────────────────────

document.getElementById('submit-form').addEventListener('submit', function(event) {
    event.preventDefault();
    var isValid = true;

    function displayError(elementId, showError) {
        var errorDiv = document.getElementById(elementId);
        if (!errorDiv) return;
        if (showError) { errorDiv.style.display = 'block'; isValid = false; }
        else           { errorDiv.style.display = 'none'; }
    }

    function hasInvalidChars(value) {
        return /[\'\"><]/.test(value);
    }

    var projectName = document.getElementById('project_name').value.trim();
    displayError('name-error-required', projectName === '');
    displayError('name-error-long',     projectName.length > 50);
    displayError('name-error-invalid',  hasInvalidChars(projectName));

    var descriptionShort = document.getElementById('description_short').value.trim();
    displayError('description-error-required', descriptionShort === '');
    displayError('description-error-long',     descriptionShort.length > 255);
    displayError('description-error-invalid',  hasInvalidChars(descriptionShort));

    var descriptionLong = document.getElementById('description_long').value.trim();
    displayError('description2-error-long', descriptionLong.length > 2000);

    var briksUsed = parseInt(document.getElementById('briks_used').value, 10);
    displayError('briks-error-range', isNaN(briksUsed) || briksUsed < 1 || briksUsed > 5000);

    var estimatedWeight = parseInt(document.getElementById('est_avg_brik_weight').value, 10);
    displayError('weight-error-range', isNaN(estimatedWeight) || estimatedWeight < 100 || estimatedWeight > 2000);

    var projectType = document.getElementById('project_type').value;
    displayError('project-type-error-required', projectType === '');

    var constructionType = document.getElementById('construction_type').value;
    displayError('construction-type-error-required', constructionType === '');

    var community = document.getElementById('community').value.trim();
    displayError('community-error-long', community.length > 255);

    var adminInputs = document.querySelectorAll('#selected_admins input[name="admin_ids[]"]');
    displayError('admins-error-required', adminInputs.length === 0);

    var location = document.getElementById('location_address').value.trim();
    displayError('location-error-required', location === '');

    if (isValid) {
        this.submit();
    } else {
        var firstError = document.querySelector('.form-field-error[style="display: block;"]');
        if (firstError) {
            firstError.scrollIntoView({ behavior: "smooth", block: "center" });
            var relatedInput = firstError.closest('.form-item').querySelector('input, select, textarea');
            if (relatedInput) relatedInput.focus();
        }
    }
});


// ── DELETE PROJECT ────────────────────────────────────────────────────────────

document.getElementById('deleteButton').addEventListener('click', function() {
    if (confirm('Are you sure you want to delete this project? This action cannot be undone.')) {
        document.getElementById('deleteForm').submit();
    }
});

</script>



<br><br>
</div><!--closes main-->

	<!--FOOTER STARTS HERE-->
	<?php require_once ("../footer-2026.php");?>

</div>

</body>
</html>
