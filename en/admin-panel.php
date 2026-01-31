<?php
require_once '../earthenAuth_helper.php'; // Include the authentication helper functions
require_once '../auth/session_start.php';

// Set page variables
$lang = basename(dirname($_SERVER['SCRIPT_NAME']));
$version = '0.52';
$page = 'admin-panel';
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

$query = "SELECT user_roles FROM tb_ecobrickers WHERE buwana_id = ?";
if ($stmt = $gobrik_conn->prepare($query)) {
    $stmt->bind_param("i", $buwana_id);
    $stmt->execute();
    $stmt->bind_result($user_roles);

    if ($stmt->fetch()) {
        // Check if the user has an admin role
        if (stripos($user_roles, 'admin') === false) {
            echo "<script>
                alert('Sorry, only admins can see this page.');
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

// Fetch overall stats
require_once '../gobrikconn_env.php';

// Initialize variables
$total_ecobrickers = 0;
$total_with_buwana_id = 0;
$unsent = 0;
$delivered = 0;
$failed = 0;

// Fetch counts
$sql = "SELECT
            COUNT(*) as total_ecobrickers,
            SUM(CASE WHEN buwana_id IS NOT NULL AND buwana_id != '' THEN 1 ELSE 0 END) as total_with_buwana_id,
            SUM(CASE WHEN emailing_status = 'unsent' THEN 1 ELSE 0 END) as unsent,
            SUM(CASE WHEN emailing_status = 'delivered' THEN 1 ELSE 0 END) as delivered,
            SUM(CASE WHEN emailing_status = 'failed' THEN 1 ELSE 0 END) as failed
        FROM tb_ecobrickers";

$result = $gobrik_conn->query($sql);

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $total_ecobrickers = intval($row['total_ecobrickers'] ?? 0);
    $total_with_buwana_id = intval($row['total_with_buwana_id'] ?? 0);
    $unsent = intval($row['unsent'] ?? 0);
    $delivered = intval($row['delivered'] ?? 0);
    $failed = intval($row['failed'] ?? 0);
}

// Calculate percentage of users with Buwana accounts
$percent_with_buwana = $total_ecobrickers > 0 ? round(($total_with_buwana_id / $total_ecobrickers) * 100, 2) : 0;

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
            <h2 data-lang-id="001-main-title">User Management</h2>

            <p id="admin-welcome-stats">
    This is an admin panel for managing GoBrik users.  So far we have <?php echo number_format($total_ecobrickers); ?> ecobrickers on GoBrik.
    <?php echo $percent_with_buwana; ?>% have an active Buwana account.
    </p>
<!--<p>Of these, <?php echo number_format($unsent); ?> have not received the test email,
    <?php echo number_format($delivered); ?> have received it, and
    <?php echo number_format($failed); ?> account emails failed to receive it.</p>

    <div id="prune-time" style="background: #7d7d7d24;
  padding: 10px;
  border-radius: 15px;
  margin-bottom: 30px;">
        <p>Would you like to prune the failed accounts from the database?</p>
<button class="confirm-button enabled" onclick="pruneFailedAccounts()" data-land-id="002-prune-button">Prune Accounts</button>
</div>-->






            <div id="table-container" style="overflow-x: auto; width: 100%;">
            <table id="newest-ecobrickers" class="display responsive nowrap" style="width:100%">
                <thead>
                    <tr>
                        <th>Ecobricker ID</th>
                        <th>Email</th>
                        <th>Notes</th>
                        <th>First Name</th>
                        <th>Roles</th>
                        <th>Briks</th>
                        <th>Logins</th>
                        <th>Email Status</th>
                         <th>Full Name</th>
                        <th>Buwana ID</th>
                        <th>GEA Status</th>
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
</div>
<?php require_once("../footer-2026.php"); ?>





<script>


$(document).ready(function() {
    // Initialize DataTable
    var table = $("#newest-ecobrickers").DataTable({
        "responsive": true,
        "serverSide": true,
        "processing": true,
        "scrollX": true, // Enable horizontal scrolling
        "ajax": {
            "url": "../api/fetch_newest_ecobrickers.php",
            "type": "POST"
        },
        "pageLength": 100, // Show 100 rows by default
        "order": [[0, "desc"]],
        "columns": [
            {
                "data": "ecobricker_id",
                "render": function(data, type, row) {
                    return `<button class="btn btn-primary" onclick="openEcobrickerModal(${data})">${data}</button>`;
                }
            },
            {
                "data": "email_addr",
                "render": function(data, type, row) {
                    return `<div style="max-width: 100px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="${data}">${data}</div>`;
                }
            },
            {
                "data": "account_notes",
                "render": function(data, type, row) {
                    return `<div style="max-width: 100px; word-wrap: break-word; white-space: normal;">${data}</div>`;
                }
            },
            { "data": "first_name" },
            {
                "data": "user_roles",
                "render": function(data, type, row) {
                    return `<a href="#" onclick="openUserRolesModal(${row.ecobricker_id})" style="text-decoration: underline;">${data}</a>`;
                }
            },
            { "data": "ecobricks_made" },
            { "data": "login_count" },
            { "data": "emailing_status" },
            { "data": "full_name" },
            { "data": "buwana_id" },
            { "data": "gea_status" },
            { "data": "location_full" }
        ],
        "columnDefs": [
            { "targets": 0, "width": "80px" }, // Ecobricker ID column
            { "targets": 1, "width": "150px" }, // Email column
            { "targets": 2, "width": "130px" }, // Notes column
            { "targets": 9, "width": "80px" }, // Buwana ID column
            { "targets": [11], "responsivePriority": 2 }, // Reduce priority for Location column
            { "targets": [8, 9], "visible": true }, // Ensure login count and email status are visible
            { "targets": "_all", "defaultContent": "", "responsivePriority": 1 } // Ensure default settings for other columns
        ]
    });

    // Adjust #main height dynamically when the table is redrawn
    table.on('draw', function () {
        adjustMainHeight();
    });

    // Function to adjust the #main height
    function adjustMainHeight() {
        const tableHeight = $('#table-container').outerHeight();
        $('#main').css('height', tableHeight + 'px');
    }

    // Initial height adjustment
    adjustMainHeight();
});



function openUserRolesModal(ecobricker_id) {
    const modal = document.getElementById('form-modal-message');
    const modalBox = document.getElementById('modal-content-box');

    // Show the modal
    modal.style.display = 'flex';
    modalBox.style.flexFlow = 'column';

    // Lock scrolling for the body and blur background
    document.getElementById('page-content')?.classList.add('blurred');
    document.getElementById('footer-full')?.classList.add('blurred');
    document.body.classList.add('modal-open'); // Locks scrolling

    // Set up the modal-content-box styles
    modalBox.style.maxHeight = '80vh'; // Ensure it doesn’t exceed 80% of the viewport height
    modalBox.style.overflowY = 'auto'; // Make the modal scrollable if content overflows

    // Fetch current user roles and populate the fields
    fetch(`../scripts/fetch_user_roles.php?ecobricker_id=${ecobricker_id}`)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                modalBox.innerHTML = `<p>${data.error}</p>`;
                return;
            }

            // Extract the data
            const fullName = data.full_name || "Unknown";
            const geaStatus = data.gea_status || "No GEA status set";
            const userRoles = data.user_roles || "No role set";
            const userCapabilities = data.user_capabilities ? data.user_capabilities.split(',') : []; // Convert to array

            // Define available capabilities
            const capabilitiesOptions = [
                "Review users",
                "Review ecobricks",
                "Delete users",
                "Delete ecobricks"
            ];

            // Generate the modal content
            modalBox.innerHTML = `
                <h3 style="text-align:center; font-size: 1.5em; margin-bottom: 20px;">Edit ${fullName}'s Account</h3>

                <p style="margin-top: 20px; font-weight: bold;margin-bottom: -10px;">User Roles</p>
                <p style="font-size:1em; margin-bottom: 10px;">Currently set to ${userRoles}</p>
                <select id="user-roles" name="user_roles" required style="width: 100%; padding: 10px; margin-bottom: 20px;">
                    <option value="" disabled selected>Change to...</option>
                    <option value="Ecobricker" ${userRoles === "Ecobricker" ? "selected" : ""}>Ecobricker</option>
                    <option value="Validator" ${userRoles === "Validator" ? "selected" : ""}>Validator</option>
                    <option value="Moderator" ${userRoles === "Moderator" ? "selected" : ""}>Moderator</option>
                    <option value="Admin" ${userRoles === "Admin" ? "selected" : ""}>Admin</option>
                </select>

                <p style="margin-top: 20px; font-weight: bold;margin-bottom: -10px;">GEA Status</p>
                <p style="font-size:1em; margin-bottom: 10px;">Currently set to ${geaStatus}</p>
                <select id="gea-status" name="gea_status" required style="width: 100%; padding: 10px; margin-bottom: 20px;">
                    <option value="" disabled selected>Change to...</option>
                    <option value="Gobriker" ${geaStatus === "Gobriker" ? "selected" : ""}>Gobriker</option>
                    <option value="Ecobricker" ${geaStatus === "Ecobricker" ? "selected" : ""}>Ecobricker</option>
                    <option value="Trainer" ${geaStatus === "Trainer" ? "selected" : ""}>Trainer</option>
                    <option value="Master Trainer" ${geaStatus === "Master Trainer" ? "selected" : ""}>Master Trainer</option>
                </select>

                <p style="margin-top: 20px; font-weight: bold;margin-bottom: -10px;">Capabilities</p>
                <p style="font-size:1em; margin-bottom: 10px;">Currently set to: ${userCapabilities.join(', ') || "No capabilities set"}</p>
                <div id="capabilities-options" style="margin-bottom: 20px;">
                    ${capabilitiesOptions
                        .map(
                            (capability) =>
                                `<label style="display: block; margin-bottom: 5px;">
                                    <input type="checkbox" value="${capability}"
                                    ${userCapabilities.includes(capability) ? "checked" : ""}
                                    style="margin-right: 10px;">
                                    ${capability}
                                </label>`
                        )
                        .join('')}
                </div>

                <a class="ecobrick-action-button" style="margin:auto;margin-top: 30px; text-align: center;" data-lang-id="000-save" onclick="saveUserRoles(${ecobricker_id})">💾 Save</a>

                <a class="ecobrick-action-button deleter-button" style="margin:auto;margin-top: 10px; text-align: center;" data-lang-id="000-save" onclick="confirmDeleteUser(${ecobricker_id})">❌ Delete User</a>
            `;
        })
        .catch(error => {
            modalBox.innerHTML += `<p>Error loading user roles: ${error.message}</p>`;
        });

    // Display the modal
    modal.classList.remove('modal-hidden');
}



function saveUserRoles(ecobricker_id) {
    const geaStatus = document.getElementById('gea-status').value;
    const userRoles = document.getElementById('user-roles').value;

    // Collect selected capabilities from the checkboxes
    const selectedCapabilities = Array.from(
        document.querySelectorAll('#capabilities-options input[type="checkbox"]:checked')
    ).map(checkbox => checkbox.value).join(','); // Combine values into a comma-separated string

    // Prepare the data payload
    const payload = {
        ecobricker_id: ecobricker_id,
        capabilities: selectedCapabilities, // Always send capabilities
    };

    // Only include `gea_status` if it's not set to "Change to..."
    if (geaStatus !== "Change to...") {
        payload.gea_status = geaStatus;
    }

    // Only include `user_roles` if it's not set to "Change to..."
    if (userRoles !== "Change to...") {
        payload.user_roles = userRoles;
    }

    fetch(`../scripts/update_user_roles.php`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(payload), // Send the payload with conditional fields
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert("User roles updated successfully!");

                // Close the modal
                closeInfoModal();

                // Reload the DataTable with the latest values
                $('#newest-ecobrickers').DataTable().ajax.reload(null, false); // Keep the current page
            } else {
                alert(`Error updating user roles: ${data.error}`);
            }
        })
        .catch(error => {
            alert(`Error: ${error.message}`);
        });
}




function openEcobrickerModal(ecobricker_id) {
    const modal = document.getElementById('form-modal-message');
    const modalBox = document.getElementById('modal-content-box');

    // Show the modal
    modal.style.display = 'flex';
    modalBox.style.flexFlow = 'column';

    // Lock scrolling for the body and blur background
    document.getElementById('page-content')?.classList.add('blurred');
    document.getElementById('footer-full')?.classList.add('blurred');
    document.body.classList.add('modal-open'); // Locks scrolling

    // Set up the modal-content-box styles
    modalBox.style.maxHeight = '80vh'; // Ensure it doesn’t exceed 80% of the viewport height
    modalBox.style.overflowY = 'auto'; // Make the modal scrollable if content overflows

    // Clear previous modal content and set up structure
    modalBox.innerHTML = `
        <h4 style="text-align:center;">Ecobricker Details (Ecobricker ID: ${ecobricker_id})</h4>
        <div id="ecobricker-table-container" style="margin-bottom: 20px;"></div>
        <a class="ecobrick-action-button deleter-button"
           style="margin:auto; margin-top: 20px; text-align: center;"
           data-lang-id="000-delete"
           onclick="confirmDeleteUser(${ecobricker_id})">❌ Delete User</a>
    `;

    // Fetch ecobricker details
    fetch(`../api/fetch_ecobricker_details.php?ecobricker_id=${ecobricker_id}`)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                modalBox.innerHTML += `<p>${data.error}</p>`;
                return;
            }

            // Build the DataTable HTML
            let tableHTML = '<table id="ecobricker-details-table" class="display" style="width:100%">';
            tableHTML += '<thead><tr><th>Field</th><th>Value</th></tr></thead><tbody>';

            for (const [field, value] of Object.entries(data)) {
                tableHTML += `<tr><td>${field}</td><td>${value || '-'}</td></tr>`;
            }

            tableHTML += '</tbody></table>';

            // Insert the table into the ecobricker-table-container
            document.getElementById('ecobricker-table-container').innerHTML = tableHTML;

            // Initialize the DataTable
            $('#ecobricker-details-table').DataTable({
                paging: false, // Disable pagination
                searching: false, // Disable search
                info: false, // Disable table info
                scrollX: true // Enable horizontal scrolling
            });
        })
        .catch(error => {
            modalBox.innerHTML += `<p>Error loading ecobricker details: ${error.message}</p>`;
        });

    // Display the modal
    modal.classList.remove('modal-hidden');
}



function confirmDeleteUser(ecobricker_id) {
    if (confirm(`Are you sure you want to delete the user with ecobricker ID: ${ecobricker_id}? This action cannot be undone.`)) {
        fetch(`../api/delete_accounts.php?id=${ecobricker_id}`, {
            method: 'GET', // Change to DELETE if supported by your API
            headers: {
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(`User with ecobricker ID ${ecobricker_id} has been successfully deleted from GoBrik, Buwana and Earthen.`);
                closeInfoModal(); // Close the modal
                // Reload the DataTable to reflect changes
                $('#newest-ecobrickers').DataTable().ajax.reload();
            } else {
                alert(`Failed to delete user: ${data.error || 'Unknown error'}`);
            }
        })
        .catch(error => {
            console.error('Error deleting user:', error);
            alert(`An error occurred while trying to delete the user: ${error.message}`);
        });
    }
}





    //FAILED Fetch

function pruneFailedAccounts() {
    const modal = document.getElementById('form-modal-message');
    const modalBox = document.getElementById('modal-content-box');

    // Show the modal
    modal.style.display = 'flex';
    modalBox.style.flexFlow = 'column';

    // Lock scrolling for the body and blur background
    document.getElementById('page-content')?.classList.add('blurred');
    document.getElementById('footer-full')?.classList.add('blurred');
    document.body.classList.add('modal-open');

    // Set up the modal-content-box styles
    modalBox.style.maxHeight = '80vh';
    modalBox.style.overflowY = 'auto';

    // Clear previous modal content and set up structure
    modalBox.innerHTML = `
        <h4 style="text-align:center;">Prune Failed Accounts</h4>
        <div id="prune-table-container" style="margin-bottom: 20px;"></div>
        <button id="confirm-prune-btn" class="confirm-button delete">❌ Confirm Prune</button>
    `;

    // Fetch the first 50 failed accounts
    fetch('../api/fetch_failed_accounts.php?limit=25')
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                modalBox.innerHTML += `<p>${data.error}</p>`;
                return;
            }

            // Build the DataTable HTML
            let tableHTML = '<table id="failed-accounts-table" class="display" style="width:100%">';
            tableHTML += `
                <thead>
                    <tr>
                        <th>Full Name</th>
                        <th>Email Address</th>
                        <th>Emailing Status</th>
                        <th>Ecobricks Made</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
            `;

            data.forEach(account => {
                tableHTML += `
                    <tr>
                        <td>${account.full_name || '-'}</td>
                        <td>${account.email_addr || '-'}</td>
                        <td>${account.emailing_status || '-'}</td>
                        <td>${account.ecobricks_made || '0'}</td>
                        <td>
                            <button onclick="grantException('${account.email_addr}')" class="exception-button">
                                Grant Exception
                            </button>
                        </td>
                    </tr>
                `;
            });

            tableHTML += '</tbody></table>';

            // Insert the table into the prune-table-container
            document.getElementById('prune-table-container').innerHTML = tableHTML;

            // Check if DataTable is already initialized and destroy it if necessary
            if ($.fn.DataTable.isDataTable('#failed-accounts-table')) {
                $('#failed-accounts-table').DataTable().destroy();
            }

            // Initialize the DataTable
            $('#failed-accounts-table').DataTable({
                paging: false,
                searching: false,
                info: false,
                scrollX: true
            });
        })
        .catch(error => {
            modalBox.innerHTML += `<p>Error loading failed accounts: ${error.message}</p>`;
        });

    // Attach event listener to the Confirm Prune button
    document.getElementById('confirm-prune-btn').addEventListener('click', () => {
        // Send a request to prune the accounts
        fetch('../api/prune_failed_accounts.php', { method: 'POST' })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    // Build a detailed result table
                    let resultHTML = '<h4>Prune Results:</h4>';
                    resultHTML += '<table id="prune-results-table" class="display" style="width:100%">';
                    resultHTML += `
                        <thead>
                            <tr>
                                <th>Email</th>
                                <th>GoBrik Status</th>
                                <th>Buwana Status</th>
                                <th>Earthen Status</th>
                            </tr>
                        </thead>
                        <tbody>
                    `;

                    data.details.forEach(result => {
                        resultHTML += `
                            <tr>
                                <td>${result.email}</td>
                                <td>${result.ecobricker_status}</td>
                                <td>${result.buwana_status}</td>
                                <td>${result.earthen_status}</td>
                            </tr>
                        `;
                    });

                    resultHTML += '</tbody></table>';

                    // Display results in the modal
                    modalBox.innerHTML = resultHTML;

                    // Check if DataTable is already initialized and destroy it if necessary
                    if ($.fn.DataTable.isDataTable('#prune-results-table')) {
                        $('#prune-results-table').DataTable().destroy();
                    }

                    // Initialize the DataTable for results
                    $('#prune-results-table').DataTable({
                        paging: false,
                        searching: false,
                        info: false,
                        scrollX: true
                    });
                } else {
                    modalBox.innerHTML = `<p>Error pruning accounts: ${data.message}</p>`;
                }
            })
            .catch(error => {
                modalBox.innerHTML = `<p>Error: ${error.message}</p>`;
            });
    });

    // Display the modal
    modal.classList.remove('modal-hidden');
}








function grantException(emailAddr) {
    fetch(`../api/grant_exception.php?email_addr=${encodeURIComponent(emailAddr)}`, { method: 'POST' })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Exception granted successfully!');
                pruneFailedAccounts(); // Refresh the list
            } else {
                alert('Error granting exception: ' + data.error);
            }
        })
        .catch(error => {
            alert('Error: ' + error.message);
        });
}



</script>

</body>
</html>
