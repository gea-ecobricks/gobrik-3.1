<?php
require_once '../earthenAuth_helper.php'; // Include the authentication helper functions

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if the user is logged in
if (isLoggedIn()) {
    header('Location: dashboard.php'); // Redirect to dashboard if the user is logged in
    exit();
}

// Set page variables
$lang = basename(dirname($_SERVER['SCRIPT_NAME']));
$version = '0.68';
$lastModified = date("Y-m-d\TH:i:s\Z", filemtime(__FILE__));
$is_logged_in = false; // Ensure not logged in for this page

// Initialize variables
$buwana_id = $_GET['id'] ?? null;  // Correctly initializing buwana_id
$page = 'activate';
$first_name = '';
$email_addr = '';
$continents = [];
$countries = [];
$watersheds = [];

// PART 1: Check if the user is already logged in
if (isset($_SESSION['buwana_id'])) {
    header("Location: dashboard.php");
    exit();
}

// PART 2: Check if buwana_id is passed in the URL
if (is_null($buwana_id)) {
    echo '<script>
        alert("Hmm... something went wrong. No buwana ID was passed along. Please try logging in again. If this problem persists, you\'ll need to create a new account.");
        window.location.href = "login.php";
    </script>';
    exit();
}

// PART 3: Look up user information using buwana_id provided in URL
require_once("../buwanaconn_env.php");

// Fetch user information using buwana_id from the Buwana database
$sql_user_info = "SELECT first_name FROM users_tb WHERE buwana_id = ?";
$stmt_user_info = $buwana_conn->prepare($sql_user_info);

if ($stmt_user_info) {
    $stmt_user_info->bind_param('i', $buwana_id);
    $stmt_user_info->execute();
    $stmt_user_info->bind_result($first_name);
    $stmt_user_info->fetch();
    $stmt_user_info->close();
} else {
    die('Error preparing statement for fetching user info: ' . $buwana_conn->error);
}

// Ensure $first_name is set and not empty
if (empty($first_name)) {
    $first_name = 'User'; // Fallback if first name is not set
}

// Fetch continents from Buwana database
$sql_continents = "SELECT continent_code, continent_name FROM continents_tb ORDER BY continent_name";
$result_continents = $buwana_conn->query($sql_continents);

if ($result_continents && $result_continents->num_rows > 0) {
    while ($row = $result_continents->fetch_assoc()) {
        $continents[] = $row;
    }
} else {
    error_log('No continents found or error in query: ' . $buwana_conn->error);
}

// Fetch countries from Buwana database
$sql_countries = "SELECT country_id, country_name, continent_code FROM countries_tb ORDER BY country_name";
$result_countries = $buwana_conn->query($sql_countries);

if ($result_countries && $result_countries->num_rows > 0) {
    while ($row = $result_countries->fetch_assoc()) {
        $countries[] = $row;
    }
} else {
    error_log('No countries found or error in query: ' . $buwana_conn->error);
}

// Fetch watersheds from Buwana database
$sql_watersheds = "SELECT watershed_id, watershed_name, continent_code FROM watersheds_tb ORDER BY watershed_name";
$result_watersheds = $buwana_conn->query($sql_watersheds);

if ($result_watersheds && $result_watersheds->num_rows > 0) {
    while ($row = $result_watersheds->fetch_assoc()) {
        $watersheds[] = $row;
    }
} else {
    error_log('No watersheds found or error in query: ' . $buwana_conn->error);
}

// PART 4: Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selected_continent_code = $_POST['continent_code'];
    $selected_country_id = $_POST['country_id'];
    $selected_watershed_id = $_POST['watershed_id'];

    // Update the Buwana user's continent, country, and watershed using buwana_id
    $sql_update_buwana = "UPDATE users_tb SET continent_code = ?, country_id = ?, watershed_id = ? WHERE buwana_id = ?";
    $stmt_update_buwana = $buwana_conn->prepare($sql_update_buwana);
    if ($stmt_update_buwana) {
        $stmt_update_buwana->bind_param('siii', $selected_continent_code, $selected_country_id, $selected_watershed_id, $buwana_id);
        $stmt_update_buwana->execute();
        $stmt_update_buwana->close();

        // PART 5: Open GoBrik connection and update tb_ecobrickers to set buwana_activated to 1
        require_once("../gobrikconn_env.php");

        $sql_update_gobrik = "UPDATE tb_ecobrickers SET buwana_activated = 1 WHERE buwana_id = ?";
        $stmt_update_gobrik = $gobrik_conn->prepare($sql_update_gobrik);

        if ($stmt_update_gobrik) {
            $stmt_update_gobrik->bind_param('i', $buwana_id); // Update based on the ecobricker's unique identifier
            if ($stmt_update_gobrik->execute()) {
                // Successfully updated GoBrik
                $stmt_update_gobrik->close();
            } else {
                error_log('Error executing update on tb_ecobrickers: ' . $stmt_update_gobrik->error);
                echo "Failed to update GoBrik record.";
            }
        } else {
            error_log('Error preparing GoBrik statement: ' . $gobrik_conn->error);
            echo "Failed to prepare GoBrik update statement.";
        }

        // Close the GoBrik connection
        $gobrik_conn->close();

        // Redirect to the next step
        header("Location: login.php?status=firsttime&id=" . urlencode($buwana_id));
        exit();
    } else {
        error_log('Error preparing statement for updating Buwana user: ' . $buwana_conn->error);
        header("Location: activate-3.php?id=" . urlencode($buwana_id) . "&error=db_update_failed");
        exit();
    }
}

// Close the Buwana database connection after all operations are done
$buwana_conn->close();
?>




<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
<meta charset="UTF-8">
<title>Activate your Buwana Account | Step 3 | GoBrik</title>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!--
GoBrik.com site version 3.0
Developed and made open source by the Global Ecobrick Alliance
See our git hub repository for the full code and to help out:
https://github.com/gea-ecobricks/gobrik-3.0/tree/main/en-->

<?php require_once ("../includes/signup-inc.php");?>

<div class="splash-title-block"></div>
<div id="splash-bar"></div>

<!-- PAGE CONTENT -->
<div id="top-page-image" class="welcome-casandra top-page-image"></div>

<div id="form-submission-box" class="landing-page-form">
    <div class="form-container">
        <div style="text-align:center;width:100%;margin:auto;">
            <div id="status-message"><?php echo htmlspecialchars($first_name); ?>, <span data-lang-id="012-status-heading">your password is set! Now let's get you localized.</span></div>
            <div id="sub-status-message" data-lang-id="013-sub-status-tell" style="font-size:1.3em;padding-top:10px;padding-bottom:10px;">Your new Buwana and GoBrik account is all about local and global ecological action. Please tell us about where you live...</div>
        </div>

        <!-- ACTIVATE 3 FORM -->

        <form id="user-info-form" method="post" action="activate-3.php?id=<?php echo htmlspecialchars($buwana_id); ?>">

            <!-- CONTINENT -->
            <div class="form-item" id="continent-select" style="display:block;">
                <label for="continent" data-lang-id="014-your-continent" style="margin-top:10px;">On what continent do you live?</label><br>
                <select name="continent_code" id="continent_code" required>
                    <option value="" disabled selected data-lang-id="015-continent-place-holder">Select your continent...</option>
                    <?php foreach ($continents as $continent) { ?>
                        <option value="<?php echo $continent['continent_code']; ?>">
                            <?php echo htmlspecialchars($continent['continent_name']); ?>
                        </option>
                    <?php } ?>
                </select>
            <p class="form-caption"  style="margin-bottom:-5px;" data-lang-id="015-continents-caption">Continents are where biomes, Earth's major ecosystems, get their unique diversity and vitality of species. </p>
            </div>

            <!-- COUNTRY -->
            <div class="form-item" id="country-select" style="display:none;margin-top:5px;">
                <label for="country" data-lang-id="014-your-country" style="margin-top:10px;">In what country do you reside?</label><br>
                <select name="country_id" id="country_id" required>
                    <option value="" disabled selected data-lang-id="015-country-place-holder"><span data-lang-id="0016-select-contry-placeholder">Select your country of residence...</span></option>
                </select>
                <p id="country-caption" class="form-caption" style="margin-bottom:-5px;">Showing all countries in </span><?php echo htmlspecialchars($continent['continent_name']); ?></p>
            </div>

        <!--LOCATION FULL-->
            <div class="form-item">
                    <label for="location_full" data-lang-id="011-location-full">Where is this ecobrick based?</label><br>
                    <div class="input-container">
                        <input type="text" id="location_full" name="location_full" aria-label="Location Full" required style="padding-left:45px;">
                        <div id="loading-spinner" class="spinner" style="display: none;"></div>
                    </div>
                    <p class="form-caption" data-lang-id="011-location-full-caption">Start typing the name of your town or city, and we'll fill in the rest using the open source, non-corporate openstreetmaps API.  Avoid using your exact address for privacy-- just your town, city or country is fine.</p>

                    <!--ERRORS-->
                    <div id="location-error-required" class="form-field-error" data-lang-id="000-field-required-error">This field is required.</div>
                </div>

                <input type="hidden" id="lat" name="latitude">
                <input type="hidden" id="lon" name="longitude">

            <!-- WATERSHED -->
            <div class="form-item" id="watershed-select" style="display:none;">
                <label for="watershed" data-lang-id="014-your-watershed" style="margin-top:10px;">In what river basin do you live?</label><br>
                <select name="watershed_id" id="watershed_id" required>
                    <option value="" disabled selected><span data-lang-id="015-watershed-place-holder">Select your river basin...</span></option>
                    <option value="Unsure" data-lang-id="016-dont-know">I am not sure</option>
                    <option value="not listed" data-lang-id="016-dont-know">Not listed</option>
                </select>
                <p class="form-caption">
                    <span data-lang-id="018-what-is-watershed">Almost everyone lives in one of Earth's 200 main watersheds.  See if you can locate yours! Learn more about </span>
                    <a href="#" onclick="showModalInfo('watershed', '<?php echo htmlspecialchars($lang); ?>')" class="underline-link" data-lang-id="019-watershed">watersheds</a>.
                </p>
            </div>

            <div id="submit-section" style="text-align:center;margin-top:25px;display:none;" data-lang-id="016-submit-complete-button">
                <input type="submit" id="submit-button" value="Complete Setup" class="submit-button enabled">
                <p style="font-size:smaller;">Can't find your watershed?  No worries! We're still working on adding them.</p>
            </div>

        </form>

    </div>
</div>
</div>
<!-- FOOTER STARTS HERE -->
<?php require_once ("../footer-2025.php"); ?>


<script>
document.addEventListener('DOMContentLoaded', function () {
    var continentSelect = document.getElementById('continent_code');
    var watershedSelect = document.getElementById('watershed-select');
    var watershedDropdown = document.getElementById('watershed_id');
    var countrySelect = document.getElementById('country-select');
    var countryDropdown = document.getElementById('country_id');
    var submitSection = document.getElementById('submit-section');

    // Initially hide the watershed, country dropdowns, and submit button
    watershedSelect.style.display = 'none';
    countrySelect.style.display = 'none';
    submitSection.style.display = 'none';

    // Event listener for continent selection
    continentSelect.addEventListener('change', function () {
        if (this.value !== '') {
            var continentCode = this.value;

            // Fetch countries based on the selected continent using AJAX
            fetchCountries(continentCode);
        } else {
            // Reset and hide subsequent dropdowns and submit button
            watershedSelect.style.display = 'none';
            countrySelect.style.display = 'none';
            submitSection.style.display = 'none';
            watershedDropdown.innerHTML = '<option value="" disabled selected>Select your watershed...</option><option value="unsure">I am unsure</option><option value="not listed">Not listed</option>';
            countryDropdown.innerHTML = '<option value="" disabled selected>Select your country of residence...</option>';
        }
    });

    // Show watershed dropdown and submit button after selecting a country
    countryDropdown.addEventListener('change', function () {
        if (this.value !== '') {
            var countryId = this.value;

            // Fetch watersheds based on the selected country using AJAX
            fetchWatersheds(countryId);

            // Show the submit section after selecting a country
            submitSection.style.display = 'block';
        } else {
            watershedSelect.style.display = 'none';
            submitSection.style.display = 'none';
        }
    });

    // Show submit button after watershed is selected
    watershedDropdown.addEventListener('change', function () {
        if (this.value !== '') {
            submitSection.style.display = 'block';
        } else {
            submitSection.style.display = 'none';
        }
    });

    // AJAX function to fetch countries
    function fetchCountries(continentCode) {
        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'fetch_countries.php', true);
        xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');

        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4 && xhr.status === 200) {
                var response = JSON.parse(xhr.responseText);

                // Clear current options
                countryDropdown.innerHTML = '<option value="" disabled selected>Select your country of residence...</option>';

                // Add new options from the response
                response.forEach(function (country) {
                    var option = document.createElement('option');
                    option.value = country.country_id;
                    option.textContent = country.country_name;
                    countryDropdown.appendChild(option);
                });

                // Show the country dropdown
                countrySelect.style.display = 'block';
            }
        };

        // Send continent code to the server
        xhr.send('continent_code=' + encodeURIComponent(continentCode));
    }

    // AJAX function to fetch watersheds
    function fetchWatersheds(countryId) {
        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'fetch_watersheds.php', true);
        xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');

        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4 && xhr.status === 200) {
                var response = JSON.parse(xhr.responseText);

                // Clear current options
                watershedDropdown.innerHTML = '<option value="" disabled selected>Select your watershed...</option><option value="unsure">I am unsure</option><option value="not listed">Not listed</option>';

                // Add new options from the response
                response.forEach(function (watershed) {
                    var option = document.createElement('option');
                    option.value = watershed.watershed_id;
                    option.textContent = watershed.watershed_name;
                    watershedDropdown.appendChild(option);
                });

                // Show the watershed dropdown
                watershedSelect.style.display = 'block';
            }
        };

        // Send country ID to the server
        xhr.send('country_id=' + encodeURIComponent(countryId));
    }
});


</script>

<script>
    // Function to update the country-caption div based on continent selection
    function updateCountryCaption() {
        // Retrieve the language setting from the server-side PHP variable
        const lang = '<?php echo htmlspecialchars($lang); ?>';

        // Get the continent menu element and the country caption div
        const continentMenu = document.getElementById('continent_code');
        const countryCaption = document.getElementById('country-caption');

        // Add an event listener to detect changes in the continent selection
        continentMenu.addEventListener('change', function () {
            // Get the selected continent's value and name
            const selectedContinent = continentMenu.value;
            const continentName = continentMenu.options[continentMenu.selectedIndex].text;

            // Prepare translations for the message in different languages
            const translations = {
                en: `Showing all countries in the continent of ${continentName}.`,
                fr: `Afficher tous les pays du continent de ${continentName}.`,
                es: `Mostrando todos los países en el continente de ${continentName}.`,
                id: `Menampilkan semua negara di benua ${continentName}.`,
                special_en: "Showing all countries in Europe. Do you live in the UK? Note that we've listed England, Northern Ireland, Wales, and Scotland separately.",
                special_fr: "Afficher tous les pays en Europe. Habitez-vous au Royaume-Uni ? Notez que nous avons répertorié séparément l'Angleterre, l'Irlande du Nord, le Pays de Galles et l'Écosse.",
                special_es: "Mostrando todos los países en Europa. ¿Vives en el Reino Unido? Tenga en cuenta que hemos enumerado por separado a Inglaterra, Irlanda del Norte, Gales y Escocia.",
                special_id: "Menampilkan semua negara di Eropa. Apakah Anda tinggal di Inggris? Harap dicatat bahwa kami telah mencantumkan Inggris, Irlandia Utara, Wales, dan Skotlandia secara terpisah."
            };

            // Determine the message based on the selected continent and language
            let message = '';
            if (selectedContinent === 'EU') {
                message = translations[`special_${lang}`] || translations['special_en']; // Use special message for Europe
            } else {
                message = translations[lang] || translations['en']; // Default to English if no translation is available
            }

            // Update the content of the country-caption div
            countryCaption.innerHTML = message;
        });
    }

    // Initialize the function on page load
    window.onload = updateCountryCaption;






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
</script>





</body>
</html>
