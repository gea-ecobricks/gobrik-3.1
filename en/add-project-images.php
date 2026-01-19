<?php
require_once '../earthenAuth_helper.php'; // 🌿 Optional helper functions

// 🌍 Set up page environment
$lang = basename(dirname($_SERVER['SCRIPT_NAME']));
$version = '3.0';
$page = 'add-project-images';
$lastModified = date("Y-m-d\TH:i:s\Z", filemtime(__FILE__));

// 🔐 Start session and verify Buwana JWT (auto-redirects if not logged in)
require_once '../auth/session_start.php';

// 🆔 Retrieve the authenticated user's Buwana ID
$buwana_id = $_SESSION['buwana_id'] ?? '';

// 🧭 Buwana app registration check
// --------------------------------------------------
// Even though the user is logged in with Buwana, there is still a chance they
// have not connected their Buwana account to this specific client app yet.
// We call the shared Buwana API to confirm the "registered" connection and
// redirect them to the app-connect flow if the relationship is missing.
$client_id = 'gbrk_f2c61a85a4cd4b8b89a7';
if (!empty($buwana_id)) {
    $api_endpoint = 'https://buwana.ecobricks.org/api/check_user_app_connection.php';
    $query = http_build_query([
        'buwana_id' => $buwana_id,
        'client_id' => $client_id,
        'lang' => $lang ?? 'en'
    ]);

    $ch = curl_init("{$api_endpoint}?{$query}");
    if ($ch) {
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $api_response = curl_exec($ch);
        $curl_error = curl_error($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($api_response !== false && $http_code === 200) {
            $connection_status = json_decode($api_response, true);
            if (json_last_error() === JSON_ERROR_NONE && isset($connection_status['connected']) && !$connection_status['connected']) {
                $redirect_url = $connection_status['app_login_url'] ?? '';
                if (!empty($redirect_url)) {
                    header("Location: {$redirect_url}");
                    exit();
                }
            }
        } else {
            error_log('Buwana connection check failed: ' . ($curl_error ?: 'Unexpected HTTP ' . $http_code));
        }
    }
}

// 🔗 Establish DB connections to GoBrik and Buwana
require_once '../gobrikconn_env.php';
require_once '../buwanaconn_env.php';
$conn = $gobrik_conn;

// 🌎 Fetch user meta from Buwana database
$user_continent_icon = getUserContinent($buwana_conn, $buwana_id);
$earthling_emoji = getUserEarthlingEmoji($buwana_conn, $buwana_id);
$user_location_watershed = getWatershedName($buwana_conn, $buwana_id);
$user_location_full = getUserFullLocation($buwana_conn, $buwana_id);
$gea_status = getGEA_status($buwana_id);
$user_roles = getUser_Role($buwana_id);
$user_community_name = getCommunityName($buwana_conn, $buwana_id);

// 👤 Look up user's GoBrik account info
$sql_lookup_user = "SELECT first_name, ecobricks_made, ecobricker_id, location_full_txt, user_capabilities FROM tb_ecobrickers WHERE buwana_id = ?";
$stmt_lookup_user = $gobrik_conn->prepare($sql_lookup_user);
if ($stmt_lookup_user) {
    $stmt_lookup_user->bind_param("i", $buwana_id);
    $stmt_lookup_user->execute();
    $stmt_lookup_user->bind_result($first_name, $ecobricks_made, $ecobricker_id, $location_full_txt, $user_capabilities_raw);
    $stmt_lookup_user->fetch();
    $stmt_lookup_user->close();
} else {
    die("Error preparing statement for tb_ecobrickers: " . $gobrik_conn->error);
}

$error_message = '';
$full_urls = [];
$thumbnail_paths = [];
$main_file_sizes = [];
$thumbnail_file_sizes = [];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['project_id'])) {
    $project_id = $_POST['project_id'];
    include '../scripts/photo-functions.php';


    // Handle project deletion
    if (isset($_POST['action']) && $_POST['action'] == 'delete_project') {
        $deleteResult = deleteProject($project_id, $gobrik_conn);
        if ($deleteResult === true) {
            echo "<script>alert('Project has been successfully deleted.'); window.location.href='add-project.php';</script>";
            exit;
        } else {    
            echo "<script>alert('" . $deleteResult . "');</script>";
            exit;
        }
    }

    $upload_dir = '../projects/photos/';
    $thumbnail_dir = '../projects/tmbs/';
 
    $db_fields = [];
    $db_values = [];
    $db_types = "";

    for ($i = 1; $i <= 6; $i++) { // Changed from 5 to 6 to accommodate six images
        $file_input_name = "photo{$i}_main";
        if (isset($_FILES[$file_input_name]) && $_FILES[$file_input_name]['error'] == UPLOAD_ERR_OK) {
            $file_extension = strtolower(pathinfo($_FILES[$file_input_name]['name'], PATHINFO_EXTENSION));
            $new_file_name_webp = 'project-' . $project_id . '-' . $i . '.webp';
            $targetPath = $upload_dir . $new_file_name_webp;

            if (resizeAndConvertToWebP($_FILES[$file_input_name]['tmp_name'], $targetPath, 1000, 88)) {
                createThumbnail($targetPath, $thumbnail_dir . $new_file_name_webp, 250, 77);
                $full_urls[] = $targetPath;
                $thumbnail_paths[] = $thumbnail_dir . $new_file_name_webp;
                $main_file_sizes[] = filesize($targetPath) / 1024;
                $thumbnail_file_sizes[] = filesize($thumbnail_dir . $new_file_name_webp) / 1024;

                array_push($db_fields, "photo{$i}_main", "photo{$i}_tmb");
                array_push($db_values, $targetPath, $thumbnail_dir . $new_file_name_webp);
                $db_types .= "ss";
            } else {
                $error_message .= "Error processing image. Please try again.<br>";
            }
        }
    }

    if (!empty($db_fields) && empty($error_message)) {
        array_push($db_fields, "ready_to_show", "logged_ts");
        array_push($db_values, 1, date("Y-m-d H:i:s"));
        $db_types .= "is";

        $fields_for_update = implode(", ", array_map(function($field) { return "{$field} = ?"; }, $db_fields));
        $update_sql = "UPDATE tb_projects SET {$fields_for_update} WHERE project_id = ?";
        $db_values[] = $project_id;
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
            'project_id' => $project_id,
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
<?php $lang='en';?>
<?php $version='2.37';?>
<?php $page='add-project-images';?>


<?php require_once ("../includes/add-project-inc.php");?>

<div class="splash-content-block"></div>
<div id="splash-bar"></div>

 <!-- PAGE CONTENT--> 


 <div id="photos-submission-box" style="display:flex;flex-flow:column;">

    <div class="form-container" id="upload-photo-form">

        <div class="step-graphic" style="width:fit-content;margin:auto;">
            <img src="../svgs/step2-log-project.svg" style="height:30px;margin-bottom:40px;" alt="Step 2: Upload images">
        </div>

        <div class="splash-form-content-block">  
            <div class="splash-box">
        
                <div class="splash-heading" data-lang-id="001-form-title">Now Upload Your Images</div>
            </div>
            <div class="splash-image" data-lang-id="003-splash-image-alt">
                <img src="../svgs/square-photo.svg?v=2" style="width:65%" alt="Please take a square photo">
            </div>
        </div>


        <p data-lang-id="002-form-description2">Show the world your project!  Upload one to five images showing your construction from different angles or times. <span style="color:red">Please upload only square photos.  Be sure photos are under 8MB.</span></p>

        
        <br>
        
        <form id="photoform" action="" method="post" enctype="multipart/form-data">
            <input type="hidden" name="project_id" value="<?php echo $_GET['project_id']; ?>">
      <!-- Photo 1 Main & Thumbnail -->
<div class="form-item">
    <div>
        <label for="photo1_main" data-lang-id="003-feature-photo">Feature image:</label><br>
        <input type="file" id="photo1_main" name="photo1_main" required>
        <p class="form-caption" data-lang-id="004-feature-desc">Please choose a featured photo for this project. Required.</p>
    </div>
</div>

<!-- Photo 2 Main & Thumbnail -->
<div class="form-item">
    <label for="photo2_main" data-lang-id="005-another-photo">Choose another photo:</label><br>
    <input type="file" id="photo2_main" name="photo2_main">
    <p class="form-caption" data-lang-id="006-another-photo-optional">Optional</p>
</div>

<!-- Photo 3 Main & Thumbnail -->
<div class="form-item">
    <label for="photo3_main" data-lang-id="007-another-photo">Choose another photo:</label><br>
    <input type="file" id="photo3_main" name="photo3_main">
    <p class="form-caption" data-lang-id="008-another-photo-optional">Optional</p>
</div>

<!-- Photo 4 Main & Thumbnail -->
<div class="form-item">
    <label for="photo4_main" data-lang-id="009-another-photo">Choose another photo:</label><br>
    <input type="file" id="photo4_main" name="photo4_main">
    <p class="form-caption" data-lang-id="010-another-photo-optional">Optional</p>
</div>

<!-- Photo 5 Main & Thumbnail -->
<div class="form-item">
    <label for="photo5_main" data-lang-id="011-another-photo">Choose another photo:</label><br>
    <input type="file" id="photo5_main" name="photo5_main">
    <p class="form-caption" data-lang-id="012-another-photo-optional">Optional</p>
</div>

<!-- Photo 5 Main & Thumbnail -->
<div class="form-item">
    <label for="photo6_main" data-lang-id="011-another-photo">Choose another photo:</label><br>
    <input type="file" id="photo6_main" name="photo6_main">
    <p class="form-caption" data-lang-id="012-another-photo-optional">Optional</p>
</div>



            <div data-lang-id="013-Xsubmit-upload-button">
                <input type='submit' value='Upload Images' aria-label='Submit Form'>
            </div>
            <div style="margin-top: 15px;">
                <a class="confirm-button" style="background:#bfc5c9;color:#1a1a1a;" href="add-project.php?id=<?php echo htmlspecialchars($_GET['project_id']); ?>">Go Back &amp; Edit Project</a>
            </div>
        </form>
    </div>



    <div id="upload-success" class="form-container" style="display:none;">
    <div class="step-graphic" style="width:fit-content;margin:auto;">
            <img src="../svgs/step3-log-project.svg" style="height:30px;margin-bottom:40px;" alt="Step 3: Upload Success">
        </div>
        <div id="upload-success-message"></div>
        <a class="confirm-button" href="project.php?project_id=<?php echo $_GET['project_id']; ?>" data-lang-id="013-view-project-post">🎉 View Project Post</a>
        <a class="confirm-button" data-lang-id="014-edit-project" href="edit-project.php?project_id=<?php echo $_GET['project_id']; ?>">Edit Project Post</a>
      

        <form id="deleteForm" action="" method="POST">
            <input type="hidden" name="project_id" value="<?php echo htmlspecialchars($_GET['project_id']); ?>">
            <input type="hidden" name="action" value="delete_project">
            <a class="confirm-button" style="background:red; cursor:pointer;" id="deleteButton" data-lang-id="014-delete-project">❌ Delete Project</a>
        </form>

    </div>


<a href="#" onclick="goBack()"  aria-label="Go back to re-enter data" class="back-link" data-lang-id="015-go-back-link">↩ Back to Step 1</a>

</div>

  
<br><br>

</div>


       
	<!--FOOTER STARTS HERE-->

	<?php require_once ("../footer-2025.php");?>

</div>


<script>
    
//DELETE BUTTON

// Define messages for different languages
var messages = {
    en: 'Are you sure you want to delete this project? This action cannot be undone.',
    id: 'Apakah Anda yakin ingin menghapus proyek ini? Tindakan ini tidak dapat dibatalkan.',
    es: '¿Estás seguro de que deseas eliminar este proyecto? Esta acción no se puede deshacer.',
    fr: 'Êtes-vous sûr de vouloir supprimer ce projet ? Cette action est irréversible.'
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





//UPLOAD SUBMIT ACTION AND BUTTON

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

    var fileInput = document.getElementById('photo1_main');
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
            description: "Nice. Your project has now been added to the database.",
            button: "➕ Add Next Project"
        },
        es: {
            heading: "Carga Exitosa!",
            description: "Genial. Tu proyecto ha sido agregado a la base de datos.",
            button: "➕ Agregar Siguiente Proyecto"
        },
        fr: {
            heading: "Téléchargement Réussi!",
            description: "Super. Votre projet a été ajouté à la base de données.",
            button: "➕ Ajouter le Projet Suivant"
        },
        id: {
            heading: "Berhasil Diunggah!",
            description: "Bagus. Proyek Anda telah ditambahkan ke dalam basis data.",
            button: "➕ Tambah Proyek Berikutnya"
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
        var modalCaption = captionText = directoryPathText + ' | ' + data.main_file_sizes[i].toFixed(1) + ' Kb | ' + data.thumbnail_file_sizes[i].toFixed(1) + ' Kb';

        galleryHTML += '<div class="gal-photo" onclick="viewGalleryImage(\'' + fullUrlText + '\', \'' + modalCaption + '\')">';
        galleryHTML += '<img src="' + data.thumbnail_paths[i] + '" alt="' + directoryPathText + '">';
        galleryHTML += '<p style="font-size:small;">' + captionText + '</p>';
        galleryHTML += '</div>';
    }

    galleryHTML += '</div>';
    successMessage += galleryHTML;

    successMessage += '<a class="confirm-button" href="add-project.php">' + selectedMessage.button + '</a>';

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
