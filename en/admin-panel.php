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

    $buwana_conn->close(); // Close the database connection
}

require_once '../gobrikconn_env.php';

// Initialize variables
$total_ecobrickers = 0;
$total_with_buwana_id = 0;
$total_emails_sent = 0;

// Fetch counts
$sql = "SELECT
            COUNT(*) as total_ecobrickers,
            SUM(CASE WHEN buwana_id IS NOT NULL AND buwana_id != '' THEN 1 ELSE 0 END) as total_with_buwana_id,
            SUM(CASE WHEN test_email_status = 'received' THEN 1 ELSE 0 END) as total_emails_sent
        FROM tb_ecobrickers";

$result = $gobrik_conn->query($sql);

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $total_ecobrickers = intval($row['total_ecobrickers'] ?? 0);
    $total_with_buwana_id = intval($row['total_with_buwana_id'] ?? 0);
    $total_emails_sent = intval($row['total_emails_sent'] ?? 0);
}

// Calculate percentages
$percent_with_buwana = $total_ecobrickers > 0 ? round(($total_with_buwana_id / $total_ecobrickers) * 100, 2) : 0;
$percent_emails_sent = $total_ecobrickers > 0 ? round(($total_emails_sent / $total_ecobrickers) * 100, 2) : 0;

$gobrik_conn->close();
?>

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
            <p>
                So far we have <?php echo number_format($total_ecobrickers); ?> ecobrickers on GoBrik and <?php echo number_format($total_emails_sent); ?> test emails have been sent.
                <?php echo $percent_with_buwana; ?>% have a buwana account and <?php echo $percent_emails_sent; ?>% have received the test email.
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
                        <th>Location</th>
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
<?php require_once("../footer-2024.php"); ?>

<script>
$(document).ready(function() {
    $("#newest-ecobrickers").DataTable({
        "responsive": true,
        "serverSide": true,
        "processing": true,
        "ajax": {
            "url": "../api/fetch_newest_ecobrickers.php",
            "type": "POST"
        },
        "pageLength": 100, // Show 100 rows by default
        "order": [[7, "desc"]], // Default sorting by the Buwana ID column (now column 7)
        "columns": [
            { "data": "full_name" },
            { "data": "gea_status" },
            { "data": "user_roles" },
            { "data": "ecobricks_made" },
            { "data": "login_count" },
            { "data": "test_email_status" },
            { "data": "location_full", "responsivePriority": 2 },
            {
                "data": "buwana_id",
                "orderable": true, // Allow sorting on this column
                "render": function (data, type, row) {
                    // Return a clickable button to open the modal
                    return `<button class="btn btn-primary" onclick="openEcobrickerModal(${data})">${data}</button>`;
                }
            }
        ],
        "columnDefs": [
            {
                "targets": [6], // Target the "location_full" column
                "visible": false, // Hide it by default
                "responsivePriority": 2 // Ensure it shows on row expansion
            }
        ]
    });
});




function openEcobrickerModal(buwana_id) {
    const modal = document.getElementById('form-modal-message');
    const modalContentBox = document.getElementById('modal-content-box');

    // Lock scrolling for the body
    document.body.classList.add('modal-open');

    // Clear previous modal content
    modalContentBox.innerHTML = `<h4>Loading Ecobricker Details for Buwana ID: ${buwana_id}...</h4>`;

    // Fetch ecobricker details
    fetch(`../api/fetch_ecobricker_details.php?buwana_id=${buwana_id}`)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                modalContentBox.innerHTML = `<p>${data.error}</p>`;
                return;
            }

            // Build the DataTable HTML
            let tableHTML = '<table id="ecobricker-details-table" class="display" style="width:100%">';
            tableHTML += '<thead><tr><th>Field</th><th>Value</th></tr></thead><tbody>';

            for (const [field, value] of Object.entries(data)) {
                tableHTML += `<tr><td>${field}</td><td>${value || '-'}</td></tr>`;
            }

            tableHTML += '</tbody></table>';

            // Insert the table into the modal
            modalContentBox.innerHTML = tableHTML;

            // Initialize the DataTable
            $('#ecobricker-details-table').DataTable({
                paging: false,
                searching: false,
                info: false,
                scrollX: true
            });
        })
        .catch(error => {
            modalContentBox.innerHTML = `<p>Error loading ecobricker details: ${error.message}</p>`;
        });

    // Display the modal
    modal.classList.remove('modal-hidden');
}





</script>

</body>
</html>
