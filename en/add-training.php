<?php

require_once '../earthenAuth_helper.php'; // Authentication helper

// PART 1: Set page variables
$lang = basename(dirname($_SERVER['SCRIPT_NAME']));
$version = '0.63';
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

// Fetch all languages
$languages = [];
$sql_languages = "SELECT language_id, languages_native_name FROM languages_tb ORDER BY languages_native_name ASC";
$result_languages = $buwana_conn->query($sql_languages);

if ($result_languages && $result_languages->num_rows > 0) {
    while ($row = $result_languages->fetch_assoc()) {
        $languages[] = $row;
    }
}

$buwana_conn->close(); // Close the database connection

require_once '../gobrikconn_env.php';

// ✅ Get `training_id` from URL (for editing existing report)
$training_id = isset($_GET['training_id']) ? intval($_GET['training_id']) : 0;
$editing = ($training_id > 0);

// ✅ Fetch existing training details if editing
if ($editing) {
    $sql_fetch = "SELECT training_title, lead_trainer, country_id, training_date, no_participants,
                  training_type, briks_made, avg_brik_weight, location_lat, location_long, training_location,
                  training_summary, training_agenda, training_success, training_challenges, training_lessons_learned,
                  youtube_result_video, moodle_url, ready_to_show, featured_description, community_id
                  FROM tb_trainings WHERE training_id = ?";

    $stmt_fetch = $gobrik_conn->prepare($sql_fetch);
    $stmt_fetch->bind_param("i", $training_id);
    $stmt_fetch->execute();
    $stmt_fetch->bind_result($training_title, $lead_trainer, $country_id, $training_date, $no_participants,
                            $training_type, $briks_made, $avg_brik_weight, $latitude, $longitude, $training_location,
                            $training_summary, $training_agenda, $training_success, $training_challenges,
                            $training_lessons_learned, $youtube_result_video, $moodle_url, $ready_to_show, $featured_description, $community_id);
    $stmt_fetch->fetch();
    $stmt_fetch->close();
}

// Fetch unique training types from the database
$training_types = [];
$query = "SELECT DISTINCT training_type FROM tb_trainings ORDER BY training_type ASC";
$result = $gobrik_conn->query($query);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $training_types[] = $row['training_type'];
    }
}

// Fetch list of countries
$countries = [];
$query = "SELECT country_id, country_name FROM countries_tb ORDER BY country_name ASC";
$result = $gobrik_conn->query($query);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $countries[] = $row;
    }
}

$community_name = ''; // Default empty
if (!empty($community_id)) {
    $sql_get_community = "SELECT com_name FROM communities_tb WHERE com_id = ?";
    $stmt_get_community = $gobrik_conn->prepare($sql_get_community);
    $stmt_get_community->bind_param("i", $community_id);
    $stmt_get_community->execute();
    $stmt_get_community->bind_result($community_name);
    $stmt_get_community->fetch();
    $stmt_get_community->close();
}


// Fetch list of communities
$communities = [];
$sql = "SELECT com_id, com_name FROM communities_tb ORDER BY com_name ASC";
$result = $gobrik_conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $communities[] = $row;
}
// ✅ If form is submitted, insert/update the training report
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    include '../scripts/photo-functions.php';

    // ✅ Capture form data safely
    $training_title = trim($_POST['training_title'] ?? '');
    $lead_trainer = trim($_POST['lead_trainer'] ?? '');
    $training_date = trim($_POST['training_date'] ?? '');

// Ensure training_date is not empty
if (!empty($training_date)) {
    // If only a date (YYYY-MM-DD) is provided, append default time "12:00"
    if (strlen($training_date) == 10) { // Example: "2025-03-11"
        $training_date .= "T12:00"; // Append default time
    }

    // Convert to MySQL DATETIME format
    $training_date = date("Y-m-d H:i:s", strtotime($training_date));
} else {
    die("Error: Training date is required."); // Debugging - Remove in production
}


    $youtube_result_video = trim($_POST['youtube_result_video'] ?? '');
    $moodle_url = trim($_POST['moodle_url'] ?? '');
    $ready_to_show = isset($_POST['ready_to_show']) ? 1 : 0;
    $featured_description = trim($_POST['featured_description'] ?? '');
    $training_summary = trim($_POST['training_summary'] ?? '');
    $training_agenda = trim($_POST['training_agenda'] ?? '');
    $training_success = trim($_POST['training_success'] ?? '');
    $training_challenges = trim($_POST['training_challenges'] ?? '');
    $training_lessons_learned = trim($_POST['training_lessons_learned'] ?? '');
    $training_location = trim($_POST['training_location'] ?? '');

    // ✅ Convert datetime-local format
    $training_date = !empty($_POST['training_date']) ? date("Y-m-d H:i:s", strtotime($_POST['training_date'])) : null;

    // ✅ Convert numeric fields safely
    $no_participants = isset($_POST['no_participants']) && is_numeric($_POST['no_participants']) ? (int) $_POST['no_participants'] : 0;
    $briks_made = isset($_POST['briks_made']) && is_numeric($_POST['briks_made']) ? (int) $_POST['briks_made'] : null;
    $avg_brik_weight = isset($_POST['avg_brik_weight']) && is_numeric($_POST['avg_brik_weight']) ? (int) $_POST['avg_brik_weight'] : null;
    $country_id = isset($_POST['country_id']) && is_numeric($_POST['country_id']) ? (int) $_POST['country_id'] : null;
    $training_type = trim($_POST['training_type'] ?? '');

    // ✅ Validate `community_id`
    $community_id = isset($_POST['community_id']) && is_numeric($_POST['community_id']) ? (int)$_POST['community_id'] : null;
    if ($community_id !== null) {
        $stmt_check_community = $gobrik_conn->prepare("SELECT com_id FROM communities_tb WHERE com_id = ?");
        $stmt_check_community->bind_param("i", $community_id);
        $stmt_check_community->execute();
        $stmt_check_community->store_result();
        if ($stmt_check_community->num_rows === 0) {
            $community_id = null;
        }
        $stmt_check_community->close();
    }


if ($editing) {
    // ✅ UPDATE existing training report
    $sql = "UPDATE tb_trainings SET
        training_title=?, lead_trainer=?, country_id=?, training_date=?,
        no_participants=?, training_type=?, briks_made=?, avg_brik_weight=?,
        location_lat=?, location_long=?, training_location=?, training_summary=?, training_agenda=?,
        training_success=?, training_challenges=?, training_lessons_learned=?,
        youtube_result_video=?, moodle_url=?, ready_to_show=?, featured_description=?, community_id=?
        WHERE training_id=?";

    $stmt = $gobrik_conn->prepare($sql);
    $stmt->bind_param("ssisisiiddssssssssisii",
        $training_title, $lead_trainer, $country_id, $training_date, $no_participants,
        $training_type, $briks_made, $avg_brik_weight, $latitude, $longitude, $training_location,
        $training_summary, $training_agenda, $training_success, $training_challenges,
        $training_lessons_learned, $youtube_result_video, $moodle_url, $ready_to_show,
        $featured_description, $community_id, $training_id
    );
} else {
    // ✅ INSERT new training report
    $sql = "INSERT INTO tb_trainings
        (training_title, lead_trainer, country_id, training_date, no_participants,
        training_type, briks_made, avg_brik_weight, location_lat, location_long,
        training_location, training_summary, training_agenda, training_success, training_challenges,
        training_lessons_learned, youtube_result_video, moodle_url, ready_to_show, featured_description, community_id)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $gobrik_conn->prepare($sql);
    $stmt->bind_param("ssisisiiddssssssssisi",
        $training_title, $lead_trainer, $country_id, $training_date, $no_participants,
        $training_type, $briks_made, $avg_brik_weight, $latitude, $longitude, $training_location,
        $training_summary, $training_agenda, $training_success, $training_challenges,
        $training_lessons_learned, $youtube_result_video, $moodle_url, $ready_to_show, $featured_description, $community_id
    );
}

    // ✅ Execute statement & check for errors
    if (!$stmt->execute()) {
        die("Database update failed: " . $stmt->error);
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
<meta property="og:title" content="<meta property="og:title" content="<?php echo !empty($training_title) ? 'Log: ' . $training_title : 'Log your Training Report'; ?>">
">
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
                <div id="language-code" onclick="showLangSelector()" aria-label="Switch languages">🌐<span data-lang-id="000-language-code"> EN</span></div>
            </div>

            <div class="splash-form-content-block">
                <div class="splash-box">
                    <div class="splash-heading" data-lang-id="001-splash-title-post">Post a GEA Training Report</div>
                </div>
                <div class="splash-image" data-lang-id="003-splash-image-alt">
                    <img src="../svgs/shanti.svg" style="width:65%" alt="GEA trainer in action: File a GEA training report">
                </div>
            </div>

            <div class="lead-page-paragraph">
                <p data-lang-id="004-form-description-post">Is your workshop, event or training complete?  Share your social success with the world! Use this form to file and post your completed event or training report. Trainings will be featured on our main page and archived in our trainings database.</p>
            </div>

   <!-- PART 6: THE FORM -->
<form id="submit-form" method="post" action="" enctype="multipart/form-data" novalidate>

    <!-- ✅ Training Title -->
    <div class="form-item" style="margin-top: 25px;">
        <label for="training_title" data-lang-id="005-title-title">Training Title:</label>
        <input type="text" id="training_title" name="training_title"
               value="<?php echo htmlspecialchars($training_title ?? '', ENT_QUOTES, 'UTF-8'); ?>"
               aria-label="Training Title" required>
        <p class="form-caption" data-lang-id="005-training-give-title">
            Give your training a title. This will be how your report is featured.
        </p>

        <!-- ✅ Errors -->
        <div id="title-error-required" class="form-field-error" data-lang-id="000-field-required-error">This field is required.</div>
        <div id="title-error-long" class="form-field-error" data-lang-id="000-title-field-too-long-error">Your training title is too long. Max 50 characters.</div>
        <div id="title-error-invalid" class="form-field-error" data-lang-id="005b-training-title-error">Your entry contains invalid characters. Avoid quotes, slashes, and greater-than signs.</div>
    </div>

    <!-- ✅ Training Date -->
    <div class="form-item">
        <label for="training_date" data-lang-id="006-title-date">Training Date:</label>
        <input type="datetime-local" id="training_date" name="training_date"
               value="<?php echo isset($training_date) ? date('Y-m-d\TH:i', strtotime($training_date)) : date('Y-m-d\T12:00'); ?>"
               aria-label="Training Date" required class="form-field-style">
        <p class="form-caption" data-lang-id="006-training-date">On what date and time did this training run?</p>

        <!-- ✅ Error -->
        <div id="date-error-required" class="form-field-error" data-lang-id="000-field-required-error" style="display: none;">This field is required.</div>
    </div>

    <!-- ✅ Number of Participants -->
    <div class="form-item">
        <label for="no_participants" data-lang-id="007-title-participants">Number of Participants:</label>
        <input type="number" id="no_participants" name="no_participants" min="1" max="5000" required
               value="<?php echo htmlspecialchars($no_participants ?? '', ENT_QUOTES, 'UTF-8'); ?>">
        <p class="form-caption" data-lang-id="007-training-count">How many people participated (including trainers)?</p>

        <!-- ✅ Errors -->
        <div id="participants-error-required" class="form-field-error" data-lang-id="000-field-required-error">This field is required.</div>
        <div id="participants-error-range" class="form-field-error" data-lang-id="000-field-participants-number-error">A number (between 1 and 5000).</div>
    </div>

    <!-- ✅ Lead Trainer -->
    <div class="form-item">
        <label for="lead_trainer" data-lang-id="008-lead-trainer">Lead Trainer:</label>
        <input type="text" id="lead_trainer" name="lead_trainer"
               value="<?php echo htmlspecialchars($lead_trainer ?? '', ENT_QUOTES, 'UTF-8'); ?>"
               aria-label="Lead Trainer" required>
        <p class="form-caption" data-lang-id="008-training-trainers">
            Who led the training? You can write multiple names, e.g., "Lucie Mann and Ani Himawati."
        </p>

        <!-- ✅ Error -->
        <div id="trainer-error-required" class="form-field-error" data-lang-id="000-field-required-error">This field is required.</div>
    </div>

    <!-- ✅ Trained Community -->
    <div class="form-item">
        <label for="community_search" data-lang-id="009-title-community">Trained Community:</label>
        <input type="text" id="community_search" name="community_search"
               placeholder="Start typing..." autocomplete="off"
               value="<?php echo htmlspecialchars($community_name ?? '', ENT_QUOTES, 'UTF-8'); ?>">

        <input type="hidden" id="community_id" name="community_id"
               value="<?php echo isset($community_id) ? htmlspecialchars($community_id, ENT_QUOTES, 'UTF-8') : ''; ?>">

        <div id="community_results" class="autocomplete-results"></div>

        <p class="form-caption" data-lang-id="008-community-trained">
            What community was this training for? Start typing to see and select a GoBrik community.
            <a href="#" onclick="openAddCommunityModal(); return false;" style="color: #007BFF; text-decoration: underline;">
                Don't see your community? Add it.
            </a>
        </p>

        <!-- ✅ Error -->
        <div id="community-error-required" class="form-field-error" data-lang-id="000-field-too-long-error">A community must be selected</div>
    </div>

    <!-- ✅ Training Type -->
    <div class="form-item">
        <label for="training_type" data-lang-id="010-title-type">What type of training was this?</label>
        <select id="training_type" name="training_type" required class="form-field-style">
            <option value="" disabled selected>Select training type...</option>

            <?php foreach ($training_types as $type): ?>
                <option value="<?php echo htmlspecialchars($type, ENT_QUOTES, 'UTF-8'); ?>"
                    <?php echo (isset($training_type) && $training_type === $type) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($type, ENT_QUOTES, 'UTF-8'); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <p class="form-caption" data-lang-id="010-training-type">Please categorize this training.</p>

        <!-- ✅ Error -->
        <div id="type-error-required" class="form-field-error" data-lang-id="000-field-required-error">This field is required.</div>
    </div>

    <!-- ✅ Training Location -->
    <div class="form-item">
        <label for="training_location" data-lang-id="021-title-location">Training Location:</label>
        <input type="text" id="training_location" name="training_location" required
               value="<?php echo htmlspecialchars($training_location ?? '', ENT_QUOTES, 'UTF-8'); ?>">
        <p class="form-caption" data-lang-id="020-location-caption">
            Please provide the general location where the training was conducted.
        </p>

        <!-- ✅ Error -->
        <div id="location-error-required" class="form-field-error" data-lang-id="000-field-required-error">This field is required.</div>
    </div>

    <!-- ✅ Publish Checkbox -->
    <div class="form-item">
        <input type="checkbox" id="ready_to_show" name="ready_to_show" value="1"
               <?php echo (isset($ready_to_show) && $ready_to_show) ? 'checked' : ''; ?>>
        <label for="ready_to_show" data-lang-id="024-title-show">Publish this training publicly?</label>
        <p class="form-caption" data-lang-id="022-training-show">
            Is this training ready to be displayed on ecobricks.org? Don't worry, you can always edit it later!
        </p>
    </div>

    <!-- ✅ Submit Button -->
    <div>
        <input type="submit" value="Next: Upload Photos ➡️" data-lang-id="100-submit-report-1">
    </div>

</form>


        </div>
    </div>


<!--                <div class="form-item">-->
<!--                    <label for="connected_ecobricks">The serials of ecobricks used in your project:</label><br>-->
<!--                    <input type="text" id="connected_ecobricks" name="connected_ecobricks" aria-label="Connected Ecobricks" placeholder="Enter serials...">-->
<!--                    <div id="serial-select"><ul id="autocomplete-results" ></ul></div>-->
<!--                    <p class="form-caption">Optional: Enter the serial numbers of ecobricks connected to this project. Separate multiple serial numbers with commas.</p>-->
<!--                </div>-->




<!-- Load jQuery and Select2 -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>

<!--
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
-->

<script>


document.addEventListener("DOMContentLoaded", function() {
    const communityInput = document.getElementById("community_search");
    const communityIdField = document.getElementById("community_id");
    const resultsDiv = document.getElementById("community_results");

    function fetchCommunities(query) {
        if (query.length >= 3) {
            fetch(`../api/search_communities.php?query=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(data => {
                    resultsDiv.innerHTML = "";
                    if (data.length === 0) {
                        resultsDiv.innerHTML = "<div class='autocomplete-item' style='color: gray;'>No results found</div>";
                    } else {
                        data.forEach(community => {
                            let div = document.createElement("div");
                            div.textContent = community.com_name;
                            div.dataset.id = community.com_id; // Store the ID
                            div.classList.add("autocomplete-item");

                            div.addEventListener("mousedown", function(event) {
                                event.preventDefault();
                                communityInput.value = community.com_name;
                                communityIdField.value = community.com_id; // Ensure correct ID is set
                                resultsDiv.innerHTML = "";
                                console.log("Selected Community ID: ", community.com_id);
                            });

                            resultsDiv.appendChild(div);
                        });
                    }
                });
        } else {
            resultsDiv.innerHTML = "";
        }
    }

    communityInput.addEventListener("input", function() {
        fetchCommunities(communityInput.value.trim());
    });

    document.addEventListener("click", function(event) {
        if (!communityInput.contains(event.target) && !resultsDiv.contains(event.target)) {
            resultsDiv.innerHTML = "";
        }
    });
});





document.getElementById('submit-form').addEventListener('submit', function(event) {
    event.preventDefault(); // Prevent submission until validation passes
    var isValid = true;

    function displayError(elementId, showError) {
        var errorDiv = document.getElementById(elementId);
        if (errorDiv) {
            errorDiv.style.display = showError ? 'block' : 'none';
            if (showError) isValid = false;
        }
    }

    function hasInvalidChars(value) {
        const invalidChars = /[<>]/; // Prevents only dangerous characters
        return invalidChars.test(value);
    }

    // 🔹 1. Training Title (Required, Max 50 chars)
    var trainingTitle = document.getElementById('training_title').value.trim();
    displayError('title-error-required', trainingTitle === '');
    displayError('title-error-long', trainingTitle.length > 50);
    displayError('title-error-invalid', hasInvalidChars(trainingTitle));

    // 🔹 2. Training Date (Required)
    var trainingDate = document.getElementById('training_date').value.trim();
    displayError('date-error-required', trainingDate === '');

    // 🔹 3. Number of Participants (1 - 5000)
    var noParticipants = parseInt(document.getElementById('no_participants').value, 10);
    displayError('participants-error-required', isNaN(noParticipants) || noParticipants < 1 || noParticipants > 5000);

    // 🔹 4. Lead Trainer (Required)
    var leadTrainer = document.getElementById('lead_trainer').value.trim();
    displayError('trainer-error-required', leadTrainer === '');

    // 🔹 5. Training Community (Required)
    var communityId = document.getElementById('community_id').value.trim();
    displayError('community-error-required', communityId === '');

    // 🔹 6. Training Type (Required)
    var trainingType = document.getElementById('training_type').value;
    displayError('type-error-required', trainingType === "");

    // 🔹 7. Briks Made (Min 0)
    var briksMade = parseInt(document.getElementById('briks_made').value, 10);
    displayError('briks-error-required', isNaN(briksMade) || briksMade < 0 || briksMade > 5000);

    // 🔹 8. Average Brik Weight (Min 0)
    var avgBrikWeight = parseInt(document.getElementById('avg_brik_weight').value, 10);
    displayError('weight-error-required', isNaN(avgBrikWeight) || avgBrikWeight < 0 || avgBrikWeight > 2000);

    // 🔹 9. Training Country (Required)
    var trainingCountry = document.getElementById('country_id').value.trim();
    displayError('country-error-required', trainingCountry === '');

    // 🔹 10. Training Summary (Required, Max 2000 chars)
    var trainingSummary = document.getElementById('training_summary').value.trim();
    displayError('summary-error-required', trainingSummary === '');
    displayError('summary-error-long', trainingSummary.length > 2000);
    displayError('summary-error-invalid', hasInvalidChars(trainingSummary));

    // 🔹 11. Training Success (Required, Max 2000 chars)
    var trainingSuccess = document.getElementById('training_success').value.trim();
    displayError('success-error-required', trainingSuccess === '');
    displayError('success-error-long', trainingSuccess.length > 2000);
    displayError('success-error-invalid', hasInvalidChars(trainingSuccess));

    // 🔹 12. Training Challenges (Required, Max 2000 chars)
    var trainingChallenges = document.getElementById('training_challenges').value.trim();
    displayError('challenges-error-required', trainingChallenges === '');
    displayError('challenges-error-long', trainingChallenges.length > 2000);
    displayError('challenges-error-invalid', hasInvalidChars(trainingChallenges));

    // 🔹 13. Lessons Learned (Required, Max 2000 chars)
    var trainingLessons = document.getElementById('training_lessons_learned').value.trim();
    displayError('lessons-error-required', trainingLessons === '');
    displayError('lessons-error-long', trainingLessons.length > 2000);
    displayError('lessons-error-invalid', hasInvalidChars(trainingLessons));

    // 🔹 14. Training Location (Required)
    var trainingLocation = document.getElementById('training_location').value.trim();
    displayError('location-error-required', trainingLocation === '');

    // 🔹 15. Featured Description (Optional, Max 255 chars)
    var featuredDescription = document.getElementById('featured_description').value.trim();
    displayError('featured-error-long', featuredDescription.length > 255);
    displayError('featured-error-invalid', hasInvalidChars(featuredDescription));

    // ✅ Scroll to First Error if any
    if (!isValid) {
        var firstError = document.querySelector('.form-field-error:not([style*="display: none"])');
        if (firstError) {
            firstError.scrollIntoView({behavior: "smooth", block: "center"});
            var relatedInput = firstError.closest('.form-item').querySelector('input, select, textarea');
            if (relatedInput) relatedInput.focus();
        }
    } else {
        this.submit(); // ✅ If valid, submit the form
    }
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







 function openAddCommunityModal() {
    const modal = document.getElementById('form-modal-message');
    const modalBox = document.getElementById('modal-content-box');

    modal.style.display = 'flex';
    modalBox.style.flexFlow = 'column';
    document.getElementById('page-content')?.classList.add('blurred');
    document.getElementById('footer-full')?.classList.add('blurred');
    document.body.classList.add('modal-open');

    modalBox.style.maxHeight = '80vh';
    modalBox.style.overflowY = 'auto';

    modalBox.innerHTML = `
        <h2 style="text-align:center;">Add Your Community</h2>
        <p>Add your community to GoBrik so you can manage local projects and ecobricks.</p>

        <form id="addCommunityForm" onsubmit="addCommunity2Buwana(event)">
            <label for="newCommunityName">Name of Community:</label>
            <input type="text" id="newCommunityName" name="newCommunityName" required>

            <label for="newCommunityType">Type of Community:</label>
            <select id="newCommunityType" name="newCommunityType" required>
                <option value="">Select Type</option>
                <option value="neighborhood">Neighborhood</option>
                <option value="city">City</option>
                <option value="school">School</option>
                <option value="organization">Organization</option>
            </select>

            <label for="communityCountry">Country:</label>
            <select id="communityCountry" name="communityCountry" required>
                <option value="">Select Country</option>
                <?php foreach ($countries as $country) : ?>
                    <option value="<?php echo $country['country_id']; ?>">
                        <?php echo htmlspecialchars($country['country_name'], ENT_QUOTES, 'UTF-8'); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="communityLanguage">Preferred Language:</label>
            <select id="communityLanguage" name="communityLanguage" required>
                <option value="">Select Language</option>
                <?php foreach ($languages as $language) : ?>
                    <option value="<?php echo $language['language_id']; ?>">
                        <?php echo htmlspecialchars($language['languages_native_name'], ENT_QUOTES, 'UTF-8'); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button type="submit" style="margin-top:10px;width:100%;" class="submit-button enabled">Submit</button>
        </form>
    `;
}


function addCommunity2Buwana(event) {
    event.preventDefault(); // Prevent normal form submission

    const form = document.getElementById('addCommunityForm');
    const formData = new FormData(form);

    fetch('../scripts/add_community.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        alert(data.message); // Show success or error message

        if (data.success) {
            // Close modal
            closeInfoModal();

            // Add the new community to the dropdown
            const communityInput = document.getElementById('community_name');
            const communityList = document.getElementById('community_list');

            // Create new option
            const newOption = document.createElement('option');
            newOption.value = data.community_name;
            newOption.textContent = data.community_name;
            communityList.appendChild(newOption);

            // Set selected value
            communityInput.value = data.community_name;
        }
    })
    .catch(error => {
        alert('Error adding community. Please try again.');
        console.error('Error:', error);
    });
}



    </script>

    <br><br>
    </div> <!--closes main-->

    <!--FOOTER STARTS HERE-->
    <?php require_once ("../footer-2024.php");?>
    </div>

    </body>
</html>
