<?php
require_once '../earthenAuth_helper.php'; // Include the authentication helper functions
require_once '../auth/session_start.php';

// Set up page variables
$lang = basename(dirname($_SERVER['SCRIPT_NAME']));
$version = '0.14';
$page = 'bug-report';
$lastModified = date("Y-m-d\TH:i:s\Z", filemtime(__FILE__));


// Check if user is logged in and session active
if ($is_logged_in) {
    $buwana_id = $_SESSION['buwana_id'] ?? ''; // Retrieve buwana_id from session

    // Include database connections
    require_once '../gobrikconn_env.php';
    require_once '../buwanaconn_env.php';

    // Fetch the user's location data
    $user_continent_icon = getUserContinent($buwana_conn, $buwana_id);
$earthling_emoji = getUserEarthlingEmoji($buwana_conn, $buwana_id);
    $user_location_watershed = getWatershedName($buwana_conn, $buwana_id);
    $user_location_full = getUserFullLocation($buwana_conn, $buwana_id);
    $gea_status = getGEA_status($buwana_id);
    $user_community_name = getCommunityName($buwana_conn, $buwana_id);
    $first_name = getFirstName($buwana_conn, $buwana_id);

    // Run messenger code here

    // Close the database connections
    $buwana_conn->close();
    $gobrik_conn->close();
} else {
    // Redirect to login page with the redirect parameter set to the current page
    echo '<script>
        alert("Please login before viewing this page.");
        window.location.href = "login.php?redirect=' . urlencode($page) . '.php";
    </script>';
    exit();
}

// Output the HTML structure
echo '<!DOCTYPE html>
<html lang="' . htmlspecialchars($lang, ENT_QUOTES, 'UTF-8') . '">
<head>
<meta charset="UTF-8">
';
?>



<!--
GoBrik.com site version 3.0
Developed and made open source by the Global Ecobrick Alliance
See our git hub repository for the full code and to help out:
https://github.com/gea-ecobricks/gobrik-3.0/tree/main/en-->

<?php require_once("../includes/bug-report-inc.php"); ?>

<div class="splash-title-block"></div>
<div id="splash-bar"></div>
<div id="top-page-image" class="bug-reported top-page-image"></div>

<!-- BUG REPORT FORM CONTENT -->
<div id="form-submission-box" style="height:fit-content;margin-top: 110px;">
    <div class="form-container">
        <div id="greeting" style="text-align:center;width:100%;margin:auto;margin-top:25px;">
            <h2 id="greeting" data-lang-id="1000-bug-report">Report a Bug</h2>
            <p id="subgreeting" data-lang-id="002-bug-report-subtitle">GoBrik 3.0 has just launched. Help us catch all the bugs by reporting any problems you encounter. Messages go to our volunteer development team. Attach a screen shot if necessary.📸</p>
        </div>

<!-- Bug Report Form -->
     <!-- Bug Report Form -->
<form id="bugReportForm" >
    <div class="bug-report-input-wrapper" style="position: relative;" data-lang-id="003-bug-form">
        <textarea id="bugReportInput" placeholder="What went wrong? Or... what could be better?" rows="6" required></textarea>
        <input type="file" id="imageUploadInput" accept="image/jpeg, image/jpg, image/png, image/webp" style="display: none;" />
        <span id="imageFileName" class="image-file-name"></span>
        <button type="button" id="uploadPhotoButton" class="upload-photo-button" title="Upload Photo" aria-label="Upload Photo" >📷</button>
    </div>
    <div>
        <button type="submit" id="bugReportSubmit" class="submit-button enabled" title="Submit Bug Report" data-lang-id="004-bug-submit">
            🐞 Submit Bug
        </button>
        <div class="load-spinner hidden" id="submitSpinner"></div>
    </div>
    <div id="feedbackMessage" class="hidden"></div>
</form>



        <!-- Feedback Message -->
        <div id="feedbackMessage" class="hidden"></div>


    </div>

<div style="padding:10px; text-align: center; margin: auto; align-self: center;margin-top: 0px;width:80%;">
        <p class="caption" data-lang-id="005-report-info" style="font-size: 0.8em;color:var(--subdued-text);">When you send a bug report your message and details on your browser and OS will be sent to our development team over GoBrik messenger.  </p>
    </div>
</div>


</div><!--closes main and starry background-->

<!-- FOOTER STARTS HERE -->
<?php require_once("../footer-2026.php"); ?>

<script>
    const lang = '<?php echo $lang; ?>';
</script>

<script>
    // Prepare thank you messages in each language
    let thankYouMessage;

    <?php if ($lang === 'es'): ?>
        thankYouMessage = `
            <h3 style="padding-top:42px;">Informe de error enviado con éxito.</h3>
            <p>Gracias por tomarte el tiempo para hacer que GoBrik sea mejor para todos.</p>
            <h2>🙏</h2>
            <p>🦉 🐢 🦋 🐠 🐂</p>
        `;
    <?php elseif ($lang === 'fr'): ?>
        thankYouMessage = `
            <h3 style="padding-top:42px;">Rapport de bug soumis avec succès.</h3>
            <p>Merci de prendre le temps de rendre GoBrik meilleur pour tout le monde.</p>
            <h2>🙏</h2>
            <p>🦉 🐢 🦋 🐠 🐂</p>
        `;
    <?php elseif ($lang === 'id'): ?>
        thankYouMessage = `
            <h3 style="padding-top:42px;">Laporan bug berhasil dikirim.</h3>
            <p>Terima kasih telah meluangkan waktu untuk membuat GoBrik lebih baik untuk semua.</p>
            <h2>🙏</h2>
            <p>🦉 🐢 🦋 🐠 🐂</p>
        `;
    <?php else: ?> <!-- Default to English -->
        thankYouMessage = `
            <h3 style="padding-top:42px;">Bug report submitted successfully.</h3>
            <p>Thank you for taking the time to make GoBrik better for everyone.</p>
            <h2>🙏</h2>
            <p>🦉 🐢 🦋 🐠 🐂</p>
        `;
    <?php endif; ?>
</script>


<script>
    const userId = '<?php echo $buwana_id; ?>'; // Get the user's ID from PHP
    const userContinentIcon = '<?php echo addslashes($user_continent_icon); ?>';
    const userLocationWatershed = '<?php echo addslashes($user_location_watershed); ?>';
    const userLocationFull = '<?php echo addslashes($user_location_full); ?>';
    const geaStatus = '<?php echo addslashes($gea_status); ?>';
    const userCommunityName = '<?php echo addslashes($user_community_name); ?>';
//     const maxFileSize = 10 * 1024 * 1024; // 10 MB

    $(document).ready(function() {
        // Handle form submission
        $('#bugReportForm').on('submit', function(event) {
            event.preventDefault();

            const bugReport = $('#bugReportInput').val().trim();
            const file = $('#imageUploadInput')[0].files[0];

            if (bugReport) {
                const formData = new FormData();
                formData.append('created_by', userId);

                // Append the user's bug report message with browser info and user data
                const browserInfo = getBrowserInfo();
                const userInfo = getUserData();
                const messageWithInfo = `${bugReport}<br><br> ${browserInfo}<br><br>${userInfo}`;

                formData.append('message', messageWithInfo);

                // If a file is selected and valid, append it to the FormData
                if (file && validateFile(file)) {
                    formData.append('image', file);
                }

                // Show the spinner and disable the submit button
                $('#bugReportSubmit').addClass('loading').prop('disabled', true).html('');
                $('#submitSpinner').removeClass('hidden');

                // Submit the bug report
                $.ajax({
                    url: '../messenger/create_bug_report.php',
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: handleFormSuccess,
                    error: handleFormError,
                    complete: function() {
                        $('#bugReportSubmit').removeClass('loading').prop('disabled', false).html('🐞 Submit Bug');
                        $('#submitSpinner').addClass('hidden');
                    }
                });
            }
        });

        // Validate the uploaded file COPIED
        function validateFile(file) {
            const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
            return validTypes.includes(file.type) && file.size <= maxFileSize;
        }


// Handle form submission success


    // Handle form submission success
function handleFormSuccess(response) {
    if (response.status === 'success') {
        $('#greeting, #imageFileName, #subgreeting, #bugReportInput, #bugReportSubmit, #uploadPhotoButton').fadeOut(300);
        $('#feedbackMessage')
            .removeClass('hidden error')
            .addClass('success')
            .html(thankYouMessage); // Display the pre-defined thank you message
    } else {
        showError(response.message || 'Failed to submit bug report. Please try again.');
    }
}






        // Handle form submission error
        function handleFormError(jqXHR, textStatus, errorThrown) {
            console.error('Error submitting bug report:', textStatus, errorThrown);
            console.error('Response:', jqXHR.responseText);
            showError('An error occurred while submitting your bug report. Please try again.');
        }

        // Display error messages
        function showError(message) {
            $('#feedbackMessage')
                .removeClass('hidden success')
                .addClass('error')
                .text(message);
        }

/*
        // Handle file upload button click
        $('#uploadPhotoButton').on('click', function() {
            if (!$(this).hasClass('remove-attachment')) {
                $('#imageUploadInput').click(); // Trigger file input
            } else {
                resetUploadButton();
                $('#imageFileName').text(''); // Clear file name
                $('#imageUploadInput').val(''); // Reset file input
            }
        });

        // Handle file selection
        $('#imageUploadInput').on('change', function(event) {
            const file = event.target.files[0];
            if (file && validateFile(file)) {
                $('#imageFileName').text(file.name);
                showUploadSuccess();
            }
        });

        // Update the upload button to indicate success
        function showUploadSuccess() {
            $('#uploadPhotoButton')
                .html('✔️')
                .css('background', 'var(--emblem-green)');

            setTimeout(function() {
                $('#uploadPhotoButton')
                    .html('📎')
                    .css('background', 'grey')
                    .addClass('attachment-added remove-attachment')
                    .attr('title', 'Click to remove attachment');
            }, 1000);
        }

        // Reset the upload button to its original state
        function resetUploadButton() {
            $('#uploadPhotoButton')
                .html('📸')
                .css('background', 'grey')
                .removeClass('attachment-added remove-attachment')
                .attr('title', 'Upload Photo');
        }

        // Handle hover behavior for attachment removal state
        $('#uploadPhotoButton').hover(
            function() {
                if ($(this).hasClass('remove-attachment')) {
                    $(this).html('❌');
                }
            },
            function() {
                if ($(this).hasClass('remove-attachment')) {
                    $(this).html('📎');
                }
            }
        ); */

        // Utility functions for gathering user and browser info
        function getBrowserInfo() {
            const userAgent = navigator.userAgent;
    const platform = navigator.platform;
    const language = navigator.language || navigator.userLanguage;
    const screenWidth = window.screen.width;
    const screenHeight = window.screen.height;
    const viewportWidth = window.innerWidth;
    const viewportHeight = window.innerHeight;

    // Detect browser name from userAgent
    let browserName = "Unknown Browser";
    if (userAgent.includes("Firefox")) {
        browserName = "Firefox";
    } else if (userAgent.includes("Edg")) {
        browserName = "Microsoft Edge";
    } else if (userAgent.includes("Chrome")) {
        browserName = "Google Chrome";
    } else if (userAgent.includes("Safari")) {
        browserName = "Safari";
    } else if (userAgent.includes("Opera") || userAgent.includes("OPR")) {
        browserName = "Opera";
    }

    // Detect device type
    let deviceType = "Desktop";
    if (/Mobi|Android|iPhone|iPad|iPod/.test(userAgent)) {
        deviceType = "Mobile";
    }

    return `Browser Info:
- Browser: ${browserName}
- User Agent: ${userAgent}
- Platform: ${platform}
- Language: ${language}
- Device Type: ${deviceType}
- Screen Size: ${screenWidth}x${screenHeight}
- Viewport Size: ${viewportWidth}x${viewportHeight}`;
}

        function getUserData() {
            return `User Info:
- Continent Icon: ${userContinentIcon}
- Location Watershed: ${userLocationWatershed}
- Full Location: ${userLocationFull}
- GEA Status: ${geaStatus}
- Community Name: ${userCommunityName}`;
        }
    });



</script>

<script src="../scripts/messenger.js?v=2.6"></script>




</body>
</html>