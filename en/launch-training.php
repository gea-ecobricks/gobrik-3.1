<?php

require_once '../earthenAuth_helper.php'; // Authentication helper

// PART 1: Set page variables
$lang = basename(dirname($_SERVER['SCRIPT_NAME']));
$version = '0.63';
$page = 'launch-training';
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
$sql_languages = "SELECT language_id, language_name_en, languages_native_name FROM languages_tb ORDER BY language_name_en ASC";
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
$training_language = 'en';
$zoom_link = '';
$zoom_link_full = '';
$feature_photo1_main = '';
$feature_photo2_main = '';
$registration_scope = '';
$trainer_contact_email = '';

// ✅ If editi   ng, fetch existing training details
if ($editing) {
    $sql_fetch = "SELECT training_title, lead_trainer, country_id, training_date, no_participants,
                  training_type, training_language, briks_made, avg_brik_weight, location_lat, location_long, training_location,
                  training_summary, training_agenda, training_success, training_challenges, training_lessons_learned,
                  youtube_result_video, moodle_url, ready_to_show, featured_description, community_id,
                  zoom_link, zoom_link_full, feature_photo1_main, feature_photo2_main, registration_scope, trainer_contact_email
                  FROM tb_trainings WHERE training_id = ?";

    $stmt_fetch = $gobrik_conn->prepare($sql_fetch);
    $stmt_fetch->bind_param("i", $training_id);
    $stmt_fetch->execute();
    $stmt_fetch->bind_result($training_title, $lead_trainer, $country_id, $training_date, $no_participants,
                            $training_type, $training_language, $briks_made, $avg_brik_weight, $latitude, $longitude, $training_location,
                            $training_summary, $training_agenda, $training_success, $training_challenges,
                            $training_lessons_learned, $youtube_result_video, $moodle_url, $ready_to_show, $featured_description, $community_id,
                            $zoom_link, $zoom_link_full, $feature_photo1_main, $feature_photo2_main, $registration_scope, $trainer_contact_email);
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

// ✅ Fetch Trainer Options
$trainers_list = [];
$result_trainers = $gobrik_conn->query("SELECT ecobricker_id, full_name FROM tb_ecobrickers WHERE gea_status LIKE '%trainer%' ORDER BY full_name ASC");
if ($result_trainers) {
    while ($row = $result_trainers->fetch_assoc()) {
        $trainers_list[] = $row;
    }
}

$selected_trainers = [];
if ($editing) {
    $stmt_tr = $gobrik_conn->prepare("SELECT ecobricker_id FROM tb_training_trainers WHERE training_id = ?");
    $stmt_tr->bind_param("i", $training_id);
    $stmt_tr->execute();
    $res_tr = $stmt_tr->get_result();
    while ($row = $res_tr->fetch_assoc()) {
        $selected_trainers[] = $row['ecobricker_id'];
    }
    $stmt_tr->close();
}

// Build array with selected trainer details for pre-population
$selected_trainers_data = [];
foreach ($trainers_list as $trainer) {
    if (in_array($trainer['ecobricker_id'], $selected_trainers)) {
        $selected_trainers_data[] = $trainer;
    }
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



<!--PART 5 TOP DECORATION-->
    <?php require_once ("../includes/launch-training-inc.php");?>

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
                    <div class="splash-heading" data-lang-id="001-splash-title-post">Launch a GEA Training</div>
                </div>
                <div class="splash-image" data-lang-id="003-splash-image-alt">
                    <img src="../svgs/shanti.svg" style="width:65%" alt="GEA trainer in action: File a GEA training report">
                </div>
            </div>

            <div class="lead-page-paragraph">
                <p data-lang-id="004-form-description-post">Use this form to launch a training, workshop or community event on GoBrik.</p>
            </div>


 <!-- PART 6 THE FORM -->
<form id="submit-form" method="post" action="launch-training_process.php" enctype="multipart/form-data" novalidate>

    <div class="form-item" style="margin-top: 25px;">
        <label for="training_title" data-lang-id="005-title-title">What is the title of your training?</label><br>
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
        <label for="trainer_search" data-lang-id="005b-title-trainers">Who are the trainers leading this training?</label><br>
        <input type="text" id="trainer_search" placeholder="Type to search..." autocomplete="off" class="form-field-style">
        <div id="trainer_results" class="autocomplete-results"></div>
        <div id="selected_trainers" class="trainer-tag-container">
            <?php foreach ($selected_trainers_data as $trainer): ?>
                <div class="trainer-tag-box" data-id="<?php echo $trainer['ecobricker_id']; ?>">
                    <span class="remove-trainer">&times;</span>
                    <span><?php echo htmlspecialchars($trainer['full_name'], ENT_QUOTES, 'UTF-8'); ?></span>
                    <input type="hidden" name="trainers[]" value="<?php echo $trainer['ecobricker_id']; ?>" id="trainer-hidden-<?php echo $trainer['ecobricker_id']; ?>">
                </div>
            <?php endforeach; ?>
        </div>
        <p class="form-caption" data-lang-id="005b-trainers-caption">Select the trainers leading this training.</p>
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
        <label for="training_language" data-lang-id="006b-title-language">What language will this training be in?</label><br>
        <select id="training_language" name="training_language" class="form-field-style">
            <?php foreach ($languages as $language): ?>
                <option value="<?php echo htmlspecialchars($language['language_id'], ENT_QUOTES, 'UTF-8'); ?>"
                    <?php echo ($training_language === $language['language_id']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($language['language_name_en'], ENT_QUOTES, 'UTF-8'); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <p class="form-caption" data-lang-id="006b-language-caption">What language will this training be in?</p>
    </div>

    <div class="form-item">
        <label for="no_participants" data-lang-id="007-title-participants">What is the maximum amount of participants for this training?</label><br>
        <input type="number" id="no_participants" name="no_participants" min="1" max="5000" 
            value="<?php echo htmlspecialchars($no_participants ?? '', ENT_QUOTES, 'UTF-8'); ?>">
            <p class="form-caption" data-lang-id="007-training-count">How many people participated (including trainers)?</p>
            <!--ERRORS-->
                    <div id="participants-error-required" class="form-field-error" data-lang-id="000-field-required-error">This field is .</div>
                    <div id="participants-error-range" class="form-field-error" data-lang-id="000-field-participants-number-error">A number (between 1 and 5000).</div>
             </div>

    


<div class="form-item">
    <label for="community_search" data-lang-id="009-title-community">What community is this training targeting?</label><br>
    <input type="text" id="community_search" name="community_search"
           placeholder="Start typing..." autocomplete="off"
           value="<?php echo htmlspecialchars($community_name ?? '', ENT_QUOTES, 'UTF-8'); ?>">

    <input type="hidden" id="community_id" name="community_id"
       value="<?php echo isset($community_id) ? htmlspecialchars($community_id, ENT_QUOTES, 'UTF-8') : ''; ?>">

    <div id="community_results" class="autocomplete-results"></div>

    <p class="form-caption">For general course select the Global Ecobrick Movement community.</p>

<div id="community-error-required" class="form-field-error" data-lang-id="000-field-too-long-error">A community must be selected</div>
                </div>



    <div class="form-item">
    <label for="training_type" data-lang-id="010-title-type">What type of training will this be?</label><br>
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


  <input type="hidden" id="briks_made" name="briks_made" value="<?php echo isset($briks_made) ? htmlspecialchars($briks_made, ENT_QUOTES, 'UTF-8') : 0; ?>">
  <input type="hidden" id="avg_brik_weight" name="avg_brik_weight" value="<?php echo isset($avg_brik_weight) ? htmlspecialchars($avg_brik_weight, ENT_QUOTES, 'UTF-8') : 0; ?>">

<div class="form-item">
    <label for="country_id" data-lang-id="013-title-country">What country is this course targeting?</label><br>
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
        For global course, leave blank or select "Earth"
    </p>
<!--ERRORS-->
                    <div id="country-error-required" class="form-field-error" data-lang-id="000-field-required-error">This field is .</div>
                </div>



<div class="form-item">
    <label for="featured_description" data-lang-id="014-feature-description">Featured Description:</label><br>
    <textarea id="featured_description" name="featured_description"
              placeholder="Write a compelling description for this training..."
              rows="5"><?php echo htmlspecialchars($featured_description ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
    <p class="form-caption" data-lang-id="014-training-description">This text is shown on the registration page to describe the training. Basic HTML formatting allowed.</p>
<!--ERRORS-->
                    <div id="featured-error-required" class="form-field-error" data-lang-id="000-field-required-error">This field is .</div>
                    <div id="featured-error-long" class="form-field-error" data-lang-id="000-field-summary-too-long-error">Your training summary is too long. Max 255 characters.</div>
                    <div id="featured-error-invalid" class="form-field-error" data-lang-id="005b-training-summary-error">Your entry contains invalid characters. Avoid quotes, slashes, and greater-than signs.</div>
                </div>


    <div class="form-item">
        <label for="training_agenda" data-lang-id="015-title-training-agenda">Training Agenda:</label><br>
        <textarea id="training_agenda" name="training_agenda"><?php echo htmlspecialchars($training_agenda ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
            <p class="form-caption" data-lang-id="015-training-agenda">Optional: Please layout the agenda that your training followed. Max 1000 words. You may not need to update this field as it was shown on the registration page to describe the training. Basic HTML formatting allowed.</p>
<!--ERRORS-->
                    <div id="agenda-error-long" class="form-field-error" data-lang-id="000-long-field-too-long-error">Your training agenda is too long. Maximum 2000 characters.</div>
                </div>

<br><br>
<hr>
<br>

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

    <input type="hidden" id="youtube_result_video" name="youtube_result_video" value="<?php echo htmlspecialchars($youtube_result_video ?? '', ENT_QUOTES, 'UTF-8'); ?>">

    <div class="form-item">
        <label for="zoom_link">Zoom Link:</label><br>
        <input type="url" id="zoom_link" name="zoom_link" class="form-field-style" value="<?php echo htmlspecialchars($zoom_link ?? '', ENT_QUOTES, 'UTF-8'); ?>">
        <p class="form-caption">Set the Zoom link if this is an online course with just the URL for the event.</p>
    </div>

    <div class="form-item">
        <label for="zoom_link_full">Zoom Link full:</label><br>
        <textarea id="zoom_link_full" name="zoom_link_full" class="form-field-style"><?php echo htmlspecialchars($zoom_link_full ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
        <p class="form-caption">Paste the full Zoom link with as much accompagning text as you think appropriate.</p>
    </div>

    <div class="form-item">
        <label for="feature_photo1_main">Set feature photo</label><br>
        <input type="file" id="feature_photo1_main" name="feature_photo1_main" class="form-field-style">
        <p class="form-caption">This is the image that will be use to list your photo on GoBrik.</p>
    </div>

    <div class="form-item">
        <label for="feature_photo2_main">Set a secondary training photo</label><br>
        <input type="file" id="feature_photo2_main" name="feature_photo2_main" class="form-field-style">
        <p class="form-caption">This image will be visible in the training registration page.</p>
    </div>

    <div class="form-item">
        <label for="registration_scope">Registration scope</label><br>
        <select id="registration_scope" name="registration_scope" class="form-field-style">
            <option value="trainers" <?php echo (isset($registration_scope) && $registration_scope=='trainers') ? 'selected' : ''; ?>>Trainers only</option>
            <option value="ecobrickers" <?php echo (isset($registration_scope) && $registration_scope=='ecobrickers') ? 'selected' : ''; ?>>Ecobrickers (All GoBrikers)</option>
            <option value="anyone" <?php echo (isset($registration_scope) && $registration_scope=='anyone') ? 'selected' : ''; ?>>Anyone (all Earthen subscribers)</option>
        </select>
        <p class="form-caption">select the target audience for your course.</p>
    </div>

    <div class="form-item">
        <label for="trainer_contact_email">Trainer contact email</label><br>
        <input type="email" id="trainer_contact_email" name="trainer_contact_email" class="form-field-style" value="<?php echo htmlspecialchars($trainer_contact_email ?? '', ENT_QUOTES, 'UTF-8'); ?>">
        <p class="form-caption">Set the contact email for this course that folks can reply to.</p>
    </div>

    <!-- Ready to Show -->
    <div class="form-item">

        <input type="checkbox" id="ready_to_show" name="ready_to_show" value="1"
               <?php echo (isset($ready_to_show) && $ready_to_show) ? 'checked' : ''; ?>>
               <label for="ready_to_show" data-lang-id="024-title-show">🚀 Publish this training publicly?</label><br>
        <p class="form-caption" data-lang-id="022-training-show">Is this training ready to be displayed on ecobricks.org?  If so, we'll post the completed workshop for to the live feed of GEA trainings.  Don't worry you can always come back here to edit the live listing!</p>
    </div>
<input type="hidden" id="training_id" name="training_id" value="<?php echo htmlspecialchars($training_id ?? '', ENT_QUOTES, 'UTF-8'); ?>">


<!--     <input type="hidden" id="lat" name="latitude" value="<?php echo htmlspecialchars($latitude ?? '', ENT_QUOTES, 'UTF-8'); ?>"> -->
<!--     <input type="hidden" id="lon" name="longitude" value="<?php echo htmlspecialchars($longitude ?? '', ENT_QUOTES, 'UTF-8'); ?>"> -->

<div>
    <input type="submit" value="Save Training" data-lang-id="100-submit-report-1">
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
var preselectedTrainers = <?php echo json_encode($selected_trainers_data); ?>;

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

    // Trainer search autocomplete
    const trainerInput = document.getElementById("trainer_search");
    const trainerResults = document.getElementById("trainer_results");
    const trainerContainer = document.getElementById("selected_trainers");

    function fetchTrainers(query) {
        if (query.length >= 3) {
            fetch(`../api/search_trainers.php?query=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(data => {
                    trainerResults.innerHTML = "";
                    if (data.length === 0) {
                        trainerResults.innerHTML = "<div class='autocomplete-item' style='color: gray;'>No results found</div>";
                    } else {
                        data.forEach(trainer => {
                            let div = document.createElement("div");
                            div.textContent = trainer.full_name;
                            div.dataset.id = trainer.ecobricker_id;
                            div.classList.add("autocomplete-item");
                            div.addEventListener("mousedown", function(event) {
                                event.preventDefault();
                                addTrainer(trainer.ecobricker_id, trainer.full_name);
                                trainerInput.value = "";
                                trainerResults.innerHTML = "";
                            });
                            trainerResults.appendChild(div);
                        });
                    }
                });
        } else {
            trainerResults.innerHTML = "";
        }
    }

    function addTrainer(id, name) {
        if (document.getElementById('trainer-hidden-' + id)) return;
        let box = document.createElement('div');
        box.className = 'trainer-tag-box';
        box.dataset.id = id;

        let remove = document.createElement('span');
        remove.className = 'remove-trainer';
        remove.textContent = '\u00D7';
        remove.addEventListener('click', function() {
            box.remove();
        });

        let text = document.createElement('span');
        text.textContent = name;

        let hidden = document.createElement('input');
        hidden.type = 'hidden';
        hidden.name = 'trainers[]';
        hidden.value = id;
        hidden.id = 'trainer-hidden-' + id;

        box.appendChild(remove);
        box.appendChild(text);
        box.appendChild(hidden);
        trainerContainer.appendChild(box);
    }

    trainerInput.addEventListener('input', function() {
        fetchTrainers(trainerInput.value.trim());
    });

    document.addEventListener('click', function(event) {
        if (!trainerInput.contains(event.target) && !trainerResults.contains(event.target)) {
            trainerResults.innerHTML = '';
        }
    });

    if (preselectedTrainers && Array.isArray(preselectedTrainers)) {
        preselectedTrainers.forEach(tr => addTrainer(tr.ecobricker_id, tr.full_name));
    }
});





document.getElementById('submit-form').addEventListener('submit', function(event) {
    event.preventDefault(); // Prevent default form submission
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
        const invalidChars = /[<>]/; // Prevents only dangerous characters
        return invalidChars.test(value);
    }

    // 🔹 1. Training Title (Max 500 chars)
    var trainingTitle = document.getElementById('training_title').value.trim();
//     displayError('title-error-required', trainingTitle === '');
    displayError('title-error-long', trainingTitle.length > 500);
    displayError('title-error-invalid', hasInvalidChars(trainingTitle));

    // 🔹 2. Training Date (Required)
    var trainingDate = document.getElementById('training_date').value.trim();
//     displayError('date-error-required', trainingDate === '');

    // 🔹 3. Number of Participants (Required, 1 - 5000)
    var noParticipants = parseInt(document.getElementById('no_participants').value, 10);
    if (isNaN(noParticipants) || noParticipants === '') {
//         displayError('participants-error-required', true);
        displayError('participants-error-range', false);
    } else if (noParticipants < 1 || noParticipants > 5000) {
//         displayError('participants-error-required', false);
        displayError('participants-error-range', true);
    } else {
//         displayError('participants-error-required', false);
        displayError('participants-error-range', false);
    }



    // 🔹 5. Training Community (Required)
    var communityId = document.getElementById('community_id').value.trim();
//     displayError('community-error-required', communityId === '');

    // 🔹 6. Training Type (Required)
    var trainingType = document.getElementById('training_type').value;
//     displayError('type-error-required', trainingType === "");



    // 🔹 9. Training Country (Required)
    var trainingCountry = document.getElementById('country_id').value.trim();
    // 🔹 14. Training Location (Required)
    var trainingLocation = document.getElementById('training_location').value.trim();
//     displayError('location-error-required', trainingLocation === '');

    // 🔹 15. Featured Description (Optional, Max 255 chars)
    var featuredDescription = document.getElementById('featured_description').value.trim();
    displayError('featured-error-long', featuredDescription.length > 2000);

    // ✅ Scroll to First Error if any
    if (!isValid) {
        var firstError = document.querySelector('.form-field-error:not([style*="display: none"])');
        if (firstError) {
            firstError.scrollIntoView({behavior: "smooth", block: "center"});
            var relatedInput = firstError.closest('.form-item').querySelector('input, select, textarea');
            if (relatedInput) relatedInput.focus();
        }
        return; // Stop execution if validation fails
    }

    // ✅ Proceed with AJAX Submission
// ✅ Prepare Form Data for Submission
    var formData = new FormData(this);
    var submitButton = document.querySelector('input[type="submit"]');
    var originalButtonText = submitButton.value;
    submitButton.value = "Processing...";
    submitButton.disabled = true;

    fetch(this.action, {
        method: "POST",
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            var newTrainingId = data.training_id || trainingId; // Use returned ID or existing one
            showTrainingSavedModal(newTrainingId);
            submitButton.value = originalButtonText;
            submitButton.disabled = false;
        } else {
            alert("Error: " + (data.error || "An unknown error occurred."));
            submitButton.value = originalButtonText;
            submitButton.disabled = false;
        }
    })
    .catch(error => {
        console.error("Submission error:", error);
        alert("There was a problem submitting the form.");
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


function showTrainingSavedModal(trainingId) {
    const modal = document.getElementById('form-modal-message');
    const modalBox = document.getElementById('modal-content-box');

    modal.style.display = 'flex';
    modalBox.style.flexFlow = 'column';
    document.getElementById('page-content')?.classList.add('blurred');
    document.getElementById('footer-full')?.classList.add('blurred');
    document.body.classList.add('modal-open');

    modalBox.innerHTML = `
        <h1>Training saved!</h1>
        <p>You can view the course listing or keeping editing the page</p>
        <div style="text-align:center;width:100%;margin:auto;margin-top:10px;">
            <a href="launch-training.php?training_id=${trainingId}" class="confirm-button enabled" style="margin:10px;">Keep Editing</a>
            <a href="register.php?training_id=${trainingId}" class="confirm-button enabled" style="margin:10px;">View Listing</a>
        </div>
    `;
}





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
