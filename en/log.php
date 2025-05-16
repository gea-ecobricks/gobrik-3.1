<?php
require_once '../earthenAuth_helper.php'; // Include the authentication helper functions

// PART 1: Set up page variables
$lang = basename(dirname($_SERVER['SCRIPT_NAME']));
$version = '0.545';
$page = 'log';
$lastModified = date("Y-m-d\TH:i:s\Z", filemtime(__FILE__));

startSecureSession(); // Start a secure session with regeneration to prevent session fixation

// PART 2: Check if user is logged in and session active
if ($is_logged_in) {
    $buwana_id = $_SESSION['buwana_id'];

    // Include database connections
    require_once '../gobrikconn_env.php';
    require_once '../buwanaconn_env.php';

    // Fetch user meta and location data
    $user_continent_icon = getUserContinent($buwana_conn, $buwana_id);
    $earthling_emoji = getUserEarthlingEmoji($buwana_conn, $buwana_id);
    $user_location_watershed = getWatershedName($buwana_conn, $buwana_id);
    $user_location_full = getUserFullLocation($buwana_conn, $buwana_id);
    $gea_status = getGEA_status($buwana_id);
    $user_ecobricker_id = getEcobrickerID($buwana_id);
    $user_community_name = getCommunityName($buwana_conn, $buwana_id);
    $user_community_id = null;
    $ecobrick_unique_id = 0;
    $first_name = getFirstName($buwana_conn, $buwana_id);

    // Fetch location latitude and longitude from users_tb
    $sql_location = "SELECT location_lat, location_long FROM users_tb WHERE buwana_id = ?";
    $stmt_location = $buwana_conn->prepare($sql_location);
    if ($stmt_location) {
        $stmt_location->bind_param("i", $buwana_id);
        $stmt_location->execute();
        $stmt_location->bind_result($user_location_lat, $user_location_long);
        $stmt_location->fetch();
        $stmt_location->close();
    } else {
        error_log("Error fetching location data: " . $buwana_conn->error);
    }

    // Fetch community_id from community name
    if (!empty($user_community_name)) {
        $stmt_com = $buwana_conn->prepare("SELECT community_id FROM communities_tb WHERE com_name = ?");
        if ($stmt_com) {
            $stmt_com->bind_param("s", $user_community_name);
            $stmt_com->execute();
            $stmt_com->bind_result($user_community_id);
            $stmt_com->fetch();
            $stmt_com->close();
        }
    }

    // Check if retry parameter is passed in the URL
    if (isset($_GET['retry']) && is_numeric($_GET['retry'])) {
        $ecobrick_unique_id = (int)$_GET['retry'];
        retryEcobrick($gobrik_conn, $ecobrick_unique_id);
    }

} else {
    header('Location: login.php?redirect=' . urlencode($page)).php;
    exit();
}

// Set flag for missing last name modal
$show_lastname_modal = false;

// Fetch first and last name for display and autofill
$sql_user = "SELECT first_name, last_name FROM users_tb WHERE buwana_id = ?";
$stmt_user = $buwana_conn->prepare($sql_user);
if ($stmt_user) {
    $stmt_user->bind_param("s", $buwana_id);
    $stmt_user->execute();
    $stmt_user->bind_result($first_name, $last_name);
    $stmt_user->fetch();
    $stmt_user->close();

    $log_full_name = $first_name . ' ' . $last_name;
    $show_lastname_modal = empty($last_name);

    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('ecobricker_maker').value = '" . htmlspecialchars($log_full_name, ENT_QUOTES) . "';
        });
    </script>";
} else {
    echo "Error fetching user information: " . $buwana_conn->error;
}

// Close connections
if ($buwana_conn) $buwana_conn->close();
if ($gobrik_conn) $gobrik_conn->close();

require_once ("../includes/log-inc.php");
?>

<div class="splash-title-block"></div>
<div id="splash-bar"></div>

<!-- PAGE CONTENT -->
   <div id="top-page-image" class="log-ecobrick top-page-image" style="height: 30px; margin-top: 140px;"></div>
<div id="form-submission-box" class="landing-page-form" style="height:auto !important">
    <div class="form-container">

    <div id="log-1-banner" class="log-one-ecobrick" style="height:160px;width:100%;margin-top:-20px;"></div>

           <div style="text-align:center;width:100%;margin:auto;">
            <h2 id="log-page-title" data-lang-id="001-log-title">Log an Ecobrick</h2>
            <p data-lang-id="002-log-subheading">Record your ecobrick to the brikchain for projects, posterity and posting!</p>
        </div>
        <div id="defaults-loaded" style="display:none;font-family:'Mulish',sans-serif; font-size:1.1em;color:green; text-align:center;margin-top:15px" data-lang-id="035-your-defaults-loaded">Your Defaults have been loaded. 🫡</div>
            <!--LOG FORM-->

<form id="submit-form" method="post" action="log_process.php" enctype="multipart/form-data" novalidate>

                <div class="form-item" style="margin-top: 25px;">
                    <label for="ecobricker_maker" data-lang-id="005-ecobricker-maker">Who made this ecobrick?</label><br>
                    <input type="text" id="ecobricker_maker" name="ecobricker_maker" aria-label="Ecobricker Maker" title="Required. Max 255 characters." required>
                    <p class="form-caption" data-lang-id="005b-ecobricker-maker-caption">Provide the name of the ecobricker. Avoid special characters.</p>

                    <!--ERRORS-->
                    <div id="maker-error-required" class="form-field-error" data-lang-id="000-field-required-error">This field is required.</div>
                    <div id="maker-error-long" class="form-field-error" data-lang-id="000-maker-field-too-long-error">The name is too long. Max 255 characters.</div>
                    <div id="maker-error-invalid" class="form-field-error" data-lang-id="005b-maker-error">The entry contains invalid characters. Avoid quotes, slashes, and greater-than signs please.</div>
                </div>

                <div class="form-item">
                    <label for="volume_ml" data-lang-id="006-volume-ml">Volume of the Ecobrick (in milliliters):</label><br>
                    <select id="volume_ml" name="volume_ml" aria-label="Volume in Milliliters" required>
                        <option value="" disabled selected data-lang-id="006-select-volume">Select volume...</option>
                        <option value="200">250 ml</option>
                        <option value="250">250 ml</option>
                        <option value="300">300 ml</option>
                        <option value="330">330 ml</option>
                        <option value="350">350 ml</option>
                        <option value="360">360 ml</option>
                        <option value="380">380 ml</option>
                        <option value="400">400 ml</option>
                        <option value="450">450 ml</option>
                        <option value="500">500 ml</option>
                        <option value="525">525 ml</option>
                        <option value="550">550 ml</option>
                        <option value="600">600 ml</option>
                        <option value="650">650 ml</option>
                        <option value="700">700 ml</option>
                        <option value="750">750 ml</option>
                        <option value="800">800 ml</option>
                        <option value="900">900 ml</option>
                        <option value="1000">1000 ml</option>
                        <option value="1100">1100 ml</option>
                        <option value="1250">1250 ml</option>
                        <option value="1350">1350 ml</option>
                        <option value="1500">1500 ml</option>
                        <option value="1750">1750 ml</option>
                        <option value="2000">2000 ml</option>
                        <option value="2250">2250 ml</option>
                        <option value="3000">3000 ml</option>
                        <option value="3100">3100 ml</option>
                        <option value="4000">4000 ml</option>
                        <option value="5000">5000 ml</option>
                        <option value="10000">10000 ml</option>
                    </select>
                    <p class="form-caption" data-lang-id="006-volume-ml-caption">Please provide the volume of the ecobrick in milliliters.</p>

                    <!--ERRORS-->
                    <div id="volume-error-required" class="form-field-error" data-lang-id="000-field-required-error">This field is required.</div>
                </div>

                <div class="form-item">
                    <label for="weight_g" data-lang-id="007-weight-g">Weight of the Ecobrick (in grams):</label><br>
                    <input type="number" id="weight_g" name="weight_g" aria-label="Weight in Grams" min="1" required>
                    <p class="form-caption" data-lang-id="007-weight-g-caption">Please provide the weight of the ecobrick in grams. Round up to the nearest gram.</p>

                    <!--ERRORS-->
                    <div id="weight-error-required" class="form-field-error" data-lang-id="000-field-required-error">This field is required.</div>
                </div>


                <div class="form-item">
                    <label for="brand_name" data-lang-id="007-brand_name">What brand of bottle is used for this ecobrick?</label><br>
                    <input type="text" id="brand_name" name="brand_name" aria-label="Brand of bottle" required>
                    <!--<p class="form-caption" data-lang-id="007-weight-g-caption">Write the name of the bottle brand</p>
                    -->
                    <!--ERRORS-->
                    <div id="brand-name-error-required" class="form-field-error" data-lang-id="000-field-required-error">This field is required.</div>
                    <div id="brand-name-error-long" class="form-field-error" data-lang-id="000-field-too-long-error">This entry should be under 100 characters. All we need is the bottle brand name i.e. "Max Water".</div>
                    <div id="brand-name-error-invalid" class="form-field-error" data-lang-id="000-field-invalid-error">The entry contains invalid characters. Avoid quotes, slashes, and greater-than signs please.</div>
                </div>


                <div class="form-item">
                    <label for="bottom_colour" data-lang-id="008-bottom-color">Bottom color of the Ecobrick:</label><br>
                    <select id="bottom_colour" name="bottom_colour" aria-label="Bottom Color" required>
                        <option value="" disabled selected data-lang-id="009-select-bottom-color">Select bottom color...</option>
                        <option value="No deliberate color set" data-lang-id="010-no-color-set">No deliberate color set</option>
                        <option value="clear" data-lang-id="011-clear">Clear</option>
                        <option value="white" data-lang-id="012-white">White</option>
                        <option value="black" data-lang-id="013-black">Black</option>
                        <option value="yellow" data-lang-id="014-yellow">Yellow</option>
                        <option value="orange" data-lang-id="015-orange">Orange</option>
                        <option value="red" data-lang-id="016-red">Red</option>
                        <option value="green" data-lang-id="016-green">Green</option>
                        <option value="green" data-lang-id="026-green">Purple</option>
                        <option value="pink" data-lang-id="017-pink">Pink</option>
                        <option value="purple" data-lang-id="018-purple">Purple</option>
                        <option value="violet" data-lang-id="019-violet">Violet</option>
                        <option value="dark blue" data-lang-id="020-dark-blue">Dark blue</option>
                        <option value="sky blue" data-lang-id="021-sky-blue">Sky blue</option>
                        <option value="brown" data-lang-id="022-brown">Brown</option>
                        <option value="grey" data-lang-id="023-grey">Grey</option>
                        <option value="silver" data-lang-id="024-silver">Silver</option>
                        <option value="gold" data-lang-id="025-gold">Gold</option>
                        <option value="cigbrick beige" data-lang-id="026-cigbrick-beige">Cigbrick beige</option>
                    </select>
                    <p class="form-caption" data-lang-id="008-bottom-color-caption">Please select the bottom color of the ecobrick.</p>
                    <!--ERRORS-->
                    <div id="color-error-required" class="form-field-error" data-lang-id="000-field-required-error">This field is required.</div>
                </div>


                <div class="form-item">
                    <label for="sequestration_type" data-lang-id="009-sequestration-type">What kind of ecobrick is this?</label><br>
                    <select id="sequestration_type" name="sequestration_type" aria-label="Sequestration Type" required>
                        <option value="" disabled selected data-lang-id="011-select-ecobrick-type">Select ecobrick type...</option>
                        <option value="Regular ecobrick" data-lang-id="012-regular-ecobrick">Regular ecobrick</option>
                        <option value="cigbrick" data-lang-id="013-cigbrick">Cigbrick</option>
                        <option value="ocean ecobrick" data-lang-id="014-ocean-ecobrick">Ocean ecobrick</option>
                    </select>
                    <p class="form-caption" data-lang-id="009-sequestration-type-caption">Please select the type of ecobrick. Learn more about <a href="#" onclick="showModalInfo('ocean')" class="underline-link">Ocean Ecobricks</a>, <a href="#" onclick="showModalInfo('cigbrick')" class="underline-link">Cigbricks</a> and <a href="#" onclick="showModalInfo('regular')" class="underline-link">Regular ecobricks</a>.</p>


                    <!--ERRORS-->
                    <div id="type-error-required" class="form-field-error" data-lang-id="000-field-required-error">This field is required.</div>
                </div>

                <div class="form-item">
                    <label for="plastic_from" data-lang-id="010-plastic-from">Where is the plastic from?</label><br>
                    <select id="plastic_from" name="plastic_from" aria-label="Plastic From" required>
                        <option value="" disabled selected data-lang-id="015-select-plastic-source">Select plastic source...</option>
                        <option value="Home" data-lang-id="016-home">Home</option>
                        <option value="Business" data-lang-id="017-business">Business</option>
                        <option value="Community" data-lang-id="018-community">Neighbourhood</option>
                        <option value="Factory" data-lang-id="019-factory">Factory</option>
                        <option value="Beach" data-lang-id="020-beach">Beach</option>
                        <option value="Ocean" data-lang-id="021-ocean">Ocean</option>
                        <option value="River" data-lang-id="022-river">River</option>
                        <option value="Forest" data-lang-id="023-forest">Forest</option>
                        <option value="Field" data-lang-id="024-field">Field</option>
                    </select>
                    <p class="form-caption" data-lang-id="010-plastic-from-caption">From where was your ecobrick's plastic sourced?</p>

                    <!--ERRORS-->
                    <div id="plastic-error-required" class="form-field-error" data-lang-id="000-field-required-error">This field is required.</div>
                </div>






            <div data-lang-id="016-submit-button" style="margin:auto;text-align: center;margin-top:30px;">
                <input type="submit" class="submit-button enabled" value="Next: Density Check" aria-label="Submit Form">
            </div>
        <div style="margin:auto;text-align: center;margin-top:10px;">
    <input type="checkbox" id="save-defaults-checkbox" name="save_defaults">
    <label for="save-defaults-checkbox" class="form-caption" data-lang-id="030-save-as-default">Save this as my default ecobrick settings.</label>
</div>


            <!--LOCALIZE BOX-->
                <div id="localize-box" class="advanced-box" aria-expanded="false" role="region" aria-labelledby="advancedBoxLabel-1">
                    <div class="advanced-box-header"  id="advancedBoxLabel-1">
                        <div class="advanced-title" data-lang-id="031-location-tags">⚙️ Location</div>
                        <div class="advanced-open-icon">+</div>
                    </div>
                    <div class="advanced-box-content" style="display:none;">

                    <p data-lang-id="111-localization-explanation">When you log an ecobrick it is tagged with your own Buwana account localization.  You can edit these defaults here:</p>



                    <div class="form-item">
                        <label for="community_select" data-lang-id="032-community-tag">Community:</label><br>
                        <div class="input-container">
                            <input type="text" id="community_select" name="community_select"
                                   value="<?= htmlspecialchars($user_community_name, ENT_QUOTES); ?>"
                                   placeholder="Start typing your community..." required style="padding-left:45px;">
                            <div id="community-pin" class="pin-icon">📌</div>
                        </div>
                        <input type="hidden" id="community_id" name="community_id" value="<?= htmlspecialchars($user_community_id, ENT_QUOTES); ?>">
                        <div id="community-suggestions" class="suggestions-box"></div>
                    </div>


                    <div class="form-item">
                        <label for="location_full" data-lang-id="033-location-tag">Location:</label><br>
                        <div class="input-container">
                            <input type="text" id="location_full" name="location_full"
                                   value="<?= htmlspecialchars($user_location_full, ENT_QUOTES); ?>"
                                   aria-label="Location Full" required style="padding-left:45px;">
                            <div id="loading-spinner" class="spinner" style="display: none;"></div>
                            <div id="location-pin" class="pin-icon">📍</div>
                        </div>

                        <div id="location-error-required" class="form-field-error" data-lang-id="000-field-required-error">This field is required.</div>
                    </div>

                    <!-- Location Watershed -->
                    <div class="form-item">
                        <label for="location_watershed" data-lang-id="032-watershed-tag">Watershed:</label><br>
                        <div class="input-container">
                            <input type="text" id="location_watershed" name="location_watershed"
                                   value="<?= htmlspecialchars($user_location_watershed, ENT_QUOTES); ?>"
                                   aria-label="Location Watershed" style="padding-left:45px;">
                            <div id="loading-spinner-watershed" class="spinner" style="display: none;"></div>
                            <div id="watershed-pin" class="pin-icon">💧</div>
                        </div>
                        <!-- Dropdown for suggestions -->
                        <div id="watershed-suggestions" class="suggestions-box"></div>
                    </div>
                    <!-- Hidden latitude and longitude fields -->
                    <input type="hidden" id="lat" name="latitude" value="<?= htmlspecialchars($user_location_lat, ENT_QUOTES); ?>">
                    <input type="hidden" id="lon" name="longitude" value="<?= htmlspecialchars($user_location_long, ENT_QUOTES); ?>">
                    <input type="hidden" id="country_id" name="country_id">
                </div>
            </div>

        </form>


            <!--END OF FORM-->


                    <!--<div class="form-item">
                            <label for="project_id" data-lang-id="014-project-id">Is this ecobrick part of a project?</label><br>
                            <input type="number" id="project_id" name="project_id" aria-label="Project ID">
                            <p class="form-caption" data-lang-id="014-project-id-caption">Optional: Provide the project ID if this ecobrick is part of a project.</p>
                            <div id="project-error-long" class="form-field-error" data-lang-id="000-field-too-long-error">Entry is too long.</div>
                        </div>

                        <div class="form-item">
                            <label for="training_id" data-lang-id="015-training-id">Was this ecobrick made in a training?</label><br>
                            <input type="number" id="training_id" name="training_id" aria-label="Training ID">
                            <p class="form-caption" data-lang-id="015-training-id-caption">Optional: Provide the training ID if this ecobrick was made in a training.</p>
                            <div id="training-error-long" class="form-field-error" data-lang-id="000-field-too-long-error">Entry is too long.</div>
                        </div>-->

        </div>

    </div>

</div>

</div> <!-- main? -->



<!--FOOTER STARTS HERE-->
<?php require_once ("../footer-2025.php");?>

</div> <!--page content-->

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
<link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">

<?php if (!empty($show_lastname_modal)): ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    setTimeout(function () {
        const modal = document.getElementById('form-modal-message');
        const messageContainer = modal.querySelector('.modal-message');
        messageContainer.innerHTML = `
            <h3 style="text-align:center;">Oops! We're missing your last name.</h3>
            <p style="text-align:center;">Looks like your GoBrik account is missing your last name. Ecobricks are best logged with your full name for posterity. Please save your last name here to make ecobrick logging faster:</p>
            <form id='update-name-form' method='post' action='update_last_name.php'>
                <label for='first_name'>First Name:</label>
                <input type='text' id='first_name' name='first_name' value="<?php echo htmlspecialchars($first_name, ENT_QUOTES); ?>" required><br>
                <label for='last_name'>Last Name:</label>
                <input type='text' id='last_name' name='last_name' required><br>
                <input type='checkbox' id='update_buwana' name='update_buwana' checked>
                <label for='update_buwana' style="font-size:0.9em">Update my Buwana account too</label><br>
                <div style="text-align:center;width:100%;margin:auto;margin-top:10px;margin-bottom:10px;">
                    <button type='submit' class="submit-button enabled">Save</button>
                    <button type='button' onclick='closeInfoModal()' class="submit-button cancel">Cancel</button>
                </div>
            </form>
        `;
        modal.style.display = 'flex';
        document.getElementById('page-content').classList.add('blurred');
        document.getElementById('footer-full').classList.add('blurred');
        document.body.classList.add('modal-open');
    }, 5000);
});
</script>
<?php endif; ?>



<script>



document.addEventListener('DOMContentLoaded', function () {
    const localizeBox = document.getElementById('localize-box');
    const advancedBoxHeader = document.querySelector('.advanced-box-header');
    const advancedBoxContent = document.querySelector('.advanced-box-content');
    const advancedOpenIcon = document.querySelector('.advanced-open-icon');

    advancedBoxHeader.addEventListener('click', function () {
        // Toggle aria-expanded attribute
        const isExpanded = localizeBox.getAttribute('aria-expanded') === 'true';
        localizeBox.setAttribute('aria-expanded', !isExpanded);

        // Toggle visibility of advanced-box-content
        if (isExpanded) {
            advancedBoxContent.style.display = 'none';  // Collapse content
            advancedOpenIcon.textContent = '+';  // Change icon to plus
        } else {
            advancedBoxContent.style.display = 'block';  // Expand content
            advancedOpenIcon.textContent = '-';  // Change icon to minus
        }
    });
});



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

    // 1. Ecobricker Maker Validation
    var ecobrickerMaker = document.getElementById('ecobricker_maker').value.trim();
    displayError('maker-error-required', ecobrickerMaker === '');
    displayError('maker-error-long', ecobrickerMaker.length > 255);
    displayError('maker-error-invalid', hasInvalidChars(ecobrickerMaker));

    // 2. Volume (ml) Validation
    var volumeML = parseInt(document.getElementById('volume_ml').value, 10);
    displayError('volume-error-required', isNaN(volumeML) || volumeML < 1);

    // 3. Weight (g) Validation
    var weightG = parseInt(document.getElementById('weight_g').value, 10);
    displayError('weight-error-required', isNaN(weightG) || weightG < 1);

    // 4. Sequestration Type Validation
    var sequestrationType = document.getElementById('sequestration_type').value.trim();
    displayError('type-error-required', sequestrationType === '');

    // 5. Plastic From Validation
    var plasticFrom = document.getElementById('plastic_from').value.trim();
    displayError('plastic-error-required', plasticFrom === '');

    // 6. Bottom Color Validation
    var bottomColour = document.getElementById('bottom_colour').value;
    displayError('color-error-required', bottomColour === '');

    // 7. Brand Name Validation
    var brandName = document.getElementById('brand_name').value.trim();
    displayError('brand-name-error-required', brandName === '');
    displayError('brand-name-error-long', brandName.length > 100);
    displayError('brand-name-error-invalid', hasInvalidChars(brandName));

    // If all validations pass, submit the form
    if (isValid) {
        this.submit();
    } else {
        // Scroll to the first error message and center it in the viewport
        var firstError = document.querySelector('.form-field-error[style="display: block;"]');
        if (firstError) {
            firstError.scrollIntoView({ behavior: "smooth", block: "center", inline: "nearest" });
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
        $("#location_full").autocomplete({
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
                console.log('Selected location:', ui.item); // Debugging line
                $('#location_full').val(ui.item.value);
                $('#lat').val(ui.item.lat);
                $('#lon').val(ui.item.lon);
            },
            minLength: 3
        });

        $('#submit-form').on('submit', function() {
            console.log('Latitude:', $('#lat').val());
            console.log('Longitude:', $('#lon').val());
            // alert('Latitude: ' + $('#lat').val() + ', Longitude: ' + $('#lon').val());
        });
    });






// Community autocomplete functions
document.addEventListener('DOMContentLoaded', function() {
    const communitySelect = document.getElementById('community_select');
    const hiddenIdInput = document.getElementById('community_id'); // Hidden input for community_id

    // Log the current community name for debugging
    console.log('Current community pre-set value:', communitySelect.value);

    // Add an event listener to trigger AJAX search when user types in the community field
    communitySelect.addEventListener('input', function() {
        const query = this.value;

        // Clear existing community_id when typing begins
        if (hiddenIdInput) {
            hiddenIdInput.value = '';
        }

        // If the user has typed at least 3 characters, trigger the AJAX search
        if (query.length >= 3) {
            const xhr = new XMLHttpRequest();
            xhr.open('GET', '../api/search_communities.php?query=' + encodeURIComponent(query), true);

            xhr.onload = function() {
                if (xhr.status === 200) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        showCommunitySuggestions(response);
                    } catch (e) {
                        console.error("Failed to parse JSON from community search response:", e);
                    }
                } else {
                    console.error("Error loading community suggestions:", xhr.status);
                }
            };

            xhr.onerror = function() {
                console.error("Network error while fetching community suggestions.");
            };

            xhr.send();
        }
    });

    // Function to display the community suggestions
    function showCommunitySuggestions(communities) {
        const suggestionsBox = document.getElementById('community-suggestions');
        suggestionsBox.innerHTML = ''; // Clear previous suggestions

        communities.forEach(function(community) {
            const suggestionItem = document.createElement('div');
            suggestionItem.textContent = community.com_name;
            suggestionItem.classList.add('suggestion-item');

            suggestionItem.addEventListener('click', function() {
                // Set the visible name
                communitySelect.value = community.com_name;

                // Set the hidden ID
                if (hiddenIdInput) {
                    hiddenIdInput.value = community.community_id;
                }

                suggestionsBox.innerHTML = ''; // Clear suggestions once selected
            });

            suggestionsBox.appendChild(suggestionItem);
        });
    }
});




//Watershed
document.addEventListener('DOMContentLoaded', function() {
    const watershedInput = document.getElementById('location_watershed');
    const watershedSuggestions = document.getElementById('watershed-suggestions');
    const latInput = document.getElementById('lat');  // Hidden field for latitude
    const lonInput = document.getElementById('lon');  // Hidden field for longitude

    // Function to fetch nearby rivers or watersheds using the Overpass API
    function fetchNearbyRivers(lat, lon) {
        const overpassUrl = `https://overpass-api.de/api/interpreter?data=[out:json];(way["waterway"="river"](around:5000,${lat},${lon});relation["waterway"="river"](around:5000,${lat},${lon}););out tags;`;

        fetch(overpassUrl)
            .then(response => response.json())
            .then(data => {
                console.log('Overpass API data:', data);  // Log raw response
                const rivers = data.elements.filter(el => el.tags && el.tags.name);
                const uniqueRivers = getUniqueRivers(rivers).slice(0, 5);  // Limit to 5 closest rivers
                displayRiverSuggestions(uniqueRivers);
            })
            .catch(error => {
                console.error('Error fetching river data:', error);
                watershedSuggestions.innerHTML = '<div>No rivers found</div>';
            });
    }

    // Function to display river suggestions in the dropdown
    function displayRiverSuggestions(rivers) {
        watershedSuggestions.innerHTML = '';  // Clear any previous suggestions

        if (rivers.length === 0) {
            watershedSuggestions.innerHTML = '<div class="suggestion-item">No rivers found</div>';
        } else {
            rivers.forEach(river => {
                const suggestionItem = document.createElement('div');
                suggestionItem.textContent = river.tags.name;
                suggestionItem.classList.add('suggestion-item');
                suggestionItem.addEventListener('click', function() {
                    watershedInput.value = river.tags.name;
                    watershedSuggestions.innerHTML = '';  // Clear suggestions once a selection is made
                });
                watershedSuggestions.appendChild(suggestionItem);
            });
        }
    }

    // Function to filter out unique river names (prevent duplicates)
    function getUniqueRivers(rivers) {
        const uniqueNames = new Set();
        return rivers.filter(river => {
            if (!uniqueNames.has(river.tags.name)) {
                uniqueNames.add(river.tags.name);
                return true;
            }
            return false;
        });
    }

    // Event listener for focusing on the location_watershed input
    watershedInput.addEventListener('focus', function() {
        const lat = latInput.value;
        const lon = lonInput.value;

        if (!lat || !lon) {
            console.error('Latitude and longitude are required to fetch nearby rivers.');
            watershedSuggestions.innerHTML = '<div>Error: No location data</div>';
            return;
        }

        // Fetch rivers when the user focuses on the watershed input
        fetchNearbyRivers(lat, lon);
    });
});



//Defaults scripts

// Function to save default ecobrick form data
function saveEcobrickDefaults() {
    // Check if the checkbox is checked
    if (document.getElementById('save-defaults-checkbox').checked) {
        // Save form data to localStorage
        const ecobrickerMaker = document.getElementById('ecobricker_maker').value;
        const volume = document.getElementById('volume_ml').value;
        const weight = document.getElementById('weight_g').value;
        const brandName = document.getElementById('brand_name').value;
        const bottomColor = document.getElementById('bottom_colour').value;
        const sequestrationType = document.getElementById('sequestration_type').value;
        const plasticFrom = document.getElementById('plastic_from').value;
        const community = document.getElementById('community_select').value;
        const locationFull = document.getElementById('location_full').value;
        const watershed = document.getElementById('location_watershed').value;
        const communityId = document.getElementById('community_id').value;


        const defaults = {
    ecobrickerMaker,
    volume,
    weight,
    brandName,
    bottomColor,
    sequestrationType,
    plasticFrom,
    community,
    communityId,
    locationFull,
    watershed
};

        localStorage.setItem('ecobrickDefaults', JSON.stringify(defaults));
        console.log('Ecobrick defaults saved.');
    }
}



// Function to restore default ecobrick form data
function restoreEcobrickDefaults() {
    const defaults = JSON.parse(localStorage.getItem('ecobrickDefaults'));
    if (defaults) {
        // Populate form fields with saved defaults
        document.getElementById('ecobricker_maker').value = defaults.ecobrickerMaker || '';
//         document.getElementById('volume_ml').value = defaults.volume || '';
//         document.getElementById('weight_g').value = defaults.weight || '';
        document.getElementById('brand_name').value = defaults.brandName || '';
        document.getElementById('bottom_colour').value = defaults.bottomColor || '';
        document.getElementById('sequestration_type').value = defaults.sequestrationType || '';
        document.getElementById('plastic_from').value = defaults.plasticFrom || '';
//         document.getElementById('location_full').value = defaults.locationFull || '';
//         document.getElementById('location_watershed').value = defaults.watershed || '';
document.getElementById('community_select').value = defaults.community || '';
document.getElementById('community_id').value = defaults.communityId || '';

        // Show the "Defaults loaded" message
        document.getElementById('defaults-loaded').style.display = 'block';
        console.log('Ecobrick defaults restored.');
    }
}

// Function to check if a specific RETRY parameter is present in the URL
function getQueryParam(param) {
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.get(param);
}

// Call restoreEcobrickDefaults only if the "retry" parameter is not set,
// and update the title if "retry" is present.
window.onload = function() {
    const retryParam = getQueryParam('retry');
    const titleElement = document.getElementById('log-page-title');

    if (retryParam) {
        // If the "retry" parameter is present, change the title of the page:
        if (titleElement) {
            titleElement.innerHTML = "Retry logging ecobrick";
        }
    } else {
        // Otherwise, call the restoreEcobrickDefaults function
        restoreEcobrickDefaults();
    }
};


// Hook saveEcobrickDefaults function to form submission
document.getElementById('submit-form').addEventListener('submit', function(event) {
    // Only save defaults if the retry parameter is not in the URL
    const retryParam = getQueryParam('retry');
    if (!retryParam) {
        saveEcobrickDefaults();
    }
});

</script>




</body>
</html>
