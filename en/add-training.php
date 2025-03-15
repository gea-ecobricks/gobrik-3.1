<?php

require_once '../earthenAuth_helper.php'; // Authentication helper

// PART 1: Set page variables
$lang = basename(dirname($_SERVER['SCRIPT_NAME']));
$version = '0.52';
$page = 'add-training';
$lastModified = date("Y-m-d\TH:i:s\Z", filemtime(__FILE__));

ob_start(); // Prevent output before headers

// PART 2: ✅ LOGIN & ROLE CHECK
if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

$buwana_id = $_SESSION['buwana_id'];
require_once '../gobrikconn_env.php';

// ✅ Fetch User Role
$gea_status = getGEA_status($buwana_id);

if (!$gea_status || stripos($gea_status, 'trainer') === false) {
    header("Location: dashboard.php?error=unauthorized");
    exit();
}

// PART 3: ✅ Fetch User Details
require_once '../buwanaconn_env.php';
$user_continent_icon = getUserContinent($buwana_conn, $buwana_id);
$user_location_watershed = getWatershedName($buwana_conn, $buwana_id);
$user_location_full = getUserFullLocation($buwana_conn, $buwana_id);

$user_community_name = getCommunityName($buwana_conn, $buwana_id);
$first_name = getFirstName($buwana_conn, $buwana_id);

$buwana_conn->close(); // Close the database connection

require_once '../gobrikconn_env.php';

// ✅ Get `training_id` from URL (for editing existing report)
$training_id = isset($_GET['training_id']) ? intval($_GET['training_id']) : 0;
$editing = ($training_id > 0);

// ✅ Fetch existing training details if editing
if ($editing) {
    $sql_fetch = "SELECT training_title, lead_trainer, training_country, training_date, no_participants, trained_community,
                  training_type, briks_made, avg_brik_weight, location_lat, location_long, location_full, training_summary,
                  training_agenda, training_success, training_challenges, training_lessons_learned
                  FROM tb_trainings WHERE training_id = ?";
    $stmt_fetch = $gobrik_conn->prepare($sql_fetch);
    $stmt_fetch->bind_param("i", $training_id);
    $stmt_fetch->execute();
    $stmt_fetch->bind_result($training_title, $lead_trainer, $training_country, $training_date, $no_participants, $trained_community,
                            $training_type, $briks_made, $avg_brik_weight, $latitude, $longitude, $location_full,
                            $training_summary, $training_agenda, $training_success, $training_challenges, $training_lessons_learned);
    $stmt_fetch->fetch();
    $stmt_fetch->close();
}

// ✅ If form is submitted, insert/update the training report
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    include '../project-photo-functions.php';

    // ✅ Sanitize Input
    $location_full = trim($_POST['location_address'] ?? 'Default Location');
    $training_title = trim($_POST['training_title']);
    $lead_trainer = trim($_POST['lead_trainer']);
    $training_country = trim($_POST['training_country']);
    $training_date = trim($_POST['training_date']);
    $no_participants = (int) ($_POST['no_participants'] ?? 0);
    $trained_community = trim($_POST['trained_community'] ?? '');
    $training_type = trim($_POST['training_type']);
    $briks_made = (int) ($_POST['briks_made'] ?? 0);
    $avg_brik_weight = isset($_POST['avg_brik_weight']) ? (int)$_POST['avg_brik_weight'] : NULL;
    $latitude = isset($_POST['latitude']) ? (double)$_POST['latitude'] : NULL;
    $longitude = isset($_POST['longitude']) ? (double)$_POST['longitude'] : NULL;
    $training_summary = trim($_POST['training_summary']);
    $training_agenda = trim($_POST['training_agenda']);
    $training_success = trim($_POST['training_success']);
    $training_challenges = trim($_POST['training_challenges']);
    $training_lessons_learned = trim($_POST['training_lessons_learned']);

    if ($editing) {
        // ✅ Update existing training report
        $sql = "UPDATE tb_trainings SET training_title=?, lead_trainer=?, training_country=?, training_date=?,
                no_participants=?, trained_community=?, training_type=?, briks_made=?, avg_brik_weight=?,
                location_lat=?, location_long=?, location_full=?, training_summary=?, training_agenda=?,
                training_success=?, training_challenges=?, training_lessons_learned=?
                WHERE training_id=?";
        $stmt = $gobrik_conn->prepare($sql);
        $stmt->bind_param("sssisiiiddssssssi",
            $training_title, $lead_trainer, $training_country, $training_date, $no_participants, $trained_community,
            $training_type, $briks_made, $avg_brik_weight, $latitude, $longitude, $location_full,
            $training_summary, $training_agenda, $training_success, $training_challenges, $training_lessons_learned,
            $training_id
        );
    } else {
        // ✅ Insert new training report
        $sql = "INSERT INTO tb_trainings (training_title, lead_trainer, training_country, training_date, no_participants,
                trained_community, training_type, briks_made, avg_brik_weight, location_lat, location_long, location_full,
                training_summary, training_agenda, training_success, training_challenges, training_lessons_learned)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $gobrik_conn->prepare($sql);
        $stmt->bind_param("sssisiiiddssssss",
            $training_title, $lead_trainer, $training_country, $training_date, $no_participants, $trained_community,
            $training_type, $briks_made, $avg_brik_weight, $latitude, $longitude, $location_full,
            $training_summary, $training_agenda, $training_success, $training_challenges, $training_lessons_learned
        );
    }

    $stmt->execute();
    $stmt->close();
    header("Location: add-training-images.php?training_id=" . $training_id);
    exit();
}
?>


<!--PART 4 GENERATE META TAGS-->

<!DOCTYPE html>
<HTML lang="en">
<HEAD>
    <META charset="UTF-8">


<title><?php echo !empty($training_title) ? $training_title : 'Log your Training Report'; ?></title>
<meta name="keywords" content="GEA Registration, Community, Event, Webinar, Course">
<meta name="description" content="<?php echo !empty($training_type) && !empty($lead_trainer) && !empty($training_date)
    ? "Log the $training_type led by $lead_trainer on $training_date on the GEA reporting system. Reports will be shared on the front page of Ecobricks.org."
    : "Log your GEA workshop. Reports will be featured on the front page of Ecobricks.org and shareable on social media."; ?>">

<!-- Facebook Open Graph Tags for social sharing -->
<meta property="og:url" content="https://www.gobrik.com/<?php echo $lang; ?>/add-report.php">
<meta property="og:type" content="website">
<meta property="og:title" content="<?php echo !empty($training_title) ? $training_title : 'Log your Training Report'; ?>">
<meta property="og:description" content="<?php echo !empty($training_type) && !empty($lead_trainer) && !empty($training_date)
    ? "Log the $training_type led by $lead_trainer on $training_date on the GEA reporting system. Reports will be shared on the front page of Ecobricks.org."
    : "Log your GEA workshop. Reports will be featured on the front page of Ecobricks.org and shareable on social media."; ?>">

<!-- Default image in case no feature image is available -->
<?php
$og_image = !empty($feature_photo1_main) ? $feature_photo1_main : "https://gobrik.com/svgs/shanti.svg";
?>
<meta property="og:image" content="<?php echo $og_image; ?>">
<meta property="fb:app_id" content="1781710898523821">
<meta property="og:image:width" content="1000">
<meta property="og:image:height" content="1000">
<meta property="og:image:alt" content="<?php echo !empty($training_title) ? $training_title : 'GEA Trainer in action'; ?>">
<meta property="og:locale" content="en_GB">

<meta property="article:modified_time" content="<?php echo date("c"); ?>">

<meta name="author" content="GoBrik.com">
<meta property="og:type" content="page">
<meta property="og:site_name" content="GoBrik.com">
<meta property="article:publisher" content="https://web.facebook.com/ecobricks.org">
<meta property="og:image:type" content="image/png">
<meta name="author" content="GoBrik.com">

<!--PART 5 TOP DECORATION-->
    <?php require_once ("../includes/add-training-inc.php");?>

    <div class="splash-content-block"></div>
    <div id="splash-bar"></div>

    <!-- PAGE CONTENT-->

    <div id="form-submission-box">
        <div class="form-container">
            <div class="form-top-header" style="display:flex;flex-flow:row;">
                <div class="step-graphic" style="width:fit-content;margin:auto;margin-left:0px">
                    <img src="../svgs/step1-log-project.svg" style="height:25px;">
                </div>
                <div id="language-code" onclick="showLangSelector()" aria-label="Switch languages"><span data-lang-id="000-language-code">🌐 EN</span></div>
            </div>

            <div class="splash-form-content-block">
                <div class="splash-box">
                    <div class="splash-heading" data-lang-id="001-splash-title">Post a GEA Training</div>
                </div>
                <div class="splash-image" data-lang-id="003-splash-image-alt">
                    <img src="../svgs/shanti.svg" style="width:65%" alt="There are many ways to conduct training with ecobricks">
                </div>
            </div>

            <div class="lead-page-paragraph">
                <p data-lang-id="004-form-description">Is your workshop complete?  Share your social success with the world! Use this form to file and post your completed event or training report. Trainings will be featured on our main page and archived in our trainings database.</p>
            </div>

    <!-- PART 6 THE FORM -->
<form id="submit-form" method="post" action="" enctype="multipart/form-data" novalidate>

    <div class="form-item" style="margin-top: 25px;">
        <label for="training_title">Training Title:</label><br>
        <input type="text" id="training_title" name="training_title"
            value="<?php echo htmlspecialchars($training_title ?? '', ENT_QUOTES, 'UTF-8'); ?>"
            aria-label="Training Title" required>
    </div>

    <div class="form-item">
        <label for="training_date">Training Date:</label><br>
        <input type="date" id="training_date" name="training_date"
            value="<?php echo htmlspecialchars($training_date ?? '', ENT_QUOTES, 'UTF-8'); ?>"
            aria-label="Training Date" required>
    </div>

    <div class="form-item">
        <label for="no_participants">Number of Participants:</label><br>
        <input type="number" id="no_participants" name="no_participants" min="1" max="5000" required
            value="<?php echo htmlspecialchars($no_participants ?? '', ENT_QUOTES, 'UTF-8'); ?>">
    </div>

    <div class="form-item">
        <label for="lead_trainer">Lead Trainer:</label><br>
        <input type="text" id="lead_trainer" name="lead_trainer"
            value="<?php echo htmlspecialchars($lead_trainer ?? '', ENT_QUOTES, 'UTF-8'); ?>"
            aria-label="Lead Trainer" required>
    </div>

    <div class="form-item">
        <label for="trained_community">Trained Community:</label><br>
        <input type="text" id="trained_community" name="trained_community"
            value="<?php echo htmlspecialchars($trained_community ?? '', ENT_QUOTES, 'UTF-8'); ?>"
            aria-label="Trained Community">
    </div>

    <div class="form-item">
        <label for="training_type">What type of training was this?</label><br>
        <select id="training_type" name="training_type" required>
            <option value="" disabled selected>Select training type...</option>
            <option value="Online Starter Workshop"
                <?php echo ($training_type === 'Online Starter Workshop') ? 'selected' : ''; ?>>Online Starter Workshop</option>
            <option value="Local Starter Workshop"
                <?php echo ($training_type === 'Local Starter Workshop') ? 'selected' : ''; ?>>Local Starter Workshop</option>
            <option value="Online Training of Trainers"
                <?php echo ($training_type === 'Online Training of Trainers') ? 'selected' : ''; ?>>Online Training of Trainers</option>
            <option value="Local Training of Trainers"
                <?php echo ($training_type === 'Local Training of Trainers') ? 'selected' : ''; ?>>Local Training of Trainers</option>
        </select>
    </div>

    <div class="form-item">
        <label for="briks_made">How many ecobricks were made?</label><br>
        <input type="number" id="briks_made" name="briks_made" min="1" max="5000" required
            value="<?php echo htmlspecialchars($briks_made ?? '', ENT_QUOTES, 'UTF-8'); ?>">
    </div>

    <div class="form-item">
        <label for="avg_brik_weight">Average Brik Weight (grams):</label><br>
        <input type="number" id="avg_brik_weight" name="avg_brik_weight" min="100" max="2000"
            value="<?php echo htmlspecialchars($avg_brik_weight ?? '', ENT_QUOTES, 'UTF-8'); ?>">
    </div>

    <div class="form-item">
        <label for="training_country">Country:</label><br>
        <input type="text" id="training_country" name="training_country" required
            value="<?php echo htmlspecialchars($training_country ?? '', ENT_QUOTES, 'UTF-8'); ?>">
    </div>

    <div class="form-item">
        <label for="training_summary">Training Summary:</label><br>
        <textarea id="training_summary" name="training_summary" required><?php echo htmlspecialchars($training_summary ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
    </div>

    <div class="form-item">
        <label for="training_agenda">Training Agenda:</label><br>
        <textarea id="training_agenda" name="training_agenda"><?php echo htmlspecialchars($training_agenda ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
    </div>

    <div class="form-item">
        <label for="training_success">Training Successes:</label><br>
        <textarea id="training_success" name="training_success" required><?php echo htmlspecialchars($training_success ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
    </div>

    <div class="form-item">
        <label for="training_challenges">Training Challenges:</label><br>
        <textarea id="training_challenges" name="training_challenges" required><?php echo htmlspecialchars($training_challenges ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
    </div>

    <div class="form-item">
        <label for="training_lessons_learned">Lessons Learned:</label><br>
        <textarea id="training_lessons_learned" name="training_lessons_learned" required><?php echo htmlspecialchars($training_lessons_learned ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
    </div>

    <div class="form-item">
        <label for="location_address">Training Location:</label><br>
        <input type="text" id="location_address" name="location_address" placeholder="Start typing your town..." required
            value="<?php echo htmlspecialchars($location_full ?? '', ENT_QUOTES, 'UTF-8'); ?>">
    </div>

    <input type="hidden" id="lat" name="latitude" value="<?php echo htmlspecialchars($latitude ?? '', ENT_QUOTES, 'UTF-8'); ?>">
    <input type="hidden" id="lon" name="longitude" value="<?php echo htmlspecialchars($longitude ?? '', ENT_QUOTES, 'UTF-8'); ?>">

    <div>
        <input type="submit" value="Next: Upload Photos ➡️">
    </div>

</form>

        </div>
    </div>


    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">

    <script>


        //TOGGLE COMMUNITY OR PERSONAL PROJECT SORT FIELDS
        // document.addEventListener("DOMContentLoaded", function() {
        //     // Initially hide all additional fields
        //     const communityField = document.getElementById("trained_community").parentNode;
        //     const trainingTypeField = document.getElementById("training_type").parentNode;
        //     const trainingLocationField = document.getElementById("training_location").parentNode;
        //
        //     communityField.style.display = 'none';
        //     trainingTypeField.style.display = 'none';
        //     trainingLocationField.style.display = 'none';

            // Function to show or hide fields based on the dropdown selection
        //     function toggleFields() {
        //         var trainingSort = document.getElementById("training_sort").value;
        //
        //         // Reset visibility
        //         communityField.style.display = 'none';
        //         trainingTypeField.style.display = 'none';
        //         trainingLocationField.style.display = 'none';
        //
        //         if (trainingSort === "community") {
        //             communityField.style.display = '';
        //         } else if (trainingSort === "personal") {
        //             trainingTypeField.style.display = '';
        //         }
        //
        //         // Show connected ecobricks and project duration fields if a project sort is selected
        //         if (trainingSort === "community" || trainingSort === "personal") {
        //             trainingLocationField.style.display = '';
        //         }
        //
        //         // Dynamically adjust the max-height for the advanced box content
        //         const advancedBoxContent = document.querySelector('.advanced-box-content');
        //         advancedBoxContent.style.maxHeight = advancedBoxContent.scrollHeight + "px";
        //     }
        //
        //     // Add change event listener to the training sort dropdown
        //     document.getElementById("training_sort").addEventListener("change", toggleFields);
        // });

        // //SHOW HIDE THE ADVANCED BOX
        // function toggleAdvancedBox(event) {
        //     // Get the current advanced box based on the clicked header
        //     let currentAdvancedBox = event.currentTarget.parentElement;
        //
        //     // Assuming the element that will have the `aria-expanded` attribute is the header itself
        //     let header = currentAdvancedBox.querySelector('.advanced-box-header');
        //
        //     // Find the content and icon specific to this advanced box
        //     let content = currentAdvancedBox.querySelector('.advanced-box-content');
        //     let icon = currentAdvancedBox.querySelector('.advanced-open-icon');
        //
        //     // Check if the content is currently expanded or not
        //     let isExpanded = header.getAttribute('aria-expanded') === 'true';
        //
        //     if (!isExpanded) {
        //         content.style.maxHeight = content.scrollHeight + 'px'  //   Set to its full height
        //         icon.textContent = '−';  // switch to minus symbol for an open state
        //         header.setAttribute('aria-expanded', 'true'); // Update aria-expanded to true
        //     } else {
        //         content.style.maxHeight = '0px';  // Collapse it
        //         icon.textContent = '+';  // Set to plus symbol
        //         header.setAttribute('aria-expanded', 'false'); // Update aria-expanded to false
        //     }
        // }

        // Attach the function to all header div's click events
        // document.addEventListener("DOMContentLoaded", function() {
        //     let headers = document.querySelectorAll('.advanced-box-header');
        //     headers.forEach(header => {
        //         header.addEventListener('click', toggleAdvancedBox);
        //     });
        // });

        document.getElementById('submit-form').addEventListener('submit', function(event) {
            event.preventDefault(); // Prevent the form from submitting until validation is complete
            var isValid = true; // Flag to determine if the form should be submitted

            // Helper function to display error messages
            function displayError(elementId, showError) {
                var errorDiv = document.getElementById(elementId);
                if (showError) {
                    errorDiv.style.display = 'block'; // Show the error message
                    isValid = false; // Set form validity flag
                } else {
                    errorDiv.style.display = 'none'; // Hide the error message
                }
            }

            // Helper function to check for invalid characters
            function hasInvalidChars(value) {
                const invalidChars = /[\'\"><]/; // Regex for invalid characters
                return invalidChars.test(value);
            }

            // 1. Training Title Validation
            var trainingTitle = document.getElementById('training_title').value.trim();
            displayError('title-error-required', trainingTitle === '');
            displayError('title-error-long', trainingTitle.length > 50);
            displayError('title-error-invalid', hasInvalidChars(trainingTitle));

            // 2. Training Date Validation
            var trainingDate = document.getElementById('training_date').value.trim();
            displayError('date-error-required', trainingDate === '');

            // // 3. Training Logged Validation
            // var trainingLogged = document.getElementById('training_logged').value.trim();
            // displayError('logged-error-required', trainingLogged === '');

            // 4. Number of Participants Validation
            var noParticipants = parseInt(document.getElementById('no_participants').value, 10);
            displayError('participants-error-range', isNaN(noParticipants) || noParticipants < 1 || noParticipants > 5000);

            // 5. Lead Trainer Validation
            var leadTrainer = document.getElementById('lead_trainer').value.trim();
            displayError('trainer-error-required', leadTrainer === '');

            // 6. Trained Community Validation (just check length)
            var trainedCommunity = document.getElementById('trained_community').value.trim();
            displayError('community-error-long', trainedCommunity.length > 255);

            // 7. Training Type Validation
            var trainingType = document.getElementById('training_type').value;
            displayError('type-error-required', trainingType === '');

            // 8. Bricks Made Validation
            var briksMade = parseInt(document.getElementById('briks_made').value, 10);
            displayError('briks-error-range', isNaN(briksMade) || briksMade < 1 || briksMade > 5000);

            // 9. Estimated Weight Validation
            var estimatedWeight = parseInt(document.getElementById('avg_brik_weight').value, 10);
            displayError('weight-error-range', isNaN(estimatedWeight) || estimatedWeight < 100 || estimatedWeight > 2000);

            // 10. Training Country Validation
            var trainingCountry = document.getElementById('training_country').value.trim();
            displayError('country-error-required', trainingCountry === '');

            // // 11. Training Location Validation
            // var trainingLocation = document.getElementById('training_location').value.trim();
            // displayError('location-error-required', trainingLocation === '');

            // 12. Training Summary Validation
            var trainingSummary = document.getElementById('training_summary').value.trim();
            displayError('summary-error-long', trainingSummary.length > 2000);
            displayError('summary-error-invalid', hasInvalidChars(trainingSummary));

            // 13. Training Agenda Validation
            var trainingAgenda = document.getElementById('training_agenda').value.trim();
            displayError('agenda-error-long', trainingAgenda.length > 2000);

            // 14. Training Success Validation
            var trainingSuccess = document.getElementById('training_success').value.trim();
            displayError('success-error-long', trainingSuccess.length > 2000);
            displayError('success-error-invalid', hasInvalidChars(trainingSuccess));

            // 15. Training Challenges Validation
            var trainingChallenges = document.getElementById('training_challenges').value.trim();
            displayError('challenges-error-long', trainingChallenges.length > 2000);
            displayError('challenges-error-invalid', hasInvalidChars(trainingChallenges));

            // 16. Lessons Learned Validation
            var trainingLessonsLearned = document.getElementById('training_lessons_learned').value.trim();
            displayError('lessons-error-long', trainingLessonsLearned.length > 2000);
            displayError('lessons-error-invalid', hasInvalidChars(trainingLessonsLearned));

            // If all validations pass, submit the form
            if (isValid) {
                this.submit();
            } else {
                // Scroll to the first error message and center it in the viewport
                var firstError = document.querySelector('.form-field-error[style="display: block;"]');
                if (firstError) {
                    firstError.scrollIntoView({behavior: "smooth", block: "center", inline: "nearest"});
                    // Optionally, find the related input and focus it
                    var relatedInput = firstError.closest('.form-item').querySelector('input, select, textarea');
                    if (relatedInput) {
                        relatedInput.focus();
                    }
                }
            }
        });

        $(function() {
            let debounceTimer;
            $("#location_address").autocomplete({
                source: function(request, response) {
                    $("#loading-spinner").show();
                    clearTimeout(debounceTimer);
                    debounceTimer = setTimeout(() => {
                        $.ajax({
                            url: "https://nominatim.openstreetmap.org/search",
                            dataType: "json",
                            headers: {
                                'User-Agent': 'ecobricks.org'
                            },
                            data: {
                                q: request.term,
                                format: "json"
                            },
                            success: function(data) {
                                $("#loading-spinner").hide();
                                response($.map(data, function(item) {
                                    return {
                                        label: item.display_name,
                                        value: item.display_name,
                                        lat: item.lat,
                                        lon: item.lon
                                    };
                                }));
                            },
                            error: function(xhr, status, error) {
                                $("#loading-spinner").hide();
                                console.error("Autocomplete error:", error);
                                response([]);
                            }
                        });
                    }, 300);
                },
                select: function(event, ui) {
                    $('#lat').val(ui.item.lat);
                    $('#lon').val(ui.item.lon);
                },
                minLength: 3
            });

            $('#submit-form').on('submit', function() {
                // console.log('Location Full:', $('#location_address').val());
                // alert('Location Full: ' + $('#location_address').val());
            });

        });

        // Autocomplete serials of ecobricks entered in form
        $(document).ready(function() {
            var $serialInput = $('#connected_ecobricks');
            var $autocompleteResults = $('#autocomplete-results'); // Ensure this UL exists in your HTML
            var $serialSelect = $('#serial-select'); // Div that contains the autocomplete results

            function performSearch(inputVal) {
                if (inputVal.length >= 4) { // Ensure there are at least 4 characters to start search
                    $.ajax({
                        url: '../get-serials.php',
                        type: 'GET',
                        data: { search: inputVal },
                        success: function(data) {
                            $autocompleteResults.empty();
                            if (data.length) {
                                data.forEach(function(item) {
                                    $autocompleteResults.append($('<li>').text(item.serial_no));
                                });
                                $serialSelect.show(); // Show the suggestions box if there are results
                            } else {
                                $autocompleteResults.append($('<li>').text("No results found"));
                                $serialSelect.hide(); // Hide if no results
                            }
                        }
                    });
                } else {
                    $autocompleteResults.empty();
                    $serialSelect.hide(); // Hide suggestions if less than 4 characters
                }
            }

            $serialInput.on('input', function() {
                var currentValue = $(this).val();
                var lastTerm = currentValue.split(',').pop().trim(); // Get the last term after a comma
                performSearch(lastTerm);
            });

            $autocompleteResults.on('click', 'li', function() {
                var selectedSerial = $(this).text();
                var currentInput = $serialInput.val();
                var lastCommaIndex = currentInput.lastIndexOf(',');

                if (lastCommaIndex === -1) {
                    // This is the first serial number entry
                    $serialInput.val(selectedSerial + ', ');
                } else {
                    // Replace the last term after the last comma with the selected serial number
                    var base = currentInput.substring(0, lastCommaIndex + 1);
                    $serialInput.val(base + ' ' + selectedSerial + ', ');
                }

                $autocompleteResults.empty();
                $serialInput.focus(); // Set focus back to input for further entries
                $serialSelect.hide(); // Hide the autocomplete suggestions box after selection
            });

            // Optionally hide the autocomplete box when the input loses focus
            $serialInput.blur(function() {
                setTimeout(function() { // Timeout to allow click event on suggestions to occur
                    $serialSelect.hide();
                }, 200);
            });
        });

    </script>

    <br><br>
    </div> <!--closes main-->

    <!--FOOTER STARTS HERE-->
    <?php require_once ("../footer-2024.php");?>
    </div>

    </body>
</html>
