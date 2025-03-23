<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
ini_set('memory_limit', '256M'); // Increase memory limit
require_once '../earthenAuth_helper.php'; // Authentication helper

// PART 1: Set page variables
$lang = basename(dirname($_SERVER['SCRIPT_NAME']));
$version = '0.54';
$page = 'add-training';
$lastModified = date("Y-m-d\TH:i:s\Z", filemtime(__FILE__));

ob_start(); // Prevent output before headers

// PART 2: ✅ LOGIN & ROLE CHECK
if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

$buwana_id = $_SESSION['buwana_id'];
require_once '../gobrikconn_env.php';

// ✅ Fetch User Role
$gea_status = getGEA_status($buwana_id);

if (!$gea_status || stripos($gea_status, 'trainer') === false) {
    header("Location: dashboard.php?error=unauthorized");
    exit();
}


// PART 3: ✅ Fetch User Details
require_once '../buwanaconn_env.php';
$user_continent_icon = getUserContinent($buwana_conn, $buwana_id);
$user_location_watershed = getWatershedName($buwana_conn, $buwana_id);
$user_location_full = getUserFullLocation($buwana_conn, $buwana_id);
$user_community_name = getCommunityName($buwana_conn, $buwana_id);
$first_name = getFirstName($buwana_conn, $buwana_id);

// Fetch all languages
$languages = [];
$sql_languages = "SELECT language_id, languages_native_name FROM languages_tb ORDER BY languages_native_name ASC";
$result_languages = $buwana_conn->query($sql_languages);

if ($result_languages && $result_languages->num_rows > 0) {
    while ($row = $result_languages->fetch_assoc()) {
        $languages[] = $row;
    }
}

$buwana_conn->close(); // Close the database connection



//PART 4
//FEATCH IMAGE URLS
// ✅ Get training_id from URL
$training_id = isset($_GET['training_id']) ? intval($_GET['training_id']) : 0;

// ✅ Fetch image URLs
$sql_fetch = "SELECT training_title,
                     training_photo0_main, training_photo0_tmb,
                     training_photo1_main, training_photo1_tmb,
                     training_photo2_main, training_photo2_tmb,
                     training_photo3_main, training_photo3_tmb,
                     training_photo4_main, training_photo4_tmb,
                     training_photo5_main, training_photo5_tmb,
                     training_photo6_main, training_photo6_tmb
              FROM tb_trainings
              WHERE training_id = ?";

$stmt_fetch = $gobrik_conn->prepare($sql_fetch);
$stmt_fetch->bind_param("i", $training_id);
$stmt_fetch->execute();
$stmt_fetch->bind_result(
    $training_title,
    $training_photo0_main, $training_photo0_tmb,
    $training_photo1_main, $training_photo1_tmb,
    $training_photo2_main, $training_photo2_tmb,
    $training_photo3_main, $training_photo3_tmb,
    $training_photo4_main, $training_photo4_tmb,
    $training_photo5_main, $training_photo5_tmb,
    $training_photo6_main, $training_photo6_tmb
);
$stmt_fetch->fetch();
$stmt_fetch->close();


$error_message = '';
$full_urls = [];
$thumbnail_paths = [];
$main_file_sizes = [];
$thumbnail_file_sizes = [];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['training_id'])) {
    $training_id = intval($_POST['training_id']);
    if ($training_id <= 0) {
        die("Error: Invalid training ID.");
    }

    include '../scripts/photo-functions.php';

    // Handle training deletion
    if (isset($_POST['action']) && $_POST['action'] == 'delete_training') {
        $deleteResult = deleteTraining($training_id, $gobrik_conn);
        if ($deleteResult === true) {
            echo "<script>alert('Training has been successfully deleted.'); window.location.href='add-training.php';</script>";
            exit;
        } else {
            echo "<script>alert('" . $deleteResult . "');</script>";
            exit;
        }
    }

    $upload_dir = '../trainings/photos/';
    $thumbnail_dir = '../trainings/tmbs/';

    $db_fields = [];
    $db_values = [];
    $db_types = "";

    // Upload photos from training_photo0_main to training_photo6_main
    for ($i = 0; $i <= 6; $i++) {
        $file_input_name = "training_photo{$i}_main";
        if (isset($_FILES[$file_input_name]) && $_FILES[$file_input_name]['error'] == UPLOAD_ERR_OK) {
            $file_extension = strtolower(pathinfo($_FILES[$file_input_name]['name'], PATHINFO_EXTENSION));
            $new_file_name_webp = 'training-' . $training_id . '-' . $i . '.webp';
            $targetPath = $upload_dir . $new_file_name_webp;

            if (resizeAndConvertToWebP($_FILES[$file_input_name]['tmp_name'], $targetPath, 1000, 88)) {
                createTrainingThumbnail($targetPath, $thumbnail_dir . $new_file_name_webp, 250, 250, 77);
                $full_urls[] = $targetPath;
                $thumbnail_paths[] = $thumbnail_dir . $new_file_name_webp;
                $main_file_sizes[] = filesize($targetPath) / 1024;
                $thumbnail_file_sizes[] = filesize($thumbnail_dir . $new_file_name_webp) / 1024;

                array_push($db_fields, "training_photo{$i}_main", "training_photo{$i}_tmb");
                array_push($db_values, $targetPath, $thumbnail_dir . $new_file_name_webp);
                $db_types .= "ss";
            } else {
                $error_message .= "Error processing image {$i}. Please try again.<br>";
            }
        }
    }

    if (!empty($db_fields) && empty($error_message)) {
        // Fetch the briks_made and avg_brik_weight for the current training
        $fetch_sql = "SELECT briks_made, avg_brik_weight FROM tb_trainings WHERE training_id = ?";
        $fetch_stmt = $conn->prepare($fetch_sql);
        $fetch_stmt->bind_param("i", $training_id);
        $fetch_stmt->execute();
        $fetch_stmt->bind_result($briks_made, $avg_brik_weight);
        $fetch_stmt->fetch();
        $fetch_stmt->close();

        // Calculate est_plastic_packed in kg
        $est_plastic_packed = round(($briks_made * $avg_brik_weight) / 1000, 1);

        array_push($db_fields, "training_logged", "ready_to_show", "est_plastic_packed");
        array_push($db_values, date("Y-m-d H:i:s"), 1, $est_plastic_packed);
        $db_types .= "sis";

        $fields_for_update = implode(", ", array_map(function($field) { return "{$field} = ?"; }, $db_fields));
        $update_sql = "UPDATE tb_trainings SET {$fields_for_update} WHERE training_id = ?";
        $db_values[] = $training_id;
        $db_types .= "i";

        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param($db_types, ...$db_values);
        if (!$update_stmt->execute()) {
            $error_message .= "Database update failed: " . $update_stmt->error;
        }
        $update_stmt->close();
    }

    if (!empty($error_message)) {
        http_response_code(400);
        header('Content-Type: application/json');
        echo json_encode(['error' => "An error has occurred: " . $error_message . " END"]);
        exit;
    } else {
        $response = array(
            'training_id' => $training_id,
            'full_urls' => $full_urls,
            'thumbnail_paths' => $thumbnail_paths,
            'main_file_sizes' => $main_file_sizes,
            'thumbnail_file_sizes' => $thumbnail_file_sizes
        );
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
}

?>



<!DOCTYPE html>
<HTML lang="en">
<HEAD>

    <META charset="UTF-8">


<title><?php echo !empty($training_title) ? $training_title : 'Log your Training Report'; ?></title>
<meta name="keywords" content="GEA Registration, Community, Event, Webinar, Course">
<meta name="description" content="<?php echo !empty($training_type) && !empty($lead_trainer) && !empty($training_date)
    ? "Log the $training_type led by $lead_trainer on $training_date on the GEA reporting system. Reports will be shared on the front page of Ecobricks.org."
    : "Log your GEA workshop. Reports will be featured on the front page of Ecobricks.org and shareable on social media."; ?>">

<!-- Facebook Open Graph Tags for social sharing -->
<meta property="og:url" content="https://www.gobrik.com/<?php echo $lang; ?>/add-report.php">
<meta property="og:type" content="website">
<meta property="og:title" content="<?php echo !empty($training_title) ? $training_title : 'Log your Training Report'; ?>">
<meta property="og:description" content="<?php echo !empty($training_type) && !empty($lead_trainer) && !empty($training_date)
    ? "Log the $training_type led by $lead_trainer on $training_date on the GEA reporting system. Reports will be shared on the front page of Ecobricks.org."
    : "Log your GEA workshop. Reports will be featured on the front page of Ecobricks.org and shareable on social media."; ?>">

<!-- Default image in case no feature image is available -->
<?php
$og_image = !empty($feature_photo1_main) ? $feature_photo1_main : "https://gobrik.com/svgs/shanti.svg";
?>
<meta property="og:image" content="<?php echo $og_image; ?>">
<meta property="fb:app_id" content="1781710898523821">
<meta property="og:image:width" content="1000">
<meta property="og:image:height" content="1000">
<meta property="og:image:alt" content="<?php echo !empty($training_title) ? $training_title : 'GEA Trainer in action'; ?>">
<meta property="og:locale" content="en_GB">

<meta property="article:modified_time" content="<?php echo date("c"); ?>">

<meta name="author" content="GoBrik.com">
<meta property="og:type" content="page">
<meta property="og:site_name" content="GoBrik.com">
<meta property="article:publisher" content="https://web.facebook.com/ecobricks.org">
<meta property="og:image:type" content="image/png">
<meta name="author" content="GoBrik.com">

<!--PART 5 TOP DECORATION-->
    <?php require_once ("../includes/add-training-inc.php");?>

    <div class="splash-content-block"></div>
    <div id="splash-bar"></div>

    <!-- PAGE CONTENT -->
<div id="photos-submission-box" style="display:flex;flex-flow:column;">

    <div class="form-container" id="upload-photo-form">

        <div class="step-graphic" style="width:fit-content;margin:auto;">
            <img src="../svgs/step2-log-project.svg" style="height:30px;margin-bottom:40px;" alt="Step 2: Upload images">
        </div>

        <div class="splash-form-content-block">
            <div class="splash-box">
                <div class="splash-heading" data-lang-id="001-form-title">Upload Training Photos</div>
            </div>
            <div class="splash-image" data-lang-id="003-splash-image-alt">
                <img src="../svgs/square-training-photo.svg" style="width:65%" alt="Please take a square photo">
            </div>
        </div>

        <p data-lang-id="002-form-description2">
            Show the world your training! Upload up to six images showing your training session and what you accomplished.
            <span style="color:red">Square photos are best. Be sure photos are under 8MB.</span>
        </p>

        <br>

        <!-- ✅ FORM STARTS HERE -->
        <form action="your-upload-handler.php" method="post" enctype="multipart/form-data">

            <?php for ($i = 0; $i <= 6; $i++): ?>
                <?php
                    $photo_main_var = "training_photo{$i}_main";
                    $photo_tmb_var = "training_photo{$i}_tmb";
                ?>
                <div class="form-item">
                    <label for="training_photo<?php echo $i; ?>_main">Upload Photo <?php echo $i; ?>:</label><br>

                    <!-- ✅ Show existing image if available -->
                    <?php if (!empty($$photo_main_var)): ?>
                        <div class="existing-image">
                            <img src="<?php echo htmlspecialchars($$photo_main_var, ENT_QUOTES, 'UTF-8'); ?>"
                                 alt="Existing Image <?php echo $i; ?>"
                                 style="max-width: 200px; max-height: 200px;">
                            <p>Current Image</p>
                        </div>
                    <?php endif; ?>

                    <input type="file" id="training_photo<?php echo $i; ?>_main" name="training_photo<?php echo $i; ?>_main">
                    <p class="form-caption">Optional: Choose a new image to replace the existing one.</p>
                </div>
            <?php endfor; ?>

            <div data-lang-id="013-submit-upload-button">
                <input type="submit" value="⬆️ Upload Photos" id="upload-progress-button" aria-label="Submit photos for upload">
            </div>

        </form>  <!-- ✅ FORM ENDS HERE -->

    </div> <!-- ✅ Closes form-container -->
</div> <!-- ✅ Closes photos-submission-box -->





        <div id="upload-success" class="form-container" style="display:none;">
            <div class="step-graphic" style="width:fit-content;margin:auto;">
                <img src="../svgs/step3-log-project.svg" style="height:30px;margin-bottom:40px;" alt="Step 3: Upload Success">
            </div>
            <div id="upload-success-message"></div>
            <a class="confirm-button" href="training.php?training_id=<?php echo $_GET['training_id']; ?>" data-lang-id="013-view-training-post">🎉 View Training Post</a>
            <a class="confirm-button" data-lang-id="014-edit-training" href="edit-training.php?training_id=<?php echo $_GET['training_id']; ?>">Edit Training Post</a>

            <form id="deleteForm" action="" method="POST">
                <input type="hidden" name="training_id" value="<?php echo htmlspecialchars($_GET['training_id']); ?>">
                <input type="hidden" name="action" value="delete_training">
                <a class="confirm-button" style="background:red; cursor:pointer;" id="deleteButton" data-lang-id="014-delete-training">❌ Delete Training</a>
            </form>
        </div>

        <a href="#" onclick="goBack()"  aria-label="Go back to re-enter data" class="back-link" data-lang-id="015-go-back-link">↩ Back to Step 1</a>

    </div>



    <br><br>

    </div>

    <!--FOOTER STARTS HERE-->
    <?php require_once ("../footer-2024.php");?>
    </div>

    <script>
        // DELETE BUTTON

        // Define messages for different languages
        var messages = {
            en: 'Are you sure you want to delete this training? This action cannot be undone.',
            id: 'Apakah Anda yakin ingin menghapus pelatihan ini? Tindakan ini tidak dapat dibatalkan.',
            es: '¿Estás seguro de que deseas eliminar esta capacitación? Esta acción no se puede deshacer.',
            fr: 'Êtes-vous sûr de vouloir supprimer cette formation ? Cette action est irréversible.'
        };

        // Detect the current language, defaulting to English if not set or unsupported
        var currentLang = window.currentLanguage || 'en';
        var confirmationMessage = messages[currentLang] || messages.en;

        // Set up the event listener
        document.getElementById('deleteButton').addEventListener('click', function(event) {
            event.preventDefault(); // Prevent navigation
            if (confirm(confirmationMessage)) {
                document.getElementById('deleteForm').submit();
            }
        });

        // UPLOAD SUBMIT ACTION AND BUTTON

        document.querySelector('#photoform').addEventListener('submit', function(event) {
            event.preventDefault();

            var button = document.getElementById('upload-progress-button');
            var originalButtonText = button.value; // Save the original button text
            button.innerHTML = '<div class="spinner-photo-loading"></div>'; // Replace button text with spinner
            button.disabled = true; // Disable button to prevent multiple submissions

            var messages = {
                en: "Please choose a file.",
                es: "Por favor, elige un archivo.",
                fr: "Veuillez choisir un fichier.",
                id: "Silakan pilih sebuah berkas."
            };

            var currentLang = window.currentLanguage || 'en';
            var chooseFileMessage = messages[currentLang] || messages.en;

            var fileInput = document.getElementById('training_photo0_main');
            if (fileInput.files.length === 0) {
                showFormModal(chooseFileMessage);
                button.innerHTML = originalButtonText; // Restore button text if no file chosen
                button.disabled = false; // Enable button
                return;
            }

            var form = event.target;
            var formData = new FormData(form);
            var xhr = new XMLHttpRequest();

            xhr.upload.onprogress = function(event) {
                if (event.lengthComputable) {
                    var progress = (event.loaded / event.total) * 100;
                    document.getElementById('upload-progress-button').style.backgroundSize = progress + '%';
                    document.getElementById('upload-progress-button').classList.add('progress-bar');
                }
            };

            xhr.onreadystatechange = function() {
                if (xhr.readyState == XMLHttpRequest.DONE) {
                    button.innerHTML = originalButtonText; // Restore button text after upload
                    button.disabled = false; // Enable button
                    handleFormResponse(xhr.responseText);
                }
            };

            xhr.open(form.method, form.action, true);
            xhr.send(formData);
        });

        // Function to handle form submission response
        function handleFormResponse(response) {
            try {
                var responseData = JSON.parse(response);
                if (responseData.error) {
                    showFormModal(responseData.error);
                    console.log(responseData.error);
                } else {
                    // Call the uploadSuccess function with the new structure
                    uploadSuccess(responseData);
                }
            } catch (error) {
                showFormModal("Error parsing server response: " + response);
                console.error(error);
            }
        }

        // Updated function to handle upload success with multiple images
        function uploadSuccess(data) {
            // Define messages for different languages
            var messages = {
                en: {
                    heading: "Upload Successful!",
                    description: "Nice. Your training has now been added to the database.",
                    button: "➕ Add Next Training"
                },
                es: {
                    heading: "Carga Exitosa!",
                    description: "Genial. Tu capacitación ha sido agregada a la base de datos.",
                    button: "➕ Agregar Siguiente Capacitación"
                },
                fr: {
                    heading: "Téléchargement Réussi!",
                    description: "Super. Votre formation a été ajoutée à la base de données.",
                    button: "➕ Ajouter la Formation Suivante"
                },
                id: {
                    heading: "Berhasil Diunggah!",
                    description: "Bagus. Pelatihan Anda telah ditambahkan ke dalam basis data.",
                    button: "➕ Tambah Pelatihan Berikutnya"
                }
            };

            var currentLang = window.currentLanguage || 'en';
            var selectedMessage = messages[currentLang] || messages.en;

            var successMessage = '<h1>' + selectedMessage.heading + '</h1>';
            successMessage += '<p>' + selectedMessage.description + '</p><br>';

            var galleryHTML = '<div id="three-column-gal" class="three-column-gal">';

            // Iterate over the thumbnail_paths and full_urls to build the gallery items with added file size details
            for (var i = 0; i < data.thumbnail_paths.length; i++) {
                var directoryPathText = data.thumbnail_paths[i].substring(data.thumbnail_paths[i].lastIndexOf('/') + 1);
                var captionText = directoryPathText + ' | ' + data.thumbnail_file_sizes[i].toFixed(1) + ' KB | ' + data.main_file_sizes[i].toFixed(1) + ' KB';
                var fullUrlText = data.full_urls[i];
                var modalCaption = directoryPathText + ' | ' + data.main_file_sizes[i].toFixed(1) + ' Kb | ' + data.thumbnail_file_sizes[i].toFixed(1) + ' Kb';

                galleryHTML += '<div class="gal-photo" onclick="viewGalleryImage(\'' + fullUrlText + '\', \'' + modalCaption + '\')">';
                galleryHTML += '<img src="' + data.thumbnail_paths[i] + '" alt="' + directoryPathText + '">';
                galleryHTML += '<p style="font-size:small;">' + captionText + '</p>';
                galleryHTML += '</div>';
            }

            galleryHTML += '</div>';
            successMessage += galleryHTML;

            successMessage += '<a class="confirm-button" href="add-training.php">' + selectedMessage.button + '</a>';

            var uploadSuccessDiv = document.getElementById('upload-success');
            var uploadSuccessMessageDiv = document.getElementById('upload-success-message');
            uploadSuccessMessageDiv.innerHTML = successMessage;
            uploadSuccessDiv.style.display = 'block';

            document.getElementById('upload-photo-form').style.display = 'none';
            window.scrollTo(0, 0);
        }

        // Function to show form modal
        function showFormModal(message) {
            var modal = document.getElementById('form-modal-message');
            var modalMessage = modal.querySelector('.modal-message');
            var modalPhoto = modal.querySelector('.modal-photo-box');
            modalMessage.innerHTML = message;
            modal.style.display = 'flex';
            modalPhoto.style.display = 'none';

            // Add blur effect and hide overflow on page-content and footer-full
            document.getElementById('page-content').classList.add('blurred');
            document.getElementById('footer-full').classList.add('blurred');
            document.body.classList.add('modal-open');

            // Close modal when clicking outside
            window.onclick = function(event) {
                if (event.target == modal) {
                    closeInfoModal();
                }
            }
        }

        // Function to close the modal
        function closeInfoModal() {
            var modal = document.getElementById('form-modal-message');
            modal.style.display = 'none';

            // Remove blur effect and show overflow on page-content and footer-full
            document.getElementById('page-content').classList.remove('blurred');
            document.getElementById('footer-full').classList.remove('blurred');
            document.body.classList.remove('modal-open');
        }

    </script>

    </body>
</html>
