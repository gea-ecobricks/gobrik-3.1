<?php
include 'lang.php';
$version = '0.35';
$page = 'dashboard';
$lastModified = date("Y-m-d\TH:i:s\Z", filemtime(__FILE__));

echo '<!DOCTYPE html>
<html lang="' . $lang . '">
<head>
<meta charset="UTF-8">
';
?>



<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../buwana_env.php'; // this file provides the database server, user, dbname information to access the server

$user_id = $_GET['id'] ?? null;

// Look up these fields from credentials_tb and users_tb using the user_id
$credential_type = '';
$credential_key = '';
$first_name = '';

if (isset($user_id)) {
    // First, look up the credential_type and credential_key from credentials_tb
    $sql_lookup_credential = "SELECT credential_type, credential_key FROM credentials_tb WHERE user_id = ?";
    $stmt_lookup_credential = $conn->prepare($sql_lookup_credential);

    if ($stmt_lookup_credential) {
        $stmt_lookup_credential->bind_param("i", $user_id);
        $stmt_lookup_credential->execute();
        $stmt_lookup_credential->bind_result($credential_type, $credential_key);
        $stmt_lookup_credential->fetch();
        $stmt_lookup_credential->close();
    } else {
        die("Error preparing statement for credentials_tb: " . $conn->error);
    }

    // Then, look up the first_name from users_tb
    $sql_lookup_user = "SELECT first_name FROM users_tb WHERE user_id = ?";
    $stmt_lookup_user = $conn->prepare($sql_lookup_user);

    if ($stmt_lookup_user) {
        $stmt_lookup_user->bind_param("i", $user_id);
        $stmt_lookup_user->execute();
        $stmt_lookup_user->bind_result($first_name);
        $stmt_lookup_user->fetch();
        $stmt_lookup_user->close();
    } else {
        die("Error preparing statement for users_tb: " . $conn->error);
    }
}

$conn->close();
?>






<title>Dashboard | GoBrik 3.0</title>

<!--
GoBrik.com site version 3.0
Developed and made open source by the Global Ecobrick Alliance
See our git hub repository for the full code and to help out:
https://github.com/gea-ecobricks/gobrik-3.0/tree/main/en-->

<?php require_once ("../includes/dashboard-inc.php");?>


<div class="splash-content-block"></div>
<div id="splash-bar"></div>

<!-- PAGE CONTENT-->

<div id="form-submission-box" style="height:100vh;">
    <div class="form-container">

        <div class="dolphin-pic" style="margin-top:-45px;background-size:contain;">
        <img src="../webps/earth-community.webp" width="80%">
    </div>

        <div style="text-align:center;width:100%;margin:auto;">
            <h2 data-lang-id="001-login-heading-signed-up">Welcome $Firstname!</h2>
            <p data-lang-id="002-login-subheading">You're logged into the brand new GoBrik 3.0!</p>
        </div>

       <!--SIGNUP FORM-->

    </div><!--closes Landing content-->
</div>

</div><!--closes main and starry background-->

<!--FOOTER STARTS HERE-->

<?php require_once ("../footer-2025.php");?>

</div><!--close page content-->







    <script type="text/javascript">


function showModalInfo(type) {
    const modal = document.getElementById('form-modal-message');
    const photobox = document.getElementById('modal-photo-box');
    const messageContainer = modal.querySelector('.modal-message');
    const modalBox = document.getElementById('modal-content-box');
    let content = '';
    photobox.style.display = 'none';
    switch (type) {

        case 'reset':
            content = `
                <img src="../pngs/exchange-bird.png" alt="Reset Password" height="250px" width="250px" class="preview-image">
                <div class="preview-title">Reset Password</div>
                <div class="preview-text">Oops!  This function is not yet operational.  Create another account for the moment as all accounts will be deleted once we migrate from beta to live.</div>
            `;
            break;

        default:
            content = '<p>Invalid term selected.</p>';
    }

    messageContainer.innerHTML = content;


    // Show the modal and update other page elements
    modal.style.display = 'flex';
    document.getElementById('page-content').classList.add('blurred');
    document.getElementById('footer-full').classList.add('blurred');
    document.body.classList.add('modal-open');
}


</script>





</body>
</html>
