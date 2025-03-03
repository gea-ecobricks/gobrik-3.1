<?php
require_once '../earthenAuth_helper.php'; // Include the authentication helper functions

// Set page variables
$lang = basename(dirname($_SERVER['SCRIPT_NAME']));
$version = '0.52';
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
                <!--<h4 data-lang-id="002-under-construction" style="color:orange;">🚧 Under construction</h4>-->
                <p data-lang-id="003-apology-XX">Offsetting is a way to take responsibility for plastic you've consumed or produced and to support the global plastic transition ecobrick movement.</p>



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
    <div class="live-aes-pricing">
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

<div id="aes-purchase-form" class="dashboard-panel">
    <div style="display: flex; flex-direction: column; align-items: center; text-align: center; padding: 20px;">

       <!-- Plastic Order Input with "Kg" inside -->
       <div style="display: flex; align-items: center; position: relative; text-align: center;">
            <label for="plastic-order-amount" style="font-size: 1.2em; margin-bottom: 10px; display: block; width: 100%;">
                Enter Plastic to Offset:
            </label>

            <div style="position: relative; display: inline-flex; align-items: center;">
                <input type="number" id="plastic-order-amount" min="1" step="1" value="10"
                    style="font-size: 3em !important; text-align: right; width: 200px; padding: 10px; padding-right: 50px;">
                <span style="position: absolute; right: 10px; font-size: 1.5em; font-weight: bold; color: grey;">
                    Kg
                </span>
            </div>
        </div>

        <!-- Price Calculation Display with Currency Selection -->
        <div style="display: flex; align-items: center; font-size: 1.5em; margin-top: 15px;">
            <span id="price-calculation">0</span>
            <select id="currency-selector" style="font-size: 1em; margin-left: 10px; padding: 5px; border: none; background: none; font-weight: bold;">
                <option value="IDR" selected>IDR</option>
                <option value="EUR">EUR</option>
                <option value="USD">USD</option>
                <option value="CAD">CAD</option>
                <option value="GBP">GBP</option>
            </select>
        </div>

        <!-- Order Button -->
        <button id="order-button" class="confirm-button enabled" style="margin-top: 20px; font-size: 1.2em; padding: 10px 20px; cursor: pointer;">
            Set up Purchase ➡️
        </button>

    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const plasticInput = document.getElementById("plastic-order-amount");
    const priceDisplay = document.getElementById("price-calculation");
    const currencySelector = document.getElementById("currency-selector");
    const orderButton = document.getElementById("order-button");

    // AES Rolling Price per kg (from PHP)
    const aesRollingPrice = <?php echo json_encode($aes_rolling, JSON_NUMERIC_CHECK); ?>;

    // Conversion rates
    const conversionRates = {
        "IDR": 1,
        "EUR": 17193.174,
        "USD": 16451,
        "CAD": 11403,
        "GBP": 20818
    };

    function updatePrice() {
        let kg = parseFloat(plasticInput.value) || 0;
        let selectedCurrency = currencySelector.value;
        let priceInIDR = kg * aesRollingPrice;
        let convertedPrice = priceInIDR / conversionRates[selectedCurrency];

        // Format price with commas and two decimal places
        priceDisplay.textContent = convertedPrice.toLocaleString(undefined, {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    // Initialize the price display on page load
    updatePrice();

    // Update price whenever input or currency changes
    plasticInput.addEventListener("input", updatePrice);
    currencySelector.addEventListener("change", updatePrice);

    // Order button alert
    orderButton.addEventListener("click", function() {
        alert("Sorry! AES offsetting is still in development. Orders cannot yet be completed.");
    });
});
</script>





 <div id="offset-learn-more" class="dashboard-panel">
     <p>Learn more about the core concepts being plastic offsetting and the way we calculate our cost per kg.
         <div class="menu-buttons-row">
            <a href="https://ecobricks.org/en/open-books.php" class="page-button" id="open-books-button" data-lang-id="001-log-an-ecobrickXX" style="margin: 10px;">↗ GEA Open Books</a>
             <a href="https://ecobricks.org/en/offsets.php" target="_blank" class="page-button" id="about-offsetting-button" data-lang-id="002-learn-about-offsettingXX" style="margin: 10px;">↗ Learn about Plastic Offsetting</a>
             <a href="https://ecobricks.org/en/brikchain.php" target="_blank" class="page-button" id="brikcahin-button" data-lang-id="002-learn-about-offsettingXX" style="margin: 10px;">↗ Brikchain</a>
         </div>
<p style="font-size: 0.85em; margin-top:10px;">
↗️ Links open to ecobricks.org</p>
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
