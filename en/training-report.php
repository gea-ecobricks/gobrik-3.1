<?php

require_once '../earthenAuth_helper.php'; // Authentication helper

// PART 1: Set page variables
$lang = basename(dirname($_SERVER['SCRIPT_NAME']));
$version = '0.63';
$page = 'training-report';
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
$earthling_emoji = getUserEarthlingEmoji($buwana_conn, $buwana_id);
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



require_once '../gobrikconn_env.php';

// ✅ Get training_id from URL (for editing)
$training_id = isset($_GET['training_id']) ? intval($_GET['training_id']) : 0;
$editing = ($training_id > 0);

// ✅ If editi   ng, fetch existing training details
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

// ✅ Fetch Unique Training Types
$training_types = [];
$result = $gobrik_conn->query("SELECT DISTINCT training_type FROM tb_trainings ORDER BY training_type ASC");
while ($row = $result->fetch_assoc()) {
    $training_types[] = $row['training_type'];
}

// ✅ Fetch List of Countries
$countries = [];
$result = $gobrik_conn->query("SELECT country_id, country_name FROM countries_tb ORDER BY country_name ASC");
while ($row = $result->fetch_assoc()) {
    $countries[] = $row;
}

// ✅ Fetch Community Name if Exists
$community_name = '';
if (!empty($community_id)) {
    $stmt = $buwana_conn->prepare("SELECT com_name FROM communities_tb WHERE community_id = ?");
    $stmt->bind_param("i", $community_id);
    $stmt->execute();
    $stmt->bind_result($community_name);
    $stmt->fetch();
    $stmt->close();
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
<meta property="og:url" content="https://www.gobrik.com/<?php echo $lang; ?>/training-report.php">
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


    <!-- PAGE CONTENT-->

    <div id="form-submission-box">
        <div class="form-container">

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


 <!-- PART 6 THE FORM -->
<form id="submit-form" method="post" action="add-training_process.php" enctype="multipart/form-data" novalidate>

    <div class="form-item" style="margin-top: 25px;">
        <label for="training_title" data-lang-id="005-title-title">Training Title:</label><br>
        <input type="text" id="training_title" name="training_title"
            value="<?php echo htmlspecialchars($training_title ?? '', ENT_QUOTES, 'UTF-8'); ?>"
            aria-label="Training Title" >
             <p class="form-caption" data-lang-id="005-training-give-title">Give your training a title.  This will be how your report is featured.</p>
 <!--ERRORS-->
                    <div id="title-error-required" class="form-field-error" data-lang-id="000-field-required-error">This field is .</div>
                    <div id="title-error-long" class="form-field-error" data-lang-id="000-title-field-too-long-error">Your training title is too long. Max 500 characters.</div>
                    <div id="title-error-invalid" class="form-field-error" data-lang-id="005b-training-title-error">Your entry contains invalid characters. Avoid quotes, slashes, and greater-than signs please.</div>
        </div>


    <div class="form-item">
    <label for="training_date" datal-lang-id="006-title-date">Training Date:</label><br>
    <input type="datetime-local" id="training_date" name="training_date"
    value="<?php echo isset($training_date) ? date('Y-m-d\TH:i', strtotime($training_date)) : date('Y-m-d\T12:00'); ?>"
    aria-label="Training Date"  class="form-field-style">
    <p class="form-caption" data-lang-id="006-training-date">On what date and time did this training run?</p>
    <div id="date-error-required" class="form-field-error" data-lang-id="000-field-required-error" style="display: hidden;">This field is .</div>

</div>

    <div class="form-item">
        <label for="no_participants" data-lang-id="007-title-participants">Number of Participants:</label><br>
        <input type="number" id="no_participants" name="no_participants" min="1" max="5000" 
            value="<?php echo htmlspecialchars($no_participants ?? '', ENT_QUOTES, 'UTF-8'); ?>">
            <p class="form-caption" data-lang-id="007-training-count">How many people participated (including trainers)?</p>
            <!--ERRORS-->
                    <div id="participants-error-required" class="form-field-error" data-lang-id="000-field-required-error">This field is .</div>
                    <div id="participants-error-range" class="form-field-error" data-lang-id="000-field-participants-number-error">A number (between 1 and 5000).</div>
             </div>

    <div class="form-item">
        <label for="lead_trainer" data-lang-id="008-lead-trainer">Lead Trainer:</label><br>
        <input type="text" id="lead_trainer" name="lead_trainer"
            value="<?php echo htmlspecialchars($lead_trainer ?? '', ENT_QUOTES, 'UTF-8'); ?>"
            aria-label="Lead Trainer" >
            <p class="form-caption" data-lang-id="008-training-trainers">Who lead the training?  You can write multiple names here if you want.  i.e. Lucie Mann and Ani Himawati</p>
             <!--ERRORS-->
                    <div id="trainer-error-required" class="form-field-error" data-lang-id="000-field-required-error">This field is .</div>
                </div>


<div class="form-item">
    <label for="community_search" data-lang-id="009-title-community">Trained Community:</label><br>
    <input type="text" id="community_search" name="community_search"
           placeholder="Start typing..." autocomplete="off"
           value="<?php echo htmlspecialchars($community_name ?? '', ENT_QUOTES, 'UTF-8'); ?>">

    <input type="hidden" id="community_id" name="community_id"
       value="<?php echo isset($community_id) ? htmlspecialchars($community_id, ENT_QUOTES, 'UTF-8') : ''; ?>">

    <div id="community_results" class="autocomplete-results"></div>

    <!-- "Add a new community" text link
    <p class="form-caption" data-lang-id="008-community-trained">
        What community was this training for?  Start typing to see and select a GoBrik community.
         <a href="#" onclick="openAddCommunityModal(); return false;"
             style="color: #007BFF; text-decoration: underline;">
              Don't see your community? Add it.
          </a>
    </p>-->

<div id="community-error-required" class="form-field-error" data-lang-id="000-field-too-long-error">A community must be selected</div>
                </div>



    <div class="form-item">
    <label for="training_type" data-lang-id="010-title-type">What type of training was this?</label><br>
    <select id="training_type" name="training_type"  class="form-field-style">
        <option value="" disabled selected>Select training type...</option>

        <?php foreach ($training_types as $type): ?>
            <option value="<?php echo htmlspecialchars($type, ENT_QUOTES, 'UTF-8'); ?>"
                <?php echo (isset($training_type) && $training_type === $type) ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($type, ENT_QUOTES, 'UTF-8'); ?>
            </option>
        <?php endforeach; ?>
    </select>
   <p class="form-caption" data-lang-id="010-training-type">Please categorize this training.</p>
   <!--ERROR-->
                    <div id="type-error-required" class="form-field-error" data-lang-id="000-field-required-error">This field is .</div>
                </div>


  <div class="form-item">
    <label for="briks_made" data-lang-id="011-title-how-many">How many ecobricks were made?</label><br>
    <input type="number" id="briks_made" name="briks_made" min="0" max="5000" 
        value="<?php echo isset($briks_made) ? htmlspecialchars($briks_made, ENT_QUOTES, 'UTF-8') : 0; ?>">
    <p class="form-caption" data-lang-id="011-how-many-briks">No ecobricks made in this training? Just set at "0" then.</p>
    <!--ERRORS-->
                    <div id="briks-error-required" class="form-field-error" data-lang-id="000-field-required-error">This field is .</div>
                    <div id="briks-error-range" class="form-field-error" data-lang-id="000-field-brik-number-error">Just a number (between 1 and 5000).</div>
                </div>

<div class="form-item">
    <label for="avg_brik_weight" data-lang-id="012-title-average">Average Brik Weight (grams):</label><br>
    <input type="number" id="avg_brik_weight" name="avg_brik_weight" min="0" max="2000" 
        value="<?php echo isset($avg_brik_weight) ? htmlspecialchars($avg_brik_weight, ENT_QUOTES, 'UTF-8') : 0; ?>">
        <p class="form-caption" data-lang-id="012-training-average">No ecobricks made in this training? Just set at "0" then.</p>
         <!--ERRORS-->
                    <div id="weight-error-range" class="form-field-error" data-lang-id="000-field-weight-number-error">Your estimated average brick weight (in grams) must be a number between 100 and 2000.</div>
                </div>

<div class="form-item">
    <label for="country_id" data-lang-id="013-title-country">Country:</label><br>
    <select id="country_id" name="country_id"  class="form-field-style">
        <!-- ✅ Ensures placeholder is selected when no country is set -->
        <option value="" disabled <?php echo empty($country_id) ? 'selected' : ''; ?>>
            Select a country...
        </option>

        <?php foreach ($countries as $country): ?>
            <option value="<?php echo htmlspecialchars($country['country_id'], ENT_QUOTES, 'UTF-8'); ?>"
                <?php echo (!empty($country_id) && $country_id == $country['country_id']) ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($country['country_name'], ENT_QUOTES, 'UTF-8'); ?>
            </option>
        <?php endforeach; ?>
    </select>

    <p class="form-caption" data-lang-id="013-training-country">
        Where was this training run? If it was an online training, select the country of the lead trainer.
    </p>
<!--ERRORS-->
                    <div id="country-error-required" class="form-field-error" data-lang-id="000-field-required-error">This field is .</div>
                </div>




<br><br>
<hr>
<br>
<h4 data-lang-id="016-training-reporting">Training Reporting</h4>
<p data-lang-id="016b-training-reporting-desc">These text fields will be used to compile your report</p>

    <div class="form-item">
        <label for="training_summary" data-lang-id="017-title-summary">Training Summary:</label><br>
        <textarea id="training_summary" name="training_summary" ><?php echo htmlspecialchars($training_summary ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
        <p class="form-caption" data-lang-id="017-training-summary">Provide a summary of the training. Max 150 words. Avoid special characters..</p>
    <!--ERRORS-->
                    <div id="summary-error-required" class="form-field-error" data-lang-id="000-field-required-error">This field is .</div>
                    <div id="summary-error-long" class="form-field-error" data-lang-id="000-field-summary-too-long-error">Your training summary is too long. Max 255 characters.</div>
                    <div id="summary-error-invalid" class="form-field-error" data-lang-id="005b-training-summary-error">Your entry contains invalid characters. Avoid quotes, slashes, and greater-than signs.</div>
                </div>


    <div class="form-item">
        <label for="training_success" data-lang-id="018-title-successes">Training Successes:</label><br>
        <textarea id="training_success" name="training_success" ><?php echo htmlspecialchars($training_success ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
      <p class="form-caption" data-lang-id="018-training-success">Share the successes of the training. Max 500 words. Avoid special characters..</p>
   <!--ERRORS-->
                    <div id="success-error-required" class="form-field-error" data-lang-id="000-field-required-error">This field is .</div>
                    <div id="success-error-long" class="form-field-error" data-lang-id="000-field-success-too-long-error">Your entry is too long. Max 2000 characters.</div>
                    <div id="success-error-invalid" class="form-field-error" data-lang-id="005b-training-success-error">Your entry contains invalid characters. Avoid quotes, slashes, and greater-than signs.</div>
                </div>

    <div class="form-item">
        <label for="training_challenges" data-lang-id="019-title-challenges">Training Challenges:</label><br>
        <textarea id="training_challenges" name="training_challenges" ><?php echo htmlspecialchars($training_challenges ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
              <p class="form-caption" data-lang-id="019-training-challenges">Share the challenges you faced leading your training. Max 500 words. Avoid special characters.</p>

            <!--ERRORS-->
                    <div id="challenges-error-required" class="form-field-error" data-lang-id="000-field-required-error">This field is .</div>
                    <div id="challenges-error-long" class="form-field-error" data-lang-id="000-field-challenges-too-long-error">Your entry is too long. Max 1500 characters.</div>
                    <div id="challenges-error-invalid" class="form-field-error" data-lang-id="005b-training-challenges-error">Your entry contains invalid characters. Avoid quotes, slashes, and greater-than signs.</div>
                </div>


    <div class="form-item">
        <label for="training_lessons_learned" data-lang-id="020-title-lessons">Lessons Learned:</label><br>
        <textarea id="training_lessons_learned" name="training_lessons_learned" ><?php echo htmlspecialchars($training_lessons_learned ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
        <p class="form-caption" data-lang-id="020-training-lessons">Share the lessons learned from leading your training. Max 1000 words. Avoid special characters.</p>
 <!--ERRORS-->
                    <div id="lessons-error-required" class="form-field-error" data-lang-id="000-field-required-error">This field is .</div>
                    <div id="lessons-error-long" class="form-field-error" data-lang-id="000-field-lessons-too-long-error">Your lessons learned are too long. Max 3000 characters.</div>
                    <div id="lessons-error-invalid" class="form-field-error" data-lang-id="005b-training-lessons-error">Your entry contains invalid characters. Avoid quotes, slashes, and greater-than signs.</div>
                </div>

<div class="form-item">
    <label for="training_location" data-lang-id="021-title-location">Training Location:</label><br>
    <input type="text" id="training_location" name="training_location" aria-label="Training Location" 
        value="<?php echo htmlspecialchars($training_location ?? '', ENT_QUOTES, 'UTF-8'); ?>">
    <p class="form-caption" data-lang-id="020-location-caption">
        Please provide the general location where the training was conducted.
    </p>

     <!--ERRORS-->
             <div id="location-error-required" class="form-field-error" data-lang-id="000-field-required-error">This field is .</div>
                </div>


<!-- Moodle URL -->
    <div class="form-item">
        <label for="moodle_url" data-lang-id="022-title-moodle">Moodle Course URL:</label><br>
        <input type="url" id="moodle_url" name="moodle_url"
               value="<?php echo htmlspecialchars($moodle_url ?? '', ENT_QUOTES, 'UTF-8'); ?>"
               placeholder="Enter Moodle Course Link" class="form-field-style">
        <p class="form-caption" data-lang-id="022-moodle-caption">
        Was there a moodle course created for this training on learning.ecobricks.org?  If so, include the URL here.
    </p>

    </div>

    <!-- YouTube Result Video -->
    <div class="form-item">
        <label for="youtube_result_video" data-lang-id="023-title-youtube">YouTube Video URL:</label><br>
        <input type="url" id="youtube_result_video" name="youtube_result_video"
               value="<?php echo htmlspecialchars($youtube_result_video ?? '', ENT_QUOTES, 'UTF-8'); ?>"
               placeholder="Enter YouTube Video URL" class="form-field-style">
         <p class="form-caption" data-lang-id="023-training-youtube">
        Was a Youtube video of this training posted?  If so include the URL here pleas.
    </p>
    </div>

    <!-- ======================= Ready to Show Toggle ======================= -->
    <div class="form-row" style="display:flex;flex-flow:row;background-color:var(--lighter);padding:20px;border:grey 1px solid;border-radius:12px;margin-top:20px;">
        <div id="left-colum" style="width: 100%;">
            <label>🚀 Publish this training publicly?</label>
            <p class="form-caption" style="margin-top:10px;">Display the final training report of this training on ecobricks.org</p>
        </div>

        <div id="right-column" style="width:100px; justify-content:center;">
            <label class="toggle-switch">
            <input type="checkbox" id="ready_to_show" name="ready_to_show" value="1"
                   <?php echo (isset($ready_to_show) && $ready_to_show) ? 'checked' : ''; ?>>
            <span class="slider"></span>
        </div>
        </label>

    </div>
<input type="hidden" id="training_id" name="training_id" value="<?php echo htmlspecialchars($training_id ?? '', ENT_QUOTES, 'UTF-8'); ?>">


<!--     <input type="hidden" id="lat" name="latitude" value="<?php echo htmlspecialchars($latitude ?? '', ENT_QUOTES, 'UTF-8'); ?>"> -->
<!--     <input type="hidden" id="lon" name="longitude" value="<?php echo htmlspecialchars($longitude ?? '', ENT_QUOTES, 'UTF-8'); ?>"> -->
<?php if ($editing): ?>
<div>
    <button type='submit' id='save-progress' class='confirm-button' style='background:grey;width: 100%;margin: 30px 0px -15px 0px;'>💾 Save Progress</button>
</div>
<?php endif; ?>

<div>
    <input type="submit" value="➡ Next: Upload Photos ️" data-lang-id="100-submit-report-1">
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
                            div.dataset.id = community.community_id; // Store the ID
                            div.classList.add("autocomplete-item");

                            div.addEventListener("mousedown", function(event) {
                                event.preventDefault();
                                communityInput.value = community.com_name;
                                communityIdField.value = community.community_id; // Ensure correct ID is set
                                resultsDiv.innerHTML = "";
                                console.log("Selected Community ID: ", community.community_id);
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
    event.preventDefault();
    var redirectToPhotos = !(event.submitter && event.submitter.id === 'save-progress');
    var submitButton = event.submitter || document.querySelector('input[type="submit"]');
    var isValid = true;
    var trainingIdField = document.getElementById('training_id');
    var trainingId = trainingIdField ? trainingIdField.value.trim() : "";

    function displayError(elementId, showError) {
        var errorDiv = document.getElementById(elementId);
        if (errorDiv) {
            errorDiv.style.display = showError ? 'block' : 'none';
            if (showError) isValid = false;
        }
    }

    function hasInvalidChars(value) {
        const invalidChars = /[<>]/;
        return invalidChars.test(value);
    }

    var trainingTitle = document.getElementById('training_title').value.trim();
    displayError('title-error-long', trainingTitle.length > 500);
    displayError('title-error-invalid', hasInvalidChars(trainingTitle));

    var trainingDate = document.getElementById('training_date').value.trim();

    var noParticipants = parseInt(document.getElementById('no_participants').value, 10);
    if (isNaN(noParticipants) || noParticipants === '') {
        displayError('participants-error-range', false);
    } else if (noParticipants < 1 || noParticipants > 5000) {
        displayError('participants-error-range', true);
    } else {
        displayError('participants-error-range', false);
    }

    var leadTrainer = document.getElementById('lead_trainer').value.trim();
    var communityId = document.getElementById('community_id').value.trim();
    var trainingType = document.getElementById('training_type').value;

    var briksMade = parseInt(document.getElementById('briks_made').value, 10);
    var avgBrikWeight = parseInt(document.getElementById('avg_brik_weight').value, 10);

    var trainingCountry = document.getElementById('country_id').value.trim();

    var trainingSummary = document.getElementById('training_summary').value.trim();
    displayError('summary-error-long', trainingSummary.length > 2000);
    displayError('summary-error-invalid', hasInvalidChars(trainingSummary));

    var trainingSuccess = document.getElementById('training_success').value.trim();
    displayError('success-error-long', trainingSuccess.length > 2000);
    displayError('success-error-invalid', hasInvalidChars(trainingSuccess));

    var trainingChallenges = document.getElementById('training_challenges').value.trim();
    displayError('challenges-error-long', trainingChallenges.length > 2000);
    displayError('challenges-error-invalid', hasInvalidChars(trainingChallenges));

    var trainingLessons = document.getElementById('training_lessons_learned').value.trim();
    displayError('lessons-error-long', trainingLessons.length > 2000);
    displayError('lessons-error-invalid', hasInvalidChars(trainingLessons));

    var trainingLocation = document.getElementById('training_location').value.trim();

    if (!isValid) {
        var firstError = document.querySelector('.form-field-error:not([style*="display: none"])');
        if (firstError) {
            firstError.scrollIntoView({behavior: "smooth", block: "center"});
            var relatedInput = firstError.closest('.form-item').querySelector('input, select, textarea');
            if (relatedInput) relatedInput.focus();
        }
        return;
    }

    var formData = new FormData(this);
    var originalButtonText = submitButton.value;
    submitButton.value = 'Processing...';
    submitButton.disabled = true;

    fetch(this.action, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            var newTrainingId = data.training_id || trainingId;
            trainingIdField.value = newTrainingId;
            if (redirectToPhotos) {
                window.location.href = 'add-training-images.php?training_id=' + newTrainingId;
            } else {
                submitButton.value = originalButtonText;
                submitButton.disabled = false;
                alert('Progress saved!');
            }
        } else {
            alert('Error: ' + (data.error || 'An unknown error occurred.'));
            submitButton.value = originalButtonText;
            submitButton.disabled = false;
        }
    })
    .catch(error => {
        console.error('Submission error:', error);
        alert('There was a problem submitting the form.');
        submitButton.value = originalButtonText;
        submitButton.disabled = false;
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
            <input type="text" id="newCommunityName" name="newCommunityName" >

            <label for="newCommunityType">Type of Community:</label>
            <select id="newCommunityType" name="newCommunityType" >
                <option value="">Select Type</option>
                <option value="neighborhood">Neighborhood</option>
                <option value="city">City</option>
                <option value="school">School</option>
                <option value="organization">Organization</option>
            </select>

            <label for="communityCountry">Country:</label>
            <select id="communityCountry" name="communityCountry" >
                <option value="">Select Country</option>
                <?php foreach ($countries as $country) : ?>
                    <option value="<?php echo $country['country_id']; ?>">
                        <?php echo htmlspecialchars($country['country_name'], ENT_QUOTES, 'UTF-8'); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="communityLanguage">Preferred Language:</label>
            <select id="communityLanguage" name="communityLanguage" >
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
    <?php require_once ("../footer-2025.php");?>
    </div>

    </body>
</html>
