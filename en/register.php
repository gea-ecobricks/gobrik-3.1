<?php
require_once '../earthenAuth_helper.php';

// Set page variables
$lang = basename(dirname($_SERVER['SCRIPT_NAME']));
$version = '0.33';
$page = 'register';
$lastModified = date("Y-m-d\TH:i:s\Z", filemtime(__FILE__));
$is_logged_in = isLoggedIn();

// Get training ID from the URL
$training_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// If no valid training ID is provided, redirect to the courses list
if ($training_id <= 0) {
    header('Location: courses.php');
    exit();
}

// Initialize training variables
$training_title = $training_date = $lead_trainer = "";
$training_type = $training_country = $training_location = "";
$country_id = 0;
$language_id = '';
$training_language = '';
$training_url = "";
$cost = '';
$currency = '';
$ecobricker_id = null;
$users_email_address = '';
$is_registered = false;
$is_pledged = false;
$is_confirmed_registration = false;
$registration_status_new = null;

// 3P defaults
$payment_mode = 'free';
$base_currency = 'IDR';
$default_price_idr = 0;
$funding_goal_idr = 0;
$min_participants_required = 0;
$pledge_deadline = '';
$payment_deadline = '';
$threshold_status = 'open';
$display_cost = 'Free / Donation';

$total_registrations_count = 0;
$total_pledges_count = 0;
$total_amount_pledged = 0;
$pledged_percent = 0;

$feature_photo1_main = '';
$feature_photo2_main = '';
$feature_photo3_main = '';
$feature_photo1_tmb = '';

$first_name = '';
$earthling_emoji = '🌏';
$training_name = '';
$featured_description = '';
$training_agenda = '';
$registration_scope = '';
$ready_to_show = 0;
$show_signup_count = 0;
$no_participants = 0;
$pledge_deadline_display = 'the pledge deadline';
$pledged_amount_from_query = 0;
$pledged_display_currency_from_query = 'IDR';
$pledged_display_amount_from_query = '';

if ($is_logged_in) {
    $buwana_id = $_SESSION['buwana_id'];

    require_once '../gobrikconn_env.php';
    require_once '../buwanaconn_env.php';

    // Fetch the user's location data
    $user_continent_icon = getUserContinent($buwana_conn, $buwana_id);
    $earthling_emoji = getUserEarthlingEmoji($buwana_conn, $buwana_id);
    $user_location_watershed = getWatershedName($buwana_conn, $buwana_id);
    $user_location_full = getUserFullLocation($buwana_conn, $buwana_id);
    $gea_status = getGEA_status($buwana_id);
    $user_roles = getUser_Role($buwana_id);
    $user_community_name = getCommunityName($buwana_conn, $buwana_id);
    $first_name = getFirstName($buwana_conn, $buwana_id);

    // Fetch ecobricker_id and user's email using buwana_id
    $sql = "SELECT ecobricker_id, email_addr FROM tb_ecobrickers WHERE buwana_id = ?";
    $stmt = $gobrik_conn->prepare($sql);
    $stmt->bind_param("i", $buwana_id);
    $stmt->execute();
    $stmt->bind_result($ecobricker_id, $users_email_address);
    $stmt->fetch();
    $stmt->close();

    // Check new registration system first
    $sql_check_new = "SELECT registration_id, status FROM training_registrations_tb WHERE training_id = ? AND buwana_id = ? LIMIT 1";
    $stmt_check_new = $gobrik_conn->prepare($sql_check_new);
    if ($stmt_check_new) {
        $stmt_check_new->bind_param("ii", $training_id, $buwana_id);
        $stmt_check_new->execute();
        $stmt_check_new->bind_result($registration_id_new, $registration_status_new);
        if ($stmt_check_new->fetch()) {
            $registration_status_new = (string)$registration_status_new;

            if (in_array($registration_status_new, ['pledged', 'awaiting_payment'], true)) {
                $is_pledged = true;
            }

            if (in_array($registration_status_new, ['confirmed'], true)) {
                $is_confirmed_registration = true;
            }

            if (in_array($registration_status_new, ['reserved', 'pledged', 'awaiting_payment', 'confirmed'], true)) {
                $is_registered = true;
            }
        }
        $stmt_check_new->close();
    }

    // Fallback legacy table for free/older registrations
    if (!$is_registered && $ecobricker_id) {
        $sql_check = "SELECT id FROM tb_training_trainees WHERE training_id = ? AND ecobricker_id = ?";
        $stmt_check = $gobrik_conn->prepare($sql_check);
        if ($stmt_check) {
            $stmt_check->bind_param("ii", $training_id, $ecobricker_id);
            $stmt_check->execute();
            $stmt_check->store_result();
            if ($stmt_check->num_rows > 0) {
                $is_registered = true;
                $is_confirmed_registration = true;
            }
            $stmt_check->close();
        }
    }
} else {
    require_once '../gobrikconn_env.php';
}

// Fetch training details
$sql = "SELECT * FROM `tb_trainings` WHERE `training_id` = ?";
$stmt = $gobrik_conn->prepare($sql);
$stmt->bind_param("i", $training_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $allowed_tags = '<b><i><u><strong><em><p><br><ul><li><ol>';

    $featured_description = strip_tags($row['featured_description'] ?? '', $allowed_tags);
    $training_agenda = strip_tags($row['training_agenda'] ?? '', $allowed_tags);

    $training_title = htmlspecialchars($row['training_title'] ?? '', ENT_QUOTES, 'UTF-8', false);
    $training_name = $training_title;
    $training_subtitle = htmlspecialchars($row['training_subtitle'] ?? '', ENT_QUOTES, 'UTF-8', false);
    $training_date = htmlspecialchars($row['training_date'] ?? '', ENT_QUOTES, 'UTF-8');
    $training_time_txt = htmlspecialchars($row['training_time_txt'] ?? '', ENT_QUOTES, 'UTF-8');
    $training_logged = htmlspecialchars($row['training_logged'] ?? '', ENT_QUOTES, 'UTF-8');
    $lead_trainer = htmlspecialchars($row['lead_trainer'] ?? '', ENT_QUOTES, 'UTF-8', false);
    $training_type = htmlspecialchars($row['training_type'] ?? '', ENT_QUOTES, 'UTF-8');
    $country_id = intval($row['country_id'] ?? 0);
    $training_location = htmlspecialchars($row['training_location'] ?? '', ENT_QUOTES, 'UTF-8');
    $registration_scope = htmlspecialchars($row['registration_scope'] ?? '', ENT_QUOTES, 'UTF-8');
    $language_id = trim($row['training_language'] ?? '');

    $display_cost = htmlspecialchars($row['display_cost'] ?? '', ENT_QUOTES, 'UTF-8');
    $cost = htmlspecialchars($row['Cost'] ?? ($row['cost'] ?? ''), ENT_QUOTES, 'UTF-8');
    $currency = htmlspecialchars($row['Currency'] ?? ($row['currency'] ?? ''), ENT_QUOTES, 'UTF-8');

    // 3P fields
    $payment_mode = htmlspecialchars($row['payment_mode'] ?? 'free', ENT_QUOTES, 'UTF-8');
    $base_currency = htmlspecialchars($row['base_currency'] ?? 'IDR', ENT_QUOTES, 'UTF-8');
    $default_price_idr = intval($row['default_price_idr'] ?? 0);
    $funding_goal_idr = intval($row['funding_goal_idr'] ?? 0);
    $min_participants_required = intval($row['min_participants_required'] ?? 0);
    $pledge_deadline = htmlspecialchars($row['pledge_deadline'] ?? '', ENT_QUOTES, 'UTF-8');
    $payment_deadline = htmlspecialchars($row['payment_deadline'] ?? '', ENT_QUOTES, 'UTF-8');
    $threshold_status = htmlspecialchars($row['threshold_status'] ?? 'open', ENT_QUOTES, 'UTF-8');

    if (!empty($pledge_deadline)) {
        $pledge_deadline_display = date("F j, Y", strtotime($pledge_deadline));
    }

    // Signup count settings
    $show_signup_count = intval($row['show_signup_count'] ?? 0);
    $no_participants = intval($row['no_participants'] ?? 0);

    // Current live stats
    $count_stmt = $gobrik_conn->prepare("
        SELECT
            (SELECT COUNT(*) FROM training_registrations_tb WHERE training_id = ?) AS total_regs,
            (SELECT COUNT(*) FROM training_pledges_tb WHERE training_id = ? AND pledge_status IN ('active','invited','paid')) AS total_pledges,
            (SELECT COALESCE(SUM(pledged_amount_idr),0) FROM training_pledges_tb WHERE training_id = ? AND pledge_status IN ('active','invited','paid')) AS total_amount
    ");
    if ($count_stmt) {
        $count_stmt->bind_param("iii", $training_id, $training_id, $training_id);
        $count_stmt->execute();
        $count_stmt->bind_result($total_registrations_count, $total_pledges_count, $total_amount_pledged);
        $count_stmt->fetch();
        $count_stmt->close();
    }

    if ($funding_goal_idr > 0) {
        $pledged_percent = round(($total_amount_pledged / $funding_goal_idr) * 100, 1);
        if ($pledged_percent > 100) {
            $pledged_percent = 100;
        }
    }

    $training_url = htmlspecialchars($row['training_url'] ?? '', ENT_QUOTES, 'UTF-8');
    $ready_to_show = $row['ready_to_show'] ?? 0;

    $feature_photo1_main = htmlspecialchars($row['feature_photo1_main'] ?? '', ENT_QUOTES, 'UTF-8');
    $feature_photo2_main = htmlspecialchars($row['feature_photo2_main'] ?? '', ENT_QUOTES, 'UTF-8');
    $feature_photo3_main = htmlspecialchars($row['feature_photo3_main'] ?? '', ENT_QUOTES, 'UTF-8');
    $feature_photo1_tmb = htmlspecialchars($row['feature_photo1_tmb'] ?? '', ENT_QUOTES, 'UTF-8');

    if ((int)$ready_to_show === 0) {
        echo "<script>alert('Sorry this training isn\\'t yet listed for public registration.'); window.location.href='courses.php';</script>";
        exit;
    }

    // Look up language and country names
    require_once '../buwanaconn_env.php';

    $training_country = '';
    if ($country_id > 0) {
        $stmt_country = $buwana_conn->prepare("SELECT country_name FROM countries_tb WHERE country_id = ?");
        $stmt_country->bind_param("i", $country_id);
        $stmt_country->execute();
        $stmt_country->bind_result($training_country_name);
        $stmt_country->fetch();
        $stmt_country->close();
        $training_country = htmlspecialchars($training_country_name ?? '', ENT_QUOTES, 'UTF-8');
    }

    $training_language = '';
    if (!empty($language_id)) {
        $stmt_language = $buwana_conn->prepare("SELECT language_name_en FROM languages_tb WHERE language_id = ?");
        $stmt_language->bind_param("s", $language_id);
        $stmt_language->execute();
        $stmt_language->bind_result($training_language_name);
        $stmt_language->fetch();
        $stmt_language->close();
        $training_language = htmlspecialchars($training_language_name ?? '', ENT_QUOTES, 'UTF-8');
    }
}

$stmt->close();

// Status/progress helpers for bars
$status_participants_current = (int)$total_registrations_count;
$status_participants_threshold = ($min_participants_required > 0) ? $min_participants_required : 12;
$status_participants_max = max(25, $status_participants_threshold);

$status_pledged_current = (int)$total_amount_pledged;
$status_pledged_threshold = ($funding_goal_idr > 0) ? $funding_goal_idr : 700000;
$status_pledged_max = max(1500000, $status_pledged_threshold);

$participants_fill_pct = max(2, round(($status_participants_current / max(1, $status_participants_max)) * 100, 2));
$participants_threshold_pct = min(100, round(($status_participants_threshold / max(1, $status_participants_max)) * 100, 2));

$pledges_fill_pct = max(2, round(($status_pledged_current / max(1, $status_pledged_max)) * 100, 2));
$pledges_threshold_pct = min(100, round(($status_pledged_threshold / max(1, $status_pledged_max)) * 100, 2));

// Values for pledged success modal from redirect query
if (isset($_GET['pledged_amount_idr'])) {
    $pledged_amount_from_query = intval($_GET['pledged_amount_idr']);
}
if (isset($_GET['display_currency']) && $_GET['display_currency'] !== '') {
    $pledged_display_currency_from_query = preg_replace('/[^A-Z]/', '', strtoupper($_GET['display_currency']));
}
if (isset($_GET['display_amount']) && $_GET['display_amount'] !== '') {
    $pledged_display_amount_from_query = (string)$_GET['display_amount'];
}

$gobrik_conn->close();

// Button state labels
$primary_button_text = '';
if ($is_pledged) {
    $primary_button_text = "🤝 You're pledged";
} elseif ($is_confirmed_registration) {
    $primary_button_text = "✅ You're already registered";
} else {
    $primary_button_text = $is_logged_in ? ($earthling_emoji . " Register") : "🔑 Register";
}

$show_free_registered_notice = $is_confirmed_registration && !$is_pledged;
$show_pledged_notice = ($is_pledged || (isset($_GET['pledged']) && $_GET['pledged'] == 1));

echo '<!DOCTYPE html>
<html lang="' . $lang . '">
<head>
<meta charset="UTF-8">
';
?>

<?php require_once("../includes/register-inc.php"); ?>

<div class="splash-title-block"></div>
<div id="splash-bar"></div>

<div id="form-submission-box" class="register-page-shell">
    <div class="form-container register-page-container">

        <div class="register-content-wrap">

            <?php if ($show_free_registered_notice): ?>
                <div id="registered-notice" class="top-container-notice">
                    <span class="notice-icon">👍</span>
                    <span>You're registered for this <?php echo $training_type; ?>! See your email or <a href="dashboard.php">dashboard</a> for full registration details.</span>
                    <button class="notice-close" aria-label="Close">&times;</button>
                </div>
            <?php endif; ?>

            <?php if ($show_pledged_notice): ?>
                <div id="pledged-notice" class="top-container-notice">
                    <span class="notice-icon">🤝</span>
                    <span>Your pledge to participate has been made! Trainers notified and public stats updated 👇.</span>
                    <button class="notice-close" aria-label="Close">&times;</button>
                </div>
            <?php endif; ?>

            <div class="intro-to-training-wrapper register-intro-card">

                <img src="<?php echo $feature_photo1_main; ?>" class="register-lead-photo" id="event-lead-photo">

                <div class="training-title-box">
                    <div class="the-titles">
                        <h3><?php echo $training_title; ?></h3>
                        <h4 class="register-subtitle"><?php echo $training_subtitle; ?></h4>
                        <p class="register-meta-line"><?php echo date("F j, Y", strtotime($training_date)); ?> | <?php echo $training_time_txt; ?></p>
                        <p class="register-meta-line"><?php echo $training_type; ?></p>
                        <p class="register-meta-line"><span data-lang-id="000-open-to">Open to:</span> <?php echo $registration_scope; ?></p>
                        <p class="register-meta-line"><?php echo $display_cost; ?></p>

                        <button id="rsvp-register-button-desktop" class="register-main-button <?php echo $is_registered ? '' : 'enabled'; ?>">
                            <?php echo $primary_button_text; ?>
                        </button>
                    </div>

                    <div class="profile-images">
                        <img src="<?php echo $feature_photo3_main; ?>">
                        <p class="profile-names register-profile-line">Led by <?php echo $lead_trainer; ?></p>
                        <p class="profile-names register-profile-line register-profile-line-small">Language: <?php echo $training_language; ?></p>

                        <?php if ($show_signup_count === 1): ?>
                            <div class="profile-names register-signup-line">
                                <span class="signup-count-text">Registrations:</span>
                                <span class="signup-count-number"><?php echo $total_registrations_count; ?></span>
                                <span class="signup-count-text">of <?php echo max($no_participants, $status_participants_threshold); ?></span>
                            </div>
                        <?php endif; ?>

                        <?php if ($payment_mode === 'pledge_threshold'): ?>
                            <div class="register-status-panel">
                                <div class="register-status-title">Course Progress</div>

                                <div class="register-progress-block">
                                    <div class="register-progress-label">
                                        Participant Threshold
                                        <span><?php echo (int)$status_participants_current; ?> / <?php echo (int)$status_participants_max; ?></span>
                                    </div>
                                    <div class="register-progress-bar">
                                        <div class="register-progress-zone-before" style="width: <?php echo $participants_threshold_pct; ?>%;"></div>
                                        <div class="register-progress-zone-after" style="left: <?php echo $participants_threshold_pct; ?>%; width: <?php echo max(0, 100 - $participants_threshold_pct); ?>%;"></div>
                                        <div class="register-progress-fill is-red" style="width: <?php echo $participants_fill_pct; ?>%;"></div>
                                        <div class="register-progress-threshold" style="left: <?php echo $participants_threshold_pct; ?>%;"></div>
                                    </div>
                                    <div class="register-progress-meta">
                                        <strong>Current:</strong> <?php echo (int)$status_participants_current; ?> &nbsp;|&nbsp;
                                        <strong>Threshold:</strong> <?php echo (int)$status_participants_threshold; ?>
                                    </div>
                                </div>

                                <div class="register-progress-block">
                                    <div class="register-progress-label">
                                        Pledge Threshold
                                        <span><?php echo number_format((int)$status_pledged_current); ?> / <?php echo number_format((int)$status_pledged_max); ?> IDR</span>
                                    </div>
                                    <div class="register-progress-bar">
                                        <div class="register-progress-zone-before" style="width: <?php echo $pledges_threshold_pct; ?>%;"></div>
                                        <div class="register-progress-zone-after" style="left: <?php echo $pledges_threshold_pct; ?>%; width: <?php echo max(0, 100 - $pledges_threshold_pct); ?>%;"></div>
                                        <div class="register-progress-fill is-red" style="width: <?php echo $pledges_fill_pct; ?>%;"></div>
                                        <div class="register-progress-threshold" style="left: <?php echo $pledges_threshold_pct; ?>%;"></div>
                                    </div>
                                    <div class="register-progress-meta">
                                        <strong>Current:</strong> <?php echo number_format((int)$status_pledged_current); ?> IDR &nbsp;|&nbsp;
                                        <strong>Threshold:</strong> <?php echo number_format((int)$status_pledged_threshold); ?> IDR
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <button id="rsvp-register-button-mobile" class="register-main-button <?php echo $is_registered ? '' : 'enabled'; ?>">
                    <?php echo $primary_button_text; ?>
                </button>
            </div>

            <p class="register-featured-description"><?php echo nl2br(htmlspecialchars_decode($featured_description, ENT_QUOTES)); ?></p>
            <p class="register-agenda"><?php echo nl2br($training_agenda); ?></p>
        </div>

        <div id="event-details" class="dashboard-panel register-details-panel">
            <img src="<?php echo $feature_photo2_main; ?>" class="register-details-photo" id="event-lead-photo">

            <h2><?php echo $training_type; ?></h2>

            <hr>
            <p><strong>Title:</strong> <?php echo $training_title; ?></p>
            <p><strong>Subtitle:</strong> <?php echo $training_subtitle; ?></p>
            <p><strong>Date:</strong> <?php echo $training_date; ?></p>
            <p><strong>Time:</strong> <?php echo $training_time_txt; ?></p>
            <p><strong>Lead Trainer(s):</strong> <?php echo $lead_trainer; ?></p>
            <p><strong>Training Type:</strong> <?php echo $training_type; ?></p>
            <p><strong>Country:</strong> <?php echo $training_country; ?></p>
            <p><strong>Location:</strong> <?php echo $training_location; ?></p>
            <p><strong>Language:</strong> <?php echo $training_language; ?></p>
            <p><strong>Training Logged:</strong> <?php echo $training_logged; ?></p>
            <p><strong>Scope:</strong> <?php echo $registration_scope; ?></p>
            <p><strong>Display Cost:</strong> <?php echo $display_cost; ?></p>
            <p><strong>Payment Mode:</strong> <?php echo $payment_mode; ?></p>
            <?php if ($payment_mode === 'pledge_threshold'): ?>
                <p><strong>Suggested Price:</strong> <?php echo number_format($default_price_idr); ?> IDR</p>
                <p><strong>Funding Goal:</strong> <?php echo number_format($funding_goal_idr); ?> IDR</p>
                <p><strong>Minimum Registrants:</strong> <?php echo $min_participants_required; ?></p>
                <p><strong>Pledge Status:</strong> <?php echo $threshold_status; ?></p>
            <?php endif; ?>
        </div>

        <button id="rsvp-bottom-button" class="confirm-button register-bottom-button <?php echo $is_registered ? '' : 'enabled'; ?>">
            <?php echo $primary_button_text; ?>
        </button>

    </div>
</div>



<script>

/* =========================================================
GLOBAL CONSTANTS
========================================================= */

const TRAINING_PAYMENT_MODE = <?php echo json_encode($payment_mode); ?>;
const SUGGESTED_AMOUNT_IDR = <?php echo (int)$default_price_idr; ?>;
const TRAINING_ID = <?php echo (int)$training_id; ?>;
const ECOBRICKER_ID = <?php echo json_encode($ecobricker_id); ?>;

const IS_PLEDGED = <?php echo $is_pledged ? 'true' : 'false'; ?>;
const IS_CONFIRMED_REGISTRATION = <?php echo $is_confirmed_registration ? 'true' : 'false'; ?>;

/* =========================================================
CURRENCY
========================================================= */

const CURRENCY_RATES={
IDR:1,
USD:0.000064,
EUR:0.000059,
CAD:0.000087,
GBP:0.000050,
MYR:0.00030
};

/* =========================================================
UTILITIES
========================================================= */

function escapeHtml(str){
return String(str)
.replaceAll('&','&amp;')
.replaceAll('<','&lt;')
.replaceAll('>','&gt;')
.replaceAll('"','&quot;')
.replaceAll("'","&#039;");
}

function formatCurrencyFromIdr(idr,currency){

const safe=Number(idr||0);

if(currency==="IDR"){
return new Intl.NumberFormat('en-US',{maximumFractionDigits:0}).format(safe)+' IDR';
}

const converted=safe*(CURRENCY_RATES[currency]||1);

return new Intl.NumberFormat('en-US',{
minimumFractionDigits:0,
maximumFractionDigits:2
}).format(converted)+' '+currency;

}

/* =========================================================
COLOR SYSTEM
========================================================= */

function mixColors(c1,c2,t){

const r=Math.round(parseInt(c1.substr(1,2),16)*(1-t)+parseInt(c2.substr(1,2),16)*t);
const g=Math.round(parseInt(c1.substr(3,2),16)*(1-t)+parseInt(c2.substr(3,2),16)*t);
const b=Math.round(parseInt(c1.substr(5,2),16)*(1-t)+parseInt(c2.substr(5,2),16)*t);

return `rgb(${r},${g},${b})`;

}

function getPledgeColor(v,min,max,suggested){

if(max<=min)return'#7ccf7a';

if(v<=suggested){

const t=(v-min)/(suggested-min||1);

return mixColors('#e8902f','#7ed957',t);

}

const t=(v-suggested)/(max-suggested||1);

return mixColors('#7ed957','#1e6a2b',t);

}

/* =========================================================
REGISTRATION BUTTON
========================================================= */

document.querySelectorAll(
'#rsvp-bottom-button,#rsvp-register-button-desktop,#rsvp-register-button-mobile'
).forEach(btn=>{
if(btn)btn.addEventListener('click',handleRegistrationClick);
});

function handleRegistrationClick(){

<?php if ($is_logged_in && isset($ecobricker_id)): ?>

<?php if ($is_registered): ?>

openCancelRegistrationModal();

<?php else: ?>

if(TRAINING_PAYMENT_MODE==='pledge_threshold'){
open3PRegistrationModal();
}else{
openConfirmRegistrationModal();
}

<?php endif; ?>

<?php else: ?>

openInfoModal();

<?php endif; ?>

}

/* =========================================================
3P MODAL
========================================================= */

function open3PRegistrationModal(){

const modal=document.getElementById('form-modal-message');
const box=modal.querySelector('.modal-message');

const suggested=Math.max(0,Number(SUGGESTED_AMOUNT_IDR||0));
const min=0;
const max=Math.max(suggested*2,1000);

box.innerHTML=`
<div class="threep-modal-wrap">

<h2>Pledge your Participation</h2>

<div class="threep-amount-readout" id="threep_amount_readout"></div>

<input type="range" id="threep_pledge_slider" min="${min}" max="${max}" step="1000">

<a id="threep_confirm_button"
class="confirm-button enabled threep-confirm-button">
🤝 Confirm Course Pledge
</a>

</div>
`;

modal.style.display='flex';

const slider=document.getElementById('threep_pledge_slider');
const readout=document.getElementById('threep_amount_readout');
const btn=document.getElementById('threep_confirm_button');

slider.value=suggested;

function update(){

const v=Number(slider.value||0);

readout.textContent=formatCurrencyFromIdr(v,'IDR');

const pct=(v-min)/(max-min);

const color=getPledgeColor(v,min,max,suggested);

slider.style.setProperty('--pledge-color',color);

slider.style.background=
`linear-gradient(90deg,
${color} 0%,
${color} ${pct*100}%,
#d9e6d6 ${pct*100}%,
#d9e6d6 100%)`;

btn.style.background=color;
btn.style.borderColor=color;

btn.href=
"registration_confirmation.php?id="+TRAINING_ID+
"&ecobricker_id="+ECOBRICKER_ID+
"&mode=pledge_threshold"+
"&pledged_amount_idr="+encodeURIComponent(v);

}

slider.addEventListener('input',update);

update();

}

/* =========================================================
CANCEL MODAL
========================================================= */

function openCancelRegistrationModal(){

const modal=document.getElementById('form-modal-message');
const box=modal.querySelector('.modal-message');

box.innerHTML=`
<div class="register-modal-stack register-modal-centered">

<h1>💔</h1>

<h2>Cancel Registration?</h2>

<a href="#" id="confirm-unregister"
class="confirm-button register-button-danger">
Cancel Registration
</a>

</div>
`;

modal.style.display='flex';

document
.getElementById('confirm-unregister')
.addEventListener('click',e=>{

e.preventDefault();

fetch('../api/unregister_training.php?id='+TRAINING_ID+'&ecobricker_id='+ECOBRICKER_ID)
.then(r=>r.json())
.then(d=>{
if(d.success)location.reload();
else alert('Unable to cancel registration.');
});

});

}

/* =========================================================
HOVER CANCEL STATE
========================================================= */

document.addEventListener('DOMContentLoaded',()=>{

if(!(IS_PLEDGED||IS_CONFIRMED_REGISTRATION))return;

const buttons=[
document.getElementById('rsvp-bottom-button'),
document.getElementById('rsvp-register-button-desktop'),
document.getElementById('rsvp-register-button-mobile')
];

const text=IS_PLEDGED?'💔 Cancel Pledge':'💔 Cancel Registration';

buttons.forEach(btn=>{
if(!btn)return;

btn.addEventListener('mouseenter',()=>{
btn.dataset.originalText=btn.innerHTML;
btn.innerHTML=text;
btn.style.background='#777';
});

btn.addEventListener('mouseleave',()=>{
btn.innerHTML=btn.dataset.originalText;
btn.style.background='';
});

});

});

</script>

<?php
$relanding = false;
if (isset($_GET['status']) && $_GET['status'] == 'relanding') {
    $relanding = true;
} elseif (isset($_GET['id']) && strpos($_GET['id'], 'status=relanding') !== false) {
    $relanding = true;
} elseif (strpos($_SERVER['REQUEST_URI'], 'status=relanding') !== false) {
    $relanding = true;
}
if ($relanding): ?>
<script>
document.addEventListener("DOMContentLoaded", function() {
    if (TRAINING_PAYMENT_MODE === 'pledge_threshold') {
        open3PRegistrationModal(
            <?php echo json_encode($training_name); ?>,
            <?php echo json_encode($training_type); ?>,
            <?php echo json_encode($training_date); ?>,
            <?php echo json_encode($training_time_txt); ?>,
            <?php echo json_encode($training_location); ?>,
            <?php echo json_encode($users_email_address); ?>,
            <?php echo json_encode($first_name); ?>
        );
    } else {
        openConfirmRegistrationModal(
            <?php echo json_encode($training_name); ?>,
            <?php echo json_encode($training_type); ?>,
            <?php echo json_encode($training_date); ?>,
            <?php echo json_encode($training_time_txt); ?>,
            <?php echo json_encode($training_location); ?>,
            <?php echo json_encode($display_cost); ?>,
            <?php echo json_encode($users_email_address); ?>,
            <?php echo json_encode($first_name); ?>
        );
    }
});
</script>
<?php endif; ?>

<?php if (isset($_GET['registered']) && $_GET['registered'] == 1): ?>
<script>
document.addEventListener("DOMContentLoaded", function() {
    openRegistrationSuccessModal("<?php echo htmlspecialchars($training_title, ENT_QUOTES, 'UTF-8'); ?>");
});

function openRegistrationSuccessModal(trainingTitle) {
    const modal = document.getElementById('form-modal-message');
    const messageContainer = modal.querySelector('.modal-message');
    const photobox = document.getElementById('modal-photo-box');

    photobox.style.display = 'none';

    let content = `
        <div class="preview-title">Registered!</div>
        <div class="register-success-modal">
            <img src="../webps/registration-confirmed.webp" class="register-success-image">
            <h1>You're registered!</h1>
            <h4>See you at <i>${escapeHtml(trainingTitle)}</i></h4>
            <p>Check your email for your registration confirmation and Zoom invitation link.</p>
            <div class="register-modal-actions register-modal-actions-column">
                <a href="register.php?id=<?php echo $training_id; ?>" class="confirm-button enabled register-modal-action-wide">Got it!</a>
            </div>
        </div>
    `;

    messageContainer.innerHTML = content;
    modal.style.display = 'flex';
    document.body.classList.add('modal-open');
}
</script>
<?php endif; ?>

<?php if (isset($_GET['pledged']) && $_GET['pledged'] == 1): ?>
<script>
document.addEventListener("DOMContentLoaded", function() {
    openPledgeSuccessModal(
        <?php echo json_encode($training_title); ?>,
        <?php echo json_encode($pledge_deadline_display); ?>,
        <?php echo (int)$pledged_amount_from_query; ?>,
        <?php echo json_encode($pledged_display_currency_from_query); ?>,
        <?php echo json_encode($pledged_display_amount_from_query); ?>
    );
});

function openPledgeSuccessModal(trainingTitle, pledgeDeadlineText, pledgedAmountIdr, pledgedDisplayCurrency, pledgedDisplayAmount) {
    const modal = document.getElementById('form-modal-message');
    const messageContainer = modal.querySelector('.modal-message');
    const photobox = document.getElementById('modal-photo-box');

    photobox.style.display = 'none';

    let amountLine = '';
    if (pledgedDisplayAmount && pledgedDisplayCurrency) {
        amountLine = `${pledgedDisplayAmount} ${pledgedDisplayCurrency}`;
    } else {
        amountLine = formatCurrencyFromIdr(pledgedAmountIdr || 0, 'IDR');
    }

    let content = `
        <div class="preview-title">Pledge Recorded!</div>
        <div class="register-success-modal">
            <img src="../webps/registration-confirmed.webp" class="register-success-image">
            <h3>Your pledge has been made!</h3>
            <p>You have pledged <b>${escapeHtml(amountLine)}</b> to participate in <b>${escapeHtml(trainingTitle)}</b>. We’ve notified the trainers and updated the public course statistics. Before <b>${escapeHtml(pledgeDeadlineText)}</b>, we’ll get back to you to confirm whether the course is going ahead and provide the way to pay.</p>
            <div class="register-modal-actions register-modal-actions-column">
                <a href="register.php?id=<?php echo $training_id; ?>" class="confirm-button enabled register-modal-action-wide">Got it!</a>
            </div>
        </div>
    `;

    messageContainer.innerHTML = content;
    modal.style.display = 'flex';
    document.body.classList.add('modal-open');
}
</script>
<?php endif; ?>

<?php require_once("../footer-2026.php"); ?>

</body>
</html>