<?php
require_once '../earthenAuth_helper.php'; // Include the authentication helper functions

// Ensure the user is logged in (handled by $is_logged_in from helper)
if (!$is_logged_in) {
    header('Location: login.php?redirect=admin-review.php');
    exit();
}

// Set up page variables
$lang = basename(dirname($_SERVER['SCRIPT_NAME'])) ?? 'en';
$version = '0.448';
$page = 'validate-1';
$lastModified = date("Y-m-d\TH:i:s\Z", filemtime(__FILE__));

// Include database connections
require_once '../gobrikconn_env.php';
require_once '../buwanaconn_env.php';

 // Fetch the user's location data
    $buwana_id = $_SESSION['buwana_id'] ?? ''; // Retrieve buwana_id from session

    // Fetch the user's location data
    $user_continent_icon = getUserContinent($buwana_conn, $buwana_id);
    $user_location_watershed = getWatershedName($buwana_conn, $buwana_id);
    $user_location_full = getUserFullLocation($buwana_conn, $buwana_id);
    $gea_status = getGEA_status($buwana_id);
    $user_community_name = getCommunityName($buwana_conn, $buwana_id);
    $ecobrick_unique_id = '';
    $first_name = getFirstName($buwana_conn, $buwana_id);

    // Check if the user is an admin
    if (strpos($gea_status, 'Admin') === false) {
        echo "<script>
            alert('Sorry, this page is for admins only.');
            window.location.href = 'dashboard.php';
        </script>";
        exit();
    }

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
if ($status === "authenticated") {
    echo "<script>
        alert('Oops! This ecobrick has already been authenticated.');
        window.location.href = 'admin-review.php';
    </script>";
    exit();
}

// Fetch ecobrick details
$sql = "SELECT serial_no, ecobrick_full_photo_url, ecobrick_thumb_photo_url, selfie_photo_url, selfie_thumb_url
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
    $stmt->bind_result($serial_no, $ecobrick_full_photo_url, $ecobrick_thumb_photo_url, $selfie_photo_url, $selfie_thumb_url);
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
        <img src="<?php echo htmlspecialchars($ecobrick_full_photo_url); ?>" alt="Basic Ecobrick Photo" style="width:500px; max-width:95%" class="rotatable-photo" id="ecobrick-photo-<?php echo $serial_no; ?>" data-rotation="0">

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
        <img src="<?php echo htmlspecialchars($selfie_photo_url); ?>" alt="Ecobrick Selfie Photo" style="max-width:500px;" class="rotatable-photo" id="selfie-photo-<?php echo $serial_no; ?>" data-rotation="0">

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





            <h2 id="ecobrick-logged-title"><span data-lang-id="000-Ecobrick">Ecobrick</span> <?php echo $serial_no; ?>.</h2>


           <!-- Add the dropdown form -->
<form id="status-update-form" method="POST" action="validation_process.php" style="margin-top: 20px;">
    <label for="ecobrick-status" style="display: block; margin-bottom: 10px;">Set Ecobrick Status:</label>
    <select id="ecobrick-status" name="status" required style="margin-bottom: 20px; padding: 10px;">
        <option value="" disabled selected>Set Ecobrick Status</option>
        <option value="Awaiting validation">Awaiting validation</option>
        <option value="Step 2 complete">step 2 complete</option>
        <option value="Step 2 complete">not ready</option>
        <option value="Rejected">Rejected</option>
    </select>
    <input type="hidden" name="ecobrick_id" value="<?php echo $ecobrick_unique_id; ?>">
    <button type="submit" id="submit-button" style="padding: 10px 20px;" class="enabled">Save</button>
    <a href="admin-review.php" id="cancel-button" style="padding: 10px 20px;" class="enabled">Cancel</a>
</form>







        </div>
    </div>
    <br><br>
</div>

</div>


<!--FOOTER STARTS HERE-->
<?php require_once ("../footer-2024.php");?>


<script>
    document.getElementById("status-update-form").addEventListener("submit", function(event) {
        event.preventDefault(); // Prevent default form submission
        const submitButton = document.getElementById("submit-button");
        submitButton.textContent = "Processing...";
        submitButton.disabled = true;

        // Prepare the form data
        const formData = new FormData(this);

        // Send the request to the validation_process.php
        fetch("validation_process.php", {
            method: "POST",
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                submitButton.textContent = "Status Updated!";
                setTimeout(() => {
                    window.location.href = "admin-review.php";
                }, 2000); // Redirect after 2 seconds
            } else {
                submitButton.textContent = "Request Failed";
                submitButton.disabled = false; // Re-enable the button
            }
        })
        .catch(error => {
            console.error("Error:", error);
            submitButton.textContent = "Request Failed";
            submitButton.disabled = false; // Re-enable the button
        });
    });
</script>

<script src="../scripts/brik-rotation.js?v=<?php echo ($version); ;?>"></script>

</body>
</html>



