<?php
require_once '../earthenAuth_helper.php'; // Include authentication helper functions

// Set page variables
$lang = basename(dirname($_SERVER['SCRIPT_NAME']));
$version = '0.1';
$page = 'register';
$lastModified = date("Y-m-d\TH:i:s\Z", filemtime(__FILE__));
$is_logged_in = isLoggedIn(); // Check if the user is logged in

// Initialize training variables
$training_id = 818; // Specific training record to fetch
$training_title = $training_date = $lead_trainer = "";
$training_type = $training_country = $training_location = "";
$training_url = "";
$first_name = "";
$ecobricker_id = null;

// Check if the user is logged in
if ($is_logged_in) {
    $buwana_id = $_SESSION['buwana_id'];

    // Include database connection
    require_once '../gobrikconn_env.php';
    require_once '../buwanaconn_env.php';

    // Fetch the user's data
    $user_continent_icon = getUserContinent($buwana_conn, $buwana_id);
    $user_location_watershed = getWatershedName($buwana_conn, $buwana_id);
    $user_location_full = getUserFullLocation($buwana_conn, $buwana_id);
    $gea_status = getGEA_status($buwana_id);
    $user_community_name = getCommunityName($buwana_conn, $buwana_id);
    $first_name = getFirstName($buwana_conn, $buwana_id);

    // Fetch ecobricker_id using buwana_id
    $sql = "SELECT ecobricker_id FROM tb_ecobrickers WHERE buwana_id = ?";
    $stmt = $gobrik_conn->prepare($sql);
    $stmt->bind_param("i", $buwana_id);
    $stmt->execute();
    $stmt->bind_result($ecobricker_id);
    $stmt->fetch();
    $stmt->close();

    $buwana_conn->close();  // Close the database connection
}

// Connect to GoBrik database and fetch training details
require_once '../gobrikconn_env.php';

$sql = "SELECT * FROM `tb_trainings` WHERE `training_id` = ?";
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

            <!-- RSVP Button -->
            <button id="rsvp-button" class="confirm-button enabled" style="margin-top: 20px; font-size: 1.2em; padding: 10px 20px; cursor: pointer;">
                <?php echo $is_logged_in ? "✅ RSVP as " . htmlspecialchars($first_name, ENT_QUOTES, 'UTF-8') : "✅ RSVP"; ?>
            </button>

            <!-- JavaScript to handle RSVP click -->
            <script>
                document.getElementById("rsvp-button").addEventListener("click", function() {
                    <?php if ($is_logged_in && $ecobricker_id): ?>
                        // Redirect logged-in users to registration-confirmed.php with training_id & ecobricker_id
                        window.location.href = "registration-confirmed.php?training_id=<?php echo $training_id; ?>&ecobricker_id=<?php echo $ecobricker_id; ?>";
                    <?php else: ?>
                        // Show login modal for non-logged-in users
                        openInfoModal("To RSVP for the Community Event, please log in with your GoBrik Buwana account.",
                            '<a href="login.php?redirect=register.php" class="modal-button">🔑 Log In</a>' +
                            '<a href="signup.php" class="modal-button">📝 Sign Up</a>');
                    <?php endif; ?>
                });

                // Function to open modal
                function openInfoModal(message, buttons) {
                    document.getElementById("modal-content-box").innerHTML = '<p>' + message + '</p>' + buttons;
                    document.getElementById("form-modal-message").classList.remove("modal-hidden");
                }


            </script>
        </div>
    </div>
</div>

<!-- Community Event Details -->
<div id="offset-learn-more" class="dashboard-panel">
    <h3>Community Event Details</h3>
    <hr>
    <p><strong>Title:</strong> <?php echo $training_title; ?></p>
    <p><strong>Date:</strong> <?php echo $training_date; ?></p>
    <p><strong>Lead Trainer(s):</strong> <?php echo $lead_trainer; ?></p>
    <p><strong>Training Type:</strong> <?php echo $training_type; ?></p>
    <p><strong>Country:</strong> <?php echo $training_country; ?></p>
    <p><strong>Location:</strong> <?php echo $training_location; ?></p>
</div>



<!-- FOOTER -->
<?php require_once("../footer-2024.php"); ?>

</body>
</html>

