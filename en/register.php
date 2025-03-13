<?php
require_once '../earthenAuth_helper.php'; // Include authentication helper functions

// Set page variables
$lang = basename(dirname($_SERVER['SCRIPT_NAME']));
$version = '0.11';
$page = 'register';
$lastModified = date("Y-m-d\TH:i:s\Z", filemtime(__FILE__));
$is_logged_in = isLoggedIn(); // Check if the user is logged in

// Define training ID at the top
$training_id = 818; // Ensure this is defined before any queries

// Initialize training variables
$training_title = $training_date = $lead_trainer = "";
$training_type = $training_country = $training_location = "";
$training_url = "";
$first_name = "";
$ecobricker_id = null;
$is_registered = false; // Default: user is not registered

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
    $user_roles = getUser_Role($buwana_id);
    $user_community_name = getCommunityName($buwana_conn, $buwana_id);

    // Fetch ecobricker_id using buwana_id
    $sql = "SELECT ecobricker_id FROM tb_ecobrickers WHERE buwana_id = ?";
    $stmt = $gobrik_conn->prepare($sql);
    $stmt->bind_param("i", $buwana_id);
    $stmt->execute();
    $stmt->bind_result($ecobricker_id);
    $stmt->fetch();
    $stmt->close();

    // Check if the user is already registered for the training
    if ($ecobricker_id) {
        $sql_check = "SELECT id FROM tb_training_trainees WHERE training_id = ? AND ecobricker_id = ?";
        $stmt_check = $gobrik_conn->prepare($sql_check);
        $stmt_check->bind_param("ii", $training_id, $ecobricker_id);
        $stmt_check->execute();
        $stmt_check->store_result();

        // If a row exists, the user is registered
        if ($stmt_check->num_rows > 0) {
            $is_registered = true;
        }

        $stmt_check->close();
    }
}

    require_once '../gobrikconn_env.php';

// Fetch training details
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


<!--<div id="form-modal-message" class="modal-hidden">
    <button type="button" onclick="closeInfoModal()" aria-label="Click to close modal" class="x-button"></button>
    <div class="modal-content-box" id="modal-content-box">
        <div class="modal-message"></div>
    </div>
    <div class="modal-photo-box" id="modal-photo-box">
        <div class ="modal-photo"></div>
    </div>-->

</div>

    <div class="splash-title-block"></div>
    <div id="splash-bar"></div>

    <!-- PAGE CONTENT -->
    <div id="top-page-image" class="gea-logo top-page-image"></div>


    <div id="form-submission-box" class="landing-page-form">
        <div class="form-container">

            <div style="text-align:center;width:100%;margin:auto;margin-top:25px;">

                <?php if ($is_registered): ?>
        <div id="registered-notice" style="background-color:#4CAF50; color:white; padding:10px 15px; border-radius:8px; display:block; width:fit-content; margin:auto; font-size:1.1em; font-weight:bold; display:flex; align-items:center;">
            <span style="margin-right:10px;">👍</span> You're registered for this <?php echo $training_type; ?>!
        </div>
    <?php endif; ?>

                <p style="font-size:1em"><?php echo $training_type; ?></p>
                 <p style="font-size:1.2emm"><strong><?php echo $training_date; ?></strong></p>

                <img src="../photos/events/terraces-forests-gladys.jpg" style="width:100%;" id="event-lead-photo">

                <h2><?php echo $training_title; ?></h2>
                <h4 >Lead by <?php echo $lead_trainer; ?></h4>
                <p><?php echo $training_summary; ?></p>

                <img src="../photos/events/r-a-tractatus.webp" style="width:100%;" id="event-lead-photo">

                <p><?php echo $training_agenda; ?></p>

 <!-- RSVP Button -->
    <button id="rsvp-button" class="confirm-button <?php echo $is_registered ? '' : 'enabled'; ?>" style="margin-top: 20px; font-size: 1.2em; padding: 10px 20px; cursor: <?php echo $is_registered ? 'default' : 'pointer'; ?>;" <?php echo $is_registered ? 'disabled' : ''; ?>>
        <?php echo $is_registered ? "✅ You're already registered" : ($is_logged_in ? "✅ RSVP as " . htmlspecialchars($first_name, ENT_QUOTES, 'UTF-8') : "✅ RSVP"); ?>
    </button>
</div>


        <div id="event-details" class="dashboard-panel" style="margin-top:20px;">
            <h4>Community Event Details</h4>
            <hr>
            <p><strong>Title:</strong> <?php echo $training_title; ?></p>
            <p><strong>Date:</strong> <?php echo $training_date; ?></p>
            <p><strong>Lead Trainer(s):</strong> <?php echo $lead_trainer; ?></p>
            <p><strong>Training Type:</strong> <?php echo $training_type; ?></p>
            <p><strong>Country:</strong> <?php echo $training_country; ?></p>
            <p><strong>Location:</strong> <?php echo $training_location; ?></p>
        </div>
    </div>







    <!-- FOOTER -->
    <?php require_once("../footer-2024.php"); ?>



<!-- JavaScript to handle RSVP click -->
<script>
document.getElementById("rsvp-button").addEventListener("click", function() {
    <?php if ($is_logged_in && isset($ecobricker_id)): ?>
        // Redirect logged-in users to registration-confirmation.php
        window.location.href = "registration_confirmation.php?training_id=<?php echo $training_id; ?>&ecobricker_id=<?php echo $ecobricker_id; ?>";
    <?php else: ?>
        // Show login modal for non-logged-in users
        openInfoModal('<?php echo $lang; ?>');
    <?php endif; ?>
});
</script>


<script>
function openInfoModal(lang) {
    const modal = document.getElementById('form-modal-message');
    const messageContainer = modal.querySelector('.modal-message');
    const photobox = document.getElementById('modal-photo-box');

    photobox.style.display = 'none'; // Hide photo box if not needed

    let title, message, loginButton, signupButton;

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

    let content = `
        <div style="text-align:center;width:100%;margin:auto;margin-top:10px;margin-bottom:10px;">
            <h1>🌍</h1>
        </div>
        <div class="preview-title">${title}</div>

        <p style="font-size:1.4;">${message}</p>
        <div style="text-align:center;width:100%;margin:auto;margin-top:10px;margin-bottom:10px;">
            <a href="login.php?redirect=register.php" class="confirm-button enabled" style="margin-top: 20px; font-size: 1.2em; padding: 10px 20px; cursor: pointer;">${loginButton}</a>
            <p style="font-size:0.9";color:grey;margin-top: 10px;">Or if you don't have an account yet...</p>
            <a href="signup.php" class="confirm-button enabled" style="margin-top: 20px; font-size: 1.2em; padding: 10px 20px; cursor: pointer;">${signupButton}</a>
        </div>
    `;

    messageContainer.innerHTML = content;

    modal.style.display = 'flex';
    document.body.classList.add('modal-open');
}

// Function to close the modal
function closeInfoModal() {
    const modal = document.getElementById('form-modal-message');
    modal.style.display = 'none';
    document.body.classList.remove('modal-open');
}
</script>

<?php if (isset($_GET['registered']) && $_GET['registered'] == 1): ?>
<script>
document.addEventListener("DOMContentLoaded", function() {
    openRegistrationSuccessModal("<?php echo htmlspecialchars($training_title, ENT_QUOTES, 'UTF-8'); ?>");
});

function openRegistrationSuccessModal(trainingTitle) {
    const modal = document.getElementById('form-modal-message');
    const messageContainer = modal.querySelector('.modal-message');
    const photobox = document.getElementById('modal-photo-box');

    photobox.style.display = 'none';

    let content = `
        <div style="text-align:center;width:100%;margin:auto;margin-top:10px;margin-bottom:10px;">
            <h1>✅</h1>
        </div>
        <div class="preview-title">You're now registered for the community event</div>
        <h4>${trainingTitle}</h4>
        <p>Check your email for your registration confirmation and Zoom invitation link.</p>
        <div style="text-align:center;width:100%;margin:auto;margin-top:10px;margin-bottom:10px;">
            <a href="dashboard.php" class="confirm-button enabled" style="margin-top: 20px; font-size: 1.2em; padding: 10px 20px; cursor: pointer;">🏠 Dashboard</a>
        </div>
    `;

    messageContainer.innerHTML = content;
    modal.style.display = 'flex';
    document.body.classList.add('modal-open');
}
</script>
<?php endif; ?>

</body>
</html>
