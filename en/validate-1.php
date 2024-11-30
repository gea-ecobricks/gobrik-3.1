<?php
require_once '../earthenAuth_helper.php'; // Include the authentication helper functions

// Ensure the user is logged in (handled by $is_logged_in from helper)
if (!$is_logged_in) {
    header('Location: login.php?redirect=log.php');
    exit();
}

// Set up page variables
$lang = basename(dirname($_SERVER['SCRIPT_NAME'])) ?? 'en';
$version = '0.448';
$page = 'log-3';
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
//
// // Handle POST AJAX skip action
// if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'skip' && isset($_POST['ecobrick_unique_id'])) {
//     header('Content-Type: application/json');
//
//     $ecobrick_unique_id = (int)$_POST['ecobrick_unique_id'];
//     if (setEcobrickStatus('Awaiting validation', $ecobrick_unique_id)) {
//         echo json_encode(['success' => true, 'message' => 'Status updated to Awaiting validation.']);
//     } else {
//         echo json_encode(['success' => false, 'message' => 'Failed to update status.']);
//     }
//     exit();
// }

echo '<!DOCTYPE html>
<html lang="' . htmlspecialchars($lang, ENT_QUOTES, 'UTF-8') . '">
<head>
<meta charset="UTF-8">
';
?>





<?php require_once ("../includes/log-3-inc.php");?>

<div class="splash-title-block"></div>
<div id="splash-bar"></div>

<!-- PAGE CONTENT-->

<div id="form-submission-box" style="margin-top:83px;">
    <div class="form-container" style="padding-top:75px;">
        <div class="splash-form-content-block" style="text-align:center; display:flex;flex-flow:column;">



            <div id="upload-success-message">
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





            <h2 id="ecobrick-logged-title"><span data-lang-id="000-Ecobrick">Ecobrick</span> <?php echo $serial_no; ?> <span data-lang-id="001-form-title"> is ready to be rviewed</span>.</h2>



        </div>
    </div>
    <br><br>
</div>

</div>


<!--FOOTER STARTS HERE-->
<?php require_once ("../footer-2024.php");?>



<script>

// ROTATE Photo

// SECTION 1: Function to send rotation request to the PHP function
function rotateEcobrickPhoto(photoUrl, thumbUrl, rotationDegrees, photoId, totalRotationDegrees) {
    // Create an AJAX request to send the rotation degrees to the server
    var xhr = new XMLHttpRequest();
    var url = "rotate_photo.php"; // PHP file that handles the photo rotation
    var params = "photo_url=" + encodeURIComponent(photoUrl) +
                 "&thumb_url=" + encodeURIComponent(thumbUrl) +
                 "&rotation=" + rotationDegrees;

    xhr.open("POST", url, true);
    xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

    // Handle the server's response
    xhr.onreadystatechange = function () {
        if (xhr.readyState == 4) {
            if (xhr.status == 200) {
                console.log("Server response: " + xhr.responseText);

                // Check if the response contains a success message
                if (xhr.responseText.trim().includes("rotated successfully")) {
                    // Alert the user of the successful rotation
                    alert("Your photo has been rotated " + totalRotationDegrees + " degrees clockwise and saved to the server.");
                    console.log("Image rotation successful for: " + photoUrl);

                    // SECTION 2: Preserve the current rotation after confirmation
                    // Do not reset the image to 0 degrees after confirmation.
                    // The image will stay at its current rotation.

                } else {
                    // Handle error response from the server
                    alert("Something went wrong saving your rotation. Error: " + xhr.responseText);
                }
            } else {
                // Handle the error if the request was unsuccessful
                alert("An error occurred. Status: " + xhr.status);
            }
        }
    };


    // Send the rotation degrees to the server
    xhr.send(params);
}

// SECTION 3: Function to adjust the height of the container after the image rotates
function adjustContainerHeight(photo, container) {
    var currentRotation = parseInt(photo.getAttribute('data-rotation')) || 0;

    // Adjust height when the image is rotated by 90 or 270 degrees
    if (currentRotation % 180 !== 0) {
        var newHeight = photo.width;
        container.style.height = newHeight + 'px';
    } else {
        // Set container height to auto when image is not rotated (0 or 180 degrees)
        container.style.height = 'auto';
    }
}

// SECTION 4: Function to handle the rotate button clicks
document.querySelectorAll('.rotate-button').forEach(function(button) {
    button.addEventListener('click', function() {
        var photoContainer = this.closest('.photo-container');
        var photo = photoContainer.querySelector('.rotatable-photo');
        var confirmButton = photoContainer.querySelector('.confirm-rotate-button');

        // Get the current rotation from the data attribute
        var currentRotation = parseInt(photo.getAttribute('data-rotation')) || 0;
        var direction = this.getAttribute('data-direction');

        // Rotate the image based on the direction
        if (direction === 'left') {
            currentRotation = (currentRotation - 90) % 360;
        } else if (direction === 'right') {
            currentRotation = (currentRotation + 90) % 360;
        }

        // Apply the rotation and update the data-rotation attribute
        photo.style.transform = 'rotate(' + currentRotation + 'deg)';
        photo.setAttribute('data-rotation', currentRotation);

        // Show the confirm button
        confirmButton.style.display = 'block';

        // Adjust the container height based on the new image rotation
        adjustContainerHeight(photo, photoContainer);
    });
});

// SECTION 5: Handle the confirmation button click to send the rotation to the server
document.querySelectorAll('.confirm-rotate-button').forEach(function(button) {
    button.addEventListener('click', function() {
        var photoContainer = this.closest('.photo-container');
        var photo = photoContainer.querySelector('.rotatable-photo');
        var currentRotation = parseInt(photo.getAttribute('data-rotation')) || 0;
        var photoUrl = this.previousElementSibling.getAttribute('data-photo-url'); // Get the original photo URL from the rotate button
        var thumbUrl = this.getAttribute('data-thumb-url'); // Get the thumbnail URL from the confirm button

        // Calculate total clockwise rotation (normalize it to 0-360)
        var totalRotationDegrees = (currentRotation + 360) % 360;

        // Trigger the PHP function to rotate the actual photo
        var photoId = photo.getAttribute('id'); // Assuming the photo ID corresponds to the ecobrick ID or serial_no
        rotateEcobrickPhoto(photoUrl, thumbUrl, currentRotation, photoId, totalRotationDegrees);
    });
});



</script>



</body>
</html>
