<?php

require_once '../earthenAuth_helper.php'; // Authentication helper

// PART 1: Set page variables
$lang = basename(dirname($_SERVER['SCRIPT_NAME']));
$version = '0.54';
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
$user_training_location = getUserFullLocation($buwana_conn, $buwana_id);
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

    $youtube_result_video = trim($_POST['youtube_result_video'] ?? '');
    $moodle_url = trim($_POST['moodle_url'] ?? '');
    $ready_to_show = isset($_POST['ready_to_show']) ? 1 : 0; // Convert checkbox to 0 or 1
    $featured_description = trim($_POST['featured_description'] ?? '');
    $training_success = isset($_POST['training_success']) ? trim($_POST['training_success']) : null;
$training_challenges = isset($_POST['training_challenges']) ? trim($_POST['training_challenges']) : null;
$training_lessons_learned = isset($_POST['training_lessons_learned']) ? trim($_POST['training_lessons_learned']) : null;


    // ✅ Convert `datetime-local` format to MySQL `DATETIME`
$training_date = !empty($_POST['training_date'])
    ? date("Y-m-d H:i:s", strtotime($_POST['training_date']))
    : NULL;

    $no_participants = isset($_POST['no_participants']) && is_numeric($_POST['no_participants'])
    ? (int) $_POST['no_participants']
    : 0; // Default to 0 if empty or not numeric



$community_id = isset($_POST['community_id']) && is_numeric($_POST['community_id']) ? (int)$_POST['community_id'] : NULL;

// Check if community_id exists in communities_tb before inserting/updating
if ($community_id !== NULL) {
    $check_community_sql = "SELECT com_id FROM communities_tb WHERE com_id = ?";
    $stmt_check_community = $gobrik_conn->prepare($check_community_sql);
    $stmt_check_community->bind_param("i", $community_id);
    $stmt_check_community->execute();
    $stmt_check_community->store_result();

    if ($stmt_check_community->num_rows === 0) {
        // Community does not exist, set to NULL
        $community_id = NULL;
    }
    $stmt_check_community->close();
}

$training_type = isset($_POST['training_type']) ? trim($_POST['training_type']) : null;
if (empty($training_type)) {
    die("Error: training_type value is empty or invalid.");
}

$briks_made = isset($_POST['briks_made']) && is_numeric($_POST['briks_made']) ? (int)$_POST['briks_made'] : null;
$avg_brik_weight = isset($_POST['avg_brik_weight']) && is_numeric($_POST['avg_brik_weight']) ? (int)$_POST['avg_brik_weight'] : null;

// Debugging - remove later
if ($briks_made === null) {
    die("Error: briks_made value is missing or invalid.");
}
if ($avg_brik_weight === null) {
    die("Error: avg_brik_weight value is missing or invalid.");
}

$country_id = isset($_POST['country_id']) && is_numeric($_POST['country_id']) ? (int)$_POST['country_id'] : null;

// Debugging (REMOVE LATER)
if ($country_id === null) {
    die("Error: country_id is missing or invalid.");
}


$featured_description = isset($_POST['featured_description']) ? trim($_POST['featured_description']) : null;

// Debugging - remove later
if ($featured_description === null) {
    die("Error: featured_description is missing.");
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
    $stmt->bind_param("ssisisiiiddssssssssssi",
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
    $stmt->bind_param("ssisisiiiddssssssssssi",
        $training_title, $lead_trainer, $country_id, $training_date, $no_participants,
        $training_type, $briks_made, $avg_brik_weight, $latitude, $longitude, $training_location,
        $training_summary, $training_agenda, $training_success, $training_challenges,
        $training_lessons_learned, $youtube_result_video, $moodle_url, $ready_to_show, $featured_description, $community_id
    );
}

// ✅ Execute statement & error checking
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
        <input type="datetime-local" id="training_date" name="training_date"
    value="<?php echo isset($training_date) ? date('Y-m-d\TH:i', strtotime($training_date)) : ''; ?>"
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
    <label for="community_search">Trained Community:</label><br>
    <input type="text" id="community_search" name="community_search"
           placeholder="Start typing..." autocomplete="off"
           value="<?php echo htmlspecialchars($community_name ?? '', ENT_QUOTES, 'UTF-8'); ?>">

    <input type="hidden" id="community_id" name="community_id"
       value="<?php echo isset($community_id) ? htmlspecialchars($community_id, ENT_QUOTES, 'UTF-8') : ''; ?>">

    <div id="community_results" class="autocomplete-results"></div>

    <!-- "Add a new community" text link -->
    <p class="form-caption" data-lang-id="012-community-caption-xx">
        Start typing to see and select a community.
        <a href="#" onclick="openAddCommunityModal(); return false;"
           style="color: #007BFF; text-decoration: underline;">
            Don't see your community? Add it.
        </a>
    </p>
</div>



    <div class="form-item">
    <label for="training_type">What type of training was this?</label><br>
    <select id="training_type" name="training_type" required>
        <option value="" disabled selected>Select training type...</option>

        <?php foreach ($training_types as $type): ?>
            <option value="<?php echo htmlspecialchars($type, ENT_QUOTES, 'UTF-8'); ?>"
                <?php echo (isset($training_type) && $training_type === $type) ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($type, ENT_QUOTES, 'UTF-8'); ?>
            </option>
        <?php endforeach; ?>
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
    <label for="country_id">Country:</label><br>
    <select id="country_id" name="country_id" required>
        <option value="" disabled>Select a country...</option>

        <?php foreach ($countries as $country): ?>
            <option value="<?php echo htmlspecialchars($country['country_id'], ENT_QUOTES, 'UTF-8'); ?>"
                <?php echo (!empty($country_id) && $country_id == $country['country_id']) ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($country['country_name'], ENT_QUOTES, 'UTF-8'); ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>


<div class="form-item">
    <label for="featured_description">Featured Description:</label><br>
    <textarea id="featured_description" name="featured_description"
              placeholder="Write a compelling description for this training..."
              rows="5"><?php echo htmlspecialchars($featured_description ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
    <p class="form-caption">This text is shown on the registration page to describe the training. Basic HTML formatting allowed.</p>
</div>


    <div class="form-item">
        <label for="training_agenda">Training Agenda:</label><br>
        <textarea id="training_agenda" name="training_agenda"><?php echo htmlspecialchars($training_agenda ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
            <p class="form-caption">This text is shown on the registration page to describe the training. Basic HTML formatting allowed.</p>

    </div>


<h4>Training Reporting</h4>

    <div class="form-item">
        <label for="training_summary">Training Summary:</label><br>
        <textarea id="training_summary" name="training_summary" required><?php echo htmlspecialchars($training_summary ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
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

<!--        <div class="form-item">
            <label for="location_address">Training Location:</label><br>
            <input type="text" id="location_address" name="location_address" placeholder="Start typing your town..." required
                value="<?php echo htmlspecialchars($training_location ?? '', ENT_QUOTES, 'UTF-8'); ?>">
        </div>
        <div id="location-error-required" class="form-field-error" data-lang-id="000-field-required-error">This field is required.</div>-->
<div class="form-item">
    <label for="training_location" data-lang-id="015-location">Training Location:</label><br>
    <input type="text" id="training_location" name="training_location" aria-label="Training Location" required
        value="<?php echo htmlspecialchars($training_location ?? '', ENT_QUOTES, 'UTF-8'); ?>">
    <p class="form-caption" data-lang-id="015-location-caption">
        Please provide the general location where the training was conducted.
    </p>

    <div id="location-error-required" class="form-field-error" data-lang-id="000-field-required-error">
        This field is required. For online workshops, specify the country of the lead trainer.
    </div>
</div>


<!-- Moodle URL -->
    <div class="form-item">
        <label for="moodle_url">Moodle Course URL:</label><br>
        <input type="url" id="moodle_url" name="moodle_url"
               value="<?php echo htmlspecialchars($moodle_url ?? '', ENT_QUOTES, 'UTF-8'); ?>"
               placeholder="Enter Moodle Course Link">
    </div>

    <!-- YouTube Result Video -->
    <div class="form-item">
        <label for="youtube_result_video">YouTube Video URL:</label><br>
        <input type="url" id="youtube_result_video" name="youtube_result_video"
               value="<?php echo htmlspecialchars($youtube_result_video ?? '', ENT_QUOTES, 'UTF-8'); ?>"
               placeholder="Enter YouTube Video URL">
    </div>

    <!-- Ready to Show -->
    <div class="form-item">
        <label for="ready_to_show">Publish this training publicly?</label><br>
        <input type="checkbox" id="ready_to_show" name="ready_to_show" value="1"
               <?php echo (isset($ready_to_show) && $ready_to_show) ? 'checked' : ''; ?>>
        <p class="form-caption">Check if this training is ready to be displayed on ecobricks.org.</p>
    </div>


    <input type="hidden" id="lat" name="latitude" value="<?php echo htmlspecialchars($latitude ?? '', ENT_QUOTES, 'UTF-8'); ?>">
    <input type="hidden" id="lon" name="longitude" value="<?php echo htmlspecialchars($longitude ?? '', ENT_QUOTES, 'UTF-8'); ?>">

    <div>
        <input type="submit" value="Next: Upload Photos ➡️">
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
    event.preventDefault(); // Prevent form submission until validation passes
    var isValid = true;

    function displayError(elementId, showError) {
        var errorDiv = document.getElementById(elementId);
        if (errorDiv) {
            errorDiv.style.display = showError ? 'block' : 'none';
            if (showError) isValid = false;
        }
    }

    function hasInvalidChars(value) {
        const invalidChars = /[<>]/; // Only block `<` and `>` to prevent XSS
        return invalidChars.test(value);
    }

    // 1. Training Title
    var trainingTitle = document.getElementById('training_title').value.trim();
    displayError('title-error-required', trainingTitle === '');
    displayError('title-error-long', trainingTitle.length > 50);
    displayError('title-error-invalid', hasInvalidChars(trainingTitle));

    // 2. Training Date
    var trainingDate = document.getElementById('training_date').value.trim();
    displayError('date-error-required', trainingDate === '');

    // 3. Number of Participants
    var noParticipants = parseInt(document.getElementById('no_participants').value, 10);
    displayError('participants-error-range', isNaN(noParticipants) || noParticipants < 1 || noParticipants > 5000);

    // 4. Lead Trainer
    var leadTrainer = document.getElementById('lead_trainer').value.trim();
    displayError('trainer-error-required', leadTrainer === '');

    // 5. Training Type (Fix)
    var trainingType = document.getElementById('training_type').value;
    displayError('type-error-required', trainingType === "" || trainingType === "disabled");

    // 6. Bricks Made
    var briksMade = parseInt(document.getElementById('briks_made').value, 10);
    displayError('briks-error-range', isNaN(briksMade) || briksMade < 1 || briksMade > 5000);

    // 7. Average Weight
    var estimatedWeight = parseInt(document.getElementById('avg_brik_weight').value, 10);
    displayError('weight-error-range', isNaN(estimatedWeight) || estimatedWeight < 100 || estimatedWeight > 2000);

    // 8. Training Country
    var trainingCountry = document.getElementById('country_id').value.trim();
    displayError('country-error-required', trainingCountry === '');

    // 9. Training Summary (Fix)
    var trainingSummary = document.getElementById('training_summary').value.trim();
    displayError('summary-error-long', trainingSummary.length > 2000);
    displayError('summary-error-invalid', hasInvalidChars(trainingSummary));

    // 10. Training Agenda
    var trainingAgenda = document.getElementById('training_agenda').value.trim();
    displayError('agenda-error-long', trainingAgenda.length > 2000);

    // 11. Training Success
    var trainingSuccess = document.getElementById('training_success').value.trim();
    displayError('success-error-long', trainingSuccess.length > 2000);
    displayError('success-error-invalid', hasInvalidChars(trainingSuccess));

    // 12. Training Challenges
    var trainingChallenges = document.getElementById('training_challenges').value.trim();
    displayError('challenges-error-long', trainingChallenges.length > 2000);
    displayError('challenges-error-invalid', hasInvalidChars(trainingChallenges));

    // 13. Lessons Learned
    var trainingLessonsLearned = document.getElementById('training_lessons_learned').value.trim();
    displayError('lessons-error-long', trainingLessonsLearned.length > 2000);
    displayError('lessons-error-invalid', hasInvalidChars(trainingLessonsLearned));

    // ✅ Scroll to First Error
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
