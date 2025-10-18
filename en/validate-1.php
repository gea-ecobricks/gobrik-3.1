<?php
require_once '../earthenAuth_helper.php'; // Include the authentication helper functions
require_once '../auth/session_start.php';

// Set up page variables
$lang = basename(dirname($_SERVER['SCRIPT_NAME'])) ?? 'en';
$version = '0.448';
$page = 'validate-1';
$lastModified = date("Y-m-d\TH:i:s\Z", filemtime(__FILE__));


// LOGIN AND ROLE CHECK:
//Check if the user is logged in, if not send them to login.
if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}



// User is logged in, proceed to check admin status
$buwana_id = $_SESSION['buwana_id'];
require_once '../gobrikconn_env.php';

$query = "SELECT user_roles FROM tb_ecobrickers WHERE buwana_id = ?";
if ($stmt = $gobrik_conn->prepare($query)) {
    $stmt->bind_param("i", $buwana_id);
    $stmt->execute();
    $stmt->bind_result($user_roles);

    if ($stmt->fetch()) {
        // Check if the user has an admin role
        if (stripos($user_roles, 'admin') === false) {
            echo "<script>
                alert('Sorry! Only admins can see this page.');
                window.location.href = 'dashboard.php';
            </script>";
            exit();
        }
    } else {
        // Redirect if no user record is found
        echo "<script>
            alert('User record not found.');
            window.location.href = 'dashboard.php';
        </script>";
        exit();
    }
    $stmt->close();
} else {
    // Handle database error
    echo "<script>
        alert('Error checking user role. Please try again later.');
        window.location.href = 'dashboard.php';
    </script>";
    exit();
}
//END LOGIN AND ROLE CHECK

// Include database connections
require_once '../gobrikconn_env.php';
require_once '../buwanaconn_env.php';

 // Fetch the user's location data
    $buwana_id = $_SESSION['buwana_id'] ?? ''; // Retrieve buwana_id from session

    // Fetch the user's location data
    $user_continent_icon = getUserContinent($buwana_conn, $buwana_id);
$earthling_emoji = getUserEarthlingEmoji($buwana_conn, $buwana_id);
    $user_location_watershed = getWatershedName($buwana_conn, $buwana_id);
    $user_location_full = getUserFullLocation($buwana_conn, $buwana_id);
    $gea_status = getGEA_status($buwana_id);
    $user_community_name = getCommunityName($buwana_conn, $buwana_id);
    $ecobrick_unique_id = '';
    $maker_id = '';
    $maker_ecobricker_id = null;
    $ecobrick_brk_amt = 0.0;
    $first_name = getFirstName($buwana_conn, $buwana_id);
    $ecobrick_owner = '';
    $owner_language_id = 'Unknown';
    $owner_ecobricker_id = null;
    $ecobrick_weight_g = null;
    $ecobrick_volume_ml = null;
    $ecobrick_density = null;
    $ecobrick_status = '';
    $date_logged_ts = '';
    $sequestration_type = '';
    $owner_display_name = '';


    $error_message = '';
    $full_urls = [];
    $thumbnail_paths = [];
    $main_file_sizes = [];
    $thumbnail_file_sizes = [];

// Validate ecobrick ID
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $ecobrick_unique_id = (int)$_GET['id'];
} else {
    echo "Invalid or missing ecobrick ID.";
    exit();
}

// Check ecobrick status
$status_check_stmt = $gobrik_conn->prepare("SELECT status FROM tb_ecobricks WHERE ecobrick_unique_id = ?");
if (!$status_check_stmt) {
    error_log("Failed to prepare status check statement: " . $gobrik_conn->error);
    echo "An error occurred. Please try again later.";
    exit();
}
$status_check_stmt->bind_param("i", $ecobrick_unique_id);
$status_check_stmt->execute();
$status_check_stmt->bind_result($status);
$status_check_stmt->fetch();
$status_check_stmt->close();

// Redirect if status is 'Awaiting validation'
if ($status !== null && strcasecmp($status, "authenticated") === 0) {
    echo "<script>
        alert('Oops! This ecobrick has already been authenticated.');
        window.location.href = 'admin-review.php';
    </script>";
    exit();
}

// Fetch ecobrick details including photo_version
$sql = "SELECT serial_no, ecobrick_full_photo_url, ecobrick_thumb_photo_url, selfie_photo_url, selfie_thumb_url, photo_version, maker_id, ecobricker_maker, ecobrick_brk_amt, owner, weight_g, volume_ml, density, date_logged_ts, sequestration_type
        FROM tb_ecobricks
        WHERE ecobrick_unique_id = ?";
$stmt = $gobrik_conn->prepare($sql);
if (!$stmt) {
    error_log("Failed to prepare ecobrick detail statement: " . $gobrik_conn->error);
    echo "An error occurred. Please try again later.";
    exit();
}
$stmt->bind_param("i", $ecobrick_unique_id);
if ($stmt->execute()) {
    $stmt->bind_result($serial_no, $ecobrick_full_photo_url, $ecobrick_thumb_photo_url, $selfie_photo_url, $selfie_thumb_url, $photo_version, $maker_id, $ecobricker_maker, $ecobrick_brk_amt, $owner, $weight_g, $volume_ml, $density, $date_logged_ts, $sequestration_type);
    if (!$stmt->fetch()) {
        // No ecobrick found
        $alert_message = getNoEcobrickAlert($lang);
        echo "<script>
            alert(" . json_encode($alert_message) . ");
            window.location.href = 'log.php';
        </script>";
        exit();
    }
    $stmt->close();
    $maker_id = $maker_id !== null ? trim((string) $maker_id) : '';
    $ecobrick_brk_amt = $ecobrick_brk_amt !== null ? (float) $ecobrick_brk_amt : 0.0;
    $owner = $owner !== null ? trim((string) $owner) : '';
    $weight_g = $weight_g !== null ? (float) $weight_g : null;
    $volume_ml = $volume_ml !== null ? (float) $volume_ml : null;
    $ecobrick_weight_g = $weight_g;
    $ecobrick_volume_ml = $volume_ml;
    $density = $density !== null ? (float) $density : null;
    if ($density === null && $weight_g !== null && $volume_ml !== null) {
        $calculated_density = $volume_ml != 0 ? $weight_g / $volume_ml : null;
        $density = $calculated_density !== null ? round($calculated_density, 2) : null;
    }
    $ecobrick_density = $density;
    $date_logged_ts = $date_logged_ts !== null ? trim((string) $date_logged_ts) : '';
    $sequestration_type = $sequestration_type !== null ? trim((string) $sequestration_type) : '';
    $ecobrick_status = $status !== null ? trim((string) $status) : '';

    $owner_display_name = trim((string) ($ecobricker_maker ?? ''));
    if ($owner_display_name === '' && $owner !== '') {
        $owner_display_name = $owner;
    }

    if ($maker_id !== '') {
        $maker_lookup = $gobrik_conn->prepare("SELECT ecobricker_id FROM tb_ecobrickers WHERE maker_id = ? LIMIT 1");
        if ($maker_lookup) {
            $maker_lookup->bind_param("s", $maker_id);
            $maker_lookup->execute();
            $maker_lookup->bind_result($matched_ecobricker_id);
            if ($maker_lookup->fetch()) {
                $maker_ecobricker_id = (int) $matched_ecobricker_id;
            }
            $maker_lookup->close();
        }
    }
    if ($maker_ecobricker_id === null && $maker_id !== '' && ctype_digit($maker_id)) {
        $maker_ecobricker_id = (int) $maker_id;
    }

    if ($owner !== '') {
        if (ctype_digit($owner)) {
            $owner_ecobricker_id = (int) $owner;
        }
    }

    if ($owner_ecobricker_id === null && $maker_ecobricker_id !== null) {
        $owner_ecobricker_id = $maker_ecobricker_id;
    }

    if ($owner_ecobricker_id !== null) {
        $language_lookup = $gobrik_conn->prepare("SELECT language_id FROM tb_ecobrickers WHERE ecobricker_id = ? LIMIT 1");
        if ($language_lookup) {
            $language_lookup->bind_param("i", $owner_ecobricker_id);
            $language_lookup->execute();
            $language_lookup->bind_result($language_id_result);
            if ($language_lookup->fetch()) {
                $owner_language_id = $language_id_result !== null ? trim((string) $language_id_result) : 'Unknown';
            }
            $language_lookup->close();
        }
    }

    $language_map = [
        'en' => ['name' => 'English', 'flag' => '🇬🇧'],
        'fr' => ['name' => 'French', 'flag' => '🇫🇷'],
        'es' => ['name' => 'Spanish', 'flag' => '🇪🇸'],
        'de' => ['name' => 'German', 'flag' => '🇩🇪'],
        'zh' => ['name' => 'Chinese', 'flag' => '🇨🇳'],
        'id' => ['name' => 'Indonesian', 'flag' => '🇮🇩']
    ];

    $owner_language_code = strtolower(trim((string) $owner_language_id));
    $owner_language_flag = '';
    $owner_language_name = 'Unknown';

    if (isset($language_map[$owner_language_code])) {
        $owner_language_name = $language_map[$owner_language_code]['name'];
        $owner_language_flag = $language_map[$owner_language_code]['flag'];
    } elseif ($owner_language_code !== '' && $owner_language_code !== 'unknown') {
        $owner_language_name = strtoupper($owner_language_code);
    }

    $owner_language_display = trim(($owner_language_flag ? $owner_language_flag . ' ' : '') . $owner_language_name);
    if ($owner_language_display === '') {
        $owner_language_display = 'Unknown';
    }
} else {
    error_log("Error executing query: " . $stmt->error);
    echo "An error occurred while fetching ecobrick details.";
    exit();
}



echo '<!DOCTYPE html>
<html lang="' . htmlspecialchars($lang, ENT_QUOTES, 'UTF-8') . '">
<head>
<meta charset="UTF-8">
';
?>


<?php require_once ("../includes/validate-1-inc.php");?>

<div class="splash-title-block"></div>
<div id="splash-bar"></div>

<!-- PAGE CONTENT-->

<div id="form-submission-box" style="margin-top:83px;">
    <div class="form-container" style="padding-top:5px;">
        <div id="validation-content" class="new-form-styling">



            <div id="ecobrick-data-chart" class="ecobrick-data-chart">
            <?php if (!empty($ecobrick_full_photo_url) && $ecobrick_full_photo_url !== 'url missing'): ?>
                <div class="photo-container" id="basic-ecobrick-photo">
                    <img src="<?php echo htmlspecialchars($ecobrick_full_photo_url); ?>?v=<?php echo htmlspecialchars($photo_version); ?>"
                 title="Version <?php echo htmlspecialchars($photo_version); ?>" alt="Basic Ecobrick Photo" style="width:500px; max-width:95%" class="rotatable-photo" id="ecobrick-photo-<?php echo $serial_no; ?>" data-rotation="0">

                    <!-- Rotate buttons for the full ecobrick photo -->
                    <div class="rotate-controls">
                        <button class="rotate-button rotate-left" data-direction="left" data-photo-url="<?php echo htmlspecialchars($ecobrick_full_photo_url); ?>" data-photo-id="ecobrick-photo-<?php echo $serial_no; ?>">↪️</button>
                        <button class="confirm-rotate-button"
                                id="confirm-rotation-<?php echo $serial_no; ?>"
                                style="display:none;"
                                data-thumb-url="<?php echo htmlspecialchars($ecobrick_thumb_photo_url); ?>">
                            ✅
                        </button>
                        <button class="rotate-button rotate-right" data-direction="right" data-photo-url="<?php echo htmlspecialchars($ecobrick_full_photo_url); ?>" data-photo-id="ecobrick-photo-<?php echo $serial_no; ?>">↩️</button>
                    </div>
                </div>
            <?php endif; ?>
                <div class="data-row">
                    <span class="data-label">Serial Number</span>
                    <span class="data-value" style="font-weight:bold; font-size:1.15em;">
                        <?php echo htmlspecialchars($serial_no, ENT_QUOTES, 'UTF-8'); ?>
                    </span>
                </div>
                <div class="data-row">
                    <span class="data-label">Owner</span>
                    <span class="data-value"><?php echo htmlspecialchars($owner ?: '—', ENT_QUOTES, 'UTF-8'); ?></span>
                </div>
                <div class="data-row">
                    <span class="data-label">Weight</span>
                    <span class="data-value"><?php echo $ecobrick_weight_g !== null ? htmlspecialchars(number_format($ecobrick_weight_g) . ' g', ENT_QUOTES, 'UTF-8') : '—'; ?></span>
                </div>
                <div class="data-row">
                    <span class="data-label">Volume</span>
                    <span class="data-value"><?php echo $ecobrick_volume_ml !== null ? htmlspecialchars(number_format($ecobrick_volume_ml) . ' ml', ENT_QUOTES, 'UTF-8') : '—'; ?></span>
                </div>
                <div class="data-row">
                    <span class="data-label">Density</span>
                    <span class="data-value"><?php echo $ecobrick_density !== null ? htmlspecialchars(number_format($ecobrick_density, 2) . ' g/ml', ENT_QUOTES, 'UTF-8') : '—'; ?></span>
                </div>
                <div class="data-row">
                    <span class="data-label">Status</span>
                    <span class="data-value"><?php echo htmlspecialchars($ecobrick_status !== '' ? $ecobrick_status : '—', ENT_QUOTES, 'UTF-8'); ?></span>
                </div>
                <div class="data-row">
                    <span class="data-label">Date Logged</span>
                    <span class="data-value"><?php echo htmlspecialchars($date_logged_ts ?: '—', ENT_QUOTES, 'UTF-8'); ?></span>
                </div>
                <div class="data-row">
                    <span class="data-label">Sequestration</span>
                    <span class="data-value"><?php echo htmlspecialchars($sequestration_type ?: '—', ENT_QUOTES, 'UTF-8'); ?></span>
                </div>
            </div>
            <?php if ($selfie_photo_url): ?>
            <div class="photo-container" id="selfie-ecobrick-photo">
                <img src="<?php echo htmlspecialchars($selfie_photo_url); ?>?v=<?php echo htmlspecialchars($photo_version); ?>"
             title="Version <?php echo htmlspecialchars($photo_version); ?>"
             alt="Ecobrick Selfie Photo"
             style="max-width:500px;"
             class="rotatable-photo"
             id="selfie-photo-<?php echo $serial_no; ?>"
             data-rotation="0">


                <!-- Rotate buttons for the selfie photo -->
                <div class="rotate-controls">
                    <button class="rotate-button rotate-left" data-direction="left" data-photo-url="<?php echo htmlspecialchars($selfie_photo_url); ?>" data-photo-id="selfie-photo-<?php echo $serial_no; ?>">↪️</button>
                    <button class="confirm-rotate-button"
                            id="confirm-rotation-selfie-<?php echo $serial_no; ?>"
                            style="display:none;"
                            data-thumb-url="<?php echo htmlspecialchars($selfie_thumb_url); ?>">
                        ✅
                    </button>
                    <button class="rotate-button rotate-right" data-direction="right" data-photo-url="<?php echo htmlspecialchars($selfie_photo_url); ?>" data-photo-id="selfie-photo-<?php echo $serial_no; ?>">↩️</button>
                </div>
            </div>
            <?php endif; ?>


        <form id="status-update-form" method="POST" action="../api/forced_validation.php">
    <div class="form-item">
        <label for="ecobrick-status">Set Final Status:</label>
        <select id="ecobrick-status" name="status" required>
            <option value="" disabled selected>Set final status...</option>
            <option value="Authenticated">Authenticated</option>
            <option value="Rejected">Rejected</option>
        </select>
    </div>

    <div class="form-item">
        <label for="star-rating">Star Rating:</label>
        <select id="star-rating" name="star_rating" required>
            <option value="" disabled selected>Select a rating...</option>
            <option value="5">⭐⭐⭐⭐⭐ (5)</option>
            <option value="4">⭐⭐⭐⭐ (4)</option>
            <option value="3">⭐⭐⭐ (3)</option>
            <option value="2">⭐⭐ (2)</option>
            <option value="1">⭐ (1)</option>
        </select>
    </div>

    <div class="form-item">
        <label for="validator-feedback">Feedback</label>
        <div class="language-caption">⚠️ The ecobricker's language is <?php echo htmlspecialchars($owner_language_display, ENT_QUOTES, 'UTF-8'); ?></div>
        <textarea id="validator-feedback" name="validator_feedback" rows="4" placeholder="Share feedback or guidance for the ecobricker..."></textarea>
        <div class="preset-selects" style="display:flex;flex-wrap:wrap;gap:0.5rem;margin-top:0.5rem;">
            <select id="preset-answers-en" style="font-size:0.9em;" aria-label="Quick preset responses in English">
                <option value="" disabled selected>👆EN - Preset Answers</option>
                <option value="Sorry, no-paper.">Sorry, no-paper.</option>
                <option value="Sorry, plastic can't be dirty.">Sorry, plastic can't be dirty.</option>
                <option value="Your ecobrick needs to be packed tight.">Your ecobrick needs to be packed tight.</option>
            </select>
            <select id="preset-answers-id" style="font-size:0.9em;" aria-label="Respon cepat Bahasa Indonesia">
                <option value="" disabled selected>👆ID - Jawaban Cepat</option>
                <option value="Maaf, tidak boleh kertas.">Maaf, tidak boleh kertas.</option>
                <option value="Maaf, plastik tidak boleh kotor.">Maaf, plastik tidak boleh kotor.</option>
                <option value="Ecobrick kamu perlu dipadatkan rapat.">Ecobrick kamu perlu dipadatkan rapat.</option>
            </select>
        </div>
    </div>

    <div class="form-item">
        <input type="hidden" name="ecobrick_id" value="<?php echo $ecobrick_unique_id; ?>">
        <button type="submit" id="submit-button" class="submit-button enabled">✅ Confirm</button>
        <a href="admin-review.php" id="cancel-button" class="submit-button cancel">Cancel</a>
    </div>
</form>








        </div>
    </div>
</div>

</div>


<!--FOOTER STARTS HERE-->
<?php require_once ("../footer-2025.php");?>

<style>
    .modal-progress-steps {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
        font-size: 1.05em;
        line-height: 1.4;
    }

    .modal-progress-step {
        display: flex;
        align-items: center;
        gap: 0.65rem;
        color: var(--text-color, #1f1f1f);
    }

    .modal-progress-step .step-text {
        flex: 1;
    }

    .modal-progress-spinner {
        width: 16px;
        height: 16px;
        border-radius: 50%;
        border: 3px solid rgba(0, 0, 0, 0.15);
        border-top-color: var(--emblem-pink, #e91e63);
        animation: spin 0.8s linear infinite;
    }

    .modal-progress-step.complete .modal-progress-spinner,
    .modal-progress-step.error .modal-progress-spinner {
        display: none;
    }

    .modal-progress-step .step-status-icon {
        display: none;
        font-size: 1.1em;
    }

    .modal-progress-step.complete .step-status-icon,
    .modal-progress-step.error .step-status-icon {
        display: inline-flex;
    }

    .modal-progress-step.error {
        color: #c0392b;
    }

    .modal-progress-actions {
        margin-top: 2rem;
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
        justify-content: center;
    }

    .modal-progress-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.75rem 1.5rem;
        border-radius: 999px;
        background: var(--emblem-blue, #0057a4);
        color: #fff;
        text-decoration: none;
        font-weight: 600;
        transition: transform 0.2s ease, background 0.2s ease;
    }

    .modal-progress-button:hover,
    .modal-progress-button:focus {
        transform: translateY(-1px);
        background: var(--emblem-blue-mid, #0a6cc5);
    }

    .modal-progress-button.secondary {
        background: var(--subdued-text, #4a4a4a);
    }

    .modal-progress-button.secondary:hover,
    .modal-progress-button.secondary:focus {
        background: var(--subdued-text-strong, #2f2f2f);
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
</style>

<script>
    const feedbackField = document.getElementById("validator-feedback");
    const ecobrickFullPhotoUrl = <?php echo json_encode((!empty($ecobrick_full_photo_url) && $ecobrick_full_photo_url !== 'url missing') ? $ecobrick_full_photo_url . '?v=' . $photo_version : ''); ?>;
    const presetSelectConfigs = [
        {
            element: document.getElementById("preset-answers-en"),
            responses: {
                "Sorry, no-paper.": "Oh no!  It looks like you've put paper into your ecobrick.  Ecobricks are for plastic only.  Paper will mold and decay inside your ecobrick (plus its not an environmental toxin like loose-plastic).  Its plastic that makes an ecobrick dense and sturdy (and its an ecobrick that helps keep the plastic from getting loose in the biosphere and contaminating things!).   For more on this please see https://ecobricks.org/how",
                "Sorry, plastic can't be dirty.": "Oh no!  It looks like some of your plastic still has dirt or food on it.  Ecobricks thrive on clean, dry plastic.  Any organic bits will rot, smell, and weaken your hard work (plus grime keeps the plastic from staying sequestered).  Please wash, dry, and trim your plastic before packing so the ecobrick shines for the long term!  For more inspiration see https://ecobricks.org/how",
                "Your ecobrick needs to be packed tight.": "Oh wow!  There's still plenty of squish space inside your ecobrick.  Ecobricks do their best work when they are packed solid with plastic from top to bottom.  Loose spots make them bend and invite them to break open (letting that plastic back into the biosphere).  Keep pressing with a stick and add more small pieces until your ecobrick feels rock hard!  For packing tips visit https://ecobricks.org/how"
            }
        },
        {
            element: document.getElementById("preset-answers-id"),
            responses: {
                "Maaf, tidak boleh kertas.": "Waduh!  Sepertinya kamu memasukkan kertas ke dalam ecobrick.  Ecobrick itu hanya untuk plastik.  Kertas bisa berjamur dan membusuk di dalam ecobrick (dan tidak seperti plastik longgar, kertas bukan racun lingkungan).  Plastiklah yang membuat ecobrick padat dan kokoh (dan menjaga plastik agar tidak lepas ke biosfer).  Untuk info lebih lanjut silakan kunjungi https://ecobricks.org/how",
                "Maaf, plastik tidak boleh kotor.": "Waduh!  Sepertinya sebagian plastikmu masih ada kotoran atau sisa makanan.  Ecobrick terbaik dibuat dari plastik yang bersih dan kering.  Sisa organik akan membusuk, berbau, dan melemahkan hasil kerjamu (dan kotoran membuat plastik sulit tetap tersekuester).  Tolong cuci, keringkan, dan potong plastik sebelum dipadatkan agar ecobrickmu awet!  Untuk inspirasi lihat https://ecobricks.org/how",
                "Ecobrick kamu perlu dipadatkan rapat.": "Wah!  Masih ada ruang kosong di dalam ecobrickmu.  Ecobrick bekerja maksimal ketika dipenuhi plastik padat dari atas sampai bawah.  Bagian yang longgar membuatnya mudah bengkok dan bisa pecah (membuat plastik lepas kembali ke biosfer).  Terus tekan dengan tongkat dan tambahkan potongan kecil sampai ecobrick terasa sekeras batu!  Untuk tips pengepakan kunjungi https://ecobricks.org/how"
            }
        }
    ];

    const ownerDisplayName = <?php echo json_encode($owner_display_name ?? ''); ?>;
    const ownerNameDisplay = ownerDisplayName && ownerDisplayName.trim() !== '' ? ownerDisplayName.trim() : 'the ecobrick owner';
    const modalElement = document.getElementById("form-modal-message");
    const modalContentBox = document.getElementById("modal-content-box");
    const modalPhotoBox = document.getElementById("modal-photo-box");
    const modalMessageContainer = modalElement ? modalElement.querySelector(".modal-message") : null;
    let progressContainer = null;

    const openProgressModal = () => {
        if (!modalElement || !modalMessageContainer) {
            return null;
        }
        if (modalPhotoBox) {
            modalPhotoBox.style.display = "none";
        }
        if (modalContentBox) {
            modalContentBox.style.display = "flex";
            modalContentBox.style.background = "var(--main-background, #ffffff)";
        }
        modalMessageContainer.innerHTML = "";

        const wrapper = document.createElement("div");
        wrapper.className = "modal-progress-steps";
        modalMessageContainer.appendChild(wrapper);

        modalElement.style.display = "flex";
        modalElement.classList.remove("modal-hidden");
        modalElement.classList.add("modal-shown");

        const pageContent = document.getElementById("page-content");
        if (pageContent) {
            pageContent.classList.add("blurred");
        }
        const footerFull = document.getElementById("footer-full");
        if (footerFull) {
            footerFull.classList.add("blurred");
        }
        document.body.classList.add("modal-open");

        return wrapper;
    };

    const createProgressStep = (text) => {
        if (!progressContainer) {
            return null;
        }

        const stepEl = document.createElement("div");
        stepEl.className = "modal-progress-step";

        const spinner = document.createElement("span");
        spinner.className = "modal-progress-spinner";
        spinner.setAttribute("aria-hidden", "true");

        const statusIcon = document.createElement("span");
        statusIcon.className = "step-status-icon";
        statusIcon.setAttribute("aria-hidden", "true");

        const textSpan = document.createElement("span");
        textSpan.className = "step-text";
        textSpan.textContent = text;

        stepEl.append(spinner, statusIcon, textSpan);
        progressContainer.appendChild(stepEl);

        return { stepEl, spinner, statusIcon, textSpan };
    };

    const markStepComplete = (step, options = {}) => {
        if (!step) {
            return;
        }
        if (step.spinner && step.spinner.parentElement) {
            step.spinner.remove();
        }
        step.stepEl.classList.add("complete");
        if (options.text !== undefined) {
            step.textSpan.textContent = options.text;
        }
        step.statusIcon.style.display = "inline-flex";
        step.statusIcon.textContent = options.icon !== undefined ? options.icon : "✅";
    };

    const markStepError = (step, text) => {
        if (!step) {
            return;
        }
        if (step.spinner && step.spinner.parentElement) {
            step.spinner.remove();
        }
        step.stepEl.classList.add("error");
        step.statusIcon.style.display = "inline-flex";
        step.statusIcon.textContent = "❌";
        if (text !== undefined) {
            step.textSpan.textContent = text;
        }
    };

    const waitMinimum = () => new Promise(resolve => setTimeout(resolve, 220));

    const formatBrikValue = (value) => {
        const num = Number.parseFloat(value);
        return Number.isFinite(num) ? num.toFixed(2) : "0.00";
    };

    if (feedbackField) {
        presetSelectConfigs.forEach(({ element, responses }) => {
            if (!element) {
                return;
            }
            element.addEventListener("change", function() {
                const response = responses[this.value];
                presetSelectConfigs.forEach(({ element: otherElement }) => {
                    if (otherElement && otherElement !== element) {
                        otherElement.selectedIndex = 0;
                    }
                });
                if (response) {
                    feedbackField.value = response;
                    feedbackField.dispatchEvent(new Event('input'));
                }
            });
        });
    }

    document.getElementById("status-update-form").addEventListener("submit", async function(event) {
        event.preventDefault();
        const submitButton = document.getElementById("submit-button");
        const statusField = document.getElementById("ecobrick-status");
        const ratingField = document.getElementById("star-rating");

        if (statusField.value && statusField.value.toLowerCase() === "rejected" && feedbackField && feedbackField.value.trim() === "") {
            alert("Please provide feedback when rejecting an ecobrick.");
            feedbackField.focus();
            return;
        }

        if (!ratingField.value) {
            alert("Please select a star rating before confirming.");
            ratingField.focus();
            return;
        }

        submitButton.textContent = "Confirming...";
        submitButton.disabled = true;

        const formData = new FormData(this);
        if (statusField.value) {
            formData.set("status", statusField.value.trim());
        }

        progressContainer = null;
        progressContainer = openProgressModal();
        if (!progressContainer) {
            alert("Unable to open the validation progress window. Please try again.");
            submitButton.textContent = "✅ Confirm";
            submitButton.disabled = false;
            return;
        }

        const steps = {
            admin: createProgressStep("Admin status confirmed..."),
            payload: createProgressStep("Validation payload extracted..."),
            saved: createProgressStep("Validation saved to GoBrik database..."),
            status: createProgressStep("Ecobrick status updated..."),
            brikcoins: createProgressStep("Checking Brikcoin issuance..."),
            email: createProgressStep("Heads up email sent to ecobrick owner " + ownerNameDisplay),
            thanks: createProgressStep("Thank you for your validation.")
        };

        const orderedSteps = [steps.admin, steps.payload, steps.saved, steps.status, steps.brikcoins, steps.email, steps.thanks];
        const findPendingStep = () => orderedSteps.find(step => step && !step.stepEl.classList.contains("complete") && !step.stepEl.classList.contains("error"));

        const handleFailure = async (message) => {
            const pending = findPendingStep() || steps.admin;
            await waitMinimum();
            markStepError(pending, message);
        };

        let validationData;
        try {
            const response = await fetch(this.action, {
                method: "POST",
                body: formData
            });
            const json = await response.json();
            if (!response.ok || !json.success) {
                throw new Error(json.error || "Failed to update the ecobrick.");
            }
            validationData = json;
        } catch (error) {
            console.error("Validation error:", error);
            await handleFailure(error.message || "Failed to update the ecobrick.");
            submitButton.textContent = "✅ Confirm";
            submitButton.disabled = false;
            return;
        }

        await waitMinimum();
        markStepComplete(steps.admin, { text: "Admin status confirmed..." });

        await waitMinimum();
        markStepComplete(steps.payload, { text: "Validation payload extracted..." });

        await waitMinimum();
        markStepComplete(steps.saved, { text: "Validation saved to GoBrik database..." });

        const statusLabel = validationData.status_label || (statusField.value ? statusField.value.trim() : "");
        await waitMinimum();
        markStepComplete(steps.status, { text: "Ecobrick status updated to " + statusLabel + "..." });

        await waitMinimum();
        if ((validationData.status || "").toLowerCase() === "authenticated") {
            const formatted = formatBrikValue(validationData.brk_value);
            markStepComplete(steps.brikcoins, { text: formatted + " Brikcoins generated on the Brikchain...", icon: "✅" });
        } else {
            markStepComplete(steps.brikcoins, { text: "Unauthenticated ecobrick, no brikcoins generated.", icon: "🚫" });
        }

        const notifyPayload = {
            status: validationData.status_label || (statusField.value ? statusField.value.trim() : ""),
            serial_no: validationData.serial_no || "<?php echo htmlspecialchars($serial_no ?? '', ENT_QUOTES, 'UTF-8'); ?>",
            ecobricker_id: validationData.maker_ecobricker_id,
            validator_comments: feedbackField ? feedbackField.value.trim() : '',
            validation_note: validationData.validation_note || "",
            authenticator_version: validationData.authenticator_version || "",
            validator_name: validationData.validator_name || "",
            maker_id: validationData.maker_id || "",
            brk_value: validationData.brk_value || 0,
            brk_tran_id: validationData.brk_legacy_tran_id,
            ecobrick_brk_amt: validationData.ecobrick_brk_amt || 0,
            ecobrick_full_photo_url: ecobrickFullPhotoUrl
        };

        let notificationSuccess = false;
        let notificationError = "";

        try {
            const notifyResponse = await fetch("../api/notify_ecobricker.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify(notifyPayload)
            });
            const notifyJson = await notifyResponse.json();
            if (!notifyResponse.ok || !notifyJson.success) {
                throw new Error(notifyJson.error || "Unable to notify ecobricker.");
            }
            notificationSuccess = true;
        } catch (error) {
            console.error("Notification error:", error);
            notificationError = error.message || "Unable to notify ecobricker.";
        }

        if (notificationSuccess) {
            await waitMinimum();
            markStepComplete(steps.email, { text: "Heads up email sent to ecobrick owner " + ownerNameDisplay, icon: "✅" });
        } else {
            await waitMinimum();
            markStepError(steps.email, "Heads up email failed: " + notificationError);
        }

        await waitMinimum();
        markStepComplete(steps.thanks, { text: "Thank you for your validation.", icon: "🙏" });

        submitButton.textContent = "Confirmed!";

        if (modalMessageContainer) {
            const actions = document.createElement("div");
            actions.className = "modal-progress-actions";
            actions.innerHTML = `
                <a class="modal-progress-button" href="admin-review.php">Continue Validating</a>
                <a class="modal-progress-button secondary" href="dashboard.php">Dashboard</a>
            `;
            modalMessageContainer.appendChild(actions);
        }
    });
</script>

<script src="../scripts/brik-rotation.js?v=<?php echo ($version); ;?>"></script>

</body>
</html>



