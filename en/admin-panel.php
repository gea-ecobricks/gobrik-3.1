<?php
require_once '../earthenAuth_helper.php'; // Include the authentication helper functions

// Set page variables
$lang = basename(dirname($_SERVER['SCRIPT_NAME']));
$version = '0.5';
$page = 'admin-panel';
$lastModified = date("Y-m-d\TH:i:s\Z", filemtime(__FILE__));
$is_logged_in = isLoggedIn(); // Check if the user is logged in using the helper function

// Check if the user is logged in
if ($is_logged_in) {
    $buwana_id = $_SESSION['buwana_id'];
    require_once '../gobrikconn_env.php';
    require_once '../buwanaconn_env.php';

    // Check admin status
    $query = "SELECT user_roles, user_capabilities FROM tb_ecobrickers WHERE buwana_id = ?";
    if ($stmt = $gobrik_conn->prepare($query)) {
        $stmt->bind_param("i", $buwana_id);
        $stmt->execute();
        $stmt->bind_result($user_roles, $user_capabilities);

        if ($stmt->fetch()) {
            if (stripos($user_roles, 'admin') === false ||
                stripos($user_capabilities, 'user review') === false ||
                stripos($user_capabilities, 'user deletions') === false) {
                // Redirect if the user is not an admin
                echo "<script>
                    alert('Sorry, this page is for admins only.');
                    window.location.href = 'dashboard.php';
                </script>";
                exit();
            }
        } else {
            // Redirect if no user record is found
            echo "<script>
                alert('User not found.');
                window.location.href = 'dashboard.php';
            </script>";
            exit();
        }
        $stmt->close();
    }

    // Fetch additional user details after admin check
    $user_continent_icon = getUserContinent($buwana_conn, $buwana_id);
    $user_location_watershed = getWatershedName($buwana_conn, $buwana_id);
    $user_location_full = getUserFullLocation($buwana_conn, $buwana_id);
    $gea_status = getGEA_status($buwana_id);
    $user_community_name = getCommunityName($buwana_conn, $buwana_id);
    $first_name = getFirstName($buwana_conn, $buwana_id);

    $buwana_conn->close(); // Close the database connection
}

// Fetch overall stats
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
                        <th>Email</th> <!-- New -->
                        <th>Notes</th> <!-- New -->
                        <th>First Name</th> <!-- New -->
                        <th>Name</th>
                        <th>GEA Status</th>
                        <th>Roles</th>
                        <th>Briks</th>
                        <th>Logins</th>
                        <th>Email Status</th>
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
        "scrollX": true, // Enable horizontal scrolling
        "ajax": {
            "url": "../api/fetch_newest_ecobrickers.php",
            "type": "POST"
        },
        "pageLength": 100, // Show 100 rows by default
        "order": [[0, "desc"]],
        "columns": [
            {
                "data": "buwana_id",
                "render": function (data, type, row) {
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
            { "data": "full_name" },
            { "data": "gea_status" },
            {
                "data": "user_roles",
                "render": function (data, type, row) {
                    return `<a href="#" onclick="openUserRolesModal(${row.buwana_id})" style="text-decoration: underline;">${data}</a>`;
                }
            },
            { "data": "ecobricks_made" },
            { "data": "login_count" },
            { "data": "test_email_status" },
            { "data": "location_full" }
        ],
        "columnDefs": [
            { "targets": 0, "width": "80px" }, // Limit width of buwana_id column
            { "targets": 1, "width": "150px" }, // Email column
            { "targets": 2, "width": "130px" }, // Notes column
            { "targets": 3, "width": "80px" }, // First Name column
            { "targets": [10], "responsivePriority": 2 }, // Reduce priority for Location column
            { "targets": [8, 9], "visible": true }, // Ensure login count and email status are visible
            { "targets": "_all", "defaultContent": "", "responsivePriority": 1 } // Ensure default settings for other columns
        ]
    });
});



function openUserRolesModal(buwana_id) {
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
    fetch(`../scripts/fetch_user_roles.php?buwana_id=${buwana_id}`)
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
            const userCapabilities = data.user_capabilities || "No capabilities set";

            // Generate the modal content
            modalBox.innerHTML = `
                <h3>Edit ${fullName}'s GoBrik Account</h3>

                <h4>User Roles</h4>
                <p>Currently set to ${userRoles}</p>
                <select id="user-roles" name="user_roles">
                    <option value="Unknown">Unknown</option>
                    <option value="Ecobricker">Ecobricker</option>
                    <option value="Validator">Validator</option>
                    <option value="Moderator">Moderator</option>
                    <option value="Admin">Admin</option>
                </select>

                <h4>GEA Status</h4>
                <p>Currently set to ${geaStatus}</p>
                <select id="gea-status" name="gea_status">
                    <option value="Unknown">Unknown</option>
                    <option value="Gobriker">Gobriker</option>
                    <option value="Ecobricker">Ecobricker</option>
                    <option value="Trainer">Trainer</option>
                    <option value="Master Trainer">Master Trainer</option>
                </select>

                <h4>Capabilities</h4>
                <p>Currently set to ${userCapabilities}</p>
                <select id="capabilities" name="user_capabilities">
                    <option value="None">None</option>
                    <option value="Review users">Review users</option>
                    <option value="Review ecobricks">Review ecobricks</option>
                    <option value="Delete users">Delete users</option>
                    <option value="Delete ecobricks">Delete ecobricks</option>
                </select>

                <a class="ecobrick-action-button" data-lang-id="000-save" onclick="saveUserRoles(${buwana_id})">💾 Save</a>
            `;
        })
        .catch(error => {
            modalBox.innerHTML = `<p>Error loading user roles: ${error.message}</p>`;
        });

    // Display the modal
    modal.classList.remove('modal-hidden');
}


function saveUserRoles(buwana_id) {
    const geaStatus = document.getElementById('gea-status').value;
    const userRoles = document.getElementById('user-roles').value;
    const capabilities = document.getElementById('capabilities').value;

    fetch(`../scripts/update_user_roles.php`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            buwana_id: buwana_id,
            gea_status: geaStatus,
            user_roles: userRoles,
            capabilities: capabilities,
        }),
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert("User roles updated successfully!");
                closeInfoModal(); // Close the modal
            } else {
                alert(`Error updating user roles: ${data.error}`);
            }
        })
        .catch(error => {
            alert(`Error: ${error.message}`);
        });
}



function openEcobrickerModal(buwana_id) {
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
        <h4>Ecobricker Details (Buwana ID: ${buwana_id})</h4>
        <button class="btn delete" style="margin-bottom: 15px;" onclick="confirmDeleteUser(${buwana_id})">❌ Delete User</button>
        <div id="ecobricker-table-container"></div>
    `;

    // Fetch ecobricker details
    fetch(`../api/fetch_ecobricker_details.php?buwana_id=${buwana_id}`)
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


function confirmDeleteUser(buwana_id) {
    if (confirm(`Are you sure you want to delete the user with Buwana ID: ${buwana_id}? This action cannot be undone.`)) {
        fetch(`../api/delete_accounts.php?id=${buwana_id}`, {
            method: 'GET', // Change to DELETE if supported by your API
            headers: {
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(`User with Buwana ID ${buwana_id} has been successfully deleted.`);
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







</script>

</body>
</html>
