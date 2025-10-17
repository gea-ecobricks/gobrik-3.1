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

    document.getElementById("status-update-form").addEventListener("submit", function(event) {
        event.preventDefault(); // Prevent default form submission
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

        fetch(this.action, {
            method: "POST",
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                submitButton.textContent = "Confirmed!";

                const notifyPayload = {
                    status: data.status_label || statusField.value,
                    serial_no: data.serial_no || "<?php echo htmlspecialchars($serial_no ?? '', ENT_QUOTES, 'UTF-8'); ?>",
                    ecobricker_id: data.maker_ecobricker_id,
                    validator_comments: feedbackField ? feedbackField.value.trim() : '',
                    validation_note: data.validation_note || "",
                    authenticator_version: data.authenticator_version || "",
                    validator_name: data.validator_name || "",
                    maker_id: data.maker_id || "",
                    brk_value: data.brk_value || 0,
                    brk_tran_id: data.brk_legacy_tran_id,
                    ecobrick_brk_amt: data.ecobrick_brk_amt || 0,
                    ecobrick_full_photo_url: ecobrickFullPhotoUrl
                };

                return fetch("../api/notify_ecobricker.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify(notifyPayload)
                })
                .then(response => response.json())
                .then(notification => {
                    if (!notification.success) {
                        console.warn("Notification warning:", notification.error || "Unable to notify ecobricker.");
                    }
                })
                .catch(error => {
                    console.error("Notification error:", error);
                })
                .finally(() => {
                    window.location.href = "admin-review.php";
                });
            } else {
                submitButton.textContent = "✅ Confirm";
                submitButton.disabled = false;
                alert(data.error || "Failed to update the ecobrick.");
            }
        })
        .catch(error => {
            console.error("Error:", error);
            submitButton.textContent = "✅ Confirm";
            submitButton.disabled = false;
            alert("An unexpected error occurred. Please try again.");
        });
    });
</script>

<script src="../scripts/brik-rotation.js?v=<?php echo ($version); ;?>"></script>

</body>
</html>



