<?php
require_once '../earthenAuth_helper.php'; // Include the authentication helper functions

// Set page variables
$lang = basename(dirname($_SERVER['SCRIPT_NAME']));
$version = '0.5';
$page = 'admin-panel';
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
    $user_location_watershed = getWatershedName($buwana_conn, $buwana_id);
    $user_location_full = getUserFullLocation($buwana_conn, $buwana_id);
    $gea_status = getGEA_status($buwana_id);
    $user_community_name = getCommunityName($buwana_conn, $buwana_id);
    $first_name = getFirstName($buwana_conn, $buwana_id);

    // Check if the user is an admin
    if (strpos($gea_status, 'Admin') === false) {
        echo "<script>
            alert('Sorry, this page is for admins only.');
            window.location.href = 'dashboard.php';
        </script>";
        exit();
    }

    $buwana_conn->close();  // Close the database connection
} else {

}

// Include database connection
require_once '../gobrikconn_env.php';


// Fetch the count of ecobricks and the total weight in kg
$sql = "SELECT COUNT(*) as ecobrick_count, SUM(weight_g) / 1000 as total_weight FROM tb_ecobricks WHERE status != 'not ready'";
$result = $gobrik_conn->query($sql);

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $ecobrick_count = number_format($row['ecobrick_count'] ?? 0);
    $total_weight = number_format(round($row['total_weight'] ?? 0)); // Format with commas and round to the nearest whole number
} else {
    $ecobrick_count = '0';
    $total_weight = '0';
}

$gobrik_conn->close();

echo '<!DOCTYPE html>
<html lang="' . htmlspecialchars($lang, ENT_QUOTES, 'UTF-8') . '">
<head>
<meta charset="UTF-8">
';
?>

<!-- Page CSS & JS Initialization -->
<?php require_once("../includes/admin-panel-inc.php"); ?>

<div class="splash-title-block"></div>
<div id="splash-bar"></div>

<!-- PAGE CONTENT -->
<div id="top-page-image" class="my-ecobricks top-page-image"></div>

<div id="form-submission-box" class="landing-page-form">
    <div class="form-container">
        <div style="text-align:center;width:100%;margin:auto;margin-top:25px;">
            <h2 data-lang-id="001-main-title">Admin Panel</h2>
            <p>
                Review ecobrickers and the status of the test welcome email.
            </p>

            <table id="newest-ecobrickers" class="display responsive nowrap" style="width:100%">
                <thead>
                    <tr>
                        <th>Buwana</th>
                        <th>Name</th>
                        <th>GEA Status</th>
                        <th>Roles</th>
                        <th>Briks</th>
                        <th>Logins</th>
                        <th>Email</th>
                        <th>Location</th> <!-- Hidden initially -->
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
        "pageLength": 100, // Show 100 results per page by default
        "order": [[0, "desc"]], // Sort by Buwana ID (highest first)
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
            { "data": "buwana_id" },        // Buwana
            { "data": "full_name" },        // Name
            { "data": "gea_status" },       // GEA Status
            { "data": "user_roles" },       // Roles
            { "data": "ecobricks_made" },   // Briks
            { "data": "login_count" },      // Logins
            { "data": "test_email_status" },// Email
            { "data": "location_full",      // Location (hidden initially)
              "responsivePriority": 2 }     // Ensures it's shown via the green "+" button
        ],
        "columnDefs": [
            {
                "targets": [7],        // Target the "Location" column
                "visible": false,      // Hide it by default
                "responsivePriority": 2 // Show it on row expansion
            }
        ]
    });
});
</script>

</body>
</html>
