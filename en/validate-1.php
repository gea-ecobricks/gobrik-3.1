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
    $existing_brk_amt = 0.0;
    $first_name = getFirstName($buwana_conn, $buwana_id);
    $ecobrick_owner = '';
    $ecobrick_weight_g = null;
    $ecobrick_volume_ml = null;
    $ecobrick_date_logged = '';
    $ecobrick_sequestration_type = '';


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
$sql = "SELECT serial_no, ecobrick_full_photo_url, ecobrick_thumb_photo_url, selfie_photo_url, selfie_thumb_url, photo_version, maker_id, ecobricker_maker, ecobrick_brk_amt, ecobrick_owner, weight_g, volume_ml, date_logged, sequestration_type
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
    $stmt->bind_result($serial_no, $ecobrick_full_photo_url, $ecobrick_thumb_photo_url, $selfie_photo_url, $selfie_thumb_url, $photo_version, $maker_id, $ecobricker_maker, $existing_brk_amt, $ecobrick_owner, $ecobrick_weight_g, $ecobrick_volume_ml, $ecobrick_date_logged, $ecobrick_sequestration_type);
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
    $existing_brk_amt = $existing_brk_amt !== null ? (float) $existing_brk_amt : 0.0;
    $ecobrick_owner = $ecobrick_owner !== null ? trim((string) $ecobrick_owner) : '';
    $ecobrick_weight_g = $ecobrick_weight_g !== null ? (float) $ecobrick_weight_g : null;
    $ecobrick_volume_ml = $ecobrick_volume_ml !== null ? (float) $ecobrick_volume_ml : null;
    $ecobrick_date_logged = $ecobrick_date_logged !== null ? trim((string) $ecobrick_date_logged) : '';
    $ecobrick_sequestration_type = $ecobrick_sequestration_type !== null ? trim((string) $ecobrick_sequestration_type) : '';

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
    <div class="form-container" style="padding-top:75px;">
        <div class="splash-form-content-block" style="text-align:center; display:flex;flex-flow:column;">



            <div id="validate-introduction">
    <!-- Ecobrick Full Photo -->
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

<!-- Selfie Photo -->
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

</div>





            <h2 id="ecobrick-logged-title"><span data-lang-id="000-Ecobrick">Ecobrick</span> <?php echo $serial_no; ?></h2>

            <div id="ecobrick-data-chart" class="ecobrick-data-chart">
                <div class="data-row">
                    <span class="data-label">Owner</span>
                    <span class="data-value"><?php echo htmlspecialchars($ecobrick_owner ?: '—', ENT_QUOTES, 'UTF-8'); ?></span>
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
                    <span class="data-label">Date Logged</span>
                    <span class="data-value"><?php echo htmlspecialchars($ecobrick_date_logged ?: '—', ENT_QUOTES, 'UTF-8'); ?></span>
                </div>
                <div class="data-row">
                    <span class="data-label">Sequestration</span>
                    <span class="data-value"><?php echo htmlspecialchars($ecobrick_sequestration_type ?: '—', ENT_QUOTES, 'UTF-8'); ?></span>
                </div>
            </div>

            <p>Welcome to beta authentications!  This is for admins only to manually force the authentication of an ecobrick.  Validation records and brikchain updates are generated, but not a 3-party authentication with detailed revisions.  Thus, please be sure to leave some feedback for the ecobricker, especially on rejections.</p>


        <form id="status-update-form" method="POST" action="../api/forced_validation.php" style="margin-top: 20px;">
    <label for="ecobrick-status" style="display: block; margin-bottom: 10px;">Set Final Status:</label>
    <select id="ecobrick-status" name="status" required style="margin-bottom: 20px; padding: 10px; max-width:300px;">
        <option value="" disabled selected>Set final status...</option>
        <option value="Authenticated">Authenticated</option>
        <option value="Rejected">Rejected</option>
    </select>

    <label for="star-rating" style="display: block; margin: 10px 0;">Star Rating:</label>
    <select id="star-rating" name="star_rating" required style="margin-bottom: 20px; padding: 10px; max-width:300px;">
        <option value="" disabled selected>Select a rating...</option>
        <option value="5">⭐⭐⭐⭐⭐ (5)</option>
        <option value="4">⭐⭐⭐⭐ (4)</option>
        <option value="3">⭐⭐⭐ (3)</option>
        <option value="2">⭐⭐ (2)</option>
        <option value="1">⭐ (1)</option>
    </select>

    <label for="validator-feedback" style="display: block; margin: 10px 0;">Feedback for the Ecobricker:</label>
    <textarea id="validator-feedback" name="validator_feedback" rows="4" placeholder="Share feedback or guidance for the ecobricker..." style="margin-bottom: 10px; padding: 10px; max-width:500px; width:100%;"></textarea>

    <label for="preset-answers" style="display:block; margin: 0 0 6px 0;">Pre-set answers:</label>
    <select id="preset-answers" style="margin-bottom: 20px; padding: 10px; max-width:500px; width:100%;">
        <option value="">Select a quick response...</option>
        <option value="Sorry, no-paper.">Sorry, no-paper.</option>
        <option value="Sorry, plastic can't be dirty.">Sorry, plastic can't be dirty.</option>
        <option value="Your ecobrick needs to be packed tight.">Your ecobrick needs to be packed tight.</option>
    </select>

    <input type="hidden" name="ecobrick_id" value="<?php echo $ecobrick_unique_id; ?>">
    <button type="submit" id="submit-button" class="submit-button enabled" style="display:block;width:100%;max-width:300px;margin:0 auto 12px auto;">✅ Confirm</button>
    <a href="admin-review.php" id="cancel-button" class="submit-button cancel" style="display:block;width:100%;max-width:300px;text-decoration:none;text-align:center;margin:8px auto 0 auto;">Cancel</a>
</form>








        </div>
    </div>
    <br><br>
</div>

</div>


<!--FOOTER STARTS HERE-->
<?php require_once ("../footer-2025.php");?>


<script>
    const feedbackField = document.getElementById("validator-feedback");
    const presetSelect = document.getElementById("preset-answers");
    const presetResponses = {
        "Sorry, no-paper.": "Oh no!  It looks like you've put paper into your ecobrick.  Ecobricks are for plastic only.  Paper will mold and decay inside your ecobrick (plus its not an environmental toxin like loose-plastic).  Its plastic that makes an ecobrick dense and sturdy (and its an ecobrick that helps keep the plastic from getting loose in the biosphere and contaminating things!).   For more on this please see https://ecobricks.org/how",
        "Sorry, plastic can't be dirty.": "Oh no!  It looks like some of your plastic still has dirt or food on it.  Ecobricks thrive on clean, dry plastic.  Any organic bits will rot, smell, and weaken your hard work (plus grime keeps the plastic from staying sequestered).  Please wash, dry, and trim your plastic before packing so the ecobrick shines for the long term!  For more inspiration see https://ecobricks.org/how",
        "Your ecobrick needs to be packed tight.": "Oh wow!  There's still plenty of squish space inside your ecobrick.  Ecobricks do their best work when they are packed solid with plastic from top to bottom.  Loose spots make them bend and invite them to break open (letting that plastic back into the biosphere).  Keep pressing with a stick and add more small pieces until your ecobrick feels rock hard!  For packing tips visit https://ecobricks.org/how"
    };

    if (presetSelect && feedbackField) {
        presetSelect.addEventListener("change", function() {
            const response = presetResponses[this.value];
            if (response) {
                feedbackField.value = response;
                feedbackField.dispatchEvent(new Event('input'));
            }
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
                    existing_brk_amt: data.existing_brk_amt || 0
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



