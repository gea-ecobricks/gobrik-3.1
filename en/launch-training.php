<?php

require_once '../earthenAuth_helper.php'; // Authentication helper
require_once '../auth/session_start.php';

// PART 1: Set page variables
$lang = basename(dirname($_SERVER['SCRIPT_NAME']));
$current_lang_dir = $lang;
$version = '0.64';
$page = 'launch-training';
$lastModified = date("Y-m-d\TH:i:s\Z", filemtime(__FILE__));

ob_start(); // Prevent output before headers

// ✅ Determine training ID from URL
$training_id = 0;
if (isset($_GET['training_id'])) {
    $training_id = intval($_GET['training_id']);
} elseif (isset($_GET['id'])) {
    $training_id = intval($_GET['id']);
}

function isCrawler() {
    $bots = [
        'Googlebot', 'Bingbot', 'Slurp', 'DuckDuckBot', 'Baiduspider', 'YandexBot',
        'Sogou', 'Exabot',
        'facebookexternalhit', 'facebot', 'Twitterbot', 'Slackbot', 'LinkedInBot', 'Pinterest',
        'WhatsApp', 'TelegramBot', 'Discordbot', 'Signal-Android', 'Signal-iOS', 'iMessagePreview'
    ];

    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    foreach ($bots as $bot) {
        if (stripos($userAgent, $bot) !== false) {
            return true;
        }
    }
    return false;
}

if (!isLoggedIn()) {
    if (isCrawler()) {
        header('Content-Type: text/html; charset=UTF-8');
        echo "<!DOCTYPE html><html lang='en'><head>";
        require_once("../meta/launch-training-$lang.php");
        echo "</head><body>";
        echo "<p>This is a preview page for bots only.</p>";
        echo "</body></html>";
        exit();
    } else {
        $login_params = [];
        if (isset($_SESSION['buwana_id'])) {
            $login_params['id'] = $_SESSION['buwana_id'];
        }

        $redirect = 'launch-training.php';
        if ($training_id) {
            $redirect .= '?training_id=' . $training_id;
        }
        $login_params['redirect'] = $redirect;

        header('Location: login.php?' . http_build_query($login_params));
        exit();
    }
}

$buwana_id = $_SESSION['buwana_id'];
require_once '../gobrikconn_env.php';

// ✅ Fetch User Role
$gea_status = getGEA_status($buwana_id);

if (!$gea_status || stripos($gea_status, 'trainer') === false) {
    header("Location: dashboard.php?error=unauthorized");
    exit();
}

// PART 3: ✅ Fetch User Details
require_once '../buwanaconn_env.php';
$user_continent_icon = getUserContinent($buwana_conn, $buwana_id);
$earthling_emoji = getUserEarthlingEmoji($buwana_conn, $buwana_id);
$user_location_watershed = getWatershedName($buwana_conn, $buwana_id);
$user_location_full = getUserFullLocation($buwana_conn, $buwana_id);
$user_community_name = getCommunityName($buwana_conn, $buwana_id);
$first_name = getFirstName($buwana_conn, $buwana_id);

// Fetch all languages
$languages = [];
$sql_languages = "SELECT language_id, language_name_en, languages_native_name FROM languages_tb ORDER BY language_name_en ASC";
$result_languages = $buwana_conn->query($sql_languages);

if ($result_languages && $result_languages->num_rows > 0) {
    while ($row = $result_languages->fetch_assoc()) {
        $languages[] = $row;
    }
}

// Fetch user's country_id
$user_country_id = null;
$sql_country_lookup = "SELECT country_id FROM users_tb WHERE buwana_id = ?";
$stmt_country_lookup = $buwana_conn->prepare($sql_country_lookup);
if ($stmt_country_lookup) {
    $stmt_country_lookup->bind_param('i', $buwana_id);
    $stmt_country_lookup->execute();
    $stmt_country_lookup->bind_result($user_country_id);
    $stmt_country_lookup->fetch();
    $stmt_country_lookup->close();
}

require_once '../gobrikconn_env.php';

// ✅ Determine if editing existing training
$editing = ($training_id > 0);

// Defaults
$training_title = '';
$training_language = 'en';
$zoom_link = '';
$zoom_link_full = '';
$feature_photo1_main = '';
$feature_photo1_tmb = '';
$feature_photo2_main = '';
$feature_photo2_tmb = '';
$feature_photo3_main = '';
$feature_photo3_tmb = '';
$training_subtitle = '';
$training_time_txt = '';
$registration_scope = 'anyone';
$trainer_contact_email = '';
$earthcal_event_url = '';
$show_report = 0;
$show_signup_count = 0;
$training_date = null;
$no_participants = '';
$lead_trainer = '';
$country_id = '';
$training_type = '';
$briks_made = 0;
$avg_brik_weight = 0;
$training_location = '';
$training_summary = '';
$training_agenda = '';
$training_success = '';
$training_challenges = '';
$training_lessons_learned = '';
$youtube_result_video = '';
$moodle_url = '';
$ready_to_show = 0;
$featured_description = '';
$community_id = '';
$community_name = '';
$cost = '';
$currency = '';
$display_cost = 'Free / Donation';

// New 3P defaults
$default_price_idr = '';
$base_currency = 'IDR';
$payment_mode = 'free';
$min_participants_required = '';
$funding_goal_idr = '';
$pledge_deadline = '';
$payment_deadline = '';
$threshold_status = 'open';
$auto_confirm_threshold = 1;
$allow_overpledge = 1;
$min_pledge_idr = 0;
$max_pledge_idr = '';

// ✅ If editing, fetch existing training details
if ($editing) {
    $sql_fetch = "SELECT training_title, training_subtitle, lead_trainer, country_id, training_date, training_time_txt, no_participants,
                  training_type, training_language, briks_made, avg_brik_weight, training_location,
                  training_summary, training_agenda, training_success, training_challenges, training_lessons_learned,
                  youtube_result_video, moodle_url, ready_to_show, show_report, show_signup_count, featured_description, community_id,
                  zoom_link, zoom_link_full, earthcal_event_url, feature_photo1_main, feature_photo1_tmb, feature_photo2_main, feature_photo2_tmb, feature_photo3_main, feature_photo3_tmb,
                  registration_scope, trainer_contact_email,
                  Cost, Currency, display_cost,
                  default_price_idr, base_currency, payment_mode, min_participants_required, funding_goal_idr,
                  pledge_deadline, payment_deadline, threshold_status, auto_confirm_threshold, allow_overpledge,
                  min_pledge_idr, max_pledge_idr
                  FROM tb_trainings WHERE training_id = ?";

    $stmt_fetch = $gobrik_conn->prepare($sql_fetch);
    $stmt_fetch->bind_param("i", $training_id);
    $stmt_fetch->execute();
    $stmt_fetch->bind_result(
        $training_title, $training_subtitle, $lead_trainer, $country_id, $training_date, $training_time_txt, $no_participants,
        $training_type, $training_language, $briks_made, $avg_brik_weight, $training_location,
        $training_summary, $training_agenda, $training_success, $training_challenges,
        $training_lessons_learned, $youtube_result_video, $moodle_url, $ready_to_show, $show_report, $show_signup_count, $featured_description, $community_id,
        $zoom_link, $zoom_link_full, $earthcal_event_url, $feature_photo1_main, $feature_photo1_tmb, $feature_photo2_main, $feature_photo2_tmb, $feature_photo3_main, $feature_photo3_tmb,
        $registration_scope, $trainer_contact_email,
        $cost, $currency, $display_cost,
        $default_price_idr, $base_currency, $payment_mode, $min_participants_required, $funding_goal_idr,
        $pledge_deadline, $payment_deadline, $threshold_status, $auto_confirm_threshold, $allow_overpledge,
        $min_pledge_idr, $max_pledge_idr
    );
    $stmt_fetch->fetch();
    $stmt_fetch->close();
}

// ✅ Fetch Unique Training Types
$training_types = [];
$result = $gobrik_conn->query("SELECT DISTINCT training_type FROM tb_trainings ORDER BY training_type ASC");
while ($row = $result->fetch_assoc()) {
    $training_types[] = $row['training_type'];
}

// ✅ Fetch List of Countries
$countries = [];
$result = $buwana_conn->query("SELECT country_id, country_name FROM countries_tb ORDER BY country_name ASC");
while ($row = $result->fetch_assoc()) {
    $countries[] = $row;
}

// ✅ Fetch Trainer Options
$trainers_list = [];
$result_trainers = $gobrik_conn->query("SELECT ecobricker_id, full_name FROM tb_ecobrickers WHERE gea_status LIKE '%trainer%' ORDER BY full_name ASC");
if ($result_trainers) {
    while ($row = $result_trainers->fetch_assoc()) {
        $trainers_list[] = $row;
    }
}

$selected_trainers = [];
if ($editing) {
    $stmt_tr = $gobrik_conn->prepare("SELECT ecobricker_id FROM tb_training_trainers WHERE training_id = ?");
    $stmt_tr->bind_param("i", $training_id);
    $stmt_tr->execute();
    $res_tr = $stmt_tr->get_result();
    while ($row = $res_tr->fetch_assoc()) {
        $selected_trainers[] = $row['ecobricker_id'];
    }
    $stmt_tr->close();
}

// Build array with selected trainer details for pre-population
$selected_trainers_data = [];
foreach ($trainers_list as $trainer) {
    if (in_array($trainer['ecobricker_id'], $selected_trainers)) {
        $selected_trainers_data[] = $trainer;
    }
}

// ✅ Fetch Community Name if Exists
if (!empty($community_id)) {
    $stmt = $buwana_conn->prepare("SELECT com_name FROM communities_tb WHERE community_id = ?");
    $stmt->bind_param("i", $community_id);
    $stmt->execute();
    $stmt->bind_result($community_name);
    $stmt->fetch();
    $stmt->close();
}
?>

<!DOCTYPE html>
<HTML lang="en">
<HEAD>
    <META charset="UTF-8">

    <?php require_once("../includes/launch-training-inc.php"); ?>

    <div class="splash-content-block"></div>
    <div id="splash-bar"></div>

    <div id="form-submission-box">
        <div class="form-container">

            <div class="splash-form-content-block">
                <div class="splash-box">
                    <div class="splash-heading" data-lang-id="001-splash-title-post"><?php echo $editing ? 'Edit your training' : 'Launch a GEA Training'; ?></div>
                    <div class="lead-page-paragraph">
                        <p data-lang-id="004-form-description-post">Use this form to launch a training, workshop or community event on GoBrik.</p>
                    </div>
                    <div style="text-align:right; margin:10px 0;">
                        <button type="button" id="starterPresetBtn" class="page-button">+ Starter Presets</button>
                        <?php if ($editing): ?>
                            <button type="button" class="page-button" style="margin-left:10px;" onclick="window.open('register.php?id=<?php echo $training_id; ?>','_blank');">&gt; View training listing</button>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="splash-image" data-lang-id="003-splash-image-alt">
                    <img src="../svgs/shanti.svg" style="width:65%" alt="GEA trainer in action: File a GEA training report">
                </div>
            </div>

<form id="submit-form" method="post" action="launch-training_process.php" novalidate>

    <div class="form-item" style="margin-top: 25px;">
        <label for="training_title" data-lang-id="005-title-title">What is the title of your training?</label><br>
        <input type="text" id="training_title" name="training_title"
               value="<?php echo htmlspecialchars($training_title ?? '', ENT_QUOTES, 'UTF-8'); ?>"
               aria-label="Training Title">
        <p class="form-caption" data-lang-id="005-training-give-title">Give your training a title. This will be how your report is featured.</p>

        <div id="title-error-required" class="form-field-error" data-lang-id="000-field-required-error">This field is .</div>
        <div id="title-error-long" class="form-field-error" data-lang-id="000-title-field-too-long-error">Your training title is too long. Max 500 characters.</div>
        <div id="title-error-invalid" class="form-field-error" data-lang-id="005b-training-title-error">Your entry contains invalid characters. Avoid quotes, slashes, and greater-than signs please.</div>
    </div>

    <div class="form-item">
        <label for="training_subtitle">Add an optional subtitle to your training...</label><br>
        <input type="text" id="training_subtitle" name="training_subtitle"
               value="<?php echo htmlspecialchars($training_subtitle ?? '', ENT_QUOTES, 'UTF-8'); ?>"
               aria-label="Training Subtitle">
    </div>

    <div class="form-item">
        <label for="trainer_search" data-lang-id="005b-title-trainers">Who are the trainers leading this training?</label><br>
        <input type="text" id="trainer_search" placeholder="Type to search..." autocomplete="off" class="form-field-style">
        <div id="trainer_results" class="autocomplete-results"></div>

        <div id="selected_trainers" class="trainer-tag-container">
            <?php foreach ($selected_trainers_data as $trainer): ?>
                <div class="trainer-tag-box" data-id="<?php echo $trainer['ecobricker_id']; ?>">
                    <span class="remove-trainer">&times;</span>
                    <span><?php echo htmlspecialchars($trainer['full_name'], ENT_QUOTES, 'UTF-8'); ?></span>
                    <input type="hidden" name="trainers[]" value="<?php echo $trainer['ecobricker_id']; ?>" id="trainer-hidden-<?php echo $trainer['ecobricker_id']; ?>">
                </div>
            <?php endforeach; ?>
        </div>
        <p class="form-caption" data-lang-id="005b-trainers-caption">Select the trainers leading this training.</p>
    </div>

    <div class="form-item">
        <label for="training_date" data-lang-id="006-title-date">Training Date:</label><br>
        <input type="datetime-local" id="training_date" name="training_date"
               value="<?php echo isset($training_date) && !empty($training_date) ? date('Y-m-d\TH:i', strtotime($training_date)) : date('Y-m-d\T12:00'); ?>"
               aria-label="Training Date" class="form-field-style">
        <p class="form-caption" data-lang-id="006-training-date">On what date and time did this training run?</p>
        <div id="date-error-required" class="form-field-error" data-lang-id="000-field-required-error">This field is .</div>
    </div>

    <div class="form-item">
        <label for="training_time_txt">Specify the time your training starts in key timezones...</label><br>
        <input type="text" id="training_time_txt" name="training_time_txt" class="form-field-style"
               placeholder="Jakarta: 7PM / UK: 1 PM / Morocco: 1PM"
               value="<?php echo htmlspecialchars($training_time_txt ?? '', ENT_QUOTES, 'UTF-8'); ?>">
    </div>

    <div class="form-item">
        <label for="training_language" data-lang-id="006b-title-language">What language will this training be in?</label><br>
        <select id="training_language" name="training_language" class="form-field-style">
            <?php foreach ($languages as $language): ?>
                <option value="<?php echo htmlspecialchars($language['language_id'], ENT_QUOTES, 'UTF-8'); ?>"
                    <?php echo ($training_language === $language['language_id']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($language['language_name_en'], ENT_QUOTES, 'UTF-8'); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <p class="form-caption" data-lang-id="006b-language-caption">What language will this training be in?</p>
    </div>

    <div class="form-item">
        <label for="training_type" data-lang-id="010-title-type">What type of training will this be?</label><br>
        <select id="training_type" name="training_type" class="form-field-style">
            <option value="" disabled selected>Select training type...</option>
            <?php foreach ($training_types as $type): ?>
                <option value="<?php echo htmlspecialchars($type, ENT_QUOTES, 'UTF-8'); ?>"
                    <?php echo (isset($training_type) && $training_type === $type) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($type, ENT_QUOTES, 'UTF-8'); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <p class="form-caption" data-lang-id="010-training-type">Please categorize this training.</p>
        <div id="type-error-required" class="form-field-error" data-lang-id="000-field-required-error">This field is .</div>
    </div>

    <input type="hidden" id="briks_made" name="briks_made" value="<?php echo isset($briks_made) ? htmlspecialchars($briks_made, ENT_QUOTES, 'UTF-8') : 0; ?>">
    <input type="hidden" id="avg_brik_weight" name="avg_brik_weight" value="<?php echo isset($avg_brik_weight) ? htmlspecialchars($avg_brik_weight, ENT_QUOTES, 'UTF-8') : 0; ?>">

    <div class="form-item">
        <label for="featured_description" data-lang-id="014-feature-description">Featured Description:</label><br>
        <textarea id="featured_description" name="featured_description" rows="5"
                  placeholder="Write a compelling description for this training..."><?php echo htmlspecialchars($featured_description ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
        <p class="form-caption" data-lang-id="014-training-description">This text is shown on the registration page to describe the training. Basic HTML formatting allowed.</p>

        <div id="featured-error-required" class="form-field-error" data-lang-id="000-field-required-error">This field is .</div>
        <div id="featured-error-long" class="form-field-error" data-lang-id="000-field-summary-too-long-error">Your training summary is too long. Max 255 characters.</div>
        <div id="featured-error-invalid" class="form-field-error" data-lang-id="005b-training-summary-error">Your entry contains invalid characters. Avoid quotes, slashes, and greater-than signs.</div>
    </div>

    <div class="form-item">
        <label for="training_agenda" data-lang-id="015-title-training-agenda">Training Agenda:</label><br>
        <textarea id="training_agenda" name="training_agenda"><?php echo htmlspecialchars($training_agenda ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
        <p class="form-caption" data-lang-id="015-training-agenda">Optional: Layout the agenda your training followed. Max 1000 words.</p>
        <div id="agenda-error-long" class="form-field-error" data-lang-id="000-long-field-too-long-error">Your training agenda is too long. Maximum 2000 characters.</div>
    </div>

    <div class="form-item">
        <label for="registration_scope">Registration Scope</label><br>
        <select id="registration_scope" name="registration_scope" class="form-field-style">
            <option value="trainers" <?php echo (isset($registration_scope) && $registration_scope == 'trainers') ? 'selected' : ''; ?>>Trainers only</option>
            <option value="ecobrickers" <?php echo (isset($registration_scope) && $registration_scope == 'ecobrickers') ? 'selected' : ''; ?>>Ecobrickers (All GoBrikers)</option>
            <option value="anyone" <?php echo (isset($registration_scope) && $registration_scope == 'anyone') ? 'selected' : ''; ?>>Anyone (all Earthen subscribers)</option>
        </select>
        <p class="form-caption">Select the target audience for your course.</p>
    </div>

    <div class="form-item">
        <label for="no_participants" data-lang-id="007-title-participants">What is the maximum amount of participants for this training?</label><br>
        <input type="number" id="no_participants" name="no_participants" min="1" max="5000"
               value="<?php echo htmlspecialchars($no_participants ?? '', ENT_QUOTES, 'UTF-8'); ?>">
        <p class="form-caption" data-lang-id="007-training-count">How many people participated (including trainers)?</p>

        <div id="participants-error-required" class="form-field-error" data-lang-id="000-field-required-error">This field is .</div>
        <div id="participants-error-range" class="form-field-error" data-lang-id="000-field-participants-number-error">A number (between 1 and 5000).</div>
    </div>

    <div class="form-item">
        <label for="community_search" data-lang-id="009-title-community">What community is this training targeting?</label><br>
        <input type="text" id="community_search" name="community_search"
               placeholder="Start typing..." autocomplete="off"
               value="<?php echo htmlspecialchars($community_name ?? '', ENT_QUOTES, 'UTF-8'); ?>">

        <input type="hidden" id="community_id" name="community_id"
               value="<?php echo isset($community_id) ? htmlspecialchars($community_id, ENT_QUOTES, 'UTF-8') : ''; ?>">

        <div id="community_results" class="autocomplete-results"></div>

        <p class="form-caption"><span data-lang-id="008-start-typing-community">
            Start typing to see and select a community. There's a good chance someone local to you has already set one up!</span>
            <br> ➕
            <a href="#" onclick="openAddCommunityModal(); return false;" style="color: #007BFF; text-decoration: underline;" data-lang-id="009-add-community">Don't see your community? Add it.</a>
        </p>

        <div id="community-error-required" class="form-field-error" data-lang-id="000-field-too-long-error">A community must be selected</div>
    </div>

    <div class="form-item">
        <label for="country_id" data-lang-id="013-title-country">What country is this course targeting?</label><br>
        <select id="country_id" name="country_id" class="form-field-style">
            <option value="" disabled <?php echo empty($country_id) ? 'selected' : ''; ?>>Select a country...</option>
            <?php foreach ($countries as $country): ?>
                <option value="<?php echo htmlspecialchars($country['country_id'], ENT_QUOTES, 'UTF-8'); ?>"
                    <?php echo (!empty($country_id) && $country_id == $country['country_id']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($country['country_name'], ENT_QUOTES, 'UTF-8'); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <p class="form-caption" data-lang-id="013-training-country">For global course, leave blank or select "Earth"</p>
        <div id="country-error-required" class="form-field-error" data-lang-id="000-field-required-error">This field is .</div>
    </div>

    <div class="form-item">
        <label for="training_location" data-lang-id="021-title-location">Training Location:</label><br>
        <input type="text" id="training_location" name="training_location" aria-label="Training Location"
               value="<?php echo htmlspecialchars($training_location ?? '', ENT_QUOTES, 'UTF-8'); ?>">
        <p class="form-caption" data-lang-id="020-location-caption">
            Please provide the general location where the training was conducted.
        </p>
        <div id="location-error-required" class="form-field-error" data-lang-id="000-field-required-error">This field is .</div>
    </div>

    <div class="form-item">
        <label for="moodle_url" data-lang-id="022-title-moodle">Moodle Course URL:</label><br>
        <input type="url" id="moodle_url" name="moodle_url"
               value="<?php echo htmlspecialchars($moodle_url ?? '', ENT_QUOTES, 'UTF-8'); ?>"
               placeholder="Enter Moodle Course Link" class="form-field-style">
        <p class="form-caption" data-lang-id="022-moodle-caption">
            Was there a Moodle course created for this training on learning.ecobricks.org? If so, include the URL here.
        </p>
    </div>

    <input type="hidden" id="youtube_result_video" name="youtube_result_video"
           value="<?php echo htmlspecialchars($youtube_result_video ?? '', ENT_QUOTES, 'UTF-8'); ?>">

    <div class="form-item">
        <label for="zoom_link">Zoom Link:</label><br>
        <input type="url" id="zoom_link" name="zoom_link" class="form-field-style"
               value="<?php echo htmlspecialchars($zoom_link ?? '', ENT_QUOTES, 'UTF-8'); ?>">
        <p class="form-caption">
            Set the Zoom link if this is an online course with just the URL for the event.
        </p>
    </div>

    <div class="form-item">
        <label for="zoom_link_full">Zoom Link Full:</label><br>
        <textarea id="zoom_link_full" name="zoom_link_full" class="form-field-style"><?php echo htmlspecialchars($zoom_link_full ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
        <p class="form-caption">
            Paste the full Zoom link with as much accompanying text as you think appropriate.
        </p>
    </div>

    <div class="form-item">
        <label for="earthcal_event_url">📆 Earthcal Event Link</label><br>
        <input type="url" id="earthcal_event_url" name="earthcal_event_url" class="form-field-style"
               value="<?php echo htmlspecialchars($earthcal_event_url ?? '', ENT_QUOTES, 'UTF-8'); ?>">
        <p class="form-caption">
            Grab a Share-Event-Link from your Earthcal so that participants can easily add this training to their personal calendars
        </p>
    </div>

    <div class="form-item">
        <label for="trainer_contact_email">Trainer Contact Email</label><br>
        <input type="email" id="trainer_contact_email" name="trainer_contact_email" class="form-field-style"
               value="<?php echo htmlspecialchars($trainer_contact_email ?? '', ENT_QUOTES, 'UTF-8'); ?>">
        <p class="form-caption">
            Set the contact email for this course that folks can reply to.
        </p>
    </div>

    <?php if ($editing): ?>

    <br><hr><br>

    <h4>Training Feature Images</h4>
    <p>These images will be added to your course listing. These images are separate from the photos you will upload later for your final training report.</p>

    <div class="form-item">
        <?php if ($editing && !empty($feature_photo1_main)): ?>
            <div id="feature_photo1_preview_container">
                <img src="<?php echo htmlspecialchars(!empty($feature_photo1_tmb) ? $feature_photo1_tmb : $feature_photo1_main, ENT_QUOTES, 'UTF-8'); ?>" style="max-width:300px;max-height:180px;margin-bottom:5px;" alt="Feature Photo 1" id="feature_photo1_preview">
            </div>
        <?php else: ?>
            <div id="feature_photo1_preview_container" style="display:none;">
                <img id="feature_photo1_preview" style="max-width:300px;max-height:180px;margin-bottom:5px;" alt="Feature Photo 1">
            </div>
        <?php endif; ?>
        <label for="feature_photo1_main">Set Feature Photo</label><br>
        <input type="url" id="feature_photo1_main" name="feature_photo1_main" class="form-field-style" value="<?php echo htmlspecialchars($feature_photo1_main ?? '', ENT_QUOTES, 'UTF-8'); ?>">
        <input type="file" id="feature_photo1_file" name="feature_photo1_main" class="feature-photo-file" accept="image/*">
        <p class="form-caption">This is the image that will be used to list your training on GoBrik.</p>
    </div>

    <div class="form-item">
        <?php if ($editing && !empty($feature_photo2_main)): ?>
            <div id="feature_photo2_preview_container">
                <img src="<?php echo htmlspecialchars(!empty($feature_photo2_tmb) ? $feature_photo2_tmb : $feature_photo2_main, ENT_QUOTES, 'UTF-8'); ?>" style="max-width:300px;max-height:180px;margin-bottom:5px;" alt="Feature Photo 2" id="feature_photo2_preview">
            </div>
        <?php else: ?>
            <div id="feature_photo2_preview_container" style="display:none;">
                <img id="feature_photo2_preview" style="max-width:300px;max-height:180px;margin-bottom:5px;" alt="Feature Photo 2">
            </div>
        <?php endif; ?>
        <label for="feature_photo2_main">Set a Second Training Feature Photo</label><br>
        <input type="url" id="feature_photo2_main" name="feature_photo2_main" class="form-field-style" value="<?php echo htmlspecialchars($feature_photo2_main ?? '', ENT_QUOTES, 'UTF-8'); ?>">
        <input type="file" id="feature_photo2_file" name="feature_photo2_main" class="feature-photo-file" accept="image/*">
        <p class="form-caption">This image will be visible on the training registration page.</p>
    </div>

    <div class="form-item">
        <?php if ($editing && !empty($feature_photo3_main)): ?>
            <div id="feature_photo3_preview_container">
                <img src="<?php echo htmlspecialchars(!empty($feature_photo3_tmb) ? $feature_photo3_tmb : $feature_photo3_main, ENT_QUOTES, 'UTF-8'); ?>" style="max-width:300px;max-height:180px;margin-bottom:5px;" alt="Feature Photo 3" id="feature_photo3_preview">
            </div>
        <?php else: ?>
            <div id="feature_photo3_preview_container" style="display:none;">
                <img id="feature_photo3_preview" style="max-width:300px;max-height:180px;margin-bottom:5px;" alt="Feature Photo 3">
            </div>
        <?php endif; ?>
        <label for="feature_photo3_main">Set a Third Training Feature Photo</label><br>
        <input type="url" id="feature_photo3_main" name="feature_photo3_main" class="form-field-style" value="<?php echo htmlspecialchars($feature_photo3_main ?? '', ENT_QUOTES, 'UTF-8'); ?>">
        <input type="file" id="feature_photo3_file" name="feature_photo3_main" class="feature-photo-file" accept="image/*">
        <p class="form-caption">This image will also be visible on the training registration page.</p>
    </div>

    <br><hr>
    <h4>Training Registration & Payment</h4>
    <p>Configure how participants will register for this course: free, fixed-price, or as a 3P threshold course where people pledge first and only pay once the course reaches viability.</p>

    <div class="form-item">
        <label for="payment_mode">How will participants register for this training?</label><br>
        <select id="payment_mode" name="payment_mode" class="form-field-style">
            <option value="free" <?php echo ($payment_mode === 'free') ? 'selected' : ''; ?>>Free / By Donation</option>
            <option value="fixed_paid" <?php echo ($payment_mode === 'fixed_paid') ? 'selected' : ''; ?>>Fixed Paid Course</option>
            <option value="pledge_threshold" <?php echo ($payment_mode === 'pledge_threshold') ? 'selected' : ''; ?>>3P Course (Pledge, Proceed & Pay)</option>
        </select>
        <p class="form-caption">
            Choose the registration model. For 3P courses, participants pledge first, and actual payment is only requested once the course reaches the required threshold.
        </p>
    </div>

    <div id="payment-config-box" style="background:var(--lighter); padding:20px; border:1px solid #ccc; border-radius:12px; margin-top:20px;">

        <div class="form-item">
            <label for="base_currency">Base Currency</label><br>
            <select id="base_currency" name="base_currency" class="form-field-style">
                <?php
                $currencyOptions = ['IDR', 'USD', 'EUR', 'GBP', 'CAD', 'AUD'];
                foreach ($currencyOptions as $opt):
                ?>
                    <option value="<?php echo $opt; ?>" <?php echo (($base_currency ?? 'IDR') === $opt) ? 'selected' : ''; ?>><?php echo $opt; ?></option>
                <?php endforeach; ?>
            </select>
            <p class="form-caption">
                Use IDR as the canonical pricing and threshold currency for 3P courses. Display conversions can be handled later on the public registration page.
            </p>
        </div>

        <div class="form-item">
            <label for="default_price_idr">Suggested Registration Price (IDR)</label><br>
            <input type="number" id="default_price_idr" name="default_price_idr" min="0" step="1000"
                   value="<?php echo htmlspecialchars($default_price_idr ?? '', ENT_QUOTES, 'UTF-8'); ?>" class="form-field-style">
            <p class="form-caption">
                This is the suggested amount and the default starting point for the slider on 3P courses. For fixed-price courses, this is the set fee.
            </p>
        </div>

        <div class="form-item">
            <label for="display_cost">Public Cost Display Text</label><br>
            <input type="text" id="display_cost" name="display_cost" class="form-field-style"
                   value="<?php echo htmlspecialchars($display_cost ?? 'Free / Donation', ENT_QUOTES, 'UTF-8'); ?>">
            <p class="form-caption">
                This text is shown publicly on the course listing, for example: “Free / Donation”, “150,000 IDR”, or “Pledge-based / from 50,000 IDR”.
            </p>
        </div>

        <div id="fixed-paid-settings" style="display:none;">
            <div class="form-item">
                <label for="fixed_price_note">Fixed Paid Course</label><br>
                <div class="form-caption" style="background:#fff; padding:14px; border-radius:8px; border:1px solid #ddd;">
                    Participants will register and then proceed directly into payment. The amount charged will use the suggested registration price above.
                </div>
            </div>
        </div>

        <div id="threep-settings" style="display:none; border-top:1px dashed #bbb; margin-top:20px; padding-top:20px;">
            <h5 style="margin-top:0;">3P Threshold Settings</h5>
            <p class="form-caption">
                These settings determine when the course becomes viable. Participants will pledge first. Once both the minimum participation and funding threshold are reached, they can be invited to complete payment.
            </p>

            <div class="form-item">
                <label for="min_participants_required">Minimum Participants Required</label><br>
                <input type="number" id="min_participants_required" name="min_participants_required" min="1" max="5000"
                       value="<?php echo htmlspecialchars($min_participants_required ?? '', ENT_QUOTES, 'UTF-8'); ?>" class="form-field-style">
                <p class="form-caption">
                    The course will not proceed unless at least this many participants have pledged / registered.
                </p>
            </div>

            <div class="form-item">
                <label for="funding_goal_idr">Funding Goal Required (IDR)</label><br>
                <input type="number" id="funding_goal_idr" name="funding_goal_idr" min="0" step="1000"
                       value="<?php echo htmlspecialchars($funding_goal_idr ?? '', ENT_QUOTES, 'UTF-8'); ?>" class="form-field-style">
                <p class="form-caption">
                    The minimum total amount pledged before the course can proceed to payment collection.
                </p>
            </div>

            <div class="form-item">
                <label for="min_pledge_idr">Minimum Allowed Pledge (IDR)</label><br>
                <input type="number" id="min_pledge_idr" name="min_pledge_idr" min="0" step="1000"
                       value="<?php echo htmlspecialchars($min_pledge_idr ?? 0, ENT_QUOTES, 'UTF-8'); ?>" class="form-field-style">
                <p class="form-caption">
                    The lowest amount a participant may choose on the pledge slider. Set to 0 to allow free support pledges / registrations.
                </p>
            </div>

            <div class="form-item">
                <label for="max_pledge_idr">Maximum Allowed Pledge (IDR)</label><br>
                <input type="number" id="max_pledge_idr" name="max_pledge_idr" min="0" step="1000"
                       value="<?php echo htmlspecialchars($max_pledge_idr ?? '', ENT_QUOTES, 'UTF-8'); ?>" class="form-field-style">
                <p class="form-caption">
                    Optional cap for the slider. Leave blank to allow open-ended over-pledging.
                </p>
            </div>

            <div class="form-item">
                <label for="pledge_deadline">Pledge Deadline</label><br>
                <input type="datetime-local" id="pledge_deadline" name="pledge_deadline" class="form-field-style"
                       value="<?php echo !empty($pledge_deadline) ? date('Y-m-d\TH:i', strtotime($pledge_deadline)) : ''; ?>">
                <p class="form-caption">
                    The deadline for people to make pledges toward the course threshold.
                </p>
            </div>

            <div class="form-item">
                <label for="payment_deadline">Payment Deadline</label><br>
                <input type="datetime-local" id="payment_deadline" name="payment_deadline" class="form-field-style"
                       value="<?php echo !empty($payment_deadline) ? date('Y-m-d\TH:i', strtotime($payment_deadline)) : ''; ?>">
                <p class="form-caption">
                    Once the threshold is reached and payment invitations are sent, this is the deadline by which participants must complete payment.
                </p>
            </div>

            <div class="form-item">
                <label for="threshold_status">Threshold Status</label><br>
                <select id="threshold_status" name="threshold_status" class="form-field-style">
                    <?php
                    $thresholdStatuses = ['open', 'threshold_met', 'confirmed', 'failed', 'cancelled', 'completed'];
                    foreach ($thresholdStatuses as $statusOpt):
                    ?>
                        <option value="<?php echo $statusOpt; ?>" <?php echo (($threshold_status ?? 'open') === $statusOpt) ? 'selected' : ''; ?>>
                            <?php echo ucfirst(str_replace('_', ' ', $statusOpt)); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <p class="form-caption">
                    Usually this should remain “open” when the course is first launched.
                </p>
            </div>

            <div class="form-row" style="display:flex;flex-flow:row;background-color:#fff;padding:20px;border:#ddd 1px solid;border-radius:12px;margin-top:20px;">
                <div style="width:100%;">
                    <label>✅ Automatically confirm when threshold is reached</label>
                    <p class="form-caption" style="margin-top:10px;">
                        If enabled, the course can automatically move forward once both thresholds are met. If disabled, you can review it manually first.
                    </p>
                </div>
                <div style="width:100px; justify-content:center;">
                    <label class="toggle-switch">
                        <input type="checkbox" id="auto_confirm_threshold" name="auto_confirm_threshold" value="1"
                            <?php echo (!empty($auto_confirm_threshold)) ? 'checked' : ''; ?>>
                        <span class="slider"></span>
                    </label>
                </div>
            </div>

            <div class="form-row" style="display:flex;flex-flow:row;background-color:#fff;padding:20px;border:#ddd 1px solid;border-radius:12px;margin-top:20px;">
                <div style="width:100%;">
                    <label>💚 Allow over-pledging</label>
                    <p class="form-caption" style="margin-top:10px;">
                        If enabled, participants may pledge above the suggested price to further support the training.
                    </p>
                </div>
                <div style="width:100px; justify-content:center;">
                    <label class="toggle-switch">
                        <input type="checkbox" id="allow_overpledge" name="allow_overpledge" value="1"
                            <?php echo (!empty($allow_overpledge)) ? 'checked' : ''; ?>>
                        <span class="slider"></span>
                    </label>
                </div>
            </div>
        </div>
    </div>

    <!-- Legacy compatibility fields during refactor -->
    <input type="hidden" id="currency" name="currency" value="<?php echo htmlspecialchars($currency ?? '', ENT_QUOTES, 'UTF-8'); ?>">
    <input type="hidden" id="cost" name="cost" value="<?php echo htmlspecialchars($cost ?? '', ENT_QUOTES, 'UTF-8'); ?>">

    <div class="form-row" style="display:flex;flex-flow:row;background-color:var(--lighter);padding:20px;border:grey 1px solid;border-radius:12px;margin-top:20px;">
        <div id="left-colum" style="width: 100%;">
            <label>🚀 Launch Training</label>
            <p class="form-caption" data-lang-id="022-training-show" style="margin-top:10px;">
                Is this training ready to be displayed on ecobricks.org? If so, we'll post the completed workshop to the live feed of GEA trainings. Don’t worry — you can always come back to edit the listing!
            </p>
        </div>

        <div id="right-column" style="width:100px; justify-content:center;">
            <label class="toggle-switch">
                <input type="checkbox" id="ready_to_show" name="ready_to_show" value="1"
                    <?php echo (isset($ready_to_show) && $ready_to_show) ? 'checked' : ''; ?>>
                <span class="slider"></span>
            </label>
        </div>
    </div>

    <div class="form-row" style="display:flex;flex-flow:row;background-color:var(--lighter);padding:20px;border:grey 1px solid;border-radius:12px;margin-top:20px;">
        <div id="left-colum" style="width: 100%;">
            <label>📋 Show Training Report</label>
            <p class="form-caption" style="margin-top:10px;">Display the final training report of this training on ecobricks.org</p>
        </div>

        <div id="right-column" style="width:100px; justify-content:center;">
            <label class="toggle-switch">
                <input type="checkbox" id="show_report" name="show_report" value="1"
                    <?php echo (isset($show_report) && $show_report) ? 'checked' : ''; ?>>
                <span class="slider"></span>
            </label>
        </div>
    </div>

    <div class="form-row" style="display:flex;flex-flow:row;background-color:var(--lighter);padding:20px;border:grey 1px solid;border-radius:12px;margin-top:20px;">
        <div id="left-colum" style="width: 100%;">
            <label>🔢 Show Signup Count</label>
            <p class="form-caption" style="margin-top:10px;">Display the count of registrations on the training's registration page.</p>
        </div>

        <div id="right-column" style="width:100px; justify-content:center;">
            <label class="toggle-switch">
                <input type="checkbox" id="show_signup_count" name="show_signup_count" value="1"
                    <?php echo (isset($show_signup_count) && $show_signup_count) ? 'checked' : ''; ?>>
                <span class="slider"></span>
            </label>
        </div>
    </div>
    <?php endif; ?>

    <input type="hidden" id="training_id" name="training_id" value="<?php echo htmlspecialchars($training_id ?? '', ENT_QUOTES, 'UTF-8'); ?>">

    <div>
        <input type="submit" value="<?php echo $editing ? '💾 Save Changes to Training' : '➕ Create Training!'; ?>" data-lang-id="100-submit-report-1">
    </div>

</form>

        </div>
    </div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>

<script>
var preselectedTrainers = <?php echo json_encode($selected_trainers_data); ?>;
var countries = <?php echo json_encode($countries); ?>;
var languages = <?php echo json_encode($languages); ?>;
const userLanguageId = "<?php echo $current_lang_dir; ?>";
const userCountryId = "<?php echo htmlspecialchars($user_country_id ?? '', ENT_QUOTES, 'UTF-8'); ?>";

document.addEventListener("DOMContentLoaded", function() {
    const communityNameInput = document.getElementById("community_search");
    const communityIdInput = document.getElementById("community_id");
    const suggestionsBox = document.getElementById("community_results");

    communityNameInput.addEventListener("input", function() {
        const query = communityNameInput.value.trim();
        communityIdInput.value = "";
        if (query.length >= 3) {
            fetch(`https://buwana.ecobricks.org/api/api/search_communities_by_id.php?query=${encodeURIComponent(query)}`)
                .then(res => res.json())
                .then(list => showCommunitySuggestions(list, suggestionsBox, communityNameInput, communityIdInput))
                .catch(err => console.error('Error loading communities:', err));
        } else {
            suggestionsBox.innerHTML = '';
        }
    });

    document.addEventListener("click", function(event) {
        if (!communityNameInput.contains(event.target) && !suggestionsBox.contains(event.target)) {
            suggestionsBox.innerHTML = "";
        }
    });

    const trainerInput = document.getElementById("trainer_search");
    const trainerResults = document.getElementById("trainer_results");
    const trainerContainer = document.getElementById("selected_trainers");

    function fetchTrainers(query) {
        if (query.length >= 3) {
            fetch(`../api/search_trainers.php?query=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(data => {
                    trainerResults.innerHTML = "";
                    if (data.length === 0) {
                        trainerResults.innerHTML = "<div class='autocomplete-item' style='color: gray;'>No results found</div>";
                    } else {
                        data.forEach(trainer => {
                            let div = document.createElement("div");
                            div.textContent = trainer.full_name;
                            div.dataset.id = trainer.ecobricker_id;
                            div.classList.add("autocomplete-item");
                            div.addEventListener("mousedown", function(event) {
                                event.preventDefault();
                                addTrainer(trainer.ecobricker_id, trainer.full_name);
                                trainerInput.value = "";
                                trainerResults.innerHTML = "";
                            });
                            trainerResults.appendChild(div);
                        });
                    }
                });
        } else {
            trainerResults.innerHTML = "";
        }
    }

    function addTrainer(id, name) {
        if (document.getElementById('trainer-hidden-' + id)) return;
        let box = document.createElement('div');
        box.className = 'trainer-tag-box';
        box.dataset.id = id;

        let remove = document.createElement('span');
        remove.className = 'remove-trainer';
        remove.textContent = '\u00D7';
        remove.addEventListener('click', function() {
            box.remove();
        });

        let text = document.createElement('span');
        text.textContent = name;

        let hidden = document.createElement('input');
        hidden.type = 'hidden';
        hidden.name = 'trainers[]';
        hidden.value = id;
        hidden.id = 'trainer-hidden-' + id;

        box.appendChild(remove);
        box.appendChild(text);
        box.appendChild(hidden);
        trainerContainer.appendChild(box);
    }

    document.querySelectorAll('#selected_trainers .trainer-tag-box').forEach(function(box) {
        var removeBtn = box.querySelector('.remove-trainer');
        if (removeBtn) {
            removeBtn.addEventListener('click', function() {
                box.remove();
            });
        }
    });

    trainerInput.addEventListener('input', function() {
        fetchTrainers(trainerInput.value.trim());
    });

    document.addEventListener('click', function(event) {
        if (!trainerInput.contains(event.target) && !trainerResults.contains(event.target)) {
            trainerResults.innerHTML = '';
        }
    });

    if (preselectedTrainers && Array.isArray(preselectedTrainers)) {
        preselectedTrainers.forEach(tr => addTrainer(tr.ecobricker_id, tr.full_name));
    }

    initPaymentModeUI();
    syncLegacyPricingFields();
});

// 3P UI helpers
function initPaymentModeUI() {
    const paymentMode = document.getElementById('payment_mode');
    const paymentConfigBox = document.getElementById('payment-config-box');
    const fixedPaidSettings = document.getElementById('fixed-paid-settings');
    const threepSettings = document.getElementById('threep-settings');

    function refreshPaymentSections() {
        const mode = paymentMode ? paymentMode.value : 'free';

        if (paymentConfigBox) {
            paymentConfigBox.style.display = 'block';
        }

        if (fixedPaidSettings) {
            fixedPaidSettings.style.display = (mode === 'fixed_paid') ? 'block' : 'none';
        }

        if (threepSettings) {
            threepSettings.style.display = (mode === 'pledge_threshold') ? 'block' : 'none';
        }

        syncLegacyPricingFields();
    }

    if (paymentMode) {
        paymentMode.addEventListener('change', refreshPaymentSections);
        refreshPaymentSections();
    }
}

function syncLegacyPricingFields() {
    const paymentMode = document.getElementById('payment_mode')?.value || 'free';
    const defaultPrice = document.getElementById('default_price_idr')?.value || '';
    const baseCurrency = document.getElementById('base_currency')?.value || 'IDR';
    const legacyCost = document.getElementById('cost');
    const legacyCurrency = document.getElementById('currency');

    if (!legacyCost || !legacyCurrency) return;

    if (paymentMode === 'free') {
        legacyCost.value = '';
        legacyCurrency.value = '';
    } else {
        legacyCost.value = defaultPrice;
        legacyCurrency.value = baseCurrency;
    }
}

document.getElementById('default_price_idr')?.addEventListener('input', syncLegacyPricingFields);
document.getElementById('base_currency')?.addEventListener('change', syncLegacyPricingFields);

document.getElementById('submit-form').addEventListener('submit', function(event) {
    event.preventDefault();
    var isValid = true;
    var trainingIdField = document.getElementById('training_id');
    var trainingId = trainingIdField ? trainingIdField.value.trim() : "";

    function displayError(elementId, showError) {
        var errorDiv = document.getElementById(elementId);
        if (errorDiv) {
            errorDiv.style.display = showError ? 'block' : 'none';
            if (showError) isValid = false;
        }
    }

    function hasInvalidChars(value) {
        const invalidChars = /[<>]/;
        return invalidChars.test(value);
    }

    var trainingTitle = document.getElementById('training_title').value.trim();
    displayError('title-error-long', trainingTitle.length > 500);
    displayError('title-error-invalid', hasInvalidChars(trainingTitle));

    var noParticipants = parseInt(document.getElementById('no_participants').value, 10);
    if (isNaN(noParticipants) || noParticipants === '') {
        displayError('participants-error-range', false);
    } else if (noParticipants < 1 || noParticipants > 5000) {
        displayError('participants-error-range', true);
    } else {
        displayError('participants-error-range', false);
    }

    var featuredDescription = document.getElementById('featured_description').value.trim();
    displayError('featured-error-long', featuredDescription.length > 2000);

    const paymentMode = document.getElementById('payment_mode')?.value || 'free';
    const defaultPrice = parseInt(document.getElementById('default_price_idr')?.value || '0', 10);
    const fundingGoal = parseInt(document.getElementById('funding_goal_idr')?.value || '0', 10);
    const minParticipantsRequired = parseInt(document.getElementById('min_participants_required')?.value || '0', 10);
    const minPledge = parseInt(document.getElementById('min_pledge_idr')?.value || '0', 10);
    const maxPledgeRaw = document.getElementById('max_pledge_idr')?.value || '';
    const maxPledge = maxPledgeRaw === '' ? null : parseInt(maxPledgeRaw, 10);

    if (paymentMode === 'fixed_paid') {
        if (isNaN(defaultPrice) || defaultPrice < 0) {
            isValid = false;
            alert('Please set a valid suggested registration price for this paid course.');
        }
    }

    if (paymentMode === 'pledge_threshold') {
        if (isNaN(defaultPrice) || defaultPrice < 0) {
            isValid = false;
            alert('Please set a valid suggested registration price for this 3P course.');
        } else if (isNaN(fundingGoal) || fundingGoal <= 0) {
            isValid = false;
            alert('Please set a funding goal for this 3P course.');
        } else if (isNaN(minParticipantsRequired) || minParticipantsRequired <= 0) {
            isValid = false;
            alert('Please set the minimum participants required for this 3P course.');
        } else if (isNaN(minPledge) || minPledge < 0) {
            isValid = false;
            alert('Please set a valid minimum pledge.');
        } else if (maxPledge !== null && maxPledge < minPledge) {
            isValid = false;
            alert('Maximum pledge cannot be less than minimum pledge.');
        }
    }

    if (!isValid) {
        var firstError = document.querySelector('.form-field-error:not([style*="display: none"])');
        if (firstError) {
            firstError.scrollIntoView({behavior: "smooth", block: "center"});
            var relatedInput = firstError.closest('.form-item')?.querySelector('input, select, textarea');
            if (relatedInput) relatedInput.focus();
        }
        return;
    }

    syncLegacyPricingFields();

    var formData = new FormData(this);
    var submitButton = document.querySelector('input[type="submit"]');
    var originalButtonText = submitButton.value;
    submitButton.value = "Processing...";
    submitButton.disabled = true;

    fetch(this.action, {
        method: "POST",
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            var newTrainingId = data.training_id || trainingId;
            showTrainingSavedModal(newTrainingId);
            submitButton.value = originalButtonText;
            submitButton.disabled = false;
        } else {
            alert("Error: " + (data.error || "An unknown error occurred."));
            submitButton.value = originalButtonText;
            submitButton.disabled = false;
        }
    })
    .catch(error => {
        console.error("Submission error:", error);
        alert("There was a problem submitting the form.");
        submitButton.value = originalButtonText;
        submitButton.disabled = false;
    });
});

function showTrainingSavedModal(trainingId) {
    const modal = document.getElementById('form-modal-message');
    const modalBox = document.getElementById('modal-content-box');

    modal.style.display = 'flex';
    modalBox.style.flexFlow = 'column';
    document.getElementById('page-content')?.classList.add('blurred');
    document.getElementById('footer-full')?.classList.add('blurred');
    document.body.classList.add('modal-open');

    const readyToShow = document.getElementById('ready_to_show')?.checked;
    const listingMsg = readyToShow
        ? 'Your training is currently publicly listed and live'
        : 'Your training is not yet listed.';

    modalBox.innerHTML = `
        <h1 style="text-align:center;">🐿️</h1>
        <h2 style="text-align:center;">Training Saved</h2>
        <p style="text-align:center;">Your training was saved. ${listingMsg}</p>
        <div style="text-align:center;width:100%;margin:auto;margin-top:10px;">
            <a href="launch-training.php?id=${trainingId}" class="confirm-button enabled" style="margin:10px;">Continue Editing</a>
            <a href="register.php?id=${trainingId}" class="confirm-button enabled" style="margin:10px;">View Registration</a>
            <a href="courses.php" class="confirm-button enabled" style="margin:10px;">View Listings</a>
        </div>
    `;
}

function setFileInputFromUrl(inputId, url) {
    const input = document.getElementById(inputId);
    if (input) {
        input.value = url;
    }
}

function presetForStarterWorkshop() {
    document.getElementById('training_title').value = 'Plastic, the Biosphere and Ecobricks';
    document.getElementById('training_subtitle').value = 'An Introduction to Ecobricking';
    document.getElementById('no_participants').value = 30;
    document.getElementById('community_search').value = 'Global Ecobrick Movement';
    document.getElementById('training_date').value = '2025-07-18T19:30';
    document.getElementById('training_time_txt').value = 'Jakarta 7:30 PM / UK 1:30 PM / Namibia 2:30 PM';

    const typeSelect = document.getElementById('training_type');
    if (typeSelect) {
        for (let opt of typeSelect.options) {
            if (opt.text.trim() === 'Online Starter Workshop' || opt.value === 'Online Starter Workshop') {
                typeSelect.value = opt.value;
                break;
            }
        }
    }

    const langSelect = document.getElementById('training_language');
    if (langSelect) {
        for (let opt of langSelect.options) {
            if (opt.text.trim() === 'English' || opt.value === 'English' || opt.value === 'en') {
                langSelect.value = opt.value;
                break;
            }
        }
    }

    const countrySelect = document.getElementById('country_id');
    if (countrySelect) {
        for (let opt of countrySelect.options) {
            if (opt.text.trim() === 'Earth' || opt.value === 'Earth') {
                countrySelect.value = opt.value;
                break;
            }
        }
    }

    document.getElementById('featured_description').value = `Just getting started ecobricking? Curious what it ecobricking is all about?  Want to be sure you are on the right track?  This basic, 1.5 our course will get you started right.  You’ll learn a lot more than just how to pack a bottle!`;

    document.getElementById('training_agenda').value = `In this introduction to ecobricking we will cover not just how to make an ecobrick, but <i>why</i>.
We’ll go over the both the primordial and modern story of our everyday plastic, as well as the illusions and dangers of it. We'll take a look at industrial plastic solutions and why ecobricking remains as relevant as ever. Finally, we'll go over correct ecobrick technique, best practices, and building possibilities.

<b>What you will learn...</b>

\t1. The primordial Earthen origins of plastic
\t2. How we can follow Earth’s example with our plastic
\t3. The modern petro-capital context of plastic
\t4. The shortcomings of industrial recycling
\t5. The dangers of plastic when it gets into the environment
\t6. The spiral green principle behind ecobricking
\t7. How to Ecobrick
\t8. Using the GoBrik app
\t9. Using ecobricks for Modules and Earth Building

<b>Schedule...</b>

\t• 40 minutes of science and theory
\t• 20 minutes of ecobrick technique and best practices
\t• 10 minutes on ecobrick applications
\t• 20 minutes of questions and discussion.

<b>Payment...</b>

Course registration is free / by donation so no payment is required to register.  Ater the course, donations are welcome (suggested donation is 20$ / 20 GBP / 200k IDR).  20% of course proceeds go to the Global Ecobrick Alliance`;

    const regScope = document.getElementById('registration_scope');
    if (regScope) regScope.value = 'anyone';

    const paymentMode = document.getElementById('payment_mode');
    if (paymentMode) paymentMode.value = 'free';

    const baseCurrency = document.getElementById('base_currency');
    if (baseCurrency) baseCurrency.value = 'IDR';

    const defaultPrice = document.getElementById('default_price_idr');
    if (defaultPrice) defaultPrice.value = '';

    const displayCost = document.getElementById('display_cost');
    if (displayCost) displayCost.value = 'Free / Donation';

    const minPledge = document.getElementById('min_pledge_idr');
    if (minPledge) minPledge.value = 0;

    initPaymentModeUI();

    setFileInputFromUrl('feature_photo1_main', 'https://gobrik.com/webps/starter-workshop-feature-1-en.webp');
    setFileInputFromUrl('feature_photo2_main', 'https://gobrik.com/webps/starter-workshop-feature-2-en.webp');
    setFileInputFromUrl('feature_photo3_main', 'https://gobrik.com/webps/starter-workshop-feature-3-en.webp');
}

document.getElementById('starterPresetBtn')?.addEventListener('click', presetForStarterWorkshop);

document.querySelectorAll('.feature-photo-file').forEach(input => {
    input.addEventListener('change', function() {
        if (!this.files || this.files.length === 0) return;
        const trainingId = document.getElementById('training_id').value;
        if (!trainingId) return;
        const formData = new FormData();
        formData.append('training_id', trainingId);
        formData.append(this.name, this.files[0]);

        fetch('add-images_process.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (!data.success) {
                alert(data.error || 'Upload failed');
                return;
            }
            const urlInput = document.getElementById(this.name);
            if (data[this.name]) urlInput.value = data[this.name];
            const preview = document.getElementById(this.name + '_preview');
            const container = document.getElementById(this.name + '_preview_container');
            if (preview) {
                const thumbKey = this.name.replace('_main', '_tmb');
                preview.src = data[thumbKey] || data[this.name];
                if (container) container.style.display = 'block';
            }
        })
        .catch(err => {
            console.error(err);
            alert('Upload error');
        });
    });
});
</script>

    <br><br>
    </div>

    <?php require_once("../footer-2026.php"); ?>
    </div>

    </body>
</html>