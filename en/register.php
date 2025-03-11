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
                <p style="font-size:small"><?php echo $training_type; ?></p>
                 <p style="font-size:medium"><strong><?php echo $training_date; ?></strong></p>

                <img src="../photos/events/terraces-forests-gladys.jpg" style="width:100%;" id="event-lead-photo">

                <h2><?php echo $training_title; ?></h2>
                <h3 style="font-size:medium">Lead by <?php echo $lead_trainer; ?></h3>
                <p><?php echo $training_summary; ?></p>
                <p><?php echo $training_agenda; ?></p>


 <!-- RSVP Button -->
            <button id="rsvp-button" class="confirm-button enabled" style="margin-top: 20px; font-size: 1.2em; padding: 10px 20px; cursor: pointer;">
                <?php echo $is_logged_in ? "✅ RSVP as " . htmlspecialchars($first_name, ENT_QUOTES, 'UTF-8') : "✅ RSVP"; ?>
            </button>

            <!-- JavaScript to handle RSVP click -->

        <br><br>


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

<!-- Modal for Non-Logged-In Users -->
<div id="form-modal-message" class="modal-hidden">
    <button type="button" onclick="closeInfoModal()" aria-label="Click to close modal" class="x-button"></button>
    <div class="modal-content-box" id="modal-content-box"></div>
</div>


<script>
function openInfoModal(lang = '<?php echo $lang; ?>') {
    const modal = document.getElementById('form-modal-message');
    const messageContainer = modal.querySelector('.modal-message');
    const photobox = document.getElementById('modal-photo-box');
    let title, message, loginButton, signupButton;

    photobox.style.display = 'none'; // Hide the photo box if not needed

    // Set translations based on language
    switch (lang) {
        case 'fr':
            title = "Connexion requise";
            message = "Pour vous inscrire à l'événement communautaire, veuillez vous connecter avec votre compte GoBrik Buwana.";
            loginButton = "🔑 Se connecter";
            signupButton = "📝 S'inscrire";
            break;
        case 'es':
            title = "Inicio de sesión requerido";
            message = "Para inscribirse en el evento comunitario, inicie sesión con su cuenta de GoBrik Buwana.";
            loginButton = "🔑 Iniciar sesión";
            signupButton = "📝 Registrarse";
            break;
        case 'id':
            title = "Diperlukan Login";
            message = "Untuk mendaftar acara komunitas, silakan masuk dengan akun GoBrik Buwana Anda.";
            loginButton = "🔑 Masuk";
            signupButton = "📝 Daftar";
            break;
        default: // English (en)
            title = "Login Required";
            message = "To RSVP for the Community Event, please log in with your GoBrik Buwana account.";
            loginButton = "🔑 Log In";
            signupButton = "📝 Sign Up";
            break;
    }

    // Construct modal content
    let content = `
        <div style="text-align:center;width:100%;margin:auto;margin-top:10px;margin-bottom:10px;">
            <h1>🌍</h1>
        </div>
        <div class="preview-title">${title}</div>
        <p class="preview-text" style="font-size:medium;">${message}</p>
        <div style="text-align:center;width:100%;margin:auto;margin-top:10px;margin-bottom:10px;">
            <a href="login.php?redirect=register.php" class="modal-button">${loginButton}</a>
            <a href="signup.php" class="modal-button">${signupButton}</a>
        </div>
    `;

    // Inject content into modal
    messageContainer.innerHTML = content;

    // Show modal
    modal.style.display = 'flex';
    document.getElementById('page-content').classList.add('blurred');
    document.getElementById('footer-full').classList.add('blurred');
    document.body.classList.add('modal-open');
}

//
</script>



    <!-- FOOTER -->
    <?php require_once("../footer-2024.php"); ?>

</body>
</html>
