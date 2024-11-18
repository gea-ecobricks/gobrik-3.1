<?php
require_once '../earthenAuth_helper.php'; // Include the authentication helper functions

// Set up page variables
$lang = basename(dirname($_SERVER['SCRIPT_NAME']));
$version = '0.446';
$page = 'log-3';
$lastModified = date("Y-m-d\TH:i:s\Z", filemtime(__FILE__));

startSecureSession(); // Start a secure session with regeneration to prevent session fixation


// Check if user is logged in and session active
if ($is_logged_in) {
    $buwana_id = $_SESSION['buwana_id'] ?? ''; // Retrieve buwana_id from session

    // Include database connection
    require_once '../gobrikconn_env.php';
    require_once '../buwanaconn_env.php';

    // Fetch the user's continent icon
    $country_icon = getUserContinent($buwana_conn, $buwana_id);
    $watershed_name = getWatershedName($buwana_conn, $buwana_id, $lang); // Corrected to include the $lang parameter

    // Fetch the user's first name from the database
    $first_name = getUserFirstName($buwana_conn, $buwana_id);

    if (isset($_GET['id'])) {
        $ecobrick_unique_id = (int)$_GET['id'];

        // Check if the ecobrick has already been processed
        $status_check_stmt = $gobrik_conn->prepare("SELECT status FROM tb_ecobricks WHERE ecobrick_unique_id = ?");
        $status_check_stmt->bind_param("i", $ecobrick_unique_id);
        $status_check_stmt->execute();
        $status_check_stmt->bind_result($status);
        $status_check_stmt->fetch();
        $status_check_stmt->close();

        // If status is 'Awaiting validation', show an alert and redirect to the dashboard
        if ($status === "Awaiting validation") {
            echo "<script>
                alert('Oops! It looks like this ecobrick has already had its serial generated and logged. Please log another ecobrick or manage it on your dashboard.');
                window.location.href = 'dashboard.php'; // Redirect to the dashboard
            </script>";
            exit();
        }


        // Fetch the ecobrick details from the database
        $sql = "SELECT serial_no, ecobrick_full_photo_url, ecobrick_thumb_photo_url, selfie_photo_url, selfie_thumb_url
                FROM tb_ecobricks
                WHERE ecobrick_unique_id = ?";
        $stmt = $gobrik_conn->prepare($sql);
        $stmt->bind_param("i", $ecobrick_unique_id);
        $stmt->execute();
        $stmt->store_result(); // Store result to check number of rows

        if ($stmt->num_rows > 0) {
            // Ecobrick found, fetch its data
            $stmt->bind_result($serial_no, $ecobrick_full_photo_url, $ecobrick_thumb_photo_url, $selfie_photo_url, $selfie_thumb_url);
            $stmt->fetch();
            $stmt->close();
        } else {
            // No ecobrick found, show alert and redirect
            $alert_message = getNoEcobrickAlert($lang);
            echo "<script>
                alert('" . addslashes($alert_message) . "');
                window.location.href = 'log.php';
            </script>";
            exit;
        }
    } else {
        echo "No ecobrick ID provided.";
        exit();
    }
} else {
    // Redirect to login page with the redirect parameter set to the current page
    header('Location: login.php?redirect=log.php');
    exit();
}


// Check if the request method is POST and the action is skip (AJAX request handling)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'skip' && isset($_POST['ecobrick_unique_id'])) {
    header('Content-Type: application/json'); // Set response headers for JSON response

    $ecobrick_unique_id = (int)$_POST['ecobrick_unique_id'];

    // Update the status of the ecobrick to 'Awaiting validation'
    if (setEcobrickStatus('Awaiting validation', $ecobrick_unique_id)) {
        echo json_encode(['success' => true, 'message' => 'Status updated to Awaiting validation.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update status.']);
    }
    exit(); // Exit to prevent the rest of the script from running
}



echo '<!DOCTYPE html>
<html lang="' . $lang . '">
<head>
<meta charset="UTF-8">
';
?>




<?php require_once ("../includes/log-3-inc.php");?>

<div class="splash-title-block"></div>
<div id="splash-bar"></div>

<!-- PAGE CONTENT-->
<div id="top-page-image" class="top-page-image log-step-3" style="margin-top: 105px; z-index: 35; position: absolute; text-align:center;width:100% ; height: 30px;"></div>

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





            <h2 id="ecobrick-logged-title"><span data-lang-id="000-Ecobrick">Ecobrick</span> <?php echo $serial_no; ?> <span data-lang-id="001-form-title"> is logged! </span>🎉</h2>


            <!-- Vision Form -->
            <form id="add-vision-form">
                <p data-lang-id="vision-form-into">Optionally, you may now add a vision to your ecobrick. This is a short message: a vision, a wish, or a prayer for the future. The message will be added to your ecobrick's record on the brikchain and visible to anyone who reviews your ecobrick's data.</p>

                <textarea name="vision_message" id="vision_message" rows="4" maxlength="255" placeholder="Your vision for this ecobrick and its future..."></textarea>
                <p class="form-caption" style="margin-top: -30px;text-align: right;margin-right: 10px;
  margin-bottom: 15px;"><span id="character-counter">256</span> <span data-lang-id="024X-char-remaining"><span></p>

                <input type="hidden" name="ecobrick_unique_id" value="<?php echo htmlspecialchars($ecobrick_unique_id); ?>">

                <div class="button-group">
                    <button type="submit" class="confirm-button" data-lang-id="027-save-button-text">Save</button>
                    <!--<a class="confirm-button" id="skip-button" data-lang-id="014-skip-button">Skip: Complete Logging</a>-->
                </div>
            </form>





            <div id="next-options" style="display:none;">
                <div class="conclusion-message"  style="font-family:'Mulish',sans-serif; font-size:1.4em;color:var(--h1);margin-top:20px;"><span data-lang-id="003-logging-is">Logging of ecobrick </span> <?php echo $serial_no; ?> <span data-lang-id="003-complete">is complete. 👍</span></div>
                <h2 data-lang-id="077-earth-thanks-you">The Earth Thanks You.</h2>
                <br>

                <a class="confirm-button" href="brik.php?serial_no=<?php echo $serial_no; ?>" data-lang-id="013-view-ecobrick-post" style="width:250px;">View Ecobrick Post</a>
                <a class="confirm-button" href="log.php?retry=<?php echo htmlspecialchars($ecobrick_unique_id); ?>" data-lang-id="015-edit-ecobrick" style="width:250px;">✏️ Edit  ecobrick</a>
                <a class="confirm-button" href="log.php" data-lang-id="015-log-another-ecobrick" style="width:250px;">➕ Log another ecobrick</a>
                <a class="confirm-button" href="dashboard.php" data-lang-id="000-dashboard" style="width:250px;">🏡 Dashboard</a>
                <form id="deleteForm" method="POST">
                    <input type="hidden" name="ecobrick_unique_id" value="<?php echo htmlspecialchars($ecobrick_unique_id); ?>">
                    <input type="hidden" name="action" value="delete_ecobrick">
                    <a class="confirm-button" style="background:red; cursor:pointer;width:250px;" id="deleteButton" data-lang-id="014-delete-ecobrick">❌ Delete Ecobrick</a>
                </form>
                <br>
                <div id="vision-added-failure" style="display:none;font-size:1.2em;">
                <!--<p><span data-lang-id="015-error-happened">😭 Hmmm... something went wrong adding your vision to </span><?php echo $ecobrick_unique_id; ?>'s <span data-lang-id="016-error-happened" record. Let us know on the beta test or bug review form, please!</span></p>-->
                <p id="post-error-message"></p>
            </div>
        <h3>🙏 💚 🌏</h3>
                <div id="vision-added-success" style="display:none;font-family:'Mulish',sans-serif; font-size:1.2em;color:var(--text-color);">
                <span style="color:green;">✔</span> <span data-lang-id="015-vision-added">Vision successfully added to ecobrick record </span> <?php echo $ecobrick_unique_id; ?>.
            </div>
                <div id="conclusion-message" style="font-family:'Mulish',sans-serif; font-size:1.2em;color:var(--text-color);"><span style="color:green;">✔</span> <span data-lang-id="003-recorded-ready" >Your ecobrick is now in the validation queue now pending peer review.</span></div>
            </div>

        </div>
    </div>
    <br><br>
</div>

</div>


<!--FOOTER STARTS HERE-->
<?php require_once ("../footer-2024.php");?>




    <script>

       document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('deleteButton').addEventListener('click', function(event) {
        event.preventDefault(); // Prevent default action

        if (confirm('Are you sure you want to delete this ecobrick from the database? This cannot be undone.')) {
            const ecobrickUniqueId = document.querySelector('input[name="ecobrick_unique_id"]').value;
            const action = document.querySelector('input[name="action"]').value;

            fetch('delete-ecobrick.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    'ecobrick_unique_id': ecobrickUniqueId,
                    'action': action // Include the action field
                })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok.');
                }
                return response.json(); // Expecting JSON from the server
            })
            .then(data => {
                if (data.success) {
                    alert('Your ecobrick has been successfully deleted. You may now log another ecobrick...');
                    window.location.href = 'log.php';
                } else {
                    alert('There was an error deleting the ecobrick: ' + data.error);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('There was an error processing your request.');
            });
        }
    });
});


//TEXT FIELD CHARACTER COUNTER

document.addEventListener('DOMContentLoaded', function () {
    const visionTextarea = document.getElementById('vision_message');
    const charCounter = document.getElementById('character-counter');
    const maxLength = 255;

    // Fetch the translation string for "characters remaining"
//     const charRemainingTextElement = document.id('[data-lang-id="024-char-remaining"]');
//     const charRemainingText = charRemainingTextElement.textContent;

    // Update character counter on input
    visionTextarea.addEventListener('input', function () {
        const remainingChars = maxLength - visionTextarea.value.length;
        // Update the counter text dynamically with the translation
        charCounter.textContent = `${remainingChars} `;
//         charRemainingTextElement.textContent = `${charRemainingText}`;
    });
});


    </script>


<!-- JavaScript to handle form submission -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const visionForm = document.getElementById('add-vision-form');
    const visionMessage = document.getElementById('vision_message');
    const ecobrickLoggedTitle = document.getElementById('ecobrick-logged-title');
    const visionAddedSuccess = document.getElementById('vision-added-success');
    const visionAddedFailure = document.getElementById('vision-added-failure');
    const nextOptions = document.getElementById('next-options');
//     const skipButton = document.getElementById('skip-button');
    const postErrorMessage = document.getElementById('post-error-message');

    // Function to hide the form and show the next steps
    function showNextOptions() {
        ecobrickLoggedTitle.style.display = 'none';
        visionForm.style.display = 'none';
        nextOptions.style.display = 'block';
    }
//
//     // Event listener for the 'Skip: Complete Logging' button
//     skipButton.addEventListener('click', function (event) {
//         event.preventDefault();
//
//         // Send a request to update the status without adding a vision
//         fetch('update_brik_status.php', {
//             method: 'POST',
//             headers: {
//                 'Content-Type': 'application/x-www-form-urlencoded',
//             },
//             body: new URLSearchParams({
//                 'action': 'skip',
//                 'ecobrick_unique_id': document.querySelector('[name="ecobrick_unique_id"]').value
//             })
//         })
//             .then(response => response.json())
//             .then(data => {
//                 if (data.success) {
//                     showNextOptions();
//                 } else {
//                     alert('Error: ' + data.message);
//                 }
//             })
//             .catch(error => {
//                 console.error('Error:', error);
//                 alert('A network error occurred. Please try again later.');
//             });
//     });

    // Event listener for the form submission
    visionForm.addEventListener('submit', function (event) {
        event.preventDefault(); // Prevent default form submission

        // Check if the vision message is empty
//         if (visionMessage.value.trim() === '') {
//             alert("Seems you forgot to actually add a vision! Please try again or hit Skip.");
//             return;
//         }

        // Send form data to log_vision.php
        const formData = new FormData(visionForm);

        fetch('log_vision.php', {
            method: 'POST',
            body: formData,
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    visionAddedSuccess.style.display = 'block';
                } else {
                    visionAddedFailure.style.display = 'block';
                    postErrorMessage.textContent = data.message || 'An error occurred while adding your vision.';
                }
                showNextOptions();
            })
            .catch(error => {
                console.error('Error:', error);
                visionAddedFailure.style.display = 'block';
                postErrorMessage.textContent = 'A network error occurred. Please try again later.';
                showNextOptions();
            });
    });
});

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
