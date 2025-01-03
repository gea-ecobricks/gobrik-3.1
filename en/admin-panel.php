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
                So far we have <?php echo number_format($total_ecobrickers); ?> ecobrickers on GoBrik and <?php echo number_format($total_emails_sent); ?> test emails have been sent.
                <?php echo $percent_with_buwana; ?>% have a buwana account and <?php echo $percent_emails_sent; ?>% have received the test email.
            </p>

        <div id="test-email-controls" style="background: #80808030;
  padding: 10px;
  border-radius: 10px;
  margin: 20px;">
    <h4>Test Emailing Script</h4>
    <p>The next test email will be sent to <span id="next-email-to-send">loading...</span></p>
    <button id="send-test-email" class="page-button">Send</button>
    <p>Status: <span id="email-status">Ready to send</span></p>
</div>


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
<?php require_once("../footer-2024.php"); ?>

<script>
   document.addEventListener('DOMContentLoaded', function () {
    const nextEmailElement = document.getElementById('next-email-to-send');
    const emailStatusElement = document.getElementById('email-status');
    const sendButton = document.getElementById('send-test-email');

    async function fetchNextEmail() {
        try {
            const response = await fetch('../scripts/get_next_email.php'); // Fetch next ecobricker details
            const data = await response.json();
            if (data.success) {
                nextEmailElement.textContent = data.email_addr || 'No email pending';
                sendButton.disabled = false; // Enable the button if there's an email to send
            } else {
                nextEmailElement.textContent = 'No email pending';
                sendButton.disabled = true; // Disable the button if no email is pending
            }
        } catch (error) {
            console.error('Error fetching next email:', error);
            nextEmailElement.textContent = 'Error fetching email';
        }
    }

    async function sendEmail() {
        try {
            emailStatusElement.textContent = 'Sending...';
            const response = await fetch('../scripts/send_test_email.php', {
                method: 'POST',
            });
            const data = await response.json();
            if (data.success) {
                emailStatusElement.textContent = 'Email sent successfully!';
                fetchNextEmail(); // Fetch the next email details
            } else {
                emailStatusElement.textContent = `Error: ${data.error}`;
                console.error('Debug Info:', data.debug_info); // Log debug info to console
                const debugDetails = document.createElement('pre');
                debugDetails.textContent = data.debug_info || 'No additional debug information.';
                emailStatusElement.appendChild(debugDetails); // Display debug info
            }
        } catch (error) {
            emailStatusElement.textContent = 'Error sending email.';
            console.error('Error sending email:', error);
        }
    }

    // Fetch the first email on page load
    fetchNextEmail();

    // Attach the event listener to the button
    sendButton.addEventListener('click', sendEmail);
});



</script>

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
            { "data": "test_email_status" },
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







</script>

</body>
</html>
