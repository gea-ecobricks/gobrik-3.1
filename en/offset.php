<?php
require_once '../earthenAuth_helper.php'; // Include the authentication helper functions

// Set page variables
$lang = basename(dirname($_SERVER['SCRIPT_NAME']));
$version = '0.51';
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
    <div id="top-page-image" class="offsetting top-page-image"></div>

    <div id="form-submission-box" class="landing-page-form">
        <div class="form-container">
            <div style="text-align:center;width:100%;margin:auto;margin-top:25px;">
                <h1 data-lang-id="001-offsetting-title">Plastic Offsetting</h1>
                <h4 data-lang-id="002-under-construction" style="color:orange;">🚧 Under construction</h4>
                <p data-lang-id="003-apology">Offset your plastic with us.</p>



<!-- LIVE AES PRICING -->
<?php
// Include the GoBrik database connection credentials
require_once '../gobrikconn_env.php';

try {
    // Query to fetch required data
    $sql = "SELECT brick_count, weight, tot_idr_exp_amt FROM vw_detail_sums_by_year_idr";
    $result = $gobrik_conn->query($sql);

    if (!$result || $result->num_rows === 0) {
        throw new Exception("Failed to retrieve data or no data available.");
    }

    // Initialize variables
    $sum_ecobricks = 0;
    $sum_weight = 0;
    $sum_expenses = 0;

    // Aggregate data
    while ($row = $result->fetch_assoc()) {
        $sum_ecobricks += (float)str_replace(',', '', $row['brick_count']);
        $sum_weight += (float)str_replace(',', '', $row['weight']);
        $sum_expenses += (float)str_replace(',', '', $row['tot_idr_exp_amt']);
    }

    // Calculate AES rolling cost
    $aes_rolling = $sum_weight > 0 ? $sum_expenses / $sum_weight : 0;

    // Output the simplified HTML
    echo '
    <div id="live-aes-pricing">
        <p><span class="blink">◉</span> ' . number_format($aes_rolling, 2) . ' IDR per 1 Kg of AES Plastic</p>
        <p style="font-size: 0.85em; margin-top:10px;">
            Our AES plastic offsets price is a function of the costs of authenticating the ' . number_format($sum_ecobricks) . ' ecobricks recorded on the GEA\'s brikchain.
        </p>
    </div>';
} catch (Exception $e) {
    // Handle errors gracefully
    echo '<p>Error: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '</p>';
}
?>



 <div style="display:flex;flex-flow:row;width:100%;justify-content:center;">
     <p>Learn more about the core concepts being plastic offsetting and the way we calculate our cost per kg.
            <a href="https://ecobricks.org/en/open-books.php" class="confirm-button" id="open-books-button" data-lang-id="001-log-an-ecobrick" style="margin: 10px;">↗️  GEA Open Books</a>
             <a href="https://ecobricks.org/en/offsets.php" target="_blank" class="confirm-button enabled" id="about-offsetting-button" data-lang-id="002-learn-about-offsetting" style="margin: 10px;">↗️ Learn about Plastic Offsetting</a>
             <a href="https://ecobricks.org/en/brikchain.php" target="_blank" class="confirm-button enabled" id="brikcahin-button" data-lang-id="002-learn-about-offsetting" style="margin: 10px;">↗️ Brikchain</a>


        </div>

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
