<?php
require_once '../earthenAuth_helper.php'; // Include the authentication helper functions
require_once '../auth/session_start.php';

// PART 1: Set up page variables
$lang = basename(dirname($_SERVER['SCRIPT_NAME']));
$version = '0.546';
$page = 'community-3p';
$lastModified = date("Y-m-d\TH:i:s\Z", filemtime(__FILE__));

$source_training_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($source_training_id <= 0) {
    header('Location: courses.php');
    exit();
}

// PART 2: Check if user is logged in and session active
if (!isLoggedIn()) {
    header('Location: login.php?redirect=' . urlencode($page . '.php'));
    exit();
}

$buwana_id = $_SESSION['buwana_id'];

// Include database connections
require_once '../gobrikconn_env.php';
require_once '../buwanaconn_env.php';

// Fetch user meta and location data
$user_continent_icon = getUserContinent($buwana_conn, $buwana_id);
$earthling_emoji = getUserEarthlingEmoji($buwana_conn, $buwana_id);
$user_location_watershed = getWatershedName($buwana_conn, $buwana_id);
$user_location_full = getUserFullLocation($buwana_conn, $buwana_id);
$gea_status = getGEA_status($buwana_id);
$user_ecobricker_id = getEcobrickerID($buwana_id);
$user_community_name = getCommunityName($buwana_conn, $buwana_id);
$user_community_id = null;
$first_name = getFirstName($buwana_conn, $buwana_id);

// Fetch user's email address from GoBrik ecobricker record
$users_email_address = '';
$full_name = '';
$stmt_user = $gobrik_conn->prepare("SELECT email_addr, full_name FROM tb_ecobrickers WHERE buwana_id = ?");
if ($stmt_user) {
    $stmt_user->bind_param("i", $buwana_id);
    $stmt_user->execute();
    $stmt_user->bind_result($users_email_address, $full_name);
    $stmt_user->fetch();
    $stmt_user->close();
}

// Fetch community_id from community name
if (!empty($user_community_name)) {
    $stmt_com = $buwana_conn->prepare("SELECT community_id FROM communities_tb WHERE com_name = ?");
    if ($stmt_com) {
        $stmt_com->bind_param("s", $user_community_name);
        $stmt_com->execute();
        $stmt_com->bind_result($user_community_id);
        $stmt_com->fetch();
        $stmt_com->close();
    }
}


// Initialize training variables
$training_title = $training_subtitle = $training_date = $lead_trainer = '';
$training_type = $training_location = $training_time_txt = '';
$payment_mode = 'free';
$base_currency = 'IDR';
$default_price_idr = 0;
$funding_goal_idr = 0;
$min_participants_required = 0;
$threshold_status = 'open';
$feature_photo1_main = '';
$trainer_contact_email = '';
$ready_to_show = 0;
$training_language = 'en';
$community_id_source = null;

// Fetch source training
$sql = "SELECT * FROM tb_trainings WHERE training_id = ?";
$stmt = $gobrik_conn->prepare($sql);
$stmt->bind_param("i", $source_training_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $training_title     = htmlspecialchars($row['training_title']     ?? '', ENT_QUOTES, 'UTF-8');
    $training_subtitle  = htmlspecialchars($row['training_subtitle']  ?? '', ENT_QUOTES, 'UTF-8');
    $training_date      = htmlspecialchars($row['training_date']      ?? '', ENT_QUOTES, 'UTF-8');
    $training_time_txt  = htmlspecialchars($row['training_time_txt']  ?? '', ENT_QUOTES, 'UTF-8');
    $lead_trainer       = htmlspecialchars($row['lead_trainer']       ?? '', ENT_QUOTES, 'UTF-8');
    $training_type      = htmlspecialchars($row['training_type']      ?? '', ENT_QUOTES, 'UTF-8');
    $training_location  = htmlspecialchars($row['training_location']  ?? '', ENT_QUOTES, 'UTF-8');
    $payment_mode       = htmlspecialchars($row['payment_mode']       ?? 'free', ENT_QUOTES, 'UTF-8');
    $base_currency      = htmlspecialchars($row['base_currency']      ?? 'IDR',  ENT_QUOTES, 'UTF-8');
    $default_price_idr       = intval($row['default_price_idr']       ?? 0);
    $funding_goal_idr        = intval($row['funding_goal_idr']        ?? 0);
    $min_participants_required = intval($row['min_participants_required'] ?? 0);
    $threshold_status   = htmlspecialchars($row['threshold_status']   ?? 'open', ENT_QUOTES, 'UTF-8');
    $feature_photo1_main = htmlspecialchars($row['feature_photo1_main'] ?? '', ENT_QUOTES, 'UTF-8');
    $trainer_contact_email = htmlspecialchars($row['trainer_contact_email'] ?? '', ENT_QUOTES, 'UTF-8');
    $ready_to_show      = intval($row['ready_to_show'] ?? 0);
    $training_language  = htmlspecialchars($row['training_language']  ?? 'en', ENT_QUOTES, 'UTF-8');
    $community_id_source = $row['community_id'] ? intval($row['community_id']) : null;
} else {
    header('Location: courses.php');
    exit();
}
$stmt->close();

if ($ready_to_show === 0 || $payment_mode !== 'pledge_threshold') {
    header("Location: register.php?id=$source_training_id");
    exit();
}

// Fetch languages for the select
$languages = [];
$sql_languages = "SELECT language_id, language_name_en FROM languages_tb ORDER BY language_name_en ASC";
$result_languages = $buwana_conn->query($sql_languages);
if ($result_languages) {
    while ($row = $result_languages->fetch_assoc()) {
        $languages[] = $row;
    }
}

// Fetch countries
$countries = [];
$result_countries = $buwana_conn->query("SELECT country_id, country_name FROM countries_tb ORDER BY country_name ASC");
if ($result_countries) {
    while ($row = $result_countries->fetch_assoc()) {
        $countries[] = $row;
    }
}

// Format amounts for display
$funding_goal_display = number_format($funding_goal_idr) . ' IDR';

// Check for success redirect
$show_success = isset($_GET['requested']) && $_GET['requested'] == '1';
$new_training_id = isset($_GET['new_id']) ? intval($_GET['new_id']) : 0;

// Check for error redirect
$error_code = isset($_GET['error']) ? trim($_GET['error']) : '';
$error_messages = [
    'missing_fields' => 'Some required fields were missing. Please fill in all required fields and try again.',
    'invalid_date'   => 'The proposed date you entered was not valid. Please select a valid date and time.',
    'not_found'      => 'This training could not be found or is not available for community requests.',
    'db'             => 'A database error occurred while submitting your request. Please try again.',
];
$error_display = isset($error_messages[$error_code]) ? $error_messages[$error_code] : '';

$gobrik_conn->close();

echo '<!DOCTYPE html>
<html lang="' . $lang . '">
<head>
<meta charset="UTF-8">
';
?>

<?php require_once("../includes/community-3p-inc.php"); ?>


<div id="form-submission-box" class="community-3p-page-shell">
    <div class="form-container community-3p-container">
        <div class="community-3p-content-wrap">

            <?php if ($show_success): ?>

                <!-- ============================================================ -->
                <!-- SUCCESS STATE                                                  -->
                <!-- ============================================================ -->
                <div class="community-3p-success-card">
                    <div class="community-3p-success-badge">✅ Request Submitted</div>
                    <h3>Your Community Training Request is In!</h3>
                    <p>
                        Thank you, <?php echo htmlspecialchars($first_name, ENT_QUOTES, 'UTF-8'); ?>!
                        Your community training request for <strong><?php echo $training_title; ?></strong> has been sent to the training team.
                    </p>
                    <p>
                        The trainers will review your request and get back to you at <strong><?php echo htmlspecialchars($users_email_address, ENT_QUOTES, 'UTF-8'); ?></strong> shortly.
                        Once they confirm, you'll receive a link to complete the full course payment for your community.
                    </p>
                    <a href="register.php?id=<?php echo $source_training_id; ?>" class="confirm-button enabled" style="display:inline-block;margin-top:16px;">↩ Back to Course</a>
                </div>

            <?php else: ?>

                <!-- ============================================================ -->
                <!-- TRAINING HEADER CARD                                          -->
                <!-- ============================================================ -->
                <div class="community-3p-header-card">
                    <?php if (!empty($feature_photo1_main)): ?>
                        <img src="<?php echo $feature_photo1_main; ?>" class="community-3p-lead-photo" alt="<?php echo $training_title; ?>">
                    <?php endif; ?>
                    <div class="community-3p-training-info">
                        <h2>Community Training Registration</h2>
                        <h3><?php echo $training_title; ?></h3>
                        <?php if (!empty($training_subtitle)): ?>
                            <p><?php echo $training_subtitle; ?></p>
                        <?php endif; ?>
                        <p><?php echo $training_type; ?><?php if (!empty($lead_trainer)): ?> · Led by <?php echo $lead_trainer; ?><?php endif; ?></p>
                    </div>
                </div>

                <!-- ============================================================ -->
                <!-- COMMITMENT SUMMARY                                             -->
                <!-- ============================================================ -->
                <div class="community-3p-commitment-card">
                    <div class="community-3p-commitment-item">
                        <div class="community-3p-commitment-label">Full Course Amount</div>
                        <div class="community-3p-commitment-value" id="commitment-amount-display"><?php echo htmlspecialchars($funding_goal_display, ENT_QUOTES, 'UTF-8'); ?></div>
                        <div class="community-3p-commitment-sub">You commit to the entire course funding</div>
                    </div>
                    <div class="community-3p-commitment-item">
                        <div class="community-3p-commitment-label">Minimum Participants</div>
                        <div class="community-3p-commitment-value"><?php echo (int)$min_participants_required; ?></div>
                        <div class="community-3p-commitment-sub">People from your community</div>
                    </div>
                    <div class="community-3p-commitment-currency-row" style="width:100%;margin-top:4px;">
                        <label for="display-currency-select" style="font-size:0.88em;opacity:0.7;">View amount in:</label>
                        <select id="display-currency-select" style="padding:4px 10px;font-size:0.9em;border-radius:6px;border:1px solid rgba(0,0,0,0.15);background:transparent;margin-left:8px;">
                            <option value="IDR">IDR</option>
                            <option value="USD">USD</option>
                            <option value="EUR">EUR</option>
                            <option value="CAD">CAD</option>
                            <option value="GBP">GBP</option>
                            <option value="MYR">MYR</option>
                        </select>
                    </div>
                </div>

                <?php if (!empty($error_display)): ?>
                <!-- ============================================================ -->
                <!-- ERROR CARD                                                     -->
                <!-- ============================================================ -->
                <div class="community-3p-error-card" style="background:rgba(200,0,0,0.07);border:1px solid rgba(200,0,0,0.25);border-radius:12px;padding:18px 22px;margin-bottom:20px;color:#b00;">
                    <strong>⚠️ There was a problem with your submission</strong>
                    <p style="margin:8px 0 0 0;"><?php echo htmlspecialchars($error_display, ENT_QUOTES, 'UTF-8'); ?></p>
                </div>
                <?php endif; ?>

                <!-- ============================================================ -->
                <!-- COMMUNITY REQUEST FORM                                         -->
                <!-- ============================================================ -->
                <div class="community-3p-form-card">
                    <h3>Propose Your Community Training</h3>
                    <p>
                        Complete the details below to request this training for your community.
                        The training content, price, and participant requirements stay the same —
                        you're proposing a new date, location, and community for a dedicated event.
                    </p>

                    <form id="community-3p-form" method="post" action="../processes/community_training_request.php" novalidate>
                        <input type="hidden" name="source_training_id" value="<?php echo (int)$source_training_id; ?>">
                        <input type="hidden" name="buwana_id" value="<?php echo (int)$buwana_id; ?>">
                        <input type="hidden" name="ecobricker_id" value="<?php echo (int)$user_ecobricker_id; ?>">

                        <div class="c3p-form-row">
                            <label for="proposed_date">Proposed Training Date &amp; Time *</label>
                            <input type="datetime-local" id="proposed_date" name="proposed_date"
                                   value="<?php echo date('Y-m-d\T12:00'); ?>" required>
                            <p class="c3p-form-caption">Propose a date and start time for your community event.</p>
                        </div>

                        <div class="c3p-form-row">
                            <label for="time_txt">Time in Key Timezones</label>
                            <input type="text" id="time_txt" name="time_txt"
                                   placeholder="e.g. Jakarta: 7PM / London: 1PM / New York: 8AM"
                                   value="">
                            <p class="c3p-form-caption">Help participants in different timezones know when to join.</p>
                        </div>

                        <div class="c3p-form-row">
                            <label for="proposed_language">Training Language *</label>
                            <select id="proposed_language" name="proposed_language" required>
                                <?php foreach ($languages as $language): ?>
                                    <option value="<?php echo htmlspecialchars($language['language_id'], ENT_QUOTES, 'UTF-8'); ?>"
                                        <?php echo ($training_language === $language['language_id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($language['language_name_en'], ENT_QUOTES, 'UTF-8'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <p class="c3p-form-caption">The language your community training will be delivered in.</p>
                        </div>

                        <div class="c3p-form-row">
                            <label for="proposed_location">Training Location *</label>
                            <input type="text" id="proposed_location" name="proposed_location"
                                   placeholder="e.g. Nairobi Community Hall, Kenya (or Online)"
                                   required>
                            <p class="c3p-form-caption">Where will the training be held? Include city and country, or write "Online".</p>
                        </div>

                        <div class="c3p-form-row">
                            <label for="community_search_c3p">Your Community</label>
                            <div class="c3p-autocomplete-wrap">
                                <input type="text" id="community_search_c3p" name="community_search"
                                       placeholder="Start typing your community name..."
                                       autocomplete="off"
                                       value="<?php echo htmlspecialchars($user_community_name ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                <input type="hidden" id="community_id_c3p" name="community_id"
                                       value="<?php echo $user_community_id !== null ? (int)$user_community_id : ''; ?>">
                                <div id="community_results_c3p" class="c3p-autocomplete-results"></div>
                            </div>
                            <p class="c3p-form-caption">Search for and select your community. <a href="#" onclick="openAddCommunityModal(); return false;" style="color:#1a56a0;">Don't see it? Add one.</a></p>
                        </div>

                        <div id="c3p-validation-error" style="display:none;color:#c00;font-size:0.93em;margin-bottom:14px;padding:10px 14px;background:rgba(200,0,0,0.07);border-radius:8px;"></div>

                        <button type="submit" id="c3p-submit-btn" class="confirm-button enabled c3p-submit-btn">
                            🤝 Submit Community Training Request
                        </button>

                    </form>
                </div>

            <?php endif; ?>

        </div>
    </div>
</div>

</div>

<script>
const SOURCE_TRAINING_ID  = <?php echo (int)$source_training_id; ?>;
const FUNDING_GOAL_IDR    = <?php echo (int)$funding_goal_idr; ?>;
const MIN_PARTICIPANTS    = <?php echo (int)$min_participants_required; ?>;
const TRAINING_NAME       = <?php echo json_encode($training_title, JSON_HEX_TAG) ?: '""'; ?>;
const USER_EMAIL          = <?php echo json_encode($users_email_address, JSON_HEX_TAG) ?: '""'; ?>;
const USER_COMMUNITY_NAME = <?php echo json_encode($user_community_name ?? '', JSON_HEX_TAG) ?: '""'; ?>;
const USER_COMMUNITY_ID   = <?php echo $user_community_id !== null ? (int)$user_community_id : 'null'; ?>;

const CURRENCY_RATES = {
    IDR: 1,
    USD: 0.000064,
    EUR: 0.000059,
    CAD: 0.000087,
    GBP: 0.000050,
    MYR: 0.00030
};

const CURRENCY_LABELS = {
    IDR: 'IDR',
    USD: 'USD',
    EUR: 'EUR',
    CAD: 'CAD',
    GBP: 'GBP',
    MYR: 'MYR'
};

function formatCurrencyFromIdr(idrAmount, currency) {
    const safeIdr = Number(idrAmount || 0);
    if (currency === 'IDR') {
        return new Intl.NumberFormat('en-US', { maximumFractionDigits: 0 }).format(safeIdr) + ' IDR';
    }
    const converted = safeIdr * (CURRENCY_RATES[currency] || 1);
    return new Intl.NumberFormat('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 2 }).format(converted) + ' ' + currency;
}

document.addEventListener('DOMContentLoaded', function() {

    // Currency selector for commitment amount display
    const currencySelect = document.getElementById('display-currency-select');
    const amountDisplay  = document.getElementById('commitment-amount-display');
    if (currencySelect && amountDisplay) {
        currencySelect.addEventListener('change', function() {
            amountDisplay.textContent = formatCurrencyFromIdr(FUNDING_GOAL_IDR, this.value);
        });
    }

    // Community autocomplete
    const communityInput = document.getElementById('community_search_c3p');
    const communityIdInput = document.getElementById('community_id_c3p');
    const suggestionBox  = document.getElementById('community_results_c3p');

    if (communityInput && communityIdInput && suggestionBox) {
        communityInput.addEventListener('input', function() {
            const query = communityInput.value.trim();
            communityIdInput.value = '';
            if (query.length >= 3) {
                fetch('https://buwana.ecobricks.org/api/search_communities_by_id.php?query=' + encodeURIComponent(query))
                    .then(function(res) { return res.json(); })
                    .then(function(list) {
                        suggestionBox.innerHTML = '';
                        if (!list || list.length === 0) {
                            suggestionBox.innerHTML = '<div class="autocomplete-item" style="color:gray;">No communities found</div>';
                        } else {
                            list.forEach(function(item) {
                                const div = document.createElement('div');
                                div.className = 'autocomplete-item';
                                div.textContent = item.com_name || item.name || '';
                                div.dataset.id = item.community_id || item.id || '';
                                div.addEventListener('mousedown', function(e) {
                                    e.preventDefault();
                                    communityInput.value = div.textContent;
                                    communityIdInput.value = div.dataset.id;
                                    suggestionBox.innerHTML = '';
                                });
                                suggestionBox.appendChild(div);
                            });
                        }
                    })
                    .catch(function(err) {
                        console.error('Community search failed:', err);
                        suggestionBox.innerHTML = '';
                    });
            } else {
                suggestionBox.innerHTML = '';
            }
        });

        document.addEventListener('click', function(e) {
            if (!communityInput.contains(e.target) && !suggestionBox.contains(e.target)) {
                suggestionBox.innerHTML = '';
            }
        });
    }

    // Form validation
    const form = document.getElementById('community-3p-form');
    const errorBox = document.getElementById('c3p-validation-error');

    if (form) {
        form.addEventListener('submit', function(e) {
            const date     = document.getElementById('proposed_date').value.trim();
            const location = document.getElementById('proposed_location').value.trim();
            const language = document.getElementById('proposed_language').value.trim();

            const errors = [];
            if (!date) errors.push('Please select a proposed training date.');
            if (!location) errors.push('Please enter a training location.');
            if (!language) errors.push('Please select a training language.');

            if (errors.length > 0) {
                e.preventDefault();
                errorBox.innerHTML = errors.map(function(err) { return '<div>• ' + err + '</div>'; }).join('');
                errorBox.style.display = 'block';
                errorBox.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            } else {
                errorBox.style.display = 'none';
                const btn = document.getElementById('c3p-submit-btn');
                if (btn) {
                    btn.disabled = true;
                    btn.textContent = 'Submitting...';
                }
            }
        });
    }

});
</script>

<?php require_once("../footer-2026.php"); ?>

</body>
</html>