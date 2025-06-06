<?php
require_once '../earthenAuth_helper.php'; // Include authentication helper functions

// Set page variables
$lang = basename(dirname($_SERVER['SCRIPT_NAME']));
$version = '0.11';
$page = 'register';
$lastModified = date("Y-m-d\TH:i:s\Z", filemtime(__FILE__));
$is_logged_in = isLoggedIn(); // Check if the user is logged in

// Get training ID from the URL
$training_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Initialize training variables
$training_title = $training_date = $lead_trainer = "";
$training_type = $training_country = $training_location = "";
$training_url = "";
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
$earthling_emoji = getUserEarthlingEmoji($buwana_conn, $buwana_id);
    $user_location_watershed = getWatershedName($buwana_conn, $buwana_id);
    $user_location_full = getUserFullLocation($buwana_conn, $buwana_id);
    $gea_status = getGEA_status($buwana_id);
    $user_roles = getUser_Role($buwana_id);
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
    $allowed_tags = '<b><i><u><strong><em><p><br><ul><li><ol>';
    $featured_description = strip_tags($row['featured_description'] ?? '', $allowed_tags);
    $training_agenda = strip_tags($row['training_agenda'] ?? '', $allowed_tags);
    $training_title = htmlspecialchars($row['training_title'] ?? '', ENT_QUOTES, 'UTF-8');
    $training_subtitle = htmlspecialchars($row['training_subtitle'] ?? '', ENT_QUOTES, 'UTF-8');
    $training_date = htmlspecialchars($row['training_date'] ?? '', ENT_QUOTES, 'UTF-8');
    $training_time_txt = htmlspecialchars($row['training_time_txt'] ?? '', ENT_QUOTES, 'UTF-8');
    $training_logged = htmlspecialchars($row['training_logged'] ?? '', ENT_QUOTES, 'UTF-8');
    $lead_trainer = htmlspecialchars($row['lead_trainer'] ?? '', ENT_QUOTES, 'UTF-8');
    $training_type = htmlspecialchars($row['training_type'] ?? '', ENT_QUOTES, 'UTF-8');
    $training_country = htmlspecialchars($row['training_country'] ?? '', ENT_QUOTES, 'UTF-8');
    $training_location = htmlspecialchars($row['training_location'] ?? '', ENT_QUOTES, 'UTF-8');
    $registration_scope = htmlspecialchars($row['registration_scope'] ?? '', ENT_QUOTES, 'UTF-8');
     $training_language = htmlspecialchars($row['training_language'] ?? '', ENT_QUOTES, 'UTF-8');
    $location_full = isset($row['location_full']) ? htmlspecialchars($row['location_full'], ENT_QUOTES, 'UTF-8') : '';


    $training_url = htmlspecialchars($row['training_url'] ?? '', ENT_QUOTES, 'UTF-8');
    $ready_to_show = $row['ready_to_show'];

    // ✅ Fetch feature photos
    $feature_photo1_main = htmlspecialchars($row['feature_photo1_main'] ?? '', ENT_QUOTES, 'UTF-8');
    $feature_photo2_main = htmlspecialchars($row['feature_photo2_main'] ?? '', ENT_QUOTES, 'UTF-8');
    $feature_photo3_main = htmlspecialchars($row['feature_photo3_main'] ?? '', ENT_QUOTES, 'UTF-8');
    $feature_photo1_tmb = htmlspecialchars($row['feature_photo1_tmb'] ?? '', ENT_QUOTES, 'UTF-8');

    if ($ready_to_show == 0) {
        echo "<script>alert('Sorry this training isn\'t yet listed for public registration.'); window.location.href='trainings.php';</script>";
        exit;
    }
}



$stmt->close();
$gobrik_conn->close();

echo '<!DOCTYPE html>
<html lang="' . $lang . '">
<head>
<meta charset="UTF-8">

';
?>



<!-- Page CSS & JS Initialization -->
<?php require_once("../includes/register-inc.php"); ?>


    <div class="splash-title-block"></div>
    <div id="splash-bar"></div>

    <!-- PAGE CONTENT
-->

<div id="top-page-image" class="gea-logo top-page-image"></div>
   <div id="form-submission-box">
        <div class="form-container">

            <div style="width:100%;margin:auto;margin-top:65px;">

                <?php if ($is_registered): ?>
        <div id="registered-notice" style="text-align:center;background-color:#4CAF50; color:white; padding:10px 15px; border-radius:8px; display:block; width:fit-content; margin:auto; font-size:1.1em; font-weight:bold; display:flex; align-items:center;">
            <span style="margin-right:10px;">👍</span> You're registered for this <?php echo $training_type; ?>!.  Check your email or dashboard for full details and Zoom link.
        </div>
    <?php endif; ?>

        <div class="intro-to-training-wrapper" style="width: 100$; background: #00000015; border-radius:15px; padding:10px;">

            <img src="<?php echo $feature_photo1_main; ?>" style="width:100%;border-radius: 10px;" id="event-lead-photo">


            <div class="training-title-box" style="width:100%;display:flex;flex-flow:row; margin-top: 20px;padding:15px;" class="form-field">
                <div class="the-titles" style="width:80%">
                <h3><?php echo $training_title; ?></h3>
                <h4><?php echo $training_subtitle; ?></h4>
                    <p style="font-size:1em"><?php echo $training_date; ?> | <?php echo $training_time_txt; ?></p>
                    <p style="font-size:1em;"><?php echo $training_type; ?> | Scope: <?php echo $registration_scope; ?></p>
                    <p style="font-size:1em;"><?php echo $training_location; ?> from: <?php echo $training_country; ?> | Language: <?php echo $training_language; ?></p>
                </div>
                <div class="profile-images" style="width:20%">
                    <img src="<?php echo $feature_photo3_main; ?>" style="width:200px;">
                    <p style="text-align:right; width:200px; text-align:center">Led by <?php echo $lead_trainer; ?></p>
                </div>
            </div>
        </div>




               <p style="margin-top:20px;font-size:1.3em"><?php echo nl2br(htmlspecialchars_decode($featured_description, ENT_QUOTES)); ?></p>



               <p><?php echo nl2br(htmlspecialchars($training_agenda, ENT_QUOTES, 'UTF-8')); ?></p>




    <button id="rsvp-button" class="confirm-button <?php echo $is_registered ? '' : 'enabled'; ?>" style="margin-top: 20px; font-size: 1.2em; padding: 10px 20px; cursor: <?php echo $is_registered ? 'default' : 'pointer'; ?>;" <?php echo $is_registered ? 'disabled' : ''; ?>>
        <?php echo $is_registered ? "✅ You're already registered" : ($is_logged_in ? "✅ RSVP as " . htmlspecialchars($first_name, ENT_QUOTES, 'UTF-8') : "✅ RSVP"); ?>
    </button>
</div>






        <div id="event-details" class="dashboard-panel" style="margin-top:20px;font-size:small;">
            <img src="<?php echo $feature_photo2_main; ?>" style="width:100%;padding:10px;" id="event-lead-photo"><h4>Community Event Details</h4>

            <hr>
            <p><strong>Title:</strong> <?php echo $training_title; ?></p>
            <p><strong>Date:</strong> <?php echo $training_date; ?></p>
            <p><strong>Lead Trainer(s):</strong> <?php echo $lead_trainer; ?></p>
            <p><strong>Training Type:</strong> <?php echo $training_type; ?></p>
            <p><strong>Country:</strong> <?php echo $training_country; ?></p>
            <p><strong>Location:</strong> <?php echo $training_location; ?></p>
            <p><strong>Scope:</strong> <?php echo $registration_scope; ?></p> <!-- ✅ Add this -->
        </div>

</div>



    </div>

</div>













<!-- JavaScript to handle RSVP click -->
<script>
document.getElementById("rsvp-button").addEventListener("click", function() {
    <?php if ($is_logged_in && isset($ecobricker_id)): ?>
        // Redirect logged-in users to registration-confirmation.php
        window.location.href = "registration_confirmation.php?id=<?php echo $training_id; ?>&ecobricker_id=<?php echo $ecobricker_id; ?>";
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
            message = "Pour vous inscrire à l'événement communautaire, veuillez vous connecter avec votre compte GoBrik.";
            loginButton = "🔑 Se connecter";
            signupButton = "📝 S'inscrire";
            break;
        case 'es':
            title = "Inicio de sesión requerido";
            message = "Para inscribirse en el evento comunitario, inicie sesión con su cuenta de GoBrik.";
            loginButton = "🔑 Iniciar sesión";
            signupButton = "📝 Registrarse";
            break;
        case 'id':
            title = "Diperlukan Login";
            message = "Untuk mendaftar acara komunitas, silakan masuk dengan akun GoBrik Anda.";
            loginButton = "🔑 Masuk";
            signupButton = "📝 Daftar";
            break;
        default: // English (en)
            title = "Login Required";
            message = "To RSVP for the Community Event, please log in with your GoBrik account.";
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
            <p style="font-size:0.9";color:grey;margin-top: 10px;">No account yet?
            <a href="signup.php"">${signupButton}</a>
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
        <div class="preview-title">Registered!</div>
        <div style="text-align:center;width:100%;margin:auto;margin-top:10px;margin-bottom:10px;">
            <img src="../webps/registration-confirmed.webp" style="width: 50%;
  max-width: 400px;">
            <h1>You're registered!</h1>
            <h4>See you at <i>${trainingTitle}</i></h4>
            <p>Check your email for your registration confirmation and Zoom invitation link.</p>
            <div style="text-align:center;width:100%;margin:auto;margin-top:10px;margin-bottom:10px;">
                <a href="register.php" class="confirm-button enabled" style="margin-top: 20px; font-size: 1.2em; padding: 10px 20px; cursor: pointer;">Got it!</a>
            </div>
        </div>
    `;

    messageContainer.innerHTML = content;
    modal.style.display = 'flex';
    document.body.classList.add('modal-open');
}
</script>
<?php endif; ?>


    <!-- FOOTER -->

    <?php require_once("../footer-2025.php"); ?>

</body>
</html>
