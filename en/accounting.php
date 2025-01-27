<?php
require_once '../earthenAuth_helper.php'; // Include the authentication helper functions
require '../vendor/autoload.php'; // Path to Composer's autoloader

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

// Set page variables
$lang = basename(dirname($_SERVER['SCRIPT_NAME']));
$version = '0.53';
$page = 'accounting';
$lastModified = date("Y-m-d\TH:i:s\Z", filemtime(__FILE__));

// LOGIN AND ROLE CHECK:
if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

$buwana_id = $_SESSION['buwana_id'];
require_once '../gobrikconn_env.php';

$query = "SELECT user_roles FROM tb_ecobrickers WHERE buwana_id = ?";
if ($stmt = $gobrik_conn->prepare($query)) {
    $stmt->bind_param("i", $buwana_id);
    $stmt->execute();
    $stmt->bind_result($user_roles);

    if ($stmt->fetch()) {
        if (stripos($user_roles, 'admin') === false) {
            echo "<script>
                alert('Sorry, only admins can see this page.');
                window.location.href = 'dashboard.php';
            </script>";
            exit();
        }
    } else {
        echo "<script>
            alert('User record not found.');
            window.location.href = 'dashboard.php';
        </script>";
        exit();
    }
    $stmt->close();
} else {
    echo "<script>
        alert('Error checking user role. Please try again later.');
        window.location.href = 'dashboard.php';
    </script>";
    exit();
}


?>





<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
<meta charset="UTF-8">
<title>Admin Send Email Check</title>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<?php require_once ("../includes/admin-panel-inc.php");?>

<div class="splash-title-block"></div>
<div id="splash-bar"></div>

<!-- PAGE CONTENT -->
<div id="top-page-image" class="message-birded top-page-image"></div>

<div id="form-submission-box" class="landing-page-form">
    <div class="form-container">

       <!-- Email confirmation form -->

<div style="text-align:center;">
    <img src="..pngs/openbooks.png" width="250px" height="250px">
    <h2>GEA OpenBooks</h2>
    <p>Our backend accounting.</p>

      <div class="overflow">
            <table id="revenues" class="display" style="width:100%">
                <thead>
                    <tr>
                        <th data-lang-id="012-id-column">ID</th>
                        <th data-lang-id="013-date-column">Date</th>
                        <th data-lang-id="014-sender-column">Sender</th>
                        <th data-lang-id="015-category-column">Category</th>
                        <th data-lang-id="016-tran-name-column">Transaction</th>
                        <th data-lang-id="017-amount-usd-column">Amount USD</th>
                        <th data-lang-id="018-amount-idr-column">Amount IDR</th>
                        <th data-lang-id="019-type-column">Type</th>
                    </tr>
                </thead>
                <tfoot>
                    <tr>
                        <th data-lang-id="012-id-column">ID</th>
                        <th data-lang-id="013-date-column">Date</th>
                        <th data-lang-id="014-sender-column">Sender</th>
                        <th data-lang-id="015-category-column">Category</th>
                        <th data-lang-id="016-tran-name-column">Transaction</th>
                        <th data-lang-id="017-amount-usd-column">Amount USD</th>
                        <th data-lang-id="018-amount-idr-column">Amount IDR</th>
                        <th data-lang-id="019-type-column">Type</th>
                    </tr>
                </tfoot>
            </table>

    </div>

</div>


</div> <!--Closes main-->


<!--FOOTER STARTS HERE-->
<?php require_once ("../footer-2024.php"); ?>




</body>
</html>