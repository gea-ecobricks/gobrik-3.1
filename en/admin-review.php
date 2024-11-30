<?php
require_once '../earthenAuth_helper.php'; // Include the authentication helper functions

// Set page variables
$lang = basename(dirname($_SERVER['SCRIPT_NAME']));
$version = '0.445';
$page = 'admin-review';
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
<?php require_once("../includes/admin-review-inc.php"); ?>


    <div class="splash-title-block"></div>
    <div id="splash-bar"></div>

    <!-- PAGE CONTENT -->
    <div id="top-page-image" class="my-ecobricks top-page-image"></div>

    <div id="form-submission-box" class="landing-page-form">
        <div class="form-container">
            <div style="text-align:center;width:100%;margin:auto;margin-top:25px;">
                <h2 data-lang-id="001-main-title">Admin Review</h2>
                <p>
                    Review and authenticate the latest ecobricks.
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
    $(document).ready(function() {
        var userLang = "<?php echo htmlspecialchars($lang); ?>"; // Get the user's language

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
                        if (type === 'display') {
                            return `<button class="serial-button" data-serial-no="${data}" data-status="${row.status}" title="View Ecobrick Details">${data}</button>`;
                        }
                        return data;
                    },
                    "orderable": false
                }
            ],
            "columnDefs": [
                { "orderable": false, "targets": [0, 6] }, // Make the image and status columns unsortable
                { "className": "all", "targets": [0, 1, 3, 7] }, // Ensure Brik (thumbnail), Maker, Weight, and Serial always display
                { "className": "min-tablet", "targets": [2, 4, 5] }, // These fields can be hidden first on smaller screens
            ],
            "initComplete": function() {
                var searchBox = $("div.dataTables_filter input");
                searchBox.attr("placeholder", "Search your briks...");

                // Add event listener for clicks on the serial number buttons
                $('#latest-ecobricks tbody').on('click', '.serial-button', function() {
                    var serialNo = $(this).data('serial-no');
                    var status = $(this).data('status');
                    viewEcobrickActions(serialNo, status, userLang);
                });
            }
        });
    });
</script>


<script>
function viewEcobrickActions(serial_no, status, lang) {
    console.log("Button clicked with serial number:", serial_no);
    const modal = document.getElementById('form-modal-message');
    const messageContainer = document.querySelector('.modal-message');
    const modalBox = document.getElementById('modal-content-box');

    // Clear existing content in the modal
    messageContainer.innerHTML = '';

    // Determine the appropriate language object
    let translations;
    switch (lang) {
        case 'fr':
            translations = fr_Translations;
            break;
        case 'es':
            translations = es_Translations;
            break;
        case 'id':
            translations = id_Translations;
            break;
        default:
            translations = en_Translations; // Default to English
    }

    // Properly encode serial number for URL safety
    let encodedSerialNo = encodeURIComponent(serial_no);
    let ecobrickURL = `https://beta.gobrik.com/en/brik.php?serial_no=${encodedSerialNo}`;

   // Construct the content (stack of buttons) using string concatenation to avoid issues
let content = '';

content += '<a class="ecobrick-action-button" href="brik.php?serial_no=' + encodedSerialNo + '" data-lang-id="013-view-ecobrick-post">';
content += '🔍 ' + translations['013-view-ecobrick-post'];
content += '</a>';

// Conditionally display the "Edit Ecobrick" button if the status is not authenticated
if (status !== "authenticated") {
    content += '<a class="ecobrick-action-button" href="log.php?retry=' + encodedSerialNo + '" data-lang-id="015-edit-ecobrick">';
    content += '✏️ ' + translations['015-edit-ecobrick'];
    content += '</a>';
}

// Add the "Share Ecobrick" button
content += '<a class="ecobrick-action-button" href="javascript:void(0);" onclick="copyEcobrickLink(\'' + ecobrickURL + '\', this)" data-lang-id="016-share-ecobrick">';
content += '🔗 ' + (translations['016-share-ecobrick'] || 'Share Ecobrick');
content += '</a>';

// Conditionally display the "Validate" Ecobrick" button if the status is not authenticated
if (status !== "authenticated") {
    content += '<a class="ecobrick-action-button" href="validate-1.php?id=' + encodedSerialNo + '" data-lang-id="015-edit-ecobrick">';
    content += '🧐 ' + translations['018-validate'] || 'Validate');
    content += '</a>';
}

// Add the "Delete Ecobrick" button
content += '<a class="ecobrick-action-button deleter-button" href="javascript:void(0);" onclick="deleteEcobrick(\'' + encodedSerialNo + '\')" data-lang-id="014-delete-ecobrick">';
content += '❌ ' + translations['014-delete-ecobrick'];
content += '</a>';

// Insert the content into the message container
messageContainer.innerHTML = content;


    // Display the modal
    modal.style.display = 'flex';
    modalBox.style.background = 'none';
    document.getElementById('page-content').classList.add('blurred');
    document.getElementById('footer-full').classList.add('blurred');
    document.body.classList.add('modal-open');
}

// Function to copy the Ecobrick URL to clipboard and change the button text
function copyEcobrickLink(url, button) {
    // Use the modern clipboard API, if available
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(url)
            .then(() => {
                // Change the button text to "URL Copied!"
                button.innerHTML = 'URL Copied!';
                // After 1 second, close the modal
                setTimeout(closeInfoModal, 1000);
            })
            .catch(err => {
                console.error('Failed to copy: ', err);
                alert('Error copying URL. Please try again.');
            });
    } else {
        // Fallback for older browsers
        const tempInput = document.createElement('input');
        tempInput.value = url;
        document.body.appendChild(tempInput);
        tempInput.select();
        document.execCommand('copy');
        document.body.removeChild(tempInput);

        // Change the button text to "URL Copied!"
        button.innerHTML = '🤩 URL Copied!';

        // After 1 second, close the modal
        setTimeout(closeInfoModal, 1000);
    }
}



// Function to delete an ecobrick

function deleteEcobrick(serial_no) {
    // Ask the user for confirmation
    if (confirm('Are you sure you want to delete this ecobrick from the database? This cannot be undone.')) {
        // Send the delete request via fetch
        fetch('delete-ecobrick.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                'serial_no': serial_no, // Send serial_no
                'action': 'delete_ecobrick' // Include action for clarity
            })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok.');
            }
            return response.json(); // Expecting JSON from the server
        })
        .then(data => {
            if (data.success) {
                alert('The specified ecobrick has been successfully deleted.');
                window.location.href = 'admin-review.php'; // Redirect after deletion
            } else {
                alert('There was an error deleting the ecobrick: ' + data.error);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('There was an error processing your request.');
        });
    }
}
</script>




</body>
</html>
