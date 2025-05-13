<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

$lang = basename(dirname($_SERVER['SCRIPT_NAME']));
$version = '0.476';
$page = 'goodbye';
$lastModified = date("Y-m-d\TH:i:s\Z", filemtime(__FILE__));

// PART 1: Check if the user is already logged in
if (isset($_SESSION['buwana_id'])) {
    header("Location: dashboard.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
<meta charset="UTF-8">

<!--
GoBrik.com site version 3.0
Developed and made open source by the Global Ecobrick Alliance
See our git hub repository for the full code and to help out:
https://github.com/gea-ecobricks/gobrik-3.0/tree/main/en-->

<?php require_once ("../includes/activate-inc.php");?>

<div class="splash-title-block"></div>
<div id="splash-bar"></div>

<!-- PAGE CONTENT -->
<div id="top-page-image" class="regen-top top-page-image"></div>

<div id="form-submission-box" class="landing-page-form">
    <div class="form-container">

      <div style="text-align:center;width:100%;margin:auto;">

        <h1 data-lang-id="001-good-bye">Goodbye!</h1>
        <p data-lang-id="002-successfuly-deleted">Your account has been successfully deleted.</p>
        <p data-lang-id="003-change-mind">If you change your mind, you can <a href="signup.php">create a new account</a> anytime.</p>
    </div>
 </div>
    </div>
</div>
</div>

<!--FOOTER STARTS HERE-->
<?php require_once ("../footer-2025.php"); ?>


</body>
</html>
