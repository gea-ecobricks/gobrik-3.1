<?php
require_once '../earthenAuth_helper.php'; // Include the authentication helper functions
require_once '../auth/session_start.php';

// Set page variables
$lang = basename(dirname($_SERVER['SCRIPT_NAME']));
$version = '0.446';
$page = 'admin-review';
$lastModified = date("Y-m-d\TH:i:s\Z", filemtime(__FILE__));


// LOGIN AND ROLE CHECK:
//Check if the user is logged in, if not send them to login.
if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

// User is logged in, proceed to check admin status
$buwana_id = $_SESSION['buwana_id'];
require_once '../gobrikconn_env.php';

$query = "SELECT user_roles, user_capabilities FROM tb_ecobrickers WHERE buwana_id = ?";
if ($stmt = $gobrik_conn->prepare($query)) {
    $stmt->bind_param("i", $buwana_id);
    $stmt->execute();
    $stmt->bind_result($user_roles, $user_capabilities);

    if ($stmt->fetch()) {
        $user_roles = $user_roles ?? '';
        $user_capabilities = $user_capabilities ?? '';

        $has_admin_role = stripos($user_roles, 'admin') !== false;
        $has_review_capability = stripos($user_capabilities, 'review ecobricks') !== false;

        // Check if the user has an admin role or review capability
        if (!$has_admin_role && !$has_review_capability) {
            echo "<script>
                alert('Sorry, only admins or reviewers can see this page.');
                window.location.href = 'dashboard.php';
            </script>";
            exit();
        }
    } else {
        // Redirect if no user record is found
        echo "<script>
            alert('User record not found.');
            window.location.href = 'dashboard.php';
        </script>";
        exit();
    }
    $stmt->close();
} else {
    // Handle database error
    echo "<script>
        alert('Error checking user role. Please try again later.');
        window.location.href = 'dashboard.php';
    </script>";
    exit();
}
//END LOGIN AND ROLE CHECK



// Fetch additional user details after admin check
require_once '../buwanaconn_env.php';
$user_continent_icon = getUserContinent($buwana_conn, $buwana_id);
$earthling_emoji = getUserEarthlingEmoji($buwana_conn, $buwana_id);
$user_location_watershed = getWatershedName($buwana_conn, $buwana_id);
$user_location_full = getUserFullLocation($buwana_conn, $buwana_id);
$gea_status = getGEA_status($buwana_id);
$user_community_name = getCommunityName($buwana_conn, $buwana_id);
$first_name = getFirstName($buwana_conn, $buwana_id);

$buwana_conn->close(); // Close the database connection

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

                <table id="latest-ecobricks" class="display responsive" style="width:100%">
                    <thead>
                        <tr>
                            <th data-lang-id="1103-brik">Brik</th>
                            <th data-lang-id="1111-maker">Maker</th>
                            <th data-lang-id="1106-status">Status</th>

                            <th data-lang-id="1104-weight" class="metric-column">Weight</th>
                            <th data-lang-id="1108-volume" class="metric-column">Volume</th>
                            <th data-lang-id="1109-density">Density</th>
                            <th data-lang-id="1105-location">Location</th>
                            <th data-lang-id="1107-serial">Serial</th>
                            <th>⭐</th>

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
    <?php require_once("../footer-2025.php"); ?>

<script>
    $(document).ready(function() {
        var userLang = "<?php echo htmlspecialchars($lang); ?>"; // Get the user's language

        const statusOrder = new Map([
            ["awaiting validation", 0],
            ["authenticated", 1],
            ["step 2", 2],
            ["step 2 complete", 3],
            ["step two complete", 3],
            ["rejected", 4]
        ]);

        const normalizeStatusLabel = (statusLabel) => {
            if (!statusLabel) {
                return "";
            }
            return statusLabel.trim().toLowerCase().replace(/^[^a-z0-9]+/, "");
        };

        const getStatusEmoji = (statusLabel) => {
            const normalized = normalizeStatusLabel(statusLabel);
            switch (normalized) {
                case "authenticated":
                    return "✅";
                case "rejected":
                    return "🔴";
                case "awaiting validation":
                    return "⏱️";
                case "step 2":
                    return "2️⃣";
                case "step 2 complete":
                case "step two complete":
                    return "2️⃣";
                default:
                    return "ℹ️";
            }
        };

        const getStatusOrder = (statusLabel) => {
            const normalized = normalizeStatusLabel(statusLabel);
            return statusOrder.has(normalized) ? statusOrder.get(normalized) : 99;
        };

        var table = $("#latest-ecobricks").DataTable({
            "responsive": true,
            "serverSide": true,
            "processing": true,
            "ajax": {
                "url": "../api/fetch_newest_briks.php",
                "type": "POST"
            },
            "pageLength": 25, // Set default number of rows per page to 10
            "order": [[2, "asc"]],
            "language": {
                "emptyTable": "All ecobricks have been validated. Stay posted, there's lots more plastic to pack!",
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
                {
                    "data": "status",
                    "render": function(data, type) {
                        const label = data || '';
                        if (type === 'display') {
                            const emoji = getStatusEmoji(label);
                            const safeLabel = $('<div>').text(label).html();
                            return `<span class="status-cell"><span class="status-emoji" aria-hidden="true">${emoji}</span><span class="status-text">${safeLabel}</span></span>`;
                        }
                        if (type === 'sort') {
                            return getStatusOrder(label);
                        }
                        return label;
                    }
                }, // Status
                { "data": "weight_g", "className": "metric-column" }, // Weight
                { "data": "volume_ml", "className": "metric-column" }, // Volume
                { "data": "density" }, // Density

                {
                    "data": "location_brik", // Location
                    "render": function(data, type, row) {
                        if (type === 'display' && data) {
                            const maxLength = 100; // Set max character length
                            return data.length > maxLength ? data.substr(0, maxLength) + '...' : data;
                        }
                        return data;
                    },
                    "className": "location-column" // Apply CSS class for width restriction
                },
                {
                    "data": "serial_no",
                    "render": function(data, type, row) {
                        if (type === 'display') {
                            return `<button class="serial-button" data-serial-no="${data}" data-status="${row.status}" title="View Ecobrick Details">${data}</button>`;
                        }
                        return data;
                    },
                    "orderable": false
                },
                {
                    "data": "feature",
                    "render": function(data, type, row) {
                        if (type === 'display') {
                            const icon = data == 1 ? '🌟' : '➕';
                            return `<span class="feature-toggle" data-serial="${row.serial_no}" data-feature="${data}">${icon}</span>`;
                        }
                        return data;
                    },
                    "orderable": false,
                    "className": "all"
                }
            ],
            "columnDefs": [
                { "orderable": false, "targets": [0, 3, 4, 5, 8] }, // Make the image and certain columns unsortable
                { "className": "all", "targets": [0, 2, 7] }, // Ensure Brik (thumbnail), Status, and Serial always display
                { "responsivePriority": 1, "targets": 0 },
                { "responsivePriority": 2, "targets": 2 },
                { "responsivePriority": 3, "targets": 7 },
                { "responsivePriority": 4, "targets": 1 },
                { "responsivePriority": 5, "targets": 8 },
                { "responsivePriority": 6, "targets": 3 },
                { "responsivePriority": 7, "targets": 4 },
                { "responsivePriority": 8, "targets": 5 },
                { "responsivePriority": 9, "targets": 6 }
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

                // Toggle feature field when the star column is clicked
                $('#latest-ecobricks tbody').on('click', '.feature-toggle', function() {
                    var serialNo = $(this).data('serial');
                    var featureVal = parseInt($(this).data('feature'));
                    var newVal = featureVal === 1 ? 0 : 1;
                    setBrikFeatured(serialNo, newVal, $(this));
                });
                adjustColumnVisibility();
            }
        });

        function adjustColumnVisibility() {
            var containerWidth = $('#latest-ecobricks_wrapper').outerWidth() || $(window).width();
            var showMetrics = containerWidth >= 992;
            var showDensity = containerWidth >= 1220;
            var showLocation = containerWidth >= 1360;

            table.column(3).visible(showMetrics);
            table.column(4).visible(showMetrics);
            table.column(5).visible(showDensity);
            table.column(6).visible(showLocation);
        }

        table.on('responsive-resize', function() {
            adjustColumnVisibility();
        });

        var resizeTimer;
        $(window).on('resize', function() {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(adjustColumnVisibility, 150);
        });
    });
</script>



<script>
function viewEcobrickActions(serial_no, status, lang) {
    console.log("Button clicked with serial number:", serial_no);
    const modal = document.getElementById('form-modal-message');
    const messageContainer = document.querySelector('.modal-message');
    const modalBox = document.getElementById('modal-content-box');

    const normalizedStatus = (status || '').toLowerCase();

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
    let ecobrickURL = `https://gobrik.com/en/brik.php?serial_no=${encodedSerialNo}`;

   // Construct the content (stack of buttons) using string concatenation to avoid issues
let content = '';

content += '<a class="ecobrick-action-button" href="brik.php?serial_no=' + encodedSerialNo + '" data-lang-id="013-view-ecobrick-post">';
content += '🔍 ' + translations['013-view-ecobrick-post'];
content += '</a>';

// Conditionally display the "Edit Ecobrick" button if the status is not authenticated
if (normalizedStatus !== "authenticated") {
    content += '<a class="ecobrick-action-button" href="log.php?retry=' + encodedSerialNo + '" data-lang-id="015-edit-ecobrick">';
    content += '✏️ ' + translations['015-edit-ecobrick'];
    content += '</a>';
}

// Add the "Share Ecobrick" button
content += '<a class="ecobrick-action-button" href="javascript:void(0);" onclick="copyEcobrickLink(\'' + ecobrickURL + '\', this)" data-lang-id="016-share-ecobrick">';
content += '🔗 ' + (translations['016-share-ecobrick'] || 'Share Ecobrick');
content += '</a>';

// Conditionally display the "Validate" Ecobrick" button if the status is not authenticated
if (normalizedStatus !== "authenticated") {
    content += '<a class="ecobrick-action-button" href="validate-1.php?id=' + encodedSerialNo + '" data-lang-id="015-validate-ecobrick">';
    content += '🧐 ' + (translations['018-validate'] || 'Validate');
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

function setBrikFeatured(serial_no, featureVal, element) {
    $.ajax({
        url: '../api/brik_feature_process.php',
        method: 'POST',
        dataType: 'json',
        data: { serial_no: serial_no, feature: featureVal },
        success: function(response) {
            if (response.success) {
                var icon = featureVal === 1 ? '🌟' : '➕';
                element.text(icon);
                element.data('feature', featureVal);
            } else {
                alert('Error updating feature');
            }
        },
        error: function() {
            alert('Error updating feature');
        }
    });
}
</script>




</body>
</html>
