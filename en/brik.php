<?php
require_once '../earthenAuth_helper.php'; // Include the authentication helper functions

// Set page variables
$lang = basename(dirname($_SERVER['SCRIPT_NAME']));
$version = '1.4';
$page = 'brik';
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

echo '

    <div id="form-submission-box" style="margin-top:80px;">
        <div class="form-container-v2" style="padding-top:0px !important">';

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
            $isAwaitingValidation = ($status === 'awaiting validations');
            $isRejected = (strpos($status, 'reject') !== false);
            $statusClass = 'status-waiting';
            $statusLabel = '🕙 Waiting for validations';

            if ($isRejected) {
                $statusClass = 'status-rejected';
                $statusLabel = '⛔ Rejected';
            }

            $isAuthenticated = ($status === "authenticated");

            $hasSelfie = !empty($selfie_photo_url);

            if ($isAuthenticated) {
                $statusClass = 'status-authenticated';
                $statusLabel = '✅ Authenticated';
            }

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
                        <div class="brik-status brik-status-pill ' . $statusClass . '">' . $statusLabel . '</div>
                    </div>
                    <div class="brik-image">
                        <a class="brik-image-link" href="javascript:void(0);" onclick="openViewEcobricV2(window.brikPreviewData, window.brikPreviewData.ecobrick_full_photo_url)">
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
                    <div class="brik-status brik-status-pill ' . $statusClass . '">' . $statusLabel . '</div>
                </div>
                <div class="brik-image">
                    <a class="brik-image-link" href="javascript:void(0);" onclick="openViewEcobricV2(window.brikPreviewData, window.brikPreviewData.ecobrick_full_photo_url)">
                        <img src="../' . htmlspecialchars($ecobrick_full_photo_url, ENT_QUOTES, 'UTF-8') . '?v=' . htmlspecialchars($photo_version, ENT_QUOTES, 'UTF-8') . '" alt="Ecobrick ' . htmlspecialchars($serial_no, ENT_QUOTES, 'UTF-8') . ' was made in ' . htmlspecialchars($location_full, ENT_QUOTES, 'UTF-8') . ' and logged on ' . htmlspecialchars($date_logged_ts, ENT_QUOTES, 'UTF-8') . '" title="Ecobrick Serial ' . htmlspecialchars($serial_no, ENT_QUOTES, 'UTF-8') . ' was made in ' . htmlspecialchars($location_full, ENT_QUOTES, 'UTF-8') . '.">
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
if (!$isAuthenticated) {
    if ($isAwaitingValidation) {
        echo '
        <div class="row-details">
            <p>This ecobrick was logged on ' . htmlspecialchars($date_logged_ts, ENT_QUOTES, 'UTF-8') . '. It is pending review and authentication.</p>
        </div>';
    } elseif ($isRejected) {
        echo '
        <div class="row-details">
            <p>This ecobrick was logged on ' . htmlspecialchars($date_logged_ts, ENT_QUOTES, 'UTF-8') . '. This ecobrick has been rejected for authentication.  It has not met the criteria of ecobrick sequestered plastic.  For the criteria of authenticated sequestered plastic see <a href="https://ecobricks.org/en/what.php" target="_blank">ecobricks.org/en/what</a>.</p>
        </div>';
    }
}


// EXPLANATION
echo '<div class="details-layout ' . ($hasSelfie ? 'has-selfie' : 'no-selfie') . '">';

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

// Show peer-review notice only while awaiting validation
if ($isAwaitingValidation) {
    echo '<p>This ecobrick has not yet been peer-reviewed. Its plastic has not been authenticated as sequestered.</p>';
}

echo '</div>'; // Close main-details div

// IF THERE'S A SELFIE IT GOES HERE
if ($hasSelfie) {
    echo '<div class="side-details">
            <a class="side-selfie-link" href="javascript:void(0);" onclick="openViewEcobricV2(window.brikPreviewData, window.brikPreviewData.selfie_photo_url)">
                <img src="' . htmlspecialchars($selfie_photo_url, ENT_QUOTES, 'UTF-8') . '?v=' . htmlspecialchars($photo_version, ENT_QUOTES, 'UTF-8') . '" alt="Ecobrick selfie for serial ' . htmlspecialchars($serial_no, ENT_QUOTES, 'UTF-8') . '">
            </a>
          </div>';
}

echo '</div>'; // Close details-layout


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

                        if (!empty($array["date_logged_ts"])) {
                            echo ' <p><b data-lang-id="127">Logged:</b> ' . htmlspecialchars($array["date_logged_ts"], ENT_QUOTES, 'UTF-8') . '</p>';
                        }
                        if (!empty($array["volume_ml"])) {
                            echo ' <p><b data-lang-id="128">Volume:</b> <var>' . htmlspecialchars($array["volume_ml"], ENT_QUOTES, 'UTF-8') . ' &#8202;ml</var></p>';
                        }
                        if (!empty($array["weight_g"])) {
                            echo ' <p><b data-lang-id="129">Weight:</b> <var>' . htmlspecialchars($array["weight_g"], ENT_QUOTES, 'UTF-8') . '&#8202;g</var></p>';
                        }
                        if (!empty($array["density"])) {
                            echo ' <p><b data-lang-id="130">Density:</b> <var>' . htmlspecialchars($array["density"], ENT_QUOTES, 'UTF-8') . '&#8202;g/ml</var></p>';
                        }
                        if (!empty($array["CO2_kg"])) {
                            echo ' <p><b data-lang-id="131">CO2e:</b><var>' . htmlspecialchars($array["CO2_kg"], ENT_QUOTES, 'UTF-8') . ' &#8202;kg</var></p>';
                        }
                        if (!empty($array["ecobrick_dec_brk_val"])) {
                            echo ' <p><b data-lang-id="132">Brikcoin value:</b> <var>' . htmlspecialchars($array["ecobrick_dec_brk_val"], ENT_QUOTES, 'UTF-8') . '&#8202;ß</var></p>';
                        }

                        if (!empty($array["owner"])) {
                            echo ' <p><b data-lang-id="133">Maker:</b> <var><i>' . htmlspecialchars($array["owner"], ENT_QUOTES, 'UTF-8') . '</i></var> </p>';
                        }
                        if (!empty($array["sequestration_type"])) {
                            echo ' <p><b data-lang-id="134">Sequestration:</b> <var>' . htmlspecialchars($array["sequestration_type"], ENT_QUOTES, 'UTF-8') . '</var></p>';
                        }
                        if (!empty($array["brand_name"])) {
                            echo ' <p><b data-lang-id="135">Brand:</b> <var>' . htmlspecialchars($array["brand_name"], ENT_QUOTES, 'UTF-8') . '</var></p>';
                        }
                        if (!empty($array["bottom_colour"])) {
                            echo ' <p><b data-lang-id="136">Bottom colour:</b> ' . htmlspecialchars($array["bottom_colour"], ENT_QUOTES, 'UTF-8') . '</p>';
                        }

                        if (!empty($array["plastic_from"])) {
                            echo ' <p><b data-lang-id="137">Plastic source:</b>' . htmlspecialchars($array["plastic_from"], ENT_QUOTES, 'UTF-8') . '</p>';
                        }

                        if (!empty($array["community_name"])) {
                            echo ' <p><b data-lang-id="138">Community:</b> <var>' . htmlspecialchars($array["community_name"], ENT_QUOTES, 'UTF-8') . '</var></p>';
                        }
                        if (!empty($array["location_city"])) {
                            echo ' <p><b data-lang-id="139">City:</b> <var>' . htmlspecialchars($array["location_city"], ENT_QUOTES, 'UTF-8') . '</var></p>';
                        }
                        if (!empty($array["location_region"])) {
                            echo ' <p><b data-lang-id="140">Region:</b> <var>' . htmlspecialchars($array["location_region"], ENT_QUOTES, 'UTF-8') . '</var></p>';
                        }
                        if (!empty($array["location_country"])) {
                            echo ' <p><b data-lang-id="141">Country:</b> ' . htmlspecialchars($array["location_country"], ENT_QUOTES, 'UTF-8') . '</p>';
                        }
                        if (!empty($array["location_full"])) {
                            echo ' <p><b data-lang-id="142">Full location:</b> <var>' . htmlspecialchars($array["location_full"], ENT_QUOTES, 'UTF-8') . '</var></p>';
                        }


                        if (!empty($array["last_validation_ts"])) {
                            echo ' <p><b data-lang-id="143">Validation:</b> ' . htmlspecialchars($array["last_validation_ts"], ENT_QUOTES, 'UTF-8') . '</var></p>';
                        }
                        if (!empty($array["validator_1"])) {
                            echo ' <p><b data-lang-id="144">Validator 1:</b> <var>' . htmlspecialchars($array["validator_1"], ENT_QUOTES, 'UTF-8') . '</var> </p>';
                        }
                        if (!empty($array["validator_2"])) {
                            echo ' <p><b data-lang-id="145">Validator 2:</b> <var>' . htmlspecialchars($array["validator_2"], ENT_QUOTES, 'UTF-8') . '</var> </p>';
                        }
                        if (!empty($array["validator_3"])) {
                            echo ' <p><b data-lang-id="146">Validator 3:</b> <var>' . htmlspecialchars($array["validator_3"], ENT_QUOTES, 'UTF-8') . '</var> </p>';
                        }
                        if (!empty($array["validation_score_avg"])) {
                            echo ' <p><b data-lang-id="147">Validation score avg.:</b> <var>' . htmlspecialchars($array["validation_score_avg"], ENT_QUOTES, 'UTF-8') . '</var></p>';
                        }
                        if (!empty($array["catalyst"])) {
                            echo ' <p><b data-lang-id="147b">Catalyst:</b> <var>' . htmlspecialchars($array["catalyst"], ENT_QUOTES, 'UTF-8') . '</var></p>';
                        }

                        if (!empty($array["final_validation_score"])) {
                            echo ' <p><b data-lang-id="148">Validation score final:</b> <var>' . htmlspecialchars($array["final_validation_score"], ENT_QUOTES, 'UTF-8') . '</var></p>';
                        }
                        if (!empty($array["weight_authenticated_kg"])) {
                            echo ' <p><b data-lang-id="149">Authenticated weight:</b> <var> ' . htmlspecialchars($array["weight_authenticated_kg"], ENT_QUOTES, 'UTF-8') . '&#8202;kg</var></p>';
                        }

                        echo ' <p data-lang-id="150"> ||| END RECORD.</p>
                                </div>
                        </div>
                        ';
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

	<div id="main-content-v2">
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

                <?php if (!empty($serial_no)) { ?>
                <script>
                    if (typeof getStatusClassName !== 'function') {
                        function getStatusClassName(statusText = '') {
                            const normalized = statusText.toLowerCase();

                            if (normalized.includes('auth')) return 'status-authenticated';
                            if (normalized.includes('await') || normalized.includes('wait')) return 'status-awaiting';
                            if (normalized.includes('reject')) return 'status-rejected';

                            return 'status-default';
                        }
                    }

                    if (typeof applyStatusPill !== 'function') {
                        function applyStatusPill(pillElement, statusText) {
                            if (!pillElement) return;

                            const baseClass = pillElement.classList.contains('ecobrick-status-pill')
                                ? 'ecobrick-status-pill status-pill'
                                : 'modal-status-pill status-pill';
                            const statusClass = getStatusClassName(statusText);

                            pillElement.className = `${baseClass} ${statusClass}`;
                            pillElement.textContent = statusText || 'Status unknown';
                            pillElement.style.display = 'inline-flex';
                        }
                    }

                    if (typeof openViewEcobricV2 !== 'function') {
                        function openViewEcobricV2(brickData, photoOverride = '') {
                            if (!brickData) return;

                            const modal = document.getElementById('form-modal-message-v2');
                            const photoContainer = modal?.querySelector('.modal-photo-v2');
                            const messageContainer = modal?.querySelector('.modal-message-v2');
                            const modalStatusPill = modal?.querySelector('.modal-status-pill');
                            const modalViewButton = modal?.querySelector('.modal-view-button');

                            if (!modal || !photoContainer || !messageContainer) return;

                            photoContainer.replaceChildren();
                            messageContainer.replaceChildren();

                            const photoWrapper = document.createElement('div');
                            photoWrapper.className = 'ecobrick-photo-wrapper';

                            const img = document.createElement('img');
                            const versionSuffix = brickData.photo_version ? `?v=${brickData.photo_version}` : '';
                            const photoSrc = photoOverride || brickData.selfie_photo_url || brickData.ecobrick_full_photo_url;

                            if (!photoSrc) return;

                            img.src = `${photoSrc}${versionSuffix}`;
                            img.alt = `Ecobrick photo for serial ${brickData.serial_no || ''}`;

                            photoWrapper.appendChild(img);
                            photoContainer.appendChild(photoWrapper);

                            const metaWrapper = document.createElement('div');
                            metaWrapper.className = 'ecobrick-meta-v2';

                            const details = document.createElement('p');
                            const weightTxt = brickData.weight_g ? `${Number(brickData.weight_g).toLocaleString()} gram` : 'an unknown weight';
                            const makerTxt = brickData.ecobricker_maker || 'an unknown maker';
                            const locationTxt = brickData.location_display || 'an undisclosed location';
                            const serialTxt = brickData.serial_no || 'an unlisted serial';
                            details.textContent = `This ${weightTxt} ecobrick ${serialTxt} was made by ${makerTxt} in ${locationTxt}.`;
                            metaWrapper.appendChild(details);

                            photoContainer.appendChild(metaWrapper);

                            const viewHref = `brik.php?serial_no=${encodeURIComponent(brickData.serial_no || '')}`;
                            if (modalViewButton) {
                                modalViewButton.href = viewHref;
                                modalViewButton.setAttribute('aria-label', `Open ecobrick ${brickData.serial_no || ''} details`);
                                modalViewButton.style.display = 'inline-flex';
                            }

                            applyStatusPill(modalStatusPill, brickData.status);

                            modal.classList.remove('modal-hidden');
                            modal.classList.add('modal-shown');

                            document.getElementById('page-content')?.classList.add('blurred');
                            document.getElementById('footer-full')?.classList.add('blurred');
                            document.body.classList.add('modal-open');
                        }
                    }

                    window.brikPreviewData = {
                        serial_no: <?php echo json_encode($serial_no); ?>,
                        weight_g: <?php echo json_encode($weight_g); ?>,
                        ecobricker_maker: <?php echo json_encode($owner); ?>,
                        location_display: <?php echo json_encode($location_full); ?>,
                        status: <?php echo json_encode($statusLabel); ?>,
                        selfie_photo_url: <?php echo json_encode($hasSelfie ? $selfie_photo_url : ''); ?>,
                        ecobrick_full_photo_url: <?php echo json_encode(!empty($ecobrick_full_photo_url) ? '../' . $ecobrick_full_photo_url : ''); ?>,
                        photo_version: <?php echo json_encode($photo_version); ?>
                    };
                </script>
                <?php } ?>

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
<?php require_once ("../footer-2026.php");?>


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
