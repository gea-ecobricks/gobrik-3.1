<?php
require_once '../earthenAuth_helper.php'; // Include authentication helper functions

// Set page variables
$lang = basename(dirname($_SERVER['SCRIPT_NAME']));
$version = '0.1';
$page = 'register';
$lastModified = date("Y-m-d\TH:i:s\Z", filemtime(__FILE__));
$is_logged_in = isLoggedIn(); // Check if the user is logged in

// Initialize training variables
$training_title = $training_date = $training_logged = $lead_trainer = "";
$trained_community = $training_type = $briks_made = $avg_brik_weight = $est_plastic_packed = "";
$training_country = $training_location = $location_full = $training_summary = "";
$training_agenda = $training_success = $training_challenges = $training_lessons_learned = "";
$training_url = $connected_ecobricks = "";
$ready_to_show = 0;

// Check if the user is logged in
if ($is_logged_in) {
    $buwana_id = $_SESSION['buwana_id'];

    // Include database connection
    require_once '../gobrikconn_env.php';
    require_once '../buwanaconn_env.php';

    // Fetch the user's location data
    $user_continent_icon = getUserContinent($buwana_conn, $buwana_id);
    $user_location_watershed = getWatershedName($buwana_conn, $buwana_id);
    $user_location_full = getUserFullLocation($buwana_conn, $buwana_id);
    $gea_status = getGEA_status($buwana_id);
    $user_community_name = getCommunityName($buwana_conn, $buwana_id);
    $first_name = getFirstName($buwana_conn, $buwana_id);

    $buwana_conn->close();  // Close the database connection
}

// Connect to GoBrik database and fetch training details
require_once '../gobrikconn_env.php';

$training_id = 818; // Specific training record to fetch
$sql = "SELECT * FROM `tb_trainings` WHERE `training_id` = ?";
$stmt = $gobrik_conn->prepare($sql);
$stmt->bind_param("i", $training_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $training_title = htmlspecialchars($row['training_title'], ENT_QUOTES, 'UTF-8');
    $training_date = htmlspecialchars($row['training_date'], ENT_QUOTES, 'UTF-8');
    $training_logged = htmlspecialchars($row['training_logged'], ENT_QUOTES, 'UTF-8');
    $lead_trainer = htmlspecialchars($row['lead_trainer'], ENT_QUOTES, 'UTF-8');
    $trained_community = htmlspecialchars($row['trained_community'], ENT_QUOTES, 'UTF-8');
    $training_type = htmlspecialchars($row['training_type'], ENT_QUOTES, 'UTF-8');
    $briks_made = $row['briks_made'];
    $avg_brik_weight = $row['avg_brik_weight'];
    $est_plastic_packed = $row['est_plastic_packed'];
    $training_country = htmlspecialchars($row['training_country'], ENT_QUOTES, 'UTF-8');
    $training_location = htmlspecialchars($row['training_location'], ENT_QUOTES, 'UTF-8');
    $location_full = htmlspecialchars($row['location_full'], ENT_QUOTES, 'UTF-8');
    $training_summary = nl2br(htmlspecialchars($row['training_summary'], ENT_QUOTES, 'UTF-8'));
    $training_agenda = nl2br(htmlspecialchars($row['training_agenda'], ENT_QUOTES, 'UTF-8'));
    $training_success = nl2br(htmlspecialchars($row['training_success'], ENT_QUOTES, 'UTF-8'));
    $training_challenges = nl2br(htmlspecialchars($row['training_challenges'], ENT_QUOTES, 'UTF-8'));
    $training_lessons_learned = nl2br(htmlspecialchars($row['training_lessons_learned'], ENT_QUOTES, 'UTF-8'));
    $training_url = htmlspecialchars($row['training_url'], ENT_QUOTES, 'UTF-8');
    $connected_ecobricks = nl2br(htmlspecialchars($row['connected_ecobricks'], ENT_QUOTES, 'UTF-8'));
    $ready_to_show = $row['ready_to_show'];
}

$stmt->close();
$gobrik_conn->close();

echo '<!DOCTYPE html>
<html lang="' . $lang . '">
<head>
<meta charset="UTF-8">
<title>' . $training_title . '</title>
';
?>

<!-- Page CSS & JS Initialization -->
<?php require_once("../includes/register-inc.php"); ?>

    <div class="splash-title-block"></div>
    <div id="splash-bar"></div>

    <!-- PAGE CONTENT -->
    <div id="top-page-image" class="gea-logo top-page-image"></div>

    <div id="form-submission-box" class="landing-page-form">
        <div class="form-container">
            <div style="text-align:center;width:100%;margin:auto;margin-top:25px;">
                <p style="font-size:smaller"><?php echo $training_type; ?></small>
                <h1><?php echo $training_title; ?></h1>
                <p style="font-size:medium"><strong><?php echo $training_date; ?></strong>
                <p style="font-size:medium">Lead by <?php echo $lead_trainer; ?></small></p>
                <p><?php echo $training_summary; ?></p>

                <?php if (!empty($training_url)) : ?>
                    <p><a href="<?php echo $training_url; ?>" target="_blank">Join the Event</a></p>
                <?php endif; ?>
            </div>
        </div>

        <div id="offset-learn-more" class="dashboard-panel">
            <h2>Training Details</h2>
            <p><strong>Location:</strong> <?php echo $training_location; ?> (<?php echo $training_country; ?>)</p>
            <p><strong>Training Agenda:</strong> <?php echo $training_agenda; ?></p>

            <?php if (!empty($training_success)) : ?>
                <p><strong>Training Success:</strong> <?php echo $training_success; ?></p>
            <?php endif; ?>

            <?php if (!empty($training_challenges)) : ?>
                <p><strong>Challenges:</strong> <?php echo $training_challenges; ?></p>
            <?php endif; ?>

            <?php if (!empty($training_lessons_learned)) : ?>
                <p><strong>Lessons Learned:</strong> <?php echo $training_lessons_learned; ?></p>
            <?php endif; ?>

            <p><strong>Briks Made:</strong> <?php echo $briks_made; ?></p>
            <p><strong>Avg Brik Weight:</strong> <?php echo $avg_brik_weight; ?>g</p>
            <p><strong>Plastic Packed:</strong> <?php echo $est_plastic_packed; ?>g</p>
        </div>

    <!-- FOOTER -->
    <?php require_once("../footer-2024.php"); ?>

</body>
</html>
