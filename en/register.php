<?php
require_once '../earthenAuth_helper.php';

// Set page variables
$lang = basename(dirname($_SERVER['SCRIPT_NAME']));
$version = '0.32';
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


$registration_status_current = null;
$show_free_registered_notice = false;
$show_pledged_notice = false;



// Check if the user is logged in
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

        // Check if user is already registered in new table first
        $sql_check_new = "SELECT registration_id, status
                          FROM training_registrations_tb
                          WHERE training_id = ? AND buwana_id = ?
                          AND status IN ('reserved','pledged','awaiting_payment','confirmed')
                          LIMIT 1";
        $stmt_check_new = $gobrik_conn->prepare($sql_check_new);
        if ($stmt_check_new) {
            $stmt_check_new->bind_param("ii", $training_id, $buwana_id);
            $stmt_check_new->execute();
            $stmt_check_new->bind_result($existing_registration_id, $existing_registration_status);

            if ($stmt_check_new->fetch()) {
                $is_registered = true;
                $registration_status_current = $existing_registration_status;
            }
            $stmt_check_new->close();
        }

        // Fallback to legacy table only if no new-style registration exists
        if (!$is_registered && $ecobricker_id) {
            $sql_check = "SELECT id
                          FROM tb_training_trainees
                          WHERE training_id = ? AND ecobricker_id = ?
                          LIMIT 1";
            $stmt_check = $gobrik_conn->prepare($sql_check);
            if ($stmt_check) {
                $stmt_check->bind_param("ii", $training_id, $ecobricker_id);
                $stmt_check->execute();
                $stmt_check->store_result();
                if ($stmt_check->num_rows > 0) {
                    $is_registered = true;
                    $registration_status_current = 'confirmed_legacy';
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

// Currency conversion rates from IDR (hardcoded approximate)
$currency_options = [
    'IDR' => 1,
    'USD' => 0.000064,
    'EUR' => 0.000059,
    'CAD' => 0.000087,
    'GBP' => 0.000050,
    'MYR' => 0.00030
];

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

// Decide which top confirmation banner to show
if (isset($_GET['pledged']) && $_GET['pledged'] == 1) {
    $show_pledged_notice = true;
} elseif ($payment_mode === 'pledge_threshold' && in_array($registration_status_current, ['pledged', 'reserved', 'awaiting_payment'], true)) {
    $show_pledged_notice = true;
} elseif ($is_registered && (
    $registration_status_current === 'confirmed' ||
    $registration_status_current === 'confirmed_legacy' ||
    $payment_mode !== 'pledge_threshold'
)) {
    $show_free_registered_notice = true;
}

$gobrik_conn->close();

echo '<!DOCTYPE html>
<html lang="' . $lang . '">
<head>
<meta charset="UTF-8">
';
?>

<?php require_once("../includes/register-inc.php"); ?>


<div id="form-submission-box" class="register-page-shell">
    <div class="form-container register-page-container">

        <div class="register-content-wrap">

            <?php if ($show_free_registered_notice): ?>
                <div id="registered-notice" class="top-container-notice">
                    <span class="notice-icon">👍</span>
                    <span style="margin-right: auto;">You're registered for this <?php echo $training_type; ?>! See your email or <a href="dashboard.php">dashboard</a> for full registration details.</span>
                    <button class="notice-close" aria-label="Close">&times;</button>
                </div>
            <?php endif; ?>

            <?php if ($show_pledged_notice): ?>
                <div id="pledged-notice" class="top-container-notice">
                    <span class="notice-icon">🤝</span>
                    <span style="margin-right: auto;">Your pledge to participate has been made! Trainers notified and public stats updated 👇.</span>
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
                        <p class="register-meta-line">Language: <?php echo $training_language; ?></p>
                        <p class="register-meta-line"><?php echo $display_cost; ?></p>

                        <button id="rsvp-register-button-desktop" class="register-main-button <?php echo $is_registered ? '' : 'enabled'; ?>">
                            <?php echo $is_registered ? "✅ You're already registered" : ($is_logged_in ? $earthling_emoji . " Register" : "🔑 Register"); ?>
                        </button>
                    </div>

                    <div class="profile-images">

                        <div class="register-trainer-card">
                            <img src="<?php echo $feature_photo3_main; ?>" class="register-trainer-photo" alt="Trainer photo">
                            <p class="register-trainer-names">Led by <?php echo $lead_trainer; ?></p>
                        </div>

                        <?php if ($show_signup_count === 1): ?>
                            <div class="profile-names register-signup-line">
                                <span class="signup-count-text">Registrations:</span>
                                <span class="signup-count-number"><?php echo $total_registrations_count; ?></span>
                                <span class="signup-count-text">of <?php echo max($no_participants, $status_participants_threshold); ?></span>
                            </div>
                        <?php endif; ?>

                        <?php if ($payment_mode === 'pledge_threshold'): ?>
                            <?php require_once '../includes/3P-graph.php'; ?>
                        <?php endif; ?>

                    </div>

                </div>

                <button id="rsvp-register-button-mobile" class="register-main-button <?php echo $is_registered ? '' : 'enabled'; ?>">
                    <?php echo $is_registered ? "✅ You're already registered" : ($is_logged_in ? $earthling_emoji . " Register" : "🔑 Register"); ?>
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
            <?php echo $is_registered ? "✅ You're already registered" : ($is_logged_in ? $earthling_emoji . " Register" : "🔑 Register"); ?>
        </button>

    </div>
</div>

</div>

<script>
const TRAINING_PAYMENT_MODE = <?php echo json_encode($payment_mode); ?>;
const SUGGESTED_AMOUNT_IDR = <?php echo (int)$default_price_idr; ?>;
const TRAINING_ID = <?php echo (int)$training_id; ?>;
const ECOBRICKER_ID = <?php echo json_encode($ecobricker_id); ?>;
const PLEDGE_DEADLINE_DISPLAY = <?php echo json_encode($pledge_deadline_display); ?>;
const PLEDGED_AMOUNT_QUERY = <?php echo (int)$pledged_amount_from_query; ?>;
const PLEDGED_DISPLAY_CURRENCY_QUERY = <?php echo json_encode($pledged_display_currency_from_query); ?>;
const PLEDGED_DISPLAY_AMOUNT_QUERY = <?php echo json_encode($pledged_display_amount_from_query); ?>;
const REGISTRATION_STATUS_CURRENT = <?php echo json_encode($registration_status_current); ?>;

const CURRENCY_RATES = {
    IDR: 1,
    USD: 0.000064,
    EUR: 0.000059,
    CAD: 0.000087,
    GBP: 0.000050,
    MYR: 0.00030
};

function formatCurrencyFromIdr(idrAmount, currency) {
    const safeIdr = Number(idrAmount || 0);
    if (currency === 'IDR') {
        return new Intl.NumberFormat('en-US', {
            maximumFractionDigits: 0
        }).format(safeIdr) + ' IDR';
    }

    const converted = safeIdr * (CURRENCY_RATES[currency] || 1);
    return new Intl.NumberFormat('en-US', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 2
    }).format(converted) + ' ' + currency;
}

function getConvertedAmount(idrAmount, currency) {
    const safeIdr = Number(idrAmount || 0);
    if (currency === 'IDR') return Math.round(safeIdr);
    return Number((safeIdr * (CURRENCY_RATES[currency] || 1)).toFixed(2));
}

function escapeHtml(str) {
    return String(str)
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}

document.getElementById("rsvp-bottom-button").addEventListener("click", handleRegistrationClick);
var rsvpDesk = document.getElementById("rsvp-register-button-desktop");
if (rsvpDesk) rsvpDesk.addEventListener("click", handleRegistrationClick);
var rsvpMob = document.getElementById("rsvp-register-button-mobile");
if (rsvpMob) rsvpMob.addEventListener("click", handleRegistrationClick);

function handleRegistrationClick() {
    <?php if ($is_logged_in && isset($ecobricker_id)): ?>
        <?php if ($is_registered): ?>
            openCancelRegistrationModal();
        <?php else: ?>
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
        <?php endif; ?>
    <?php else: ?>
        openInfoModal();
    <?php endif; ?>
}

function activateCustomTooltips(scope = document) {
    const nodes = scope.querySelectorAll('[data-tooltip]');
    nodes.forEach(node => {
        if (node.dataset.tooltipBound === '1') return;
        node.dataset.tooltipBound = '1';

        let tooltipEl = null;

        function showTooltip() {
            tooltipEl = document.createElement('div');
            tooltipEl.className = 'custom-tooltip-bubble';
            tooltipEl.textContent = node.getAttribute('data-tooltip');
            document.body.appendChild(tooltipEl);

            const rect = node.getBoundingClientRect();
            const tipRect = tooltipEl.getBoundingClientRect();

            let top = window.scrollY + rect.top - tipRect.height - 10;
            let left = window.scrollX + rect.left + (rect.width / 2) - (tipRect.width / 2);

            if (left < 8) left = 8;
            if (left + tipRect.width > window.innerWidth - 8) {
                left = window.innerWidth - tipRect.width - 8;
            }
            if (top < window.scrollY + 8) {
                top = window.scrollY + rect.bottom + 10;
            }

            tooltipEl.style.top = `${top}px`;
            tooltipEl.style.left = `${left}px`;
            requestAnimationFrame(() => tooltipEl.classList.add('visible'));
        }

        function hideTooltip() {
            if (tooltipEl) {
                tooltipEl.remove();
                tooltipEl = null;
            }
        }

        node.addEventListener('mouseenter', showTooltip);
        node.addEventListener('mouseleave', hideTooltip);
        node.addEventListener('focus', showTooltip);
        node.addEventListener('blur', hideTooltip);
    });
}

document.addEventListener('DOMContentLoaded', function() {
    activateCustomTooltips(document);

    document.querySelectorAll('.notice-close').forEach(btn => {
        btn.addEventListener('click', function() {
            const notice = this.closest('.top-container-notice');
            if (notice) notice.style.display = 'none';
        });
    });
});
</script>

<script>
function openInfoModal() {
    const modal = document.getElementById('form-modal-message');
    const messageContainer = modal.querySelector('.modal-message');
    const photobox = document.getElementById('modal-photo-box');

    photobox.style.display = 'none';

    const content = `
        <div class="register-modal-stack register-modal-centered">
            <h1>🔑</h1>
            <h2>Login to Register</h2>
            <p>To register for this course you must use your GoBrik account.</p>
            <div class="register-modal-actions register-modal-actions-column">
                <a href="login.php?redirect=register.php?id=<?php echo $training_id; ?>?status=relanding" class="confirm-button enabled register-modal-action-wide">Login</a>
                <a href="signup.php" class="confirm-button enabled register-modal-action-wide">Sign Up</a>
            </div>
            <p class="register-modal-footnote">GoBrik authentication is powered by Buwana SSO for regenerative apps</p>
        </div>
    `;

    messageContainer.innerHTML = content;
    modal.style.display = 'flex';
    document.body.classList.add('modal-open');
    activateCustomTooltips(messageContainer);
}

function closeInfoModal() {
    const modal = document.getElementById('form-modal-message');
    modal.style.display = 'none';
    document.body.classList.remove('modal-open');
}


function formatCurrencyNumberOnly(idrAmount, currency) {
    const safeIdr = Number(idrAmount || 0);

    if (currency === 'IDR') {
        return new Intl.NumberFormat('en-US', {
            maximumFractionDigits: 0
        }).format(safeIdr);
    }

    const converted = safeIdr * (CURRENCY_RATES[currency] || 1);
    return new Intl.NumberFormat('en-US', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 2
    }).format(converted);
}

function openConfirmRegistrationModal(trainingName, trainingType, trainingDate, trainingTime, trainingLocation, displayCost, userEmail, firstName) {
    const modal = document.getElementById('form-modal-message');
    const messageContainer = modal.querySelector('.modal-message');
    const photobox = document.getElementById('modal-photo-box');

    photobox.style.display = 'none';

    const content = `
        <div class="register-modal-stack register-modal-centered">
            <div>
                <h1>🗓️</h1>
                <h2>${escapeHtml(trainingName)}</h2>
                <p>${escapeHtml(firstName)}, please confirm your registration to this ${escapeHtml(trainingType)} taking place at ${escapeHtml(trainingDate)} (${escapeHtml(trainingTime)}) on ${escapeHtml(trainingLocation)}. The training is ${escapeHtml(displayCost)} so there is no need to make any initial payments.</p>
            </div>
            <div class="register-modal-actions register-modal-actions-column">
                <a href="registration_confirmation.php?id=<?php echo $training_id; ?>&ecobricker_id=<?php echo $ecobricker_id; ?>" class="confirm-button enabled register-modal-action-wide">✅ Confirm Registration</a>
                <a href="register.php?id=<?php echo $training_id; ?>" class="confirm-button register-modal-action-wide register-button-muted">Back to Course</a>
            </div>
            <p class="register-modal-footnote">Upon confirmation we will send you the access links and information to your Buwana account e-mail: <b>${escapeHtml(userEmail)}</b></p>
        </div>
    `;

    messageContainer.innerHTML = content;
    modal.style.display = 'flex';
    document.body.classList.add('modal-open');
    activateCustomTooltips(messageContainer);
}

function open3PRegistrationModal(trainingName, trainingType, trainingDate, trainingTime, trainingLocation, userEmail, firstName) {
    const modal = document.getElementById('form-modal-message');
    const messageContainer = modal.querySelector('.modal-message');
    const photobox = document.getElementById('modal-photo-box');
    photobox.style.display = 'none';

    const suggested = Math.max(0, Number(SUGGESTED_AMOUNT_IDR || 0));
    const min = 0;
    const max = Math.max(suggested * 2, 1000);
    const initial = suggested;

    const content = `
        <div class="threep-modal-wrap">

            <div class="threep-training-pill">${escapeHtml(trainingName)}</div>

            <div class="threep-modal-head">
                <div class="threep-modal-head-main">
                    <h2 class="threep-modal-title">Pledge your Participation</h2>
                </div>
            </div>

            <p class="threep-modal-copy">
                ${escapeHtml(firstName)}, this course uses Pledge, Proceed and Pay. By confirming your participation for an amount of your choosing, you help the course reach its minimum threshold and happen! Only when enough participants have pledged will the course go-ahead and will your payment be due.
            </p>

            <div class="threep-modal-slider-block">
               <div class="threep-readout-row">
                   <div class="threep-amount-readout" id="threep_amount_readout">${formatCurrencyNumberOnly(initial, 'IDR')}</div>
                   <div class="threep-currency-switcher">
                       <span class="threep-currency-switch-label">Switch currency</span>
                       <select id="pledge_currency_select" class="threep-currency-select">
                           <option value="IDR">🇮🇩 IDR</option>
                           <option value="USD">🇺🇸 USD</option>
                           <option value="EUR">🇪🇺 EUR</option>
                           <option value="CAD">🇨🇦 CAD</option>
                           <option value="GBP">🇬🇧 GBP</option>
                           <option value="MYR">🇲🇾 MYR</option>
                       </select>
                   </div>
               </div>

                <div class="threep-slider-row">

                    <span class="threep-slider-pill pill-zero" id="threep_zero_label">
                       ${formatCurrencyNumberOnly(initial, 'IDR')}
                    </span>

                    <input
                        type="range"
                        id="threep_pledge_slider"
                        min="${min}"
                        max="${max}"
                        value="${initial}"
                        step="1000"
                    >

                    <span class="threep-slider-pill pill-max" id="threep_max_label">
                        ${formatCurrencyFromIdr(max,'IDR')}
                    </span>

                </div>

                <div class="threep-suggested-row">
                    <div class="threep-suggested-copy">
                        Trainer suggested amount:
                        <strong id="threep_suggested_amount">${formatCurrencyNumberOnly(suggested, 'IDR')} IDR</strong>
                    </div>


                </div>
            </div>

            <div class="register-modal-actions register-modal-actions-column register-modal-actions-centered">
                <a href="#" id="threep_confirm_button" class="confirm-button enabled register-modal-action-wide threep-confirm-button">🤝 Confirm Course Pledge</a>
                <p class="threep-confirm-footnote subdued">
                    Confirmation will be sent to your Buwana account e-mail: <b>${escapeHtml(userEmail)}</b>
                </p>
            </div>

        </div>
    `;

    messageContainer.innerHTML = content;
    modal.style.display = 'flex';
    document.body.classList.add('modal-open');
    activateCustomTooltips(messageContainer);

    const slider = document.getElementById('threep_pledge_slider');
    const currencySelect = document.getElementById('pledge_currency_select');
    const amountReadout = document.getElementById('threep_amount_readout');
    const suggestedReadout = document.getElementById('threep_suggested_amount');
    const confirmBtn = document.getElementById('threep_confirm_button');
    const edgeLabels = document.querySelectorAll('.threep-slider-edge');

   function formatSliderColor(pct) {
       let hue;

       if (pct <= 0.5) {
           hue = 30 + (pct / 0.5) * 110;   // 30 -> 140
       } else {
           hue = 140 + ((pct - 0.5) / 0.5) * 80; // 140 -> 220
       }

       return `hsl(${hue}, 72%, 46%)`;
   }


    function update3PReadout() {

        const currency = currencySelect.value;
        const idrAmount = Number(slider.value || 0);
        const pct = max > 0 ? (idrAmount / max) : 0;

        amountReadout.textContent = formatCurrencyNumberOnly(idrAmount, currency);
        suggestedReadout.textContent = formatCurrencyNumberOnly(suggested, currency) + ' ' + currency;

        document.getElementById("threep_zero_label").textContent =
            formatCurrencyFromIdr(min, currency);

        document.getElementById("threep_max_label").textContent =
            formatCurrencyFromIdr(max, currency);

        const sliderColor = formatSliderColor(pct);

        slider.style.setProperty('--pledge-color', sliderColor);
        slider.style.background =
            `linear-gradient(90deg, ${sliderColor} 0%, ${sliderColor} ${pct * 100}%, #d9dee3 ${pct * 100}%, #d9dee3 100%)`;

        confirmBtn.style.background = sliderColor;
        confirmBtn.style.setProperty('--pledge-color', sliderColor);

        document.getElementById("threep_zero_label").style.color = formatSliderColor(0);
        document.getElementById("threep_max_label").style.color = formatSliderColor(1);

        const convertedDisplayAmount = getConvertedAmount(idrAmount, currency);

        confirmBtn.href =
            "registration_confirmation.php?id=<?php echo $training_id; ?>" +
            "&ecobricker_id=<?php echo $ecobricker_id; ?>" +
            "&mode=pledge_threshold" +
            "&pledged_amount_idr=" + encodeURIComponent(idrAmount) +
            "&display_currency=" + encodeURIComponent(currency) +
            "&display_amount=" + encodeURIComponent(convertedDisplayAmount);
    }

    slider.addEventListener('input', update3PReadout);
    currencySelect.addEventListener('change', update3PReadout);
    update3PReadout();
}

<?php if ($is_registered): ?>
function openCancelRegistrationModal() {

    const modal = document.getElementById('form-modal-message');
    const messageContainer = modal.querySelector('.modal-message');
    const photobox = document.getElementById('modal-photo-box');

    photobox.style.display = 'none';

    const isPledgeState =
        TRAINING_PAYMENT_MODE === 'pledge_threshold' &&
        ['pledged','reserved','awaiting_payment'].includes(REGISTRATION_STATUS_CURRENT);

    const modalTitle = isPledgeState ? 'Withdraw Pledge?' : 'Cancel Registration?';

    const modalBody = isPledgeState
        ? 'Are you sure you want to withdraw your pledge for this course?<br>Your pledge will be removed from the public course totals.'
        : 'Are you sure you want to un-enroll from this course?<br>If you\'ve already made a payment it cannot be refunded.';

    const actionLabel = isPledgeState
        ? 'Withdraw Pledge'
        : 'Cancel Registration';

    const content = `
        <div class="register-modal-stack register-modal-centered">
            <div>
                <h1>💔</h1>
                <h2>${modalTitle}</h2>
                <p>${modalBody}</p>
            </div>

            <div class="register-modal-actions">
                <a href="#" id="confirm-unregister" class="confirm-button register-button-danger register-modal-action-half">
                    ${actionLabel}
                </a>

                <a href="courses.php" class="confirm-button register-button-muted register-modal-action-half">
                    ↩️ Back to Courses
                </a>
            </div>
        </div>
    `;

    messageContainer.innerHTML = content;

    modal.style.display = 'flex';
    document.body.classList.add('modal-open');

    document.getElementById('confirm-unregister').addEventListener('click', function(e) {

        e.preventDefault();

        fetch('../api/unregister_training.php?id=<?php echo $training_id; ?>&ecobricker_id=<?php echo $ecobricker_id; ?>')
            .then(r => r.json())
            .then(data => {

                if (data.success) {
                    openUnregisterSuccessModal();
                } else {
                    alert('Unable to cancel registration.');
                }

            })
            .catch(() => alert('Unable to cancel registration.'));

    });
}
<?php endif; ?>

function openUnregisterSuccessModal() {
    const modal = document.getElementById('form-modal-message');
    const messageContainer = modal.querySelector('.modal-message');
    const photobox = document.getElementById('modal-photo-box');

    photobox.style.display = 'none';

    const content = `
        <div class="register-modal-stack register-modal-centered">
            <h1>😿</h1>
            <h2>You're un-enrolled.</h2>
            <p>We're sorry to see you go! We hope you can find another course that suits your interests and availability from our course listings</p>
            <div class="register-modal-actions register-modal-actions-column">
                <a href="courses.php" class="confirm-button enabled register-modal-action-wide">OK</a>
            </div>
        </div>
    `;

    messageContainer.innerHTML = content;
    modal.style.display = 'flex';
    document.body.classList.add('modal-open');
}


document.addEventListener('DOMContentLoaded', function() {

    // Only enable rollover cancel behaviour if the user is registered
    const isRegistered = <?php echo $is_registered ? 'true' : 'false'; ?>;

    if (!isRegistered) return;

    const btns = [
        document.getElementById('rsvp-bottom-button'),
        document.getElementById('rsvp-register-button-desktop'),
        document.getElementById('rsvp-register-button-mobile')
    ];

    const isPledgeState =
        TRAINING_PAYMENT_MODE === 'pledge_threshold' &&
        ['pledged','reserved','awaiting_payment'].includes(REGISTRATION_STATUS_CURRENT);

    const rolloverText = isPledgeState
        ? '💔 Withdraw Pledge'
        : '💔 Cancel Registration';

    btns.forEach(btn => {

        if (!btn) return;

        btn.addEventListener('mouseover', function() {
            this.dataset.originalText = this.innerHTML;
            this.dataset.originalBg = this.style.background;
            this.style.background = 'grey';
            this.innerHTML = rolloverText;
        });

        btn.addEventListener('mouseout', function() {
            this.style.background = this.dataset.originalBg;
            this.innerHTML = this.dataset.originalText;
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
            <p>You have pledged <b>${escapeHtml(amountLine)}</b> to participate in <b>${escapeHtml(trainingTitle)}</b>.  We’ve notified the trainers and updated the public course statistics. Before <b>${escapeHtml(pledgeDeadlineText)}</b>, we’ll get back to you to confirm whether the course is going ahead and provide the way to pay.</p>
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

