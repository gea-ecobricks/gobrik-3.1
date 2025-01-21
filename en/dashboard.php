<?php
require_once '../earthenAuth_helper.php'; // Include the authentication helper functions

// Set up page variables
$lang = basename(dirname($_SERVER['SCRIPT_NAME']));
$version = '0.546';
$page = 'dashboard';
$lastModified = date("Y-m-d\TH:i:s\Z", filemtime(__FILE__));

startSecureSession(); // Start a secure session with regeneration to prevent session fixation


// Check if user is logged in and session active
if ($is_logged_in) {
    $buwana_id = $_SESSION['buwana_id'] ?? ''; // Retrieve buwana_id from session

    // Include database connections
    require_once '../gobrikconn_env.php';
    require_once '../buwanaconn_env.php';

    // Fetch the user's location data
    $user_continent_icon = getUserContinent($buwana_conn, $buwana_id);
    $user_location_watershed = getWatershedName($buwana_conn, $buwana_id);
    $user_location_full = getUserFullLocation($buwana_conn, $buwana_id);
    $gea_status = getGEA_status($buwana_id);
    $user_roles = getUser_Role($buwana_id);
    $user_community_name = getCommunityName($buwana_conn, $buwana_id);

    // Fetch user details from the GoBrik database
    $sql_lookup_user = "SELECT first_name, ecobricks_made, ecobricker_id, location_full_txt FROM tb_ecobrickers WHERE buwana_id = ?";
    $stmt_lookup_user = $gobrik_conn->prepare($sql_lookup_user);

    if ($stmt_lookup_user) {
        $stmt_lookup_user->bind_param("i", $buwana_id);
        $stmt_lookup_user->execute();
        $stmt_lookup_user->bind_result($first_name, $ecobricks_made, $ecobricker_id, $location_full_txt);
        $stmt_lookup_user->fetch();
        $stmt_lookup_user->close();
    } else {
        die("Error preparing statement for tb_ecobrickers: " . $gobrik_conn->error);
    }

    // Set maker_id for use in JavaScript
    $maker_id = $ecobricker_id;

    // Calculate the user's ecobricks summary data
    $sql_summary = "SELECT COUNT(*) as total_ecobricks, SUM(weight_g) / 1000 as total_weight_kg, SUM(volume_ml) as total_volume_ml FROM tb_ecobricks WHERE maker_id = ? AND status != 'not ready'";
    $stmt_summary = $gobrik_conn->prepare($sql_summary);

    if ($stmt_summary) {
        $stmt_summary->bind_param("s", $maker_id);
        $stmt_summary->execute();
        $stmt_summary->bind_result($total_ecobricks, $total_weight_kg, $total_volume_ml);
        $stmt_summary->fetch();
        $stmt_summary->close();
    } else {
        die("Error preparing statement for ecobricks summary: " . $gobrik_conn->error);
    }

    // Calculate net density (total weight divided by total volume)
    $net_density = $total_volume_ml > 0 ? ($total_weight_kg * 1000) / $total_volume_ml : 0; // Convert weight back to grams for density calculation

    // Process locationFullTxt by extracting the last and third-last elements
    // Ensure $location_full_txt is a string
$location_full_txt = $location_full_txt ?? ''; // Default to an empty string if null

// Process locationFullTxt by extracting the last and third-last elements
$location_parts = explode(',', $location_full_txt);
$location_parts = array_map('trim', $location_parts); // Trim whitespace from each part
$location_last = $location_parts[count($location_parts) - 1] ?? '';
$location_third_last = $location_parts[count($location_parts) - 3] ?? '';
$locationFullTxt = $location_third_last . ', ' . $location_last;


    // Close the database connections
    $buwana_conn->close();
    $gobrik_conn->close();
} else {
    // Redirect to login page with the redirect parameter set to the current page
    echo '<script>
        alert("Please login before viewing this page.");
        window.location.href = "login.php?redirect=' . urlencode($page) . '.php";
    </script>';
    exit();
}

// Output the HTML structure
echo '<!DOCTYPE html>
<html lang="' . htmlspecialchars($lang, ENT_QUOTES, 'UTF-8') . '">
<head>
<meta charset="UTF-8">
';
?>




<!--
GoBrik.com site version 3.0
Developed and made open source by the Global Ecobrick Alliance
See our git hub repository for the full code and to help out:
https://github.com/gea-ecobricks/gobrik-3.0/tree/main/en-->

<?php require_once("../includes/dashboard-inc.php"); ?>

<div class="splash-title-block"></div>
<div id="splash-bar"></div>
<div id="top-page-image" class="dolphin-pic top-page-image"></div>
<!-- DASHBOARD CONTENT -->
<div id="form-submission-box" style="height:fit-content;margin-top: 110px;">
    <div class="form-container">
        <div style="text-align:center;width:100%;margin:auto;">
            <h2 id="greeting">Hello <?php echo htmlspecialchars($first_name); ?>!</h2>
            <p id="subgreeting">Welcome to the new GoBrik 3.0!</p>
        </div>
        <div style="display:flex;flex-flow:row;width:100%;justify-content:center;">
            <a href="log.php" class="confirm-button enabled" id="log-ecobrick-button" data-lang-id="001-log-an-ecobrick" style="margin: 10px;">➕ Log an Ecobrick</a>
            <button id="take-gobrik-tour" style="margin: 10px;" class="confirm-button enabled" data-lang-id="001b-take-gobrik-tour" aria-label="Tour" onclick="startTour()"> 🛳️ GoBrik Tour</button>

        </div>


<!--TRAINER MENU-->
<?php if (strpos(strtolower($gea_status), 'trainer') !== false): ?>
    <div id="gea-trainer-menu" class="dashboard-panel">
        <h4 class="panel-title">GEA Trainer Menu</h4>
        <div class="menu-buttons-row">
            <a href="https://nextcloud.ecobricks.org/index.php/s/wCC2BwBwkW7GzTA" target="_blank" class="page-button">Trainer File Kit</a>
            <a href="https://learning.ecobricks.org" target="_blank" class="page-button">GEA Courses</a>
            <a href="https://ecobricks.org/<?php echo htmlspecialchars($lang); ?>/media.php" target="_blank" class="page-button">Ecobricks Media Kit</a>
            <a href="admin-review.php" class="page-button">Validate Ecobricks</a>
            <a href="bug-report.php" class="page-button">Report a Bug</a>

        </div>
    </div>
<?php endif; ?>

<!--ADMIN-->

<?php if (strpos(strtolower($user_roles), 'admin') !== false): ?>
    <div id="admin-menu" class="dashboard-panel">
        <h4 class="panel-title">Admin Menu</h4>
        <div class="menu-buttons-row">
            <a href="admin-panel.php" class="page-button">Admin Panel</a>
            <a href="admin-review.php" class="page-button">Ecobrick Review</a>
            <a id="messenger-button" href="messenger.php" class="page-button">Messenger BETA</a>
        </div>
    </div>
<?php endif; ?>


<?php if (strpos(strtolower($gea_status), 'master trainer') !== false): ?>
    <div id="admin-menu" class="dashboard-panel">
        <h4 class="panel-title">Master Trainer Menu</h4>
        <div class="menu-buttons-row">
            <!-- Add Revenue button with onclick and aria property -->
            <button class="page-button" id="add-revenue" onclick="addRevenueTrans()" aria-label="Click to add a revenue transaction">➕ Record GEA Revenue</button>
        </div>
    </div>
<?php endif; ?>




<!-- populated by fetch_newest_briks.php -->
<div style="text-align:center;width:100%;margin:auto;margin-top:25px;">
    <h3 data-lang-id="002-my-ecobricks">My Ecobricks</h3>
    <table id="latest-ecobricks" class="display responsive nowrap" style="width:100%">
        <thead>
            <tr>
                <th data-lang-id="1103-brik">Brik</th>
                <th data-lang-id="1104-weight">Weight</th>
                <th data-lang-id="1108-volume">Volume</th>
                <th data-lang-id="1109-density">Density</th>
                <th data-lang-id="1110-date-logged">Logged</th>
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

</div><!--closes main and starry background-->

<!-- FOOTER STARTS HERE -->
<?php require_once("../footer-2024.php"); ?>

<script>


//ADD Revenue
function addRevenueTrans() {
    // Select modal elements
    const modal = document.getElementById('form-modal-message');
    const modalBox = document.getElementById('modal-content-box');

    // Show the modal
    modal.style.display = 'flex';
    modalBox.style.flexFlow = 'column';

    // Lock scrolling for the body and blur the background
    document.getElementById('page-content')?.classList.add('blurred');
    document.getElementById('footer-full')?.classList.add('blurred');
    document.body.classList.add('modal-open'); // Locks scrolling

    // Set up the modal-content-box styles
    const modalContentBox = document.getElementById('modal-content-box');
    modalContentBox.style.maxHeight = '80vh'; // Ensure it doesn’t exceed 80% of the viewport height
    modalContentBox.style.overflowY = 'auto'; // Make the modal scrollable if content overflows

    // Create the form HTML
    const formHTML = `
        <h4 style="text-align:center">Add Revenue Transaction</h4>
        <form id="add-revenue-form" onsubmit="submitRevenueTrans(event)">
            <div class="form-item" style="margin-top: 25px;">
                <label for="amount-idr">Amount (IDR):</label>
                <input type="number" id="amount-idr" name="amount_idr" required />
            </div>
            <div class="form-item">
                <label for="sender">From:</label>
                <input type="text" id="sender" name="sender" required />
            </div>
            <div class="form-item">
                <label for="transaction-date">Transaction Date:</label>
                <input type="date" id="transaction-date" name="transaction_date" value="${new Date().toISOString().split('T')[0]}" required />
            </div>
            <div class="form-item">
                <label for="description">Transaction Description:</label>
                <br>
                <textarea id="description" name="description" rows="4" required></textarea>
            </div>
            <div data-lang-id="016-submit-button" style="margin:auto;text-align: center;margin-top:30px;">
                <button type="submit" class="submit-button enabled" aria-label="Submit Form">Submit</button>
            </div>
        </form>
    `;

    // Populate the modal content
    modalContentBox.innerHTML = formHTML;

    // Show the modal
    modal.classList.remove('modal-hidden');
}

// Function to handle form submission
function submitRevenueTrans(event) {
    event.preventDefault(); // Prevent default form submission

    // Get form data
    const amountIDR = document.getElementById('amount-idr').value;
    const sender = document.getElementById('sender').value;
    const transactionDate = document.getElementById('transaction-date').value;
    const description = document.getElementById('description').value;

    // Send the data to the backend
    $.ajax({
        url: '../api/add_revenue_trans.php',
        type: 'POST',
        data: {
            amount_idr: amountIDR,
            sender: sender,
            transaction_date: transactionDate,
            description: description
        },
        success: function (response) {
            const data = JSON.parse(response);
            if (data.success) {
                alert('Revenue transaction added successfully!');
                closeInfoModal(); // Close the modal
                location.reload(); // Reload the page to refresh data
            } else {
                alert(`Error: ${data.error}`);
            }
        },
        error: function (xhr, status, error) {
            alert('Failed to add revenue transaction. Please try again later.');
            console.error('AJAX Error:', status, error);
        }
    });
}



    //GET ECOBRICKER'S ECOBRICKS


    $(document).ready(function() {
        var ecobrickerId = "<?php echo htmlspecialchars($ecobricker_id); ?>"; // Get the logged-in user's ecobricker_id
        var userLang = "<?php echo htmlspecialchars($lang); ?>"; // Get the user's language

        $("#latest-ecobricks").DataTable({
            "responsive": true,
            "serverSide": true,
            "processing": true,
            "ajax": {
                "url": "../api/fetch_my_briks.php",
                "type": "POST",
                "data": function(d) {
                    d.ecobricker_id = ecobrickerId; // Pass the ecobricker_id to filter the results to the user's ecobricks
                }
            },
            "pageLength": 10, // Show 10 briks per page
            "language": {
                "emptyTable": "It looks like you haven't logged any ecobricks yet!",
                "lengthMenu": "Show _MENU_ briks",
                "search": "",
                "info": "Showing _START_ to _END_ of _TOTAL_ ecobricks",
                "infoEmpty": "No ecobricks available",
                "loadingRecords": "Loading ecobricks...",
                "processing": "Processing...",
                "paginate": {
                    "first": "First",
                    "last": "Last",
                    "next": "Next",
                    "previous": "Previous"
                }
            },
            "columns": [
                { "data": "ecobrick_thumb_photo_url", "orderable": false },
                { "data": "weight_g" },
                { "data": "volume_ml" },
                { "data": "density" },
                { "data": "date_logged_ts" },
                { "data": "status", "orderable": false },
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
                { "className": "all", "targets": [0, 1, 6] }, // Ensure Brik (thumbnail), Weight, and Serial always display
                { "className": "min-tablet", "targets": [2, 3, 4] } // These fields can be hidden first on smaller screens
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







<script type="text/javascript">

// JavaScript to determine the user's time of day and display an appropriate greeting
window.onload = function() {
    var now = new Date();
    var hours = now.getHours();
    var greeting;
    var lang = "<?php echo htmlspecialchars($lang); ?>"; // Get the language from PHP

    // Determine greeting based on the time of day
    if (hours < 12) {
        switch (lang) {
            case 'fr':
                greeting = "Bonjour";
                break;
            case 'es':
                greeting = "Buenos días";
                break;
            case 'id':
                greeting = "Selamat pagi";
                break;
            case 'en':
            default:
                greeting = "Good morning";
                break;
        }
    } else if (hours < 18) {
        switch (lang) {
            case 'fr':
                greeting = "Bon après-midi";
                break;
            case 'es':
                greeting = "Buenas tardes";
                break;
            case 'id':
                greeting = "Selamat siang";
                break;
            case 'en':
            default:
                greeting = "Good afternoon";
                break;
        }
    } else {
        switch (lang) {
            case 'fr':
                greeting = "Bonsoir";
                break;
            case 'es':
                greeting = "Buenas noches";
                break;
            case 'id':
                greeting = "Selamat malam";
                break;
            case 'en':
            default:
                greeting = "Good evening";
                break;
        }
    }

    document.getElementById("greeting").innerHTML = greeting + " <?php echo htmlspecialchars($first_name); ?>!";
}



// Main greeting function to determine the user's time of day and display an appropriate greeting
function mainGreeting() {
    var now = new Date();
    var hours = now.getHours();
    var greeting;
    var lang = "<?php echo htmlspecialchars($lang); ?>"; // Get the language from PHP

    // Determine greeting based on the time of day
    if (hours < 12) {
        switch (lang) {
            case 'fr':
                greeting = "Bonjour";
                break;
            case 'es':
                greeting = "Buenos días";
                break;
            case 'id':
                greeting = "Selamat pagi";
                break;
            case 'en':
            default:
                greeting = "Good morning";
                break;
        }
    } else if (hours < 18) {
        switch (lang) {
            case 'fr':
                greeting = "Bon après-midi";
                break;
            case 'es':
                greeting = "Buenas tardes";
                break;
            case 'id':
                greeting = "Selamat siang";
                break;
            case 'en':
            default:
                greeting = "Good afternoon";
                break;
        }
    } else {
        switch (lang) {
            case 'fr':
                greeting = "Bonsoir";
                break;
            case 'es':
                greeting = "Buenas noches";
                break;
            case 'id':
                greeting = "Selamat malam";
                break;
            case 'en':
            default:
                greeting = "Good evening";
                break;
        }
    }

    document.getElementById("greeting").innerHTML = greeting + " <?php echo htmlspecialchars($first_name); ?>!";
}


// Secondary greeting function to provide additional dynamic content
function secondaryGreeting() {
    // Retrieve the language setting from the server-side PHP variable
    const lang = '<?php echo htmlspecialchars($lang ?? '', ENT_QUOTES, 'UTF-8'); ?>';
    const ecobricksMade = <?php echo (int)($ecobricks_made ?? 0); ?>;
    const locationFullTxt = '<?php echo htmlspecialchars($user_location_full ?? '', ENT_QUOTES, 'UTF-8'); ?>';
    const totalWeight = '<?php echo number_format($total_weight_kg ?? 0, 1); ?>';
    const netDensity = '<?php echo number_format($net_density ?? 0, 2); ?>';

    // Determine the appropriate language object based on the current language setting
    let translations;
    switch (lang) {
        case 'fr':
            translations = fr_Page_Translations;
            break;
        case 'es':
            translations = es_Page_Translations;
            break;
        case 'id':
            translations = id_Page_Translations;
            break;
        default:
            translations = en_Page_Translations; // Default to English if no match is found
    }

    // Determine the message to display based on the number of ecobricks made
    let message;
    if (ecobricksMade < 1) {
        message = translations.welcomeBeta;
    } else {
        // Replace placeholders with dynamic values
        message = translations.loggedEcobricks
            .replace('{ecobricksMade}', ecobricksMade)
            .replace('{locationFullTxt}', locationFullTxt)
            .replace('{totalWeight}', totalWeight)
            .replace('{netDensity}', netDensity);
    }

    // Set the inner HTML of the subgreeting paragraph
    document.getElementById('subgreeting').innerHTML = message;
}




//GET MESSAGE COUNT FOR NOTIFICATION
const userId = "<?php echo $buwana_id; ?>";

function setMessageCountNotification() {
    $.ajax({
        url: '../messenger/check_message_stats.php',
        method: 'GET',
        data: { user_id: userId }, // Assuming userId is globally defined
        success: function(response) {
            if (response.status === 'success') {
                const unreadMessages = response.unread_messages;

                // Update the messenger button with the unread message count
                const messengerButton = $('#messenger-button');
                if (unreadMessages > 0) {
                    messengerButton.html(`Messenger BETA <span style="color:red">+${unreadMessages}</span>`);
                } else {
                    messengerButton.text('Messenger BETA'); // Reset text if no unread messages
                }
            } else {
                console.error('Error retrieving message count:', response.message);
            }
        },
        error: function(error) {
            console.error('Error in AJAX request:', error);
        }
    });
}

// Pass PHP variable to JavaScript
const geaStatus = "<?php echo $gea_status; ?>";

window.onload = function() {
    mainGreeting();
    secondaryGreeting();

    // Only call setMessageCountNotification if geaStatus contains "admin"
    if (geaStatus.toLowerCase().includes("admin")) {
        setMessageCountNotification();
    }
};

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
    let ecobrickURL = `https://gobrik.com/en/brik.php?serial_no=${encodedSerialNo}`;

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
                alert('Your ecobrick has been successfully deleted.');
                window.location.href = 'dashboard.php'; // Redirect after deletion
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


<script>
    // Check if the tour has been taken
    document.addEventListener('DOMContentLoaded', function() {
        if (localStorage.getItem('gobrikTourTaken') === 'true') {
            // If the tour has been taken, hide the button
            document.getElementById('take-gobrik-tour').style.display = 'none';
        }
    });

    // Function to start the guided tour and set localStorage
    function startTour() {
        guidedTour();  // Call your guided tour function

        // Record in localStorage that the tour has been taken
        localStorage.setItem('gobrikTourTaken', 'true');

        // Hide the button after it is clicked
        document.getElementById('take-gobrik-tour').style.display = 'none';
    }

    // Example function for guided tour (replace with your actual guidedTour function)
    function guidedTour() {
        // Your guided tour logic here
        alert("Starting the GoBrik guided tour!");
    }
</script>

</body>
</html>
