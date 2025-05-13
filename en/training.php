
<!--PART 4 GENERATE META TAGS-->

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
    <?php require_once ("../includes/training-inc.php");?>

    <div class="splash-content-block"></div>
    <div id="splash-bar"></div>


<?php
require_once '../gobrikconn_env.php';

$trainingId = $_GET['training_id'];

$sql = "SELECT * FROM tb_trainings WHERE training_id = ?";
$stmt = $gobrik_conn->prepare($sql);
$stmt->bind_param("i", $trainingId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $array = $result->fetch_assoc();

		echo 
		'<div class="splash-content-block">
			<div class="splash-box">
	
				<div class="splash-heading">' . htmlspecialchars($array["training_title"], ENT_QUOTES, 'UTF-8') . '</div>
				
				<div class="splash-sub">' . htmlspecialchars($array["training_type"], ENT_QUOTES, 'UTF-8') . '</div>
			</div>
			
			<div class="splash-image">
				<a href="javascript:void(0);" onclick="viewGalleryImage(\'' . htmlspecialchars($array["training_photo0_main"], ENT_QUOTES, 'UTF-8') . '\', \'Ecobrick training ' . htmlspecialchars($array["training_id"], ENT_QUOTES, 'UTF-8') . ' was completed in ' . htmlspecialchars($array["training_country"], ENT_QUOTES, 'UTF-8') . ' and started on ' . htmlspecialchars($array["training_date"], ENT_QUOTES, 'UTF-8') . '\')"><img src="../' . htmlspecialchars($array["training_photo0_main"], ENT_QUOTES, 'UTF-8') . '" alt="Project ' . htmlspecialchars($array["training_id"], ENT_QUOTES, 'UTF-8') . ' was completed in ' . htmlspecialchars($array["training_location"], ENT_QUOTES, 'UTF-8') . ' and started on ' . htmlspecialchars($array["training_date"], ENT_QUOTES, 'UTF-8') . '"
			title="Project' . htmlspecialchars($array["training_id"], ENT_QUOTES, 'UTF-8') . ' was made in ' . htmlspecialchars($array["training_country"], ENT_QUOTES, 'UTF-8') . ' and started on ' . htmlspecialchars($array["training_date"], ENT_QUOTES, 'UTF-8') . '"></a>
			</div>    
		</div>
	
		<div id="splash-bar"></div>';

        echo '
		<div id="main-content">
            <div class="row">
                <div class="main">
                    <div class="row-details">

			            <div class="lead-page-paragraph">
                            <p>'. $array["training_title"] .'<span data-lang-id="110"> was a </span>'. $array["training_type"] .' <span data-lang-id="111">workshop run in </span>'. $array["training_country"] .'<span data-lang-id="112">.  The workshop involved </span>'. $array["no_participants"] .'<span data-lang-id="113"> and was run by GEA trainer(s) </span>'. $array["lead_trainer"] .'</p>
                        </div>
                   
                        <div id="three-column-gal" class="three-column-gal" style="margin-top:40px">';
 
                         // Loop through the available photos (up to 5)
						 for ($i = 0; $i <= 6; $i++) {
                            $photo_main_field = "training_photo" . $i . "_main";
                            $photo_tmb_field = "training_photo" . $i . "_tmb";
                            
                            // Retrieve the photo paths from the array
                            $photo_main = $array[$photo_main_field];
                            $photo_tmb = $array[$photo_tmb_field];
                            
                            // Check if the photo exists
                            if (!empty($photo_main) && !empty($photo_tmb)) {
                                echo '<div class="gal-photo" onclick="viewGalleryImage(\'../' . htmlspecialchars($photo_main, ENT_QUOTES, 'UTF-8') . '\', \'Training photo ' . $i . ' | ' . htmlspecialchars($array["training_title"], ENT_QUOTES, 'UTF-8') . ' \')">
                                        <img src="../' . htmlspecialchars($photo_tmb, ENT_QUOTES, 'UTF-8') . '">
                                      </div>';
                            }
                        }
                        echo '</div>
                        <div class="page-paragraph">';
                    
                    if (!empty($array["training_summary"])) {
                        echo '<h3><p>Training Summary</p></h3>
                              <p>'. $array["training_summary"] .'</p>
                              <br>';
                    }
                    
                    if (!empty($array["training_agenda"])) {
                        echo '<h3><p>Training Agenda</p></h3>
                              <p>'. $array["training_agenda"] .'</p>
                              <br>';
                    }
                    
                    if (!empty($array["training_success"])) {
                        echo '<h3><p>Success Story</p></h3>
                              <p>'. $array["training_success"] .'</p>
                              <br>';
                    }
                    
                    if (!empty($array["training_challenges"])) {
                        echo '<h3><p>Challenges</p></h3>
                              <p>'. $array["training_challenges"] .'</p>
                              <br>';
                    }
                    
                    if (!empty($array["training_lessons_learned"])) {
                        echo '<h3><p>Lessons Learned</p></h3>
                              <p>'. $array["training_lessons_learned"] .'</p>
                              <br>';
                    }
                    
                    echo '</div>


			
			
			
            
            <div class="page-paragraph">

            <br><hr><br> 
				<h3><p data-lang-id="151">Global Ecobrick Alliance Workshops & Trainings</p></h3>
			
				<p data-lang-id="152">Check out our currently available ecobrick, earthen and regenerative courses on our GoBrik app:</p>
				<br>

	
                <p><a class="action-btn-blue" href="https://gobrik.com/courses.php" data-lang-id="154" style="margin-top:10px;>🔎 Find a Course</a></p>
				<p style="font-size: 0.85em; margin-top:20px;" data-lang-id="155">Current courses</a></p>


				</div>
			</div>
        </div>';	
			

	}


else {
   


echo '
<div class="splash-content-block">
		<div class="splash-box">
			<div class="splash-heading">';
	
			echo 'Sorry! :-(</div>
			<div class="splash-sub" data-lang-id="151">There are no results for a training with the id of '. $trainingId .' in our database.</div>
		</div>
		<div class="splash-image"><img src="../svgs/shanti.svg?v2" style="width: 80%; margin-top:20px;border:none;box-shadow:none;" alt="empty ecobrick"></div>	
	</div>
	<div id="splash-bar"></div>

	<div id="main-content">
		<div class="row">
			<div class="main">
				<br><br>

				
			
			<div class="lead-page-paragraph">
			<p data-lang-id="152">🚧  It seems training '. $trainingId .' has not been published to the ecobricks.org database.  This could be because of a publication error.  It could also be because the training ID is mis-entered in the URL.
				</p></div><br><hr><br>
				
				<div class="page-paragraph">
                <p style="font-size:smaller">Training Location:</p>
                <p style="font-size:normal">' . $array["location_full"] . '</p><br>
                <br><hr><br> 
                <div class="page-paragraph">
                    <h3><p data-lang-id="151">Global Ecobrick Alliance Workshops & Trainings</p></h3>
                
                    <p data-lang-id="152">Check out our currently available ecobrick, earthen and regenerative courses on our GoBrik app:</p>
    
        
                    <p><a class="action-btn-blue" href="https://gobrik.com/courses.php" data-lang-id="154">🔎 Find a Course</a></p>
                    <p style="font-size: 0.85em; margin-top:20px;" data-lang-id="155">A listing of our current course options</a></p>
    
    
                    </div>
                </div>';	

	
		}
		$conn->close();

		?>
		
        <div class="side">

            <?php require_once ("side-modules/good-use.php");?>


            <?php require_once ("side-modules/signup-now.php");?>

		
   


		</div>

	</div>
</div>



	<!--FOOTER STARTS HERE-->

	<?php require_once ("../footer-2025.php");?>





</div>



<script>

function ecobrickPreview(brik_serial, weight, owner, location) {
    // Construct the image source URL
    var imageUrl = 'https://ecobricks.org/briks/ecobrick-' + brik_serial + '-file.jpeg';

    const modal = document.getElementById('form-modal-message');
    const contentBox = modal.querySelector('.modal-content-box'); // This is the part we want to hide
    const photoBox = modal.querySelector('.modal-photo-box'); // This is where we'll show the image
    const photoContainer = modal.querySelector('.modal-photo'); // The container for the image

    // Hide the content box and show the photo box
    contentBox.style.display = 'none'; // Hide the content box
    photoBox.style.display = 'block'; // Make sure the photo box is visible

    // Clear previous images from the photo container
    photoContainer.innerHTML = '';

    // Create and append the ecobrick image to the photo container
    var img = document.createElement('img');
    img.src = imageUrl;
    img.alt = "Ecobrick " + brik_serial;
    img.style.maxWidth = '90%';
    img.style.maxHeight = '75vh';
    img.style.minHeight ="400px";
    img.style.minWidth ="400px";
    img.style.margin = 'auto';
    // img.style.backgroundColor ='#8080802e';
    photoContainer.appendChild(img);

    // Add ecobrick details and view details button inside photo container
    var details = document.createElement('div');
    details.className = 'ecobrick-details';
    details.innerHTML = '<p>Ecobrick ' + brik_serial + ' | ' + weight + 'g of plastic sequestered by ' + owner + ' in ' + location + '.</p>' +
                        '<a href="details-ecobrick-page.php?serial_no=' + brik_serial + '" class="btn featured-gallery-button" style="margin-bottom: 50px;height: 25px;padding: 5px;border: none;padding: 5px 12px;">ℹ️ View Full Details</a>';
    photoContainer.appendChild(details);

    // Hide other parts of the modal that are not used for this preview
    modal.querySelector('.modal-content-box').style.display = 'none'; // Assuming this contains elements not needed for this preview

    // Show the modal
    modal.style.display = 'flex';

    //Blur out background
    document.getElementById('page-content')?.classList.add('blurred');
    document.getElementById('footer-full')?.classList.add('blurred');
    document.body.classList.add('modal-open');

}
</script>


</body>
</html>