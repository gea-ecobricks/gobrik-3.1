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

// If no valid training ID is provided, redirect to the courses list
if ($training_id <= 0) {
    header('Location: courses.php');
    exit();
}

// Initialize training variables
$training_title = $training_date = $lead_trainer = "";
$training_type = $training_country = $training_location = "";
$country_id = 0;
$language_id = '';
$training_language = '';
$training_url = "";
$cost = '';
$currency = '';
$ecobricker_id = null;
$users_email_address = '';
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

    // Fetch ecobricker_id and user's email using buwana_id
    $sql = "SELECT ecobricker_id, email_addr FROM tb_ecobrickers WHERE buwana_id = ?";
    $stmt = $gobrik_conn->prepare($sql);
    $stmt->bind_param("i", $buwana_id);
    $stmt->execute();
    $stmt->bind_result($ecobricker_id, $users_email_address);
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
    $training_name = $training_title; // alias for modal text
    $training_subtitle = htmlspecialchars($row['training_subtitle'] ?? '', ENT_QUOTES, 'UTF-8');
    $training_date = htmlspecialchars($row['training_date'] ?? '', ENT_QUOTES, 'UTF-8');
    $training_time_txt = htmlspecialchars($row['training_time_txt'] ?? '', ENT_QUOTES, 'UTF-8');
    $training_logged = htmlspecialchars($row['training_logged'] ?? '', ENT_QUOTES, 'UTF-8');
    $lead_trainer = htmlspecialchars($row['lead_trainer'] ?? '', ENT_QUOTES, 'UTF-8');
    $training_type = htmlspecialchars($row['training_type'] ?? '', ENT_QUOTES, 'UTF-8');
    $country_id = intval($row['country_id'] ?? 0);
    $training_location = htmlspecialchars($row['training_location'] ?? '', ENT_QUOTES, 'UTF-8');
    $registration_scope = htmlspecialchars($row['registration_scope'] ?? '', ENT_QUOTES, 'UTF-8');
    $language_id = trim($row['training_language'] ?? '');

    $display_cost = htmlspecialchars($row['display_cost'] ?? '', ENT_QUOTES, 'UTF-8');
    $cost = htmlspecialchars($row['cost'] ?? '', ENT_QUOTES, 'UTF-8');
    $currency = htmlspecialchars($row['currency'] ?? '', ENT_QUOTES, 'UTF-8');



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

    // Look up the language and country names using the IDs
    require_once '../buwanaconn_env.php';

    $training_country = '';
    if ($country_id > 0) {
        $stmt_country = $buwana_conn->prepare("SELECT country_name FROM countries_tb WHERE country_id = ?");
        $stmt_country->bind_param("i", $country_id);
        $stmt_country->execute();
        $stmt_country->bind_result($training_country_name);
        $stmt_country->fetch();
        $stmt_country->close();
        $training_country = htmlspecialchars($training_country_name ?? '', ENT_QUOTES, 'UTF-8');
    }

    $training_language = '';
    if (!empty($language_id)) {
        $stmt_language = $buwana_conn->prepare("SELECT language_name_en FROM languages_tb WHERE language_id = ?");
        $stmt_language->bind_param("s", $language_id);
        $stmt_language->execute();
        $stmt_language->bind_result($training_language_name);
        $stmt_language->fetch();
        $stmt_language->close();
        $training_language = htmlspecialchars($training_language_name ?? '', ENT_QUOTES, 'UTF-8');
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

    <div id="top-page-image" class="gea-logo top-page-image"></div>
-->


   <div id="form-submission-box">
        <div class="form-container" style="padding-top:0px;">

            <div style="width:100%;margin:auto;margin-top:5px;">

                <?php if ($is_registered): ?>
        <div id="registered-notice" class="top-container-notice">
            <span style="margin-right:10px;">👍</span><span> You're registered for this <?php echo $training_type; ?>!  See your email or <a href="dashboard.php">dashboard</a> for full registration details.</span>
        </div>
    <?php endif; ?>

        <div class="intro-to-training-wrapper" style="width: 100$; background: var(--course-module); border-radius:15px; padding:10px;">

            <img src="<?php echo $feature_photo1_main; ?>" style="width:100%;border-radius: 10px;" id="event-lead-photo">


            <div class="training-title-box">
                <div class="the-titles" style="width:80%">
                    <h3><?php echo $training_title; ?></h3>
                    <h4 style="margin: 10px 0px 10px 0px;"><?php echo $training_subtitle; ?></h4>
                        <p style="font-size:1em"><?php echo $training_date; ?> | <?php echo $training_time_txt; ?></p>
                        <p style="font-size:1em;"><?php echo $training_type; ?> | Scope: <?php echo $registration_scope; ?></p>
                        <p style="font-size:1em;"><?php echo $display_cost; ?></p>
                        <button id="rsvp-register-button" class="<?php echo $is_registered ? '' : 'enabled'; ?>" style="margin-top: 20px;font-size: 1.3em; padding: 10px 20px; cursor: pointer;">
                                                                    <?php echo $is_registered ? "✅ You're already registered" : ($is_logged_in ? $earthling_emoji . " Register" : "🔑Register"); ?>
                                    </button>

                </div>
                <div class="profile-images">
                    <img src="<?php echo $feature_photo3_main; ?>">
                    <p class="profile-names">Led by <?php echo $lead_trainer; ?></p>
                    <p class="profile-names" style="font-size:1em;">Language: <?php echo $training_language; ?></p>
                </div>
            </div>

            <button id="rsvp-register-button" class="<?php echo $is_registered ? '' : 'enabled'; ?>" style="margin-top: 20px;font-size: 1.3em; padding: 10px 20px; cursor: pointer;">
                                            <?php echo $is_registered ? "✅ You're already registered" : ($is_logged_in ? $earthling_emoji . " Register" : "🔑Register"); ?>
            </button>
        </div>




               <p style="margin-top:20px;font-size:1.5em; padding: 15px;"><?php echo nl2br(htmlspecialchars_decode($featured_description, ENT_QUOTES)); ?></p>



               <p style="font-size:1.23em; padding: 15px;margin-top: 0px;"><?php echo nl2br($training_agenda); ?></p>





</div>






        <div id="event-details" class="dashboard-panel" style="margin-top:20px;font-size:small;">
            <img src="<?php echo $feature_photo2_main; ?>" style="width:100%;padding:10px;" id="event-lead-photo"><h4><?php echo $training_type; ?></h4>

            <hr>
            <p><strong>Title:</strong> <?php echo $training_title; ?></p>
            <p><strong>Subtitle:</strong> <?php echo $training_subtitle; ?></p>
            <p><strong>Date:</strong> <?php echo $training_date; ?></p>
            <p><strong>Time:</strong> <?php echo $training_time_txt; ?></p>
            <p><strong>Lead Trainer(s):</strong> <?php echo $lead_trainer; ?></p>
            <p><strong>Training Type:</strong> <?php echo $training_type; ?></p>
            <p><strong>Country:</strong> <?php echo $training_country; ?></p>
            <p><strong>Location:</strong> <?php echo $training_location; ?></p>
            <p><strong>Language:</strong> <?php echo $training_language; ?></p>


            <p><strong>Training Logged:</strong> <?php echo $training_logged; ?></p>
            <p><strong>Scope:</strong> <?php echo $registration_scope; ?></p>
            <p><strong>Cost:</strong> <?php echo $cost; ?></p>
            <p><strong>Currency:</strong> <?php echo $currency; ?></p>
            <p><strong>Display Cost:</strong> <?php echo $display_cost; ?></p>
        </div>

        <button id="rsvp-bottom-button" class="confirm-button <?php echo $is_registered ? '' : 'enabled'; ?>" style="margin-top: 20px;margin-bottom:75px; font-size: 1.3em; padding: 10px 20px; cursor: pointer; width:100%;">
            <?php echo $is_registered ? "✅ You're already registered" : ($is_logged_in ? $earthling_emoji . " Register" : "🔑Register"); ?>
        </button>

</div>



    </div>

</div>













<!-- JavaScript to handle RSVP click -->
<script>
document.getElementById("rsvp-bottom-button").addEventListener("click", handleRegistrationClick);
document.getElementById("rsvp-register-button").addEventListener("click", handleRegistrationClick);

function handleRegistrationClick() {
    <?php if ($is_logged_in && isset($ecobricker_id)): ?>
        <?php if ($is_registered): ?>
            openCancelRegistrationModal();
        <?php else: ?>
            openConfirmRegistrationModal(
                <?php echo json_encode($training_name); ?>,
                <?php echo json_encode($training_type); ?>,
                <?php echo json_encode($training_date); ?>,
                <?php echo json_encode($training_time_txt); ?>,
                <?php echo json_encode($training_location); ?>,
                <?php echo json_encode($display_cost); ?>,
                <?php echo json_encode($users_email_address); ?>
            );
        <?php endif; ?>
    <?php else: ?>
        openInfoModal();
    <?php endif; ?>
}
</script>


<script>
function openInfoModal() {
    const modal = document.getElementById('form-modal-message');
    const messageContainer = modal.querySelector('.modal-message');
    const photobox = document.getElementById('modal-photo-box');

    photobox.style.display = 'none';

    const content = `
        <div style="display:flex;flex-direction:column;height:100%;justify-content:space-between;text-align:center;">
            <h1>🔑</h1>
            <h2>Login to Register</h2>
            <p>To register for this course you must use your GoBrik account.</p>
            <div style="text-align:center;width:100%;margin:auto;margin-top:10px;margin-bottom:10px;">
                <a href="login.php?redirect=register.php?id=<?php echo $training_id; ?>" class="confirm-button enabled" style="margin-right:10px;">Login</a>
                <a href="signup.php" class="confirm-button enabled">Sign Up</a>
            </div>
            <p style="font-size: 1em; color: grey;">GoBrik authentication is powered by Buwana SSO for regenerative apps</p>
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

function openConfirmRegistrationModal(trainingName, trainingType, trainingDate, trainingTime, trainingLocation, displayCost, userEmail) {
    const modal = document.getElementById('form-modal-message');
    const messageContainer = modal.querySelector('.modal-message');
    const photobox = document.getElementById('modal-photo-box');

    photobox.style.display = 'none';

    const content = `
        <div style="display:flex;flex-direction:column;height:100%;justify-content:space-between;text-align:center;">
            <div>
                <h1>✔️</h1>
                <h2>${trainingName}</h2>
                <p>Please confirm your registration to this ${trainingType} taking place at ${trainingDate} (${trainingTime}) on ${trainingLocation}. The training is ${displayCost} so there is no need to make any initial payments.</p>
            </div>
            <div style="display:flex;width:100%;margin-top:20px;flex-flow:column">
                <a href="registration_confirmation.php?id=<?php echo $training_id; ?>&ecobricker_id=<?php echo $ecobricker_id; ?>" class="confirm-button enabled" style="flex:1;width:80%;">Confirm Registration</a>
                <a href="register.php?id=<?php echo $training_id; ?>" class="confirm-button" style="background:grey;flex:1;width:80%;">Back to Course</a>
            </div>
            <p style="font-size:1em; color: grey;" >Upon confirmation we will send you the access links and information to your Buwana account e-mail: ${userEmail}</p>
        </div>
    `;

    messageContainer.innerHTML = content;
    modal.style.display = 'flex';
    document.body.classList.add('modal-open');
}

<?php if ($is_registered): ?>
function openCancelRegistrationModal() {
    const modal = document.getElementById('form-modal-message');
    const messageContainer = modal.querySelector('.modal-message');
    const photobox = document.getElementById('modal-photo-box');

    photobox.style.display = 'none';

    const content = `
        <div style="display:flex;flex-direction:column;height:100%;justify-content:space-between;text-align:center;">
            <div>
                <h1>💔</h1>
                <h4>Cancel Registration?</h4>
                <p>Are you sure you want to un-enroll from this course?<br>If you've made a payment it cannot be refunded.</p>
            </div>
            <div style="display:flex;gap:10px;width:100%;margin-top:20px;">
                <a href="#" id="confirm-unregister" class="confirm-button" style="background:red;color:white;flex:1;">Cancel Registration</a>
                <a href="courses.php" class="confirm-button" style="background:grey;flex:1;">↩️ Back to Courses</a>
            </div>
        </div>
    `;

    messageContainer.innerHTML = content;
    modal.style.display = 'flex';
    document.body.classList.add('modal-open');

    document.getElementById('confirm-unregister').addEventListener('click', function(e) {
        e.preventDefault();
        fetch('../api/unregister_training.php?id=<?php echo $training_id; ?>&ecobricker_id=<?php echo $ecobricker_id; ?>')
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    window.location.href = 'courses.php';
                } else {
                    alert('Unable to cancel registration.');
                }
            })
            .catch(() => alert('Unable to cancel registration.'));
    });
}

document.addEventListener('DOMContentLoaded', function() {
    const btns = [document.getElementById('rsvp-bottom-button'), document.getElementById('rsvp-register-button')];
    btns.forEach(btn => {
        if (!btn) return;
        btn.addEventListener('mouseover', function() {
            this.dataset.originalText = this.innerHTML;
            this.dataset.originalBg = this.style.background;
            this.style.background = 'grey';
            this.innerHTML = '💔 Cancel Registration';
        });
        btn.addEventListener('mouseout', function() {
            this.style.background = this.dataset.originalBg;
            this.innerHTML = this.dataset.originalText;
        });
    });
});
<?php endif; ?>
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
