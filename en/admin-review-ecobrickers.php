<?php
require_once '../earthenAuth_helper.php'; // Include the authentication helper functions

// Set page variables
$lang = basename(dirname($_SERVER['SCRIPT_NAME']));
$version = '0.445';
$page = 'admin-review';
$lastModified = date("Y-m-d\TH:i:s\Z", filemtime(__FILE__));
$is_logged_in = isLoggedIn();

// Check if the user is logged in
if ($is_logged_in) {
    $buwana_id = $_SESSION['buwana_id'];
    require_once '../gobrikconn_env.php';
    require_once '../buwanaconn_env.php';

    // Fetch user's location and other details
    $user_location_full = getUserFullLocation($buwana_conn, $buwana_id);
    $first_name = getFirstName($buwana_conn, $buwana_id);

    $buwana_conn->close();
}

require_once '../gobrikconn_env.php';
$gobrik_conn->close();

echo '<!DOCTYPE html>
<html lang="' . htmlspecialchars($lang, ENT_QUOTES, 'UTF-8') . '">
<head>
<meta charset="UTF-8">
<title>Admin Review - New Ecobrickers</title>
</head>';
?>

<!-- Page CSS & JS Initialization -->
<?php require_once("../includes/admin-review-inc.php"); ?>

<div class="splash-title-block"></div>
<div id="splash-bar"></div>

<!-- PAGE CONTENT -->
<div id="top-page-image" class="my-ecobricks top-page-image"></div>

<div id="form-submission-box" class="landing-page-form">
    <div class="form-container">
        <div style="text-align:center;width:100%;margin:auto;margin-top:25px;">
            <h2 data-lang-id="001-main-title">Admin Review - New Ecobrickers</h2>
            <p>
                Review and authenticate the newest activated ecobrickers.
            </p>

            <table id="newest-ecobrickers" class="display responsive nowrap" style="width:100%">
                <thead>
                    <tr>
                        <th>Full Name</th>
                        <th>GEA Status</th>
                        <th>User Roles</th>
                        <th>Ecobricks Made</th>
                        <th>Login Count</th>
                        <th>Location</th>
                        <th>Community ID</th>
                        <th>Country ID</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- DataTables will populate this via AJAX -->
                </tbody>
            </table>

        </div>
    </div>
</div>

<!-- FOOTER -->
<?php require_once("../footer-2024.php"); ?>

<script>
$(document).ready(function() {
    var userLang = "<?php echo htmlspecialchars($lang); ?>"; // Get the user's language

    $("#newest-ecobrickers").DataTable({
        "responsive": true,
        "serverSide": true,
        "processing": true,
        "ajax": {
            "url": "../api/fetch_newest_ecobrickers.php",
            "type": "POST"
        },
        "pageLength": 10,
        "language": {
            "emptyTable": "It looks like no ecobrickers have activated their accounts yet!",
            "info": "Showing _START_ to _END_ of _TOTAL_ ecobrickers",
            "infoEmpty": "No ecobrickers available",
            "loadingRecords": "Loading ecobrickers...",
            "processing": "Processing...",
            "search": "",
            "paginate": {
                "first": "First",
                "last": "Last",
                "next": "Next",
                "previous": "Previous"
            }
        },
        "columns": [
            { "data": "full_name" },
            { "data": "gea_status" },
            { "data": "user_roles" },
            { "data": "ecobricks_made" },
            { "data": "login_count" },
            { "data": "location_full" },
            { "data": "community_id" },
            { "data": "country_id" }
        ]
    });
});
</script>

</body>
</html>
