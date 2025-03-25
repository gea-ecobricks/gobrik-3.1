<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
ini_set('memory_limit', '256M'); // Increase memory limit
require_once '../earthenAuth_helper.php'; // Authentication helper

// PART 1: Set page variables
$lang = basename(dirname($_SERVER['SCRIPT_NAME']));
$version = '0.61';
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
require_once '../gobrikconn_env.php'; // Ensure DB connection is established

// ✅ Get training_id from URL if available
$training_id = isset($_GET['training_id']) ? intval($_GET['training_id']) : 0;

// ✅ If no training_id is found, fetch the latest (highest) ID from the database
if ($training_id === 0) {
    $sql = "SELECT MAX(training_id) AS latest_training_id FROM tb_trainings";
    $stmt = $gobrik_conn->prepare($sql);
    $stmt->execute();
    $stmt->bind_result($latest_training_id);
    $stmt->fetch();
    $stmt->close();

    // ✅ Set training_id to the latest one found
    $training_id = $latest_training_id ?? 0; // Fallback to 0 if no records exist
}

// ✅ If no valid training_id is found, redirect to avoid errors
if ($training_id === 0) {
    die("Error: No valid training record found. Please go back and submit the form again.");
}
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
        $fetch_stmt = $gobrik_conn->prepare($fetch_sql);
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

        $update_stmt = $gobrik_conn->prepare($update_sql);
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


  <!-- PAGE CONTENT-->

    <div id="form-submission-box">
        <div class="form-container">
            <div class="form-top-header" style="display:flex;flex-flow:row;">
                <div class="step-graphic" style="width:fit-content;margin:auto;margin-left:0px">
                    <img src="../svgs/step2-log-project.svg" style="height:25px;">
                </div>
                <div id="language-code" onclick="showLangSelector()" aria-label="Switch languages"><span data-lang-id="000-language-code">🌐 EN</span></div>
            </div>

            <div class="splash-form-content-block">
                <div class="splash-box">
                    <div class="splash-heading" data-lang-id="001-splash-title-upload">Upload Training Photos</div>
                </div>
                <div class="splash-image" data-lang-id="003-splash-image-upload">
                    <img src="../svgs/square-training-photo.svg" style="width:65%" alt="Upload your training photos">
                </div>
            </div>

            <div class="lead-page-paragraph-upload">
<p data-lang-id="004-form-description-upload" style="text-align: center; padding:20px;"> Show the world your training! Upload up to seven images showing your training session and what you accomplished.
            <span style="color:red">Square photos are best. Be sure photos are under 8MB.</span> </p>           </div>

       <!-- PART 6 THE FORM -->
<form id="photoform" method="post" enctype="multipart/form-data">

    <!-- ✅ Hidden field for training_id -->
    <input type="hidden" name="training_id" value="<?php echo htmlspecialchars($_GET['training_id']); ?>">

    <?php for ($i = 0; $i <= 6; $i++): ?>
        <?php
            $photo_main_var = "training_photo{$i}_main";
            $photo_tmb_var = "training_photo{$i}_tmb";
            $photo_number = $i + 1; // ✅ Adjust display number (start at 1)
            $existingPhotoUrl = $$photo_main_var ?? ''; // Get the existing photo URL
            $existingFileName = $existingPhotoUrl ? basename($existingPhotoUrl) : ''; // Extract file name
        ?>
        <div class="form-item">
            <label for="training_photo<?php echo $i; ?>_main">
                Upload Photo <?php echo $photo_number; ?>:
            </label><br>

            <!-- ✅ Show existing image if available -->
            <div class="existing-image-container" id="image-container-<?php echo $i; ?>"
                 style="display: <?php echo !empty($existingPhotoUrl) ? 'block' : 'none'; ?>;">
                <img src="<?php echo htmlspecialchars($existingPhotoUrl, ENT_QUOTES, 'UTF-8'); ?>"
                     alt="Existing Image <?php echo $photo_number; ?>"
                     class="existing-image"
                     id="preview-<?php echo $i; ?>"
                     style="max-width: 200px; max-height: 200px;">
                <p class="file-name" id="file-name-<?php echo $i; ?>">
                    <?php echo !empty($existingFileName) ? "Current Image: <strong>" . htmlspecialchars($existingFileName, ENT_QUOTES, 'UTF-8') . "</strong>" : ""; ?>
                </p>
            </div>

            <!-- ✅ File Upload Field -->
            <input type="file" id="training_photo<?php echo $i; ?>_main"
                   name="training_photo<?php echo $i; ?>_main"
                   class="photo-input"
                   data-photo-number="<?php echo $photo_number; ?>"
                   <?php if (!empty($existingPhotoUrl)): ?> data-has-image="true"<?php endif; ?>>

            <!-- ✅ Display the file name if already uploaded -->
            <span class="file-name" id="selected-file-<?php echo $i; ?>">
                <?php echo !empty($existingFileName) ? htmlspecialchars($existingFileName, ENT_QUOTES, 'UTF-8') : "No file selected..."; ?>
            </span>

            <p class="form-caption" data-lang-id="select-photo-<?php echo $photo_number; ?>-instruction">
                Select a photo for Upload Photo <?php echo $photo_number; ?>.
            </p>

            <!-- ✅ Clear Button (Only Show if Image Exists) -->
            <button type="button" class="clear-photo-button"
                    id="clear-btn-<?php echo $i; ?>"
                    data-clear-target="training_photo<?php echo $i; ?>_main"
                    data-image-container="image-container-<?php echo $i; ?>"
                    data-preview="preview-<?php echo $i; ?>"
                    data-file-name="file-name-<?php echo $i; ?>"
                    data-selected-file="selected-file-<?php echo $i; ?>"
                    style="display: <?php echo !empty($existingPhotoUrl) ? 'inline-block' : 'none'; ?>;">
                Clear uploaded image
            </button>
        </div>
    <?php endfor; ?>

    <div data-lang-id="013-submit-upload-button">
        <input type="submit" value="⬆️ Upload Photos" id="upload-progress-button" aria-label="Submit photos for upload">
    </div>

</form> <!-- ✅ FORM ENDS HERE -->






<!-- ✅ BACK LINK -->
<div style="text-align:center; padding:20px;width: 100%;">
<a href="#" onclick="goBack()"  aria-label="Go back to re-enter data" class="back-link" data-lang-id="015-go-back-link">↩ Back to Step 1</a>
</div>

</div>
</div> <!-- ✅ Closes photos-submission-box -->

<!-- ✅ UPLOAD SUCCESS MESSAGE -->
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



    <br><br>

    </div>
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




        // ✅ UPLOAD SUBMIT ACTION AND BUTTON

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

    // ✅ Check if at least one file is selected
    var fileSelected = false;
    for (var i = 0; i <= 6; i++) {
        var fileInput = document.getElementById(`training_photo${i}_main`);
        if (fileInput && fileInput.files.length > 0) {
            fileSelected = true;
            break; // ✅ Exit loop if a file is found
        }
    }

    if (!fileSelected) {
        showFormModal(chooseFileMessage);
        button.innerHTML = originalButtonText; // Restore button text
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
            button.innerHTML = originalButtonText; // Restore button text
            button.disabled = false; // Enable button
            handleFormResponse(xhr.responseText);
        }
    };

    xhr.open(form.method, form.action, true);
    xhr.send(formData);
});



//photo clearing functions

document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll(".photo-input").forEach(input => {
        const fileNameSpan = input.nextElementSibling; // The span next to the input

        // ✅ Update file name on file selection
        input.addEventListener("change", function () {
            if (this.files.length > 0) {
                fileNameSpan.textContent = this.files[0].name; // Show selected file name
            } else {
                fileNameSpan.textContent = "No file selected..."; // Reset if empty
            }
        });

        // ✅ Change "Browse..." to "Change Image..." if an image is already uploaded
        if (input.dataset.hasImage === "true") {
            input.setAttribute("title", "Change Image...");
        }
    });

    // ✅ Handle "Clear uploaded image" button click
    document.querySelectorAll(".clear-photo-button").forEach(button => {
        button.addEventListener("click", function () {
            const targetInputId = this.getAttribute("data-clear-target");
            const fileInput = document.getElementById(targetInputId);
            const fileNameSpan = document.getElementById(this.getAttribute("data-selected-file"));
            const imageContainer = document.getElementById(this.getAttribute("data-image-container"));
            const previewImage = document.getElementById(this.getAttribute("data-preview"));
            const fileNameText = document.getElementById(this.getAttribute("data-file-name"));

            // ✅ Clear input field
            fileInput.value = "";

            // ✅ Hide and clear preview image and text
            if (imageContainer) {
                imageContainer.style.display = "none";
            }
            if (previewImage) {
                previewImage.src = "";
            }
            if (fileNameText) {
                fileNameText.innerHTML = "";
            }
            if (fileNameSpan) {
                fileNameSpan.textContent = "No file selected...";
            }

            // ✅ Hide the clear button itself
            this.style.display = "none";
        });
    });
});




function handleFormResponse(response) {
    try {
        console.log("Raw server response:", response); // ✅ Debugging

        // ✅ Attempt to safely parse JSON
        var responseData = typeof response === "string" ? JSON.parse(response.trim()) : response;

        // ✅ Check if there's an error in the response
        if (responseData.error) {
            showFormModal(responseData.error);
            console.log("Error in response:", responseData.error);
        } else {
            console.log("Parsed JSON response:", responseData); // ✅ Debugging
            uploadSuccess(responseData);
        }
    } catch (error) {
        console.error("Error parsing server response:", error, "Raw response:", response);
        showFormModal("Error parsing server response. Check console for details.");
    }
}


function uploadSuccess(data) {
    // ✅ Define messages for different languages
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

    // ✅ Construct success message
    var successMessage = `
        <h1>${selectedMessage.heading}</h1>
        <p>${selectedMessage.description}</p><br>
    `;

    // ✅ Create gallery HTML
    var galleryHTML = '<div id="three-column-gal" class="three-column-gal">';

    for (var i = 0; i < data.thumbnail_paths.length; i++) {
        var directoryPathText = data.thumbnail_paths[i].substring(data.thumbnail_paths[i].lastIndexOf('/') + 1);
        var captionText = `${directoryPathText} | ${data.thumbnail_file_sizes[i].toFixed(1)} KB | ${data.main_file_sizes[i].toFixed(1)} KB`;
        var fullUrlText = data.full_urls[i];
        var modalCaption = `${directoryPathText} | ${data.main_file_sizes[i].toFixed(1)} KB | ${data.thumbnail_file_sizes[i].toFixed(1)} KB`;

        galleryHTML += `
            <div class="gal-photo">
                <img src="${data.thumbnail_paths[i]}" alt="${directoryPathText}">
                <p style="font-size:small;">${captionText}</p>
            </div>
        `;
    }

    galleryHTML += '</div>';
    successMessage += galleryHTML;

    // ✅ Add "Next Training" button
    successMessage += `<a class="confirm-button" href="add-training.php">${selectedMessage.button}</a>`;

    // ✅ Update the success div content
    var uploadSuccessDiv = document.getElementById('upload-success');
    var uploadSuccessMessageDiv = document.getElementById('upload-success-message');
    uploadSuccessMessageDiv.innerHTML = successMessage;

    // ✅ Show success message & hide submission form
    document.getElementById('form-submission-box').style.display = 'none'; // Hide form
    uploadSuccessDiv.style.display = 'block'; // Show success message

    // ✅ Scroll to top for better UX
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
