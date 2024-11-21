<?php
require_once '../earthenAuth_helper.php'; // Include the authentication helper functions

// Set page variables
$lang = basename(dirname($_SERVER['SCRIPT_NAME']));
$version = '0.445';
$page = 'offset';
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

    $buwana_conn->close();  // Close the database connection
} else {

}


echo '<!DOCTYPE html>
<html lang="' . htmlspecialchars($lang, ENT_QUOTES, 'UTF-8') . '">
<head>
<meta charset="UTF-8">
';
?>

<!-- Page CSS & JS Initialization -->
<?php require_once("../includes/offset-inc.php"); ?>

    <div class="splash-title-block"></div>
    <div id="splash-bar"></div>

    <!-- PAGE CONTENT -->
    <div id="top-page-image" class="top-page-image"><img src="../svgs/eco-accounting-icon-from-ecobricks-org.svg"></div>

    <div id="form-submission-box" class="landing-page-form">
        <div class="form-container">
            <div style="text-align:center;width:100%;margin:auto;margin-top:25px;">
                <h4 data-lang-id="001-latest-ecobricks">Under Construction</h4>
                <h2 data-lang-id="002-as-of-today">Plastic Offsetting</h2>
                <p>Our plastic offsetting system is offline as we move from GoBrik 2.0 to GoBrik 3.0.  Stay tuned... it will be back and better soon!</p>

            </div>

        </div>
    </div>
</div>


    <!-- FOOTER -->
    <?php require_once("../footer-2024.php"); ?>


<script>
    $(document).ready(function() {
        $("#latest-ecobricks").DataTable({
            "responsive": true,
            "serverSide": true,
            "processing": true,
            "ajax": {
                "url": "../api/fetch_newest_briks.php",
                "type": "POST"
            },
            "pageLength": 10, // Set default number of rows per page to 10
            "language": {
                "emptyTable": "It looks like no ecobricks have been logged yet!",
                "info": "Showing _START_ to _END_ of _TOTAL_ ecobricks",
                "infoEmpty": "No ecobricks available",
                "loadingRecords": "Loading ecobricks...",
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
                { "data": "ecobrick_thumb_photo_url" }, // Brik thumbnail
                { "data": "ecobricker_maker" }, // Maker
                { "data": "location_brik" }, // Location
                { "data": "weight_g" }, // Weight
                { "data": "volume_ml" }, // Volume
                { "data": "density" }, // Density
                { "data": "status" }, // Status
                {
            "data": "serial_no",
            "render": function(data, type, row) {
                // Construct the URL for the serial_no
                const serialUrl = 'brik.php?serial_no=' + encodeURIComponent(data);

                // Return a button with the serial number
                return '<a href="' + serialUrl + '" class="serial-button" data-text="' + data + '">'
                    + '<span>' + data + '</span>'
                    + '</a>';
            }
        }
            ],
            "columnDefs": [
                { "orderable": false, "targets": [0, 6] }, // Make the image and status columns unsortable
                { "className": "all", "targets": [0, 1, 3, 7] }, // Ensure Brik (thumbnail), Maker, Weight, and Serial always display
                { "className": "min-tablet", "targets": [2, 4, 5] }, // These fields can be hidden first on smaller screens
            ],
            "initComplete": function() {
                var searchBox = $("div.dataTables_filter input");
                searchBox.attr("placeholder", "Search briks...");
            }
        });
    });
</script>





</body>
</html>
