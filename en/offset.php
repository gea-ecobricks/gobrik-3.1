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
                <p data-lang-id="003-apology">Our plastic offsetting system is offline as we move from GoBrik 2.0 to GoBrik 3.0.  Stay tuned... it will be back and better soon!</p>


	<!--LIVE DATA-->
	<?php
// Include the GoBrik database connection credentials
require_once '../gobrikconn_env.php';

try {
    // Query the view to fetch all rows for aggregation
    $sql = "SELECT year,
                   brick_count,
                   total_brk,
                   weight,
                   tot_idr_exp_amt,
                   tot_idr_rev_amt,
                   final_aes_plastic_cost_idr
            FROM vw_detail_sums_by_year_idr";

    $result = $gobrik_conn->query($sql);

    if (!$result || $result->num_rows === 0) {
        throw new Exception("Failed to retrieve data or no data available.");
    }

    // Initialize sum variables
    $sum_ecobricks = 0;
    $sum_brikcoins = 0;
    $sum_weight = 0;
    $sum_expenses = 0;
    $sum_revenue = 0;
    $sum_costs = 0;
    $row_count = 0;

    // Aggregate data
    while ($row = $result->fetch_assoc()) {
        // Normalize values by removing commas and converting to float
        $sum_ecobricks += (float)str_replace(',', '', $row['brick_count']);
        $sum_brikcoins += (float)str_replace(',', '', $row['total_brk']);
        $sum_weight += (float)str_replace(',', '', $row['weight']);
        $sum_expenses += (float)str_replace(',', '', $row['tot_idr_exp_amt']);
        $sum_revenue += (float)str_replace(',', '', $row['tot_idr_rev_amt']);
        $sum_costs += (float)str_replace(',', '', $row['final_aes_plastic_cost_idr']);
        $row_count++;
    }

    // Calculate average cost
    $avg_cost = $row_count > 0 ? $sum_costs / $row_count : 0;

    // Calculate AES rolling cost
    $aes_rolling = $sum_weight > 0 ? $sum_expenses / $sum_weight : 0;

    // Output the HTML
    echo '
    <div class="live-data" style="margin-top:30px;display:flex;flex-flow:row"">
        <div id="aes-image" style="width:15%">
                <img src="../svgs/aes-brk-vertical.svg?v=2" style="width:95%; margin-top:20px;"><p style="font-size: 1.0em; margin-top:5px;">
            </div>
        <div id="brikchain-totals">
            <p><span class="blink">◉  </span> ' . number_format($aes_rolling, 2) . ' &#8202; IDR per 1 Kg of AES Plastic</p>
            <ul>
                <li>Total ecobricks authenticated: ' . number_format($sum_ecobricks) . '</li>
                <li>Total Brikcoins Generated: ' . number_format($sum_brikcoins) . '&#8202;ß</li>
                <li>Total Authenticatd Sequestered Plastic: ' . number_format($sum_weight, 2) . '&#8202;kg</li>
                <li>Total System Expenses: ' . number_format($sum_expenses, 2) . '&#8202;IDR</li>

                <!--
                <li>Total AES Sales: ' . number_format($sum_revenue, 2) . ' IDR</li>
                <li>Avg AES Price: ' . number_format($avg_cost, 2) . ' IDR</li>-->
            </ul>
            <p style="font-size: 0.85em; margin-top:20px;" data-lang-id="006-current-pricing">
                The price per kg of <a href="offsets.php">of AES plastic offsets</a> is a function of system authenticataed plastic and GEA system expenses.
            </p>
        </div>
    </div>';
} catch (Exception $e) {
    // Handle any errors and output a friendly message
    echo '<p>Error: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '</p>';
}
?>


 <div style="display:flex;flex-flow:row;width:100%;justify-content:center;">
            <a href="log.php" class="confirm-button enabled" id="log-ecobrick-button" data-lang-id="001-log-an-ecobrick" style="margin: 10px;">➕ Log an Ecobrick</a>
             <a href="https://ecobricks.org/en/offsets.php" target="_blank" class="confirm-button enabled" id="log-ecobrick-button" data-lang-id="002-learn-about-offsetting" style="margin: 10px;">↗️ Learn about Plastic Offsetting</a>


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
