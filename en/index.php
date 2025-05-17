<?php
require_once '../earthenAuth_helper.php'; // Include the authentication helper functions

// Set page variables
$lang = basename(dirname($_SERVER['SCRIPT_NAME']));
$version = '0.426';
$page = 'index';
$lastModified = date("Y-m-d\TH:i:s\Z", filemtime(__FILE__));
$is_logged_in = isLoggedIn(); // Check if the user is logged in using the helper function


// Check if the user is logged in
if (isLoggedIn()) {
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
    $user_community_name = getCommunityName($buwana_conn, $buwana_id);
    $first_name = getFirstName($buwana_conn, $buwana_id);

    $buwana_conn->close();  // Close the database connection
} else {

}

// // Determine if the user is logged in for dynamic content handling later
// $is_logged_in = isset($buwana_id) && !empty($first_name);

echo '<!DOCTYPE html>
<html lang="' . htmlspecialchars($lang, ENT_QUOTES, 'UTF-8') . '">
<head>
<meta charset="UTF-8">
';
?>


<title>GoBrik | 3.0</title>


<!--
Deploy code
cd repositories/gobrik-3-0-2
cp -R en/* id/

GoBrik.com site version 3.0
Developed and made open source by the Global Ecobrick Alliance
See our git hub repository for the full code and to help out:
https://github.com/gea-ecobricks/gobrik-3.0/tree/main/en-->



<?php require_once ("../includes/index-inc.php");?>


<div class="clouds-new2" style=" margin-bottom:25px;background-color:var(--general-background);">
    <div class="biosphere">
            <img src="../webps/biosphere-blanked.webp" width="400" height="400" alt="biosphere">
    </div>
    <div class="landing-content" style="height:100vh;justify-content: center;
  display: flex;
  flex-flow: column;">

        <div class="main-landing-graphic">
            <img src="../webps/ecobrick-team-blanked.webp" style="width:100%;height:43%;" alt="Unite with ecobrickers around the world">
        </div>
        <div class="big-header" data-lang-id="000-lead-header">Together we can keep our plastic out of the biosphere and out of industry.</div>
        <div class="welcome-text" data-lang-id="001-welcome-text">GoBrik helps manage your ecobricks, plastic and projects so that together we can build our greenest visions.</div>
        <div class="sign-buttons" style="display:flex;flex-flow:row;justify-content: center;">
            <div>
                <button type="button" aria-label="sign in" class="sign-innn" onclick="location.href='login.php'" title="Click here to sign in" style="cursor:pointer;">
                <i style="background: url(../svgs/bottle-icon.svg) no-repeat; width:20px; height:26px;display: inline-block;background-size:contain;margin-bottom:-5px;margin-right:4px;"></i><span data-lang-id="002-sign-in">Log in</span></button>
            </div>

            <div>
                <button type="button" aria-label="Sign up" onclick="location.href='signup.php'" class="sign-uppp" style="cursor:pointer;">
                <i style="background: url(../svgs/strike-icon.svg) no-repeat; width:20px; height:26px;display: inline-block;background-size:contain;margin-bottom: -5px;margin-left:4px;"></i><span data-lang-id="003-sign-up">Sign up</span></button>
            </div>

        </div>

        <div class="tree-text" style="padding-bottom:15px;" data-lang-id="004-account-options">
        Use your GoBrik or Buwana account to sign in.
        No account? Sign up for free!
        </div>

    </div>  <!--  landing-content-->
</div> <!-- clouds-->


<!-- FULL ECOBRICK FLOW GALLERY -->

<?php include '../gobrikconn_env.php';?>

<div class="featured-content-gallery" style="overflow-x:clip;">
        <div class="feed-live">
            <p data-lang-id="005-featured-live-brikchain"><span class="blink">⬤  </span>Live brikchain feed of authenticated ecobricks.  Click to preview.</p>
        </div>
        <div class="gallery-flex-container">


    <?php
$sql = "SELECT * FROM vw_gallery_feed;";
$result = $gobrik_conn->query($sql);

if ($result->num_rows > 0) {
    // output data of each row
    while ($row = $result->fetch_assoc()) {
        echo '<div class="gal-photo">
                <div class="photo-box">
                    <img src="../' . $row["thumb_url"] . '?v=1" alt="Ecobrick ' . $row["ecobrick_unique_id"] . ' by ' . $row["ecobrick_owner"] . ' in ' . $row["location"] . '" title="Ecobrick ' . $row["ecobrick_unique_id"] . ' by ' . $row["ecobrick_owner"] . ' in ' . $row["location"] . '" loading="lazy" onclick="ecobrickPreview(\'' . $row["photo_url"] . '\', \'' . $row["ecobrick_unique_id"] . '\', \'' . $row["weight_in_g"] . '\', \'' . $row["ecobrick_owner"] . '\', \'' . $row["location"] . '\')">
                </div>
            </div>';
    }
} else {
    echo "Failed to connect to the Brikchain database";
}
?>

<a class="photo-box-end" href="brikchain.php"></a>


</div>


        <div class="feature-content-box">
            <div class="big-header" data-lang-id="006-featured-live-heading">Ecobricking.  Live.</div>
            <div class="welcome-text" data-lang-id="007-featured-live-subheading">Ecobricks are being made, logged and validated around the world right this moment.</div>
            <div class="landing-button" style="margin:5px auto 5px auto;justify-content: center;
  display: flex">
                <a href="brikchain.php" class="feature-button"  data-lang-id="008-featured-live-button" aria-label="view brikchain">⛓️ The Brikchain</a>
            </div>
            <div class="tree-text" data-lang-id="009-featured-live-links">A feed & archive of authenticated ecobricks</div>

        </div>
    </div>







    <div class="bottom-scope" style="width:100%;height:100%;">
         <div class="landing-content">
            <div class="tree-coins" data-lang-id="010-second-feature-img" ><img src="../webps/2023-tree-blank.webp" style="width:100%;" alt="Build your greenest visions with ecobricks">


            </div>

            <div class="welcome-text" data-lang-id="011-second-text">
                Together we're securing plastic out of the biosphere to make building blocks, generate brikcoins and co-create green spaces.
               <br><br>
               <img src="../svgs/aes-brk.svg" style="width:200px;" width="200" height="77" alt="Introducing Brikcoins and AES Plastic Offsetting">
            </div>

            <div class="tree-text" data-lang-id="012-gobrik-sub-text">
                GoBrik provides ecobrickers and their communities with the tools to manage their ecobricking and to quantify its ecological value.
            </div>

        <br><br>


        </div><!--closes Landing content-->
    </div>



</div><!--closes main and starry background-->

	<!--FOOTER STARTS HERE-->

	<?php require_once ("../footer-2025.php");?>

<!--close page content-->


</body>

</html>
