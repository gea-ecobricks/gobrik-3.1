<?php
require_once '../earthenAuth_helper.php'; // Include the authentication helper functions

// Set page variables
$lang = basename(dirname($_SERVER['SCRIPT_NAME']));
$version = '0.445';
$page = 'newest-briks';
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
<?php require_once("../includes/newest-briks-inc.php"); ?>


    <div class="splash-title-block"></div>
    <div id="splash-bar"></div>

    <!-- PAGE CONTENT -->
    <div id="top-page-image" class="my-ecobricks top-page-image"></div>

    <div id="form-submission-box" class="landing-page-form">
        <div class="form-container">
            <div style="text-align:center;width:100%;margin:auto;margin-top:25px;">
                <h2 data-lang-id="001-latest-ecobricks">Newest Ecobricks</h2>
                <p><span data-lang-id="002-as-of-today">As of today, </span><?php echo $ecobrick_count; ?> <span data-lang-id="002b-have-been">ecobricks have been logged on GoBrik,
                    representing over </span><?php echo $total_weight; ?> kg <span data-lang-id="002c-of-seq-plastic">of sequestered plastic!</span>
                </p>

                <table id="latest-ecobricks" class="display responsive nowrap" style="width:100%">
                    <thead>
                        <tr>
                            <th data-lang-id="1103-brik">Brik</th>
                            <th data-lang-id="1111-maker">Maker</th>
                            <th data-lang-id="1105-location">Location</th>
                            <th data-lang-id="1104-weight">Weight</th>
                            <th data-lang-id="1108-volume">Volume</th>
                            <th data-lang-id="1109-density">Density</th>
                            <th data-lang-id="1106-status">Status</th>
                            <th data-lang-id="1107-serial">Serial</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- DataTables will populate this via AJAX -->
                    </tbody>
                </table>

            </div>

        </div>
    </div>
</div>


    <!-- FOOTER -->
    <?php require_once("../footer-2024.php"); ?>


<script>
    $(document).ready(function () {
        $('#brikchain-transactions').DataTable({
            serverSide: true, // Enable server-side processing
            processing: true, // Show a processing indicator
            ajax: {
                url: '../api/fetch_brik_transactions.php', // Server endpoint to fetch data
                type: 'POST' // HTTP method
            },
            columns: [
                { data: 'tran_id', title: '🔎 Transaction' },
                { data: 'send_ts', title: 'Issued' },
                { data: 'sender', title: 'Sender' },
                { data: 'receiver_or_receivers', title: 'Recipient' },
                { data: 'block_tran_type', title: 'Type' },
                { data: 'block_amt', title: 'Block' },
                { data: 'individual_amt', title: 'Shard' },
                {
                    data: 'ecobrick_serial_no',
                    title: 'Ecobrick',
                    render: function(data, type, row) {
                        if (data && data.trim() !== '') {
                            return `<a href="brik.php?$serial_no=${data}" target="_blank">${data}</a>`;
                        }
                        return '';
                    }
                }
            ],
            order: [[0, 'desc']], // Sort by `tran_id` in descending order
            pageLength: 10, // Default number of rows per page
            lengthMenu: [10, 25, 50, 100] // Options for rows per page
        });
    });
</script>







</body>
</html>
