<?php
require_once '../earthenAuth_helper.php';
startSecureSession();

if (!isset($_SESSION['buwana_id'])) {
    header('Location: login.php');
    exit();
}

$buwana_id = $_SESSION['buwana_id'];

require_once '../gobrikconn_env.php';
require_once '../buwanaconn_env.php';

// Ensure POST request
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: log.php");
    exit();
}

try {
    // STEP 1: Generate ecobrick serial and ID
    $ecobrick_unique_id = 0; // Default ID for new ecobricks
    if (!empty($_POST['retry_id'])) {
        $ecobrick_unique_id = (int)$_POST['retry_id'];
    }

    $ids = setSerialNumber($gobrik_conn, $ecobrick_unique_id);
    $ecobrick_unique_id = $ids['ecobrick_unique_id'];
    $serial_no = $ids['serial_no'];

    // STEP 2: Gather form inputs
    $ecobricker_maker = trim($_POST['ecobricker_maker']);
    $volume_ml = (int)trim($_POST['volume_ml']);
    $weight_g = (int)trim($_POST['weight_g']);
    $sequestration_type = trim($_POST['sequestration_type']);
    $plastic_from = trim($_POST['plastic_from']);
    $brand_name = trim($_POST['brand_name']);
    $location_full = trim($_POST['location_full']);
    $bottom_colour = trim($_POST['bottom_colour']);
    $location_lat = (float)trim($_POST['latitude']);
    $location_long = (float)trim($_POST['longitude']);
    $location_watershed = trim($_POST['location_watershed']);
    $community_name = trim($_POST['community_select']);

    // STEP 3: Derived values
    $user_ecobricker_id = getEcobrickerID($buwana_id);
    $maker_id = $user_ecobricker_id;
    $owner = $ecobricker_maker;
    $status = "not ready";
    $density = $weight_g / $volume_ml;
    $universal_volume_ml = $volume_ml;
    $date_logged_ts = date("Y-m-d H:i:s");
    $date_published_ts = $date_logged_ts;
    $CO2_kg = ($weight_g * 6.1) / 1000;
    $last_ownership_change = date("Y-m-d");
    $actual_maker_name = $ecobricker_maker;
    $brik_notes = "Directly logged on beta.GoBrik.com";

    // STEP 4: Get country_id
    $country_id = null;
    $location_parts = explode(',', $location_full);
    $country_name = trim(end($location_parts));

    $stmt_country = $gobrik_conn->prepare("SELECT country_id FROM countries_tb WHERE country_name = ?");
    if ($stmt_country) {
        $stmt_country->bind_param('s', $country_name);
        $stmt_country->execute();
        $stmt_country->bind_result($country_id);
        $stmt_country->fetch();
        $stmt_country->close();
    }

// STEP 5: Get community_id directly from the form, fallback to DB lookup if missing
$community_id = isset($_POST['community_id']) ? (int)$_POST['community_id'] : 0;

if ($community_id === 0 && !empty($community_name)) {
    $stmt_community = $buwana_conn->prepare("SELECT community_id FROM communities_tb WHERE com_name = ?");
    if ($stmt_community) {
        $stmt_community->bind_param("s", $community_name);
        $stmt_community->execute();
        $stmt_community->bind_result($fetched_id);
        if ($stmt_community->fetch()) {
            $community_id = $fetched_id;
        }
        $stmt_community->close();
    }
}


    // STEP 6: Check for existing ecobrick
    $existing_count = 0;
    $stmt_check = $gobrik_conn->prepare("SELECT COUNT(*) FROM tb_ecobricks WHERE ecobrick_unique_id = ?");
    $stmt_check->bind_param('i', $ecobrick_unique_id);
    $stmt_check->execute();
    $stmt_check->bind_result($existing_count);
    $stmt_check->fetch();
    $stmt_check->close();

    // STEP 7: Update or insert
    if ($existing_count > 0) {
        // UPDATE
        $sql = "UPDATE tb_ecobricks
                SET ecobricker_maker=?, volume_ml=?, weight_g=?, sequestration_type=?,
                    plastic_from=?, location_full=?, bottom_colour=?, location_lat=?, location_long=?,
                    brand_name=?, owner=?, status=?, universal_volume_ml=?, density=?, date_logged_ts=?,
                    CO2_kg=?, last_ownership_change=?, actual_maker_name=?, brik_notes=?, date_published_ts=?,
                    location_watershed=?, community_id=?, country_id=?
                WHERE ecobrick_unique_id=?";
        $stmt = $gobrik_conn->prepare($sql);
        $stmt->bind_param(
            "siissssddsssidsdsssssiii",
            $ecobricker_maker, $volume_ml, $weight_g, $sequestration_type,
            $plastic_from, $location_full, $bottom_colour, $location_lat, $location_long,
            $brand_name, $owner, $status, $universal_volume_ml, $density, $date_logged_ts,
            $CO2_kg, $last_ownership_change, $actual_maker_name, $brik_notes, $date_published_ts,
            $location_watershed, $community_id, $country_id, $ecobrick_unique_id
        );
    } else {
        // INSERT
        $sql = "INSERT INTO tb_ecobricks (
                    ecobrick_unique_id, serial_no, ecobricker_maker, volume_ml, weight_g, sequestration_type,
                    plastic_from, location_full, bottom_colour, location_lat, location_long, brand_name, owner, status,
                    universal_volume_ml, density, date_logged_ts, CO2_kg, last_ownership_change,
                    actual_maker_name, brik_notes, date_published_ts, location_watershed, community_id, country_id, maker_id
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $gobrik_conn->prepare($sql);
        $stmt->bind_param(
            "issiissssddsssidsdsssssiis",
            $ecobrick_unique_id, $serial_no, $ecobricker_maker, $volume_ml, $weight_g,
            $sequestration_type, $plastic_from, $location_full, $bottom_colour, $location_lat, $location_long,
            $brand_name, $owner, $status, $universal_volume_ml, $density, $date_logged_ts,
            $CO2_kg, $last_ownership_change, $actual_maker_name, $brik_notes, $date_published_ts,
            $location_watershed, $community_id, $country_id, $maker_id
        );
    }

    // STEP 8: Execute query
    if ($stmt === false) {
        throw new Exception("Error preparing SQL statement: " . $gobrik_conn->error);
    }

    if ($stmt->execute()) {
        $stmt->close();
        $gobrik_conn->close();
        header("Location: log-2.php?id=" . urlencode($serial_no));
        exit();
    } else {
        throw new Exception("Database error: " . $stmt->error);
    }

} catch (Exception $e) {
    error_log("Log Process Error: " . $e->getMessage());
    echo "An error occurred: " . htmlspecialchars($e->getMessage());
}
