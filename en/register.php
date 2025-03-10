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
$training_type = $training_country = $training_location = "";
$training_url = "";
$first_name = "";

// Check if the user is logged in
if ($is_logged_in) {
    $buwana_id = $_SESSION['buwana_id'];

    // Include database connection
    require_once '../gobrikconn_env.php';
    require_once '../buwanaconn_env.php';

    // Fetch the user's first name
    $first_name = getFirstName($buwana_conn, $buwana_id);

    $buwana_conn->close();  // Close the database connection
}

// Connect to GoBrik database and fetch training details
require_once '../gobrikconn_env.php';

$training_id = 818; // Specific training record to fetch
$sql = "SELECT training_title, training_date, lead_trainer, training_type, training_country, training_location, training_url FROM `tb_trainings` WHERE `training_id` = ?";
$stmt = $gobrik_conn->prepare($sql);
$stmt->bind_param("i", $training_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $training_title = htmlspecialchars($row['training_title'], ENT_QUOTES, 'UTF-8');
    $training_date = htmlspecialchars($row['training_date'], ENT_QUOTES, 'UTF-8');
    $lead_trainer = htmlspecialchars($row['lead_trainer'], ENT_QUOTES, 'UTF-8');
    $training_type = htmlspecialchars($row['training_type'], ENT_QUOTES, 'UTF-8');
    $training_country = htmlspecialchars($row['training_country'], ENT_QUOTES, 'UTF-8');
    $training_location = htmlspecialchars($row['training_location'], ENT_QUOTES, 'UTF-8');
    $training_url = htmlspecialchars($row['training_url'], ENT_QUOTES, 'UTF-8');
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
<div id="top-page-image" class="gea-logo top-page-image">
    <img src="../photos/events/terraces-forests-gladys.jpg" style="width:100%;" id="event-lead-photo" alt="Event Lead Photo">
</div>

<div id="form-submission-box" class="landing-page-form">
    <div class="form-container">
        <div style="text-align:center;width:100%;margin:auto;margin-top:25px;">
            <p style="font-size:small"><?php echo $training_type; ?></p>
            <p style="font-size:medium"><strong><?php echo $training_date; ?></strong></p>

            <h2><?php echo $training_title; ?></h2>
            <h3 style="font-size:medium">Lead by <?php echo $lead_trainer; ?></h3>

            <button id="rsvp-button" class="confirm-button enabled" style="margin-top: 20px; font-size: 1.2em; padding: 10px 20px; cursor: pointer;" href="<?php echo $training_url; ?>">
                <?php echo $is_logged_in ? "RSVP as " . htmlspecialchars($first_name, ENT_QUOTES, 'UTF-8') : "RSVP"; ?>
            </button>

            <?php if (!$is_logged_in) : ?>
                <p>To RSVP, you'll need to log in with your GoBrik account or sign up for an account.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<div id="offset-learn-more" class="dashboard-panel">
    <h3>Community Event Details</h3>
    <p><strong>Training Title:</strong> <?php echo $training_title; ?></p>
    <p><strong>Training Date:</strong> <?php echo $training_date; ?></p>
    <p><strong>Lead Trainer(s):</strong> <?php echo $lead_trainer; ?></p>
    <p><strong>Training Type:</strong> <?php echo $training_type; ?></p>
    <p><strong>Training Country:</strong> <?php echo $training_country; ?></p>
    <p><strong>Training Location:</strong> <?php echo $training_location; ?></p>
</div>

<!-- FOOTER -->
<?php require_once("../footer-2024.php"); ?>

</body>
</html>
