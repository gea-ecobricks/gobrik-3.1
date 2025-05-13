<?php
require_once '../earthenAuth_helper.php'; // Include the authentication helper functions

// Set page variables
$lang = basename(dirname($_SERVER['SCRIPT_NAME']));
$version = '0.448';
$page = 'newest-briks';
$lastModified = date("Y-m-d\TH:i:s\Z", filemtime(__FILE__));
$is_logged_in = isLoggedIn(); // Check if the user is logged in using the helper function

// Check if the user is logged in
if ($is_logged_in) {

    $buwana_id = $_SESSION['buwana_id'];

    // Include database connections
    require_once '../gobrikconn_env.php';
    require_once '../buwanaconn_env.php';

    // Fetch user data
    $user_continent_icon = getUserContinent($buwana_conn, $buwana_id);
$earthling_emoji = getUserEarthlingEmoji($buwana_conn, $buwana_id);
    $user_location_watershed = getWatershedName($buwana_conn, $buwana_id);
    $user_location_full = getUserFullLocation($buwana_conn, $buwana_id);
    $gea_status = getGEA_status($buwana_id);
    $user_community_name = getCommunityName($buwana_conn, $buwana_id);
    $first_name = getFirstName($buwana_conn, $buwana_id);

    $buwana_conn->close();  // Close the database connection
}

// Include main database connection
require_once '../gobrikconn_env.php';

echo '<!DOCTYPE html>
<html lang="' . htmlspecialchars($lang, ENT_QUOTES, 'UTF-8') . '">
<head>
<meta charset="UTF-8">
';

require_once ("../includes/brik-inc.php");

echo '<div class="splash-title-block"></div>
    <div id="splash-bar"></div>
    <div id="form-submission-box" style="margin-top:80px;">
        <div class="form-container" style="padding-top:0px !important">';

// Get the contents from the Ecobrick table using the serial_no from the URL
$serialNo = isset($_GET['serial_no']) ? $_GET['serial_no'] : null;

if ($serialNo) {
    $sql = "SELECT serial_no, weight_g, location_full, ecobrick_full_photo_url, date_logged_ts, last_validation_ts, status, vision, owner, volume_ml, sequestration_type, density, CO2_kg, selfie_photo_url, ecobrick_dec_brk_val, brand_name, bottom_colour, plastic_from, community_name, location_city, location_region, location_country, validator_1, validator_2, validator_3, validation_score_avg, catalyst, final_validation_score, weight_authenticated_kg, photo_version FROM tb_ecobricks WHERE serial_no = ?";
    $stmt = $gobrik_conn->prepare($sql);

    if ($stmt) {
        // Bind the serial_no as a string
        $stmt->bind_param("s", $serialNo);
        $stmt->execute();
        $stmt->store_result();

        // Check if any results were found
        if ($stmt->num_rows === 0) {
            // No results, show alert and redirect
            echo '<script>
                alert("Sorry, no ecobrick can be found for this serial number.");
                window.location.href = "newest.php";
            </script>';
            exit();  // Exit to prevent further execution
        }

        // Bind the result variables
        $stmt->bind_result(
            $serial_no, $weight_g, $location_full, $ecobrick_full_photo_url, $date_logged_ts, $last_validation_ts, $status,
            $vision, $owner, $volume_ml, $sequestration_type, $density, $CO2_kg, $selfie_photo_url, $ecobrick_dec_brk_val,
            $brand_name, $bottom_colour, $plastic_from, $community_name, $location_city, $location_region, $location_country,
            $validator_1, $validator_2, $validator_3, $validation_score_avg, $catalyst, $final_validation_score, $weight_authenticated_kg,
            $photo_version // New field
        );

        // Fetch the results
        while ($stmt->fetch()) {
            // Check the status of the ecobrick
            $status = strtolower($status);
            $isAuthenticated = ($status === "authenticated");

            // AUTHENTICATED ECOBRICK
            if ($isAuthenticated) {
                echo '
                <div class="brik-content-block">
                    <div class="brik-info-box">
                        <div class="brik-serial-no"><span data-lang-id="001-splash-title">Ecobrick</span> ' . htmlspecialchars($serial_no, ENT_QUOTES, 'UTF-8') . '</div>
                        <div class="brik-sub-title">';

                // VISION
                if (!empty(trim($vision))) {
                    // If $vision has content, clean and display it
                    $cleanedVisionText = str_replace('"', '', trim($vision));
                    echo '<p><div class="vision-quote"> "' . htmlspecialchars($cleanedVisionText, ENT_QUOTES, 'UTF-8') . '" </div></p>';
                } else {
                    // If $vision is empty, display the alternative message
                    echo htmlspecialchars($weight_g, ENT_QUOTES, 'UTF-8') .
                        '&#8202;g <span data-lang-id="002-splash-subtitle">of plastic has been secured out of the biosphere in</span> ' .
                        htmlspecialchars($location_full, ENT_QUOTES, 'UTF-8');
                }

                echo '
                        </div>
                        <div class="brik-status authenticated">✅ Authenticated</div>
                    </div>
                    <div class="brik-image">
                        <a href="javascript:void(0);" onclick="viewGalleryImage(\'' . htmlspecialchars($ecobrick_full_photo_url, ENT_QUOTES, 'UTF-8') . '?v=' . htmlspecialchars($photo_version, ENT_QUOTES, 'UTF-8') . '\', \'Ecobrick ' . htmlspecialchars($serial_no, ENT_QUOTES, 'UTF-8') . ' was made in ' . htmlspecialchars($location_full, ENT_QUOTES, 'UTF-8') . ' and logged on ' . htmlspecialchars($date_logged_ts, ENT_QUOTES, 'UTF-8') . '\')">
                            <img src="../' . htmlspecialchars($ecobrick_full_photo_url, ENT_QUOTES, 'UTF-8') . '?v=' . htmlspecialchars($photo_version, ENT_QUOTES, 'UTF-8') . '" alt="Ecobrick ' . htmlspecialchars($serial_no, ENT_QUOTES, 'UTF-8') . '" title="Ecobrick Serial ' . htmlspecialchars($serial_no, ENT_QUOTES, 'UTF-8') . ' was made in ' . htmlspecialchars($location_full, ENT_QUOTES, 'UTF-8') . ' and authenticated on ' . htmlspecialchars($last_validation_ts, ENT_QUOTES, 'UTF-8') . '">
                        </a>
                    </div>
                </div>';
            } else {

            // NON AUTHENTICATED ECOBRICKS
            echo '
            <div class="brik-content-block">
                <div class="brik-info-box">
                    <div class="brik-serial-no"><span data-lang-id="001-splash-title">Ecobrick</span> ' . htmlspecialchars($serial_no, ENT_QUOTES, 'UTF-8') . '</div>
                    <div class="brik-sub-title">';

            // VISION
            if (!empty(trim($vision))) {
                // If $vision has content, clean and display it
                $cleanedVisionText = str_replace('"', '', trim($vision));
                echo '<p><div class="vision-quote"> "' . htmlspecialchars($cleanedVisionText, ENT_QUOTES, 'UTF-8') . '" </div></p>';
            } else {
                // If $vision is empty, display the alternative message
                echo '<p><b>' . htmlspecialchars($owner, ENT_QUOTES, 'UTF-8') .
                     ' <span data-lang-id="110">has ecobricked </span> ' . htmlspecialchars($weight_g, ENT_QUOTES, 'UTF-8') .
                     '&#8202;g<span data-lang-id="111"> of community plastic in </span>' . htmlspecialchars($location_full, ENT_QUOTES, 'UTF-8') .
                     '<span data-lang-id="112"> using a </span>' . htmlspecialchars($volume_ml, ENT_QUOTES, 'UTF-8') .
                     'ml <span data-lang-id="113"> bottle to make a </span>' . htmlspecialchars($sequestration_type, ENT_QUOTES, 'UTF-8') .
                     '.</b></p>';
            }

            echo '
                    </div>
                    <div class="brik-status waiting">🕙 Waiting for validations</div>
                </div>
                <div class="brik-image">
                    <a href="javascript:void(0);" onclick="viewGalleryImage(\'' . htmlspecialchars($ecobrick_full_photo_url, ENT_QUOTES, 'UTF-8') . '?v=\', \'Ecobrick ' . htmlspecialchars($serial_no, ENT_QUOTES, 'UTF-8') . ' was made in ' . htmlspecialchars($location_full, ENT_QUOTES, 'UTF-8') . ' and logged on ' . htmlspecialchars($date_logged_ts, ENT_QUOTES, 'UTF-8') . '\')">
                        <img src="../' . htmlspecialchars($ecobrick_full_photo_url, ENT_QUOTES, 'UTF-8') . '?v=' . htmlspecialchars($photo_version, ENT_QUOTES, 'UTF-8') . '" alt="Ecobrick ' . htmlspecialchars($serial_no, ENT_QUOTES, 'UTF-8') . ' was made in ' . htmlspecialchars($location_full, ENT_QUOTES, 'UTF-8') . ' and logged on ' . htmlspecialchars($date_logged_ts, ENT_QUOTES, 'UTF-8') . '" title="Ecobrick Serial ' . htmlspecialchars($serial_no, ENT_QUOTES, 'UTF-8') . ' was made in ' . htmlspecialchars($location_full, ENT_QUOTES, 'UTF-8') . ' and authenticated on ' . htmlspecialchars($last_validation_ts, ENT_QUOTES, 'UTF-8') . '">
                    </a>
                </div>
            </div>';
        }

        }

        $stmt->close();  // Close the statement
    } else {
        echo "Failed to prepare the SQL statement: " . $gobrik_conn->error;
    }
} else {
    echo '<script>
        alert("No serial number provided.");
        window.location.href = "newest-briks.php";
    </script>';
    exit();  // Exit to prevent further execution
}
// Continue with the rest of the page content as it is
if (strpos(strtolower($status), 'authenticated') === false) {
    echo '
    <div class="row-details">
        <p>This ecobrick was logged on ' . htmlspecialchars($date_logged_ts, ENT_QUOTES, 'UTF-8') . '. It is pending review and authentication.</p>
    </div>';
}


// EXPLANATION
echo '<div class="main-details">';

// Check if $vision is not empty
if (!empty(trim($vision))) {
    echo '<p>' . htmlspecialchars($owner, ENT_QUOTES, 'UTF-8') .
         ' <span data-lang-id="110">has ecobricked </span> ' . htmlspecialchars($weight_g, ENT_QUOTES, 'UTF-8') .
         '&#8202;g<span data-lang-id="111"> of community plastic in </span>' . htmlspecialchars($location_full, ENT_QUOTES, 'UTF-8') .
         '<span data-lang-id="112"> using a </span>' . htmlspecialchars($volume_ml, ENT_QUOTES, 'UTF-8') .
         'ml <span data-lang-id="113"> bottle to make a </span>' . htmlspecialchars($sequestration_type, ENT_QUOTES, 'UTF-8') .
         '.</p>';
}

// Additional explanation about the ecobrick
echo '<p><span data-lang-id="114">This ecobrick has a density of </span>' . htmlspecialchars($density, ENT_QUOTES, 'UTF-8') . '&#8202;g/ml <span data-lang-id="115">and represents </span>' . htmlspecialchars($CO2_kg, ENT_QUOTES, 'UTF-8') . '&#8202;kg <span data-lang-id="116">of sequestered CO2. The ecobrick is permanently marked with Serial Number </span>' . htmlspecialchars($serial_no, ENT_QUOTES, 'UTF-8') . '<span data-lang-id="117"> and was added to the validation queue on </span>' . htmlspecialchars($date_logged_ts, ENT_QUOTES, 'UTF-8') . '.</p>';

// Check if $status does not contain "authenticated"
if (strpos(strtolower($status), 'authenticated') === false) {
    echo '<p>This ecobrick has not yet been peer-reviewed. Its plastic has not been authenticated as sequestered.</p>';
}

echo '<br></div>'; // Close main-details div

// IF THERE'S A SELFIE IT GOES HERE
if (!empty($selfie_photo_url)) {
    echo '<div class="side-details">
            <img src="' . htmlspecialchars($selfie_photo_url, ENT_QUOTES, 'UTF-8') . '?v=' . htmlspecialchars($photo_version, ENT_QUOTES, 'UTF-8') . '" width="100%">
          </div>';
}


// Get the contents from the Ecobrick table as an ordered View, using the serial_no from the URL.  See: https://www.w3schools.com/php/php_mysql_select_where.asp1
	$serialNo = $_GET['serial_no'];

	$sql = "SELECT * FROM tb_ecobricks WHERE serial_no = '" . $serialNo . "'";

	$result = $gobrik_conn->query($sql);
	if ($result->num_rows > 0) {

    while($array = $result->fetch_assoc()) {

		echo
		'
<div id="data-chunk">
				<div class="ecobrick-data">
					<p style="margin-left: -32px;font-weight: bold;" data-lang-id="125"> +++ Raw Brikchain Data Record</p><br>
					<p>--------------------</p>
					<p data-lang-id="126">BEGIN BRIK RECORD ></p>';

			echo ' <p><b data-lang-id="127">Logged:</b> ' . $array["date_logged_ts"] .'</p>' ;
			echo ' <p><b data-lang-id="128">Volume:</b> <var>'. $array["volume_ml"] .' &#8202;ml</var></p>' ;
			echo ' <p><b data-lang-id="129">Weight:</b> <var>' . $array["weight_g"] .'&#8202;g</var></p>' ;
			echo ' <p><b data-lang-id="130">Density:</b> <var>' . $array["density"] .'&#8202;g/ml</var></p>' ;
			echo ' <p><b data-lang-id="131">CO2e:</b><var>' . $array["CO2_kg"] .' &#8202;kg</var></p>' ;
			echo ' <p><b data-lang-id="132">Brikcoin value:</b> <var>' . $array["ecobrick_dec_brk_val"] .'&#8202;ß</var></p>' ;

			echo ' <p><b data-lang-id="133">Maker:</b> <var><i>' . $array["owner"] .'</i></var> </p>' ;
			echo ' <p><b data-lang-id="134">Sequestration:</b> <var>' . $array["sequestration_type"].'</var></p>' ;
			echo ' <p><b data-lang-id="135">Brand:</b> <var>' . $array["brand_name"] .'</var></p>' ;
			echo ' <p><b data-lang-id="136">Bottom colour:</b> ' . $array["bottom_colour"] .'</p>' ;

			echo ' <p><b data-lang-id="137">Plastic source:</b>' . $array["plastic_from"] .'</p>' ;

			echo ' <p><b data-lang-id="138">Community:</b> <var>' . $array["community_name"] .'</var></p>' ;
			echo ' <p><b data-lang-id="139">City:</b> <var>' . $array["location_city"] .'</var></p>' ;
			echo ' <p><b data-lang-id="140">Region:</b> <var>' . $array["location_region"] .'</var></p>' ;
			echo ' <p><b data-lang-id="141">Country:</b> ' . $array["location_country"] .'</p>' ;
			echo ' <p><b data-lang-id="142">Full location:</b> <var>' . $array["location_full"] .'</var></p>' ;


			echo ' <p><b data-lang-id="143">Validation:</b> ' . $array["last_validation_ts"] .'</var></p>' ;
			echo ' <p><b data-lang-id="144">Validator 1:</b> <var>' . $array["validator_1"] .'</var> </p>' ;
			echo ' <p><b data-lang-id="145">Validator 2:</b> <var>' . $array["validator_2"] .'</var> </p>' ;
			echo ' <p><b data-lang-id="146">Validator 3:</b> <var>' . $array["validator_3"] .'</var> </p>' ;
			echo ' <p><b data-lang-id="147">Validation score avg.:</b> <var>' . $array["validation_score_avg"] .'</var></p>' ;
        echo ' <p><b data-lang-id="147b">Catalyst:</b> <var>' . $array["catalyst"] .'</var></p>' ;

			echo ' <p><b data-lang-id="148">Validation score final:</b> <var>' . $array["final_validation_score"] .'</var></p>' ;
			echo ' <p><b data-lang-id="149">Authenticated weight:</b> <var> ' . $array["weight_authenticated_kg"] .'&#8202;kg</p>
			<p data-lang-id="150"> ||| END RECORD.</p>
				</div>
			</div>
			' ;
				}


} else {



echo '
<div class="splash-content-block">
		<div class="splash-box">
			<div class="splash-heading">';

			echo 'Sorry! :-(</div>
			<div class="splash-sub" data-lang-id="151x">No results for ecobrick '. $serialNo .' in the Brikchain.  Most likely this is because the Brikchain data is still in migration.</div>
		</div>
		<div class="splash-image"><img src="../webp/empty-ecobrick-450px.webp?v2" style="width: 80%; margin-top:20px;" alt="empty ecobrick"></div>
	</div>
	<div id="splash-bar"></div>

	<a name="top"></a>

	<div id="main-content">
		<div class="row">
			<div class="main">
				<br><br>



			<div class="ecobrick-data">
			<p data-lang-id="152x">🚧 The data for ecobrick '. $serialNo .' has not yet been migrated to the blockchain.  This could be because of transfer delay.  Normally publishing occurs within 30 seconds of authentication.  If more than 24hrs has passed, an error has occurred or this ecobrick was not authenticated.
				</p></div><br><br><br><br>

				<div class="page-paragraph">
				<p><h3 data-lang-id="154">The Brikchain</h3></p>

				<p data-lang-id="155">When an ecobrick is authenticated, it is published to the brikcoin manual blockchain and coins are issued according to its ecological value.  This is what we call the Brikchain.  On the Brikchain, you can find authenticated ecobricks, blocks and transactions that underpin the Brickoin complimentary currency.</p>

			<p data-lang-id="156">As a non-capital, manual process, Brikcoins favors anyone anywhere willing to work with their hands to make a meaningful ecological contribution.</p>
				<br>
				<p><a class="action-btn-blue" href="brikchain.php" data-lang-id="157">🔎 Browse the Brikchain</a></p>
				<p style="font-size: 0.85em; margin-top:20px;" data-lang-id="158">The live chain of transactions and ecobricks.</a></p>
				</div>
			</div>

			<div class="side">

		';
		}
		$gobrik_conn->close();

		?>

			<!--


            <table id="singleEcobrickTable" class="display">
                <thead>
                    <tr>
                        <th>Brikchain Record</th>
                        <th>Value</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td data-lang-id="127">Logged:</td><td><?php echo htmlspecialchars($date_logged_ts, ENT_QUOTES, 'UTF-8'); ?></td></tr>
                    <tr><td data-lang-id="128">Volume:</td><td><?php echo htmlspecialchars($volume_ml, ENT_QUOTES, 'UTF-8'); ?> ml</td></tr>
                    <tr><td data-lang-id="129">Weight:</td><td><?php echo htmlspecialchars($weight_g, ENT_QUOTES, 'UTF-8'); ?> g</td></tr>
                    <tr><td data-lang-id="130">Density:</td><td><?php echo htmlspecialchars($density, ENT_QUOTES, 'UTF-8'); ?> g/ml</td></tr>
                    <tr><td data-lang-id="131">CO2e:</td><td><?php echo htmlspecialchars($CO2_kg, ENT_QUOTES, 'UTF-8'); ?> kg</td></tr>
                    <tr><td data-lang-id="132">Brikcoin value:</td><td><?php echo htmlspecialchars($ecobrick_dec_brk_val, ENT_QUOTES, 'UTF-8'); ?> ß</td></tr>
                    <tr><td data-lang-id="133">Maker:</td><td><?php echo htmlspecialchars($owner, ENT_QUOTES, 'UTF-8'); ?></td></tr>
                    <tr><td data-lang-id="134">Sequestration:</td><td><?php echo htmlspecialchars($sequestration_type, ENT_QUOTES, 'UTF-8'); ?></td></tr>
                    <tr><td data-lang-id="135">Brand:</td><td><?php echo htmlspecialchars($brand_name, ENT_QUOTES, 'UTF-8'); ?></td></tr>
                    <tr><td data-lang-id="136">Bottom colour:</td><td><?php echo htmlspecialchars($bottom_colour, ENT_QUOTES, 'UTF-8'); ?></td></tr>
                    <tr><td data-lang-id="137">Plastic source:</td><td><?php echo htmlspecialchars($plastic_from, ENT_QUOTES, 'UTF-8'); ?></td></tr>
                    <tr><td data-lang-id="138">Community:</td><td><?php echo htmlspecialchars($community_name, ENT_QUOTES, 'UTF-8'); ?></td></tr>
                    <tr><td data-lang-id="139">City:</td><td><?php echo htmlspecialchars($location_city, ENT_QUOTES, 'UTF-8'); ?></td></tr>
                    <tr><td data-lang-id="140">Region:</td><td><?php echo htmlspecialchars($location_region, ENT_QUOTES, 'UTF-8'); ?></td></tr>
                    <tr><td data-lang-id="141">Country:</td><td><?php echo htmlspecialchars($location_country, ENT_QUOTES, 'UTF-8'); ?></td></tr>
                    <tr><td data-lang-id="142">Full location:</td><td><?php echo htmlspecialchars($location_full, ENT_QUOTES, 'UTF-8'); ?></td></tr>
                    <tr><td data-lang-id="143">Validation:</td><td><?php echo htmlspecialchars($last_validation_ts, ENT_QUOTES, 'UTF-8'); ?></td></tr>
                    <tr><td data-lang-id="144">Validator 1:</td><td><?php echo htmlspecialchars($validator_1, ENT_QUOTES, 'UTF-8'); ?></td></tr>
                    <tr><td data-lang-id="145">Validator 2:</td><td><?php echo htmlspecialchars($validator_2, ENT_QUOTES, 'UTF-8'); ?></td></tr>
                    <tr><td data-lang-id="146">Validator 3:</td><td><?php echo htmlspecialchars($validator_3, ENT_QUOTES, 'UTF-8'); ?></td></tr>
                    <tr><td data-lang-id="147">Validation score avg.:</td><td><?php echo htmlspecialchars($validation_score_avg, ENT_QUOTES, 'UTF-8'); ?></td></tr>
                    <tr><td data-lang-id="147b">Catalyst:</td><td><?php echo htmlspecialchars($catalyst, ENT_QUOTES, 'UTF-8'); ?></td></tr>
                    <tr><td data-lang-id="148">Validation score final:</td><td><?php echo htmlspecialchars($final_validation_score, ENT_QUOTES, 'UTF-8'); ?></td></tr>
                    <tr><td data-lang-id="149">Authenticated weight:</td><td><?php echo htmlspecialchars($weight_authenticated_kg, ENT_QUOTES, 'UTF-8'); ?> kg</td></tr>
                </tbody>
            </table>

-->



</div>

</div>
</div>
</div>

<!--FOOTER STARTS HERE-->
<?php require_once ("../footer-2025.php");?>


<script>
    $(document).ready(function() {
        $('#singleEcobrickTable').DataTable({
            "paging": false,
            "searching": false,
            "info": true,
            "ordering": true // Disable ordering if desired
        });
    });
</script>
</body>
</html>
