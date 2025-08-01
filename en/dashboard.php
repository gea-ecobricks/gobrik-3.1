<?php
require_once '../earthenAuth_helper.php'; // 🌿 Optional helper functions

// 🌍 Set up page environment
$lang = basename(dirname($_SERVER['SCRIPT_NAME']));
$version = '1.03';
$page = 'dashboard';
$lastModified = date("Y-m-d\TH:i:s\Z", filemtime(__FILE__));

// 🔐 Start session and verify Buwana JWT (auto-redirects if not logged in)
require_once '../auth/session_start.php';

// 🆔 Retrieve the authenticated user's Buwana ID
$buwana_id = $_SESSION['buwana_id'] ?? '';

// 🔗 Establish DB connections to GoBrik and Buwana
require_once '../gobrikconn_env.php';
require_once '../buwanaconn_env.php';

// 🌎 Fetch user meta from Buwana database
$user_continent_icon = getUserContinent($buwana_conn, $buwana_id);
$earthling_emoji = getUserEarthlingEmoji($buwana_conn, $buwana_id);
$user_location_watershed = getWatershedName($buwana_conn, $buwana_id);
$user_location_full = getUserFullLocation($buwana_conn, $buwana_id);
$gea_status = getGEA_status($buwana_id);
$user_roles = getUser_Role($buwana_id);
$user_community_name = getCommunityName($buwana_conn, $buwana_id);

// 👤 Look up user's GoBrik account info
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

// 🪪 Set maker_id for further lookups
$maker_id = $ecobricker_id;

// 📦 Fetch summary of ecobricks logged by user
$sql_summary = "SELECT COUNT(*) as total_ecobricks, SUM(weight_g) / 1000 as total_weight_kg, SUM(volume_ml) as total_volume_ml
                FROM tb_ecobricks
                WHERE maker_id = ? AND status != 'not ready'";
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

// ⚖️ Calculate net density (g/ml)
$net_density = $total_volume_ml > 0 ? ($total_weight_kg * 1000) / $total_volume_ml : 0;

// 📍 Process user location
$location_full_txt = $location_full_txt ?? '';
$location_parts = array_map('trim', explode(',', $location_full_txt));
$location_last = $location_parts[count($location_parts) - 1] ?? '';
$location_third_last = $location_parts[count($location_parts) - 3] ?? '';
$locationFullTxt = $location_third_last . ', ' . $location_last;

// 🎓 Fetch trainings where user is trainer
$trainings = [];
$sql_trainings = "SELECT t.training_id, t.training_title, t.training_date, t.training_location, t.training_type, t.ready_to_show, t.show_report,
                         (SELECT COUNT(*) FROM tb_training_trainees WHERE training_id = t.training_id) AS trainee_count
                  FROM tb_trainings t
                  INNER JOIN tb_training_trainers tt ON t.training_id = tt.training_id
                  WHERE tt.ecobricker_id = ?";
$stmt_trainings = $gobrik_conn->prepare($sql_trainings);
if ($stmt_trainings) {
    $stmt_trainings->bind_param("i", $ecobricker_id);
    $stmt_trainings->execute();
    $result_trainings = $stmt_trainings->get_result();
    while ($row = $result_trainings->fetch_assoc()) {
        $trainings[] = $row;
    }
    $stmt_trainings->close();
} else {
    die("Error preparing statement for trainer trainings: " . $gobrik_conn->error);
}

// 📋 Fetch trainings where user is a registered trainee
$registered_trainings = [];
$sql_registered_trainings = "SELECT t.training_id, t.training_title, t.training_date, t.training_location,
                                    t.training_country, t.training_type, t.zoom_link, t.zoom_link_full
                             FROM tb_trainings t
                             INNER JOIN tb_training_trainees tt ON t.training_id = tt.training_id
                             WHERE tt.ecobricker_id = ?";
$stmt_registered_trainings = $gobrik_conn->prepare($sql_registered_trainings);
if ($stmt_registered_trainings) {
    $stmt_registered_trainings->bind_param("i", $ecobricker_id);
    $stmt_registered_trainings->execute();
    $result_registered_trainings = $stmt_registered_trainings->get_result();
    while ($row = $result_registered_trainings->fetch_assoc()) {
        $registered_trainings[] = $row;
    }
    $stmt_registered_trainings->close();
} else {
    die("Error preparing statement for registered trainings: " . $gobrik_conn->error);
}

// 🧱 Fetch featured ecobricks for homepage slider
$featured_ecobricks = [];
$sql_featured = "SELECT ecobrick_full_photo_url, serial_no, photo_version
                 FROM tb_ecobricks
                 WHERE feature = 1 AND status != 'not ready'
                 ORDER BY date_logged_ts DESC
                 LIMIT 10";
$stmt_featured = $gobrik_conn->prepare($sql_featured);
if ($stmt_featured) {
    $stmt_featured->execute();
    $result_featured = $stmt_featured->get_result();
    while ($row = $result_featured->fetch_assoc()) {
        $featured_ecobricks[] = $row;
    }
    $stmt_featured->close();
} else {
    die("Error preparing statement for featured ecobricks: " . $gobrik_conn->error);
}

// 🔒 Clean exit: close DB connections
$buwana_conn->close();
$gobrik_conn->close();
?>





<!--
GoBrik.com site version 3.1
Developed and made open source by the Global Ecobrick Alliance
See our git hub repository for the full code and to help out:
https://github.com/gea-ecobricks/gobrik-3.0/tree/main/en-->

<?php require_once("../includes/dashboard-inc.php"); ?>

<div id="slider-box">
    <div id="registered-notice" class="top-container-notice">
                <span id="notice-icon" style="margin-right:10px;">🌟</span>
                <span id="notice-text">'What Should Green Really mean?' GEA webinar on August 10th!  <a href="https://gobrik.com/en/courses.php">Register</a></span>
                <button class="notice-close" aria-label="Close">&times;</button>
            </div>
    <div id="ecobrick-slider">
        <?php foreach ($featured_ecobricks as $index => $brick): ?>
            <div class="slide<?php echo $index === 0 ? ' active' : ''; ?>">
                <img src="<?php echo htmlspecialchars($brick['ecobrick_full_photo_url']); ?>?v=<?php echo htmlspecialchars($brick['photo_version']); ?>"
                     alt="Ecobrick <?php echo htmlspecialchars($brick['serial_no']); ?>">
            </div>
        <?php endforeach; ?>
        <div id="slider-dots">
            <?php foreach ($featured_ecobricks as $index => $_): ?>
                <span class="dot<?php echo $index === 0 ? ' active' : ''; ?>" data-slide="<?php echo $index; ?>"></span>
            <?php endforeach; ?>
        </div>
    </div>
    <div style="text-align:center;width:100%;margin:auto;">
        <h2 id="greeting">Hello <?php echo htmlspecialchars($first_name); ?>!</h2>
        <p id="subgreeting">Welcome to your new dashboard.</p>
    </div>
</div>
<!-- DASHBOARD CONTENT -->
<div id="form-submission-box" style="height:fit-content;margin-top: 110px;">
    <div class="form-container">

        <div style="display:flex;flex-flow:row;width:100%;justify-content:center;">
            <a href="log.php" class="confirm-button enabled" id="log-ecobrick-button" data-lang-id="001-log-an-ecobrick" style="margin: 10px;">➕ Log an Ecobrick</a>
            <button id="take-gobrik-tour" style="margin: 10px;" class="confirm-button enabled" data-lang-id="001b-take-gobrik-tour" aria-label="Tour" onclick="startTour()"> 🛳️ GoBrik Tour</button>

        </div>

        <!-- TRAINER TRAININGS -->
<?php if (strpos(strtolower($gea_status), 'trainer') !== false): ?>
        <div id="my-trainings-panel" class="dashboard-panel" style="text-align:center;width:100%;margin:auto;margin-top:25px;">
            <h3 data-lang-id="002-my-trainings">My Trainings</h3>
            <div class="menu-buttons-row">
                            <a href="launch-training.php" class="page-button" style="margin: 10px;">🚀 New Training</a>
                            <a href="training-report.php" class="page-button" id="event-register-button" data-lang-id="004-log-training" style="margin: 10px;">📝 Log Report</a>
                        </div>
            <p>Trainings that you are managing...</p>


            <table id="trainer-trainings" class="display" style="width:100%;">
                <thead>
                    <tr>
                        <th>Training</th>
                        <th>Date</th>
                        <th>Signups</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $pendingReport = null; foreach ($trainings as $training): ?>
                        <?php
                            $training_date_ts = strtotime($training['training_date']);
                            $is_listed = $training['ready_to_show'] == 1;
                            $show_report = $training['show_report'] == 1;

                            if (!$is_listed) {
                                // Training not listed yet
                                $circle = '⚪';
                            } elseif ($training_date_ts > time()) {
                                // Listed and upcoming
                                $circle = '🟢';
                            } elseif ($show_report && $is_listed) {
                                // Report complete and public after the date
                                $circle = '✅';
                            } else {
                                // Listed, past and no report yet
                                $circle = '🔴';
                                if (!isset($pendingReport)) {
                                    $pendingReport = [
                                        'id' => $training['training_id'],
                                        'title' => $training['training_title'],
                                        'date' => date('Y-m-d', $training_date_ts)
                                    ];
                                }
                            }
                        ?>
                        <tr>
                            <td style="white-space:normal;"><?php echo $circle . ' ' . htmlspecialchars($training['training_title']); ?></td>

                            <!-- Format the date to remove time -->
                            <td><?php echo date("Y-m-d", strtotime($training['training_date'])); ?></td>

                            <!-- Updated Signups Column -->
                            <td style="text-align:center;padding:10px;">
                                <a href="javascript:void(0);" class="log-report-btn signup-btn" onclick="openTraineesModal(<?php echo $training['training_id']; ?>, '<?php echo htmlspecialchars($training['training_title'], ENT_QUOTES, 'UTF-8'); ?>')" style="display:inline-block;">
                                    <span class="signup-count"><?php echo (int) $training['trainee_count']; ?></span>
                                    <span class="hover-emoji">👥</span>
                                </a>
                            </td>

                            <!-- Actions column -->
                            <td style="text-align:center;">
                                <button class="serial-button settings-button" data-show-report="<?php echo $training['show_report']; ?>" data-ready-to-show="<?php echo $training['ready_to_show']; ?>" onclick="actionsTrainingModal(this, <?php echo $training['training_id']; ?>)">
                                    <span class="default-emoji">✏️</span><span class="hover-emoji">⚙️</span>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
<?php endif; ?>



<!--ADMIN-->

<?php if (strpos(strtolower($user_roles), 'admin') !== false): ?>
    <div id="admin-menu" class="dashboard-panel">
        <h4 class="panel-title">Admin Menu</h4>
        <div class="menu-buttons-row">
            <a href="admin-review.php" class="page-button">Ecobrick Review</a>
            <a id="messenger-button" href="messenger.php" class="page-button">Messenger BETA</a>
        </div>
    </div>
<?php endif; ?>

<?php if (strpos(strtolower($user_roles), 'admin') !== false): ?>
    <div id="admin-menu" class="dashboard-panel">
        <h4 class="panel-title">Admin Email Subscriptions Manager</h4>
        <div class="menu-buttons-row">
            <a href="admin-emailer.php" class="page-button">📨 GoBrik Intro Emailer</a>
            <a href="admin-panel.php" class="page-button">🔎 User Management</a>
            <a href="earthen-sender.php" class="page-button">📨 Earthen Manual Mailer</a>
            <a href="../scripts/process_email_failures.php" class="page-button">❌ Purge Failed Earthen Accounts</a>
            <a href="https://earthen.io/ghost" class="page-button">👻 Earthen Ghost Login</a>
        </div>
    </div>
<?php endif; ?>



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
            <a href="accounting.php" class="page-button">GEA Accounting</a>
            <!-- Training management buttons moved below -->
            <a href="finalizer.php" class="page-button" id="event-register-button" data-lang-id="005-totem-training" style="margin: 10px;">+ Set Buwana Totem</a>



        </div>
    </div>
<?php endif; ?>





<!-- MY REGISTRATIONS -->
<div style="text-align:center;width:100%;margin:auto;margin-top:25px;">
    <h3 data-lang-id="002-my-registrations">My Registrations</h3>
    <p>Trainings that you've registered for.</p>

    <table id="trainee-trainings" class="display responsive nowrap" style="width:100%;">
        <thead>
            <tr>
                <th>Training</th>
                <th>Date</th>
                <th>Location</th>
                <th>Country</th>
                <th>Type</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($registered_trainings as $training): ?>
                <tr>
                    <td><?php echo htmlspecialchars($training['training_title']); ?></td>
                    <td><?php echo htmlspecialchars($training['training_date']); ?></td>
                    <td style="text-align:center;">
                        <a href="javascript:void(0);"
                           style="text-decoration:underline; font-weight:bold;"
                           onclick="openRegisteredTrainingsModal(<?php echo $training['training_id']; ?>,
                                                                  '<?php echo htmlspecialchars($training['training_location'], ENT_QUOTES, 'UTF-8'); ?>')">
                            <?php echo htmlspecialchars($training['training_location']); ?> 🔎
                        </a>
                    </td>
                    <td><?php echo htmlspecialchars($training['training_country']); ?></td>
                    <td><?php echo htmlspecialchars($training['training_type']); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>




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
<?php require_once("../footer-2025.php"); ?>



<script>
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





// REGISTRATION (TRAININGS)
$(document).ready(function() {
    let table = $("#trainee-trainings").DataTable({
        "pageLength": 10,
        "searching": false,
        "lengthChange": false,
        "language": {
            "emptyTable": "You haven't registered for any trainings yet.",
            "info": "Showing _START_ to _END_ of _TOTAL_ trainings",
            "infoEmpty": "No trainings available",
            "loadingRecords": "Loading trainings...",
            "processing": "Processing...",
            "paginate": {
                "first": "First",
                "last": "Last",
                "next": "Next",
                "previous": "Previous"
            }
        },
        "columnDefs": [
            { "orderable": false, "targets": [2] }, // Disable sorting on "Location"
            { "targets": [3, 4], "visible": true }, // Default: show Country, Type
            { "targets": [3, 4], "visible": false, "responsivePriority": 1 } // Hide on small screens
        ]
    });

    // Adjust visibility based on screen size
    function adjustTableColumns() {
        if (window.innerWidth < 769) {
            table.column(3).visible(false); // Hide Country
            table.column(4).visible(false); // Hide Type
        } else {
            table.column(3).visible(true);
            table.column(4).visible(true);
        }
    }

    // Run on page load
    adjustTableColumns();

    // Run on window resize
    $(window).resize(function() {
        adjustTableColumns();
    });
});


function openRegisteredTrainingsModal(trainingId, trainingLocation) {
    const modal = document.getElementById('form-modal-message');
    const modalBox = document.getElementById('modal-content-box');

    // Show the modal
    modal.style.display = 'flex';
    modalBox.style.flexFlow = 'column';
    modalBox.style.alignItems = 'center'; // Center content
    modalBox.style.textAlign = 'center';

    // Lock scrolling for the body and blur background
    document.getElementById('page-content')?.classList.add('blurred');
    document.getElementById('footer-full')?.classList.add('blurred');
    document.body.classList.add('modal-open');

    // Escape function to prevent XSS
    function escapeHTML(str) {
        return str.replace(/</g, "&lt;").replace(/>/g, "&gt;");
    }

    // Set up initial loading structure
    modalBox.innerHTML = `
        <p>Loading training details...</p>
    `;

    // Fetch training details via AJAX
    fetch(`../api/fetch_registered_training.php?training_id=${trainingId}`)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                modalBox.innerHTML = `<p style="color:red;">${escapeHTML(data.error)}</p>`;
                return;
            }

            // Build modal content
            modalBox.innerHTML = `
                <img src="${escapeHTML(data.feature_photo1_tmb)}" alt="Training Image"
                    style="width: 500px; max-width: 100%; border-radius: 8px; margin-bottom: 20px;">

                <h3>${escapeHTML(data.training_title)}</h3>

                <p>${escapeHTML(data.training_type)} is being led by ${escapeHTML(data.lead_trainer)}
                   on ${escapeHTML(data.training_date)} at ${escapeHTML(data.training_location)} at ${escapeHTML(data.training_time_txt)}.</p>

                <button onclick="window.open('${escapeHTML(data.zoom_link)}', '_blank')"
                    style="margin: 10px; padding: 10px 20px; font-size: 16px; background-color: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer;">
                    Launch Zoom
                </button>

                <button onclick="window.open('${escapeHTML(data.moodle_url)}', '_blank')"
                    style="margin: 10px; padding: 10px 20px; font-size: 16px; background-color: #28a745; color: white; border: none; border-radius: 5px; cursor: pointer;">
                    Launch Moodle Agenda
                </button>

                <br><br>
                <p style="font-size:0.9em">${escapeHTML(data.zoom_link_full)}</p>
            `;
        })
        .catch(error => {
            modalBox.innerHTML = `<p style="color:red;">Error loading training details: ${escapeHTML(error.message)}</p>`;
        });

    // Show the modal
    modal.classList.remove('modal-hidden');
}

function openTraineeSender(trainingId) {
    const modal = document.getElementById('form-modal-message');
    const modalBox = document.getElementById('modal-content-box');

    modal.style.display = 'flex';
    modalBox.style.flexFlow = 'column';

    document.getElementById('page-content')?.classList.add('blurred');
    document.getElementById('footer-full')?.classList.add('blurred');
    document.body.classList.add('modal-open');

    modalBox.innerHTML = `<p>Loading message...</p>`;

    fetch(`../api/fetch_registered_training.php?training_id=${trainingId}`)
        .then(r => r.json())
        .then(data => {
            if (data.error) {
                modalBox.innerHTML = `<p style="color:red;">${escapeHTML(data.error)}</p>`;
                return;
            }

            const msg = `Hi there $first_name,\n\nThank you again for registering for our ${escapeHTML(data.training_title)} event!  \n\nThis is a reminder that today, at ${escapeHTML(data.training_time_txt)} the ${escapeHTML(data.training_type)} begins!\n\nThe training is on Zoom.  Here's the full zoom link and invite you will need to access:\n\n------------------------\n\n${escapeHTML(data.zoom_link_full)}\n\n------------------------\n\nWe'll be opening up the meeting 15 minutes earlier to test systems and audio.  Feel free to join early for a meet and greet.\n\nMeanwhile, we're also setting up a support chat for the week.  I don't know about you, but I've got a lot of plastic saved up and it needs packing.  So after the workshop we're going to use the group to let you (and us!) share our ecobricking progress and ask questions.\n\nWe do our best to avoid meta products in the same way we avoid single-use plastic products, so sorry no whatsapp.  We use Signal (a free, open-source, foundation-run equivalent).  Click the link to join the group now or after the workshop:\n\nhttps://signal.group/#CjQKICIVvzmbBXqB7_9-5XyXd53zbdw7RLqVWKbQ8UzX2EkREhC0_jo3SCAr40xIO_jePrmT\n\nUnlike some of our GEA workshops, no need to bring anything to this workshop except your curiousity.  It will be interactive, so be prepared to share and anwser questions via mic and via chat.\n\nAlright, see you soon!\n\n${escapeHTML(data.lead_trainer)}\n${escapeHTML(data.trainer_contact_email)}`;

            const defaultTitle = `Reminder: ${escapeHTML(data.training_title)} starts today`;

            modalBox.innerHTML = `
                <h4 style="text-align:center;">Send a message to Participants</h4>
                <p style="text-align:center;">Use this quick tool and default message to send a message to everyone who has signed up for the training</p>
                <input id="trainee-title" type="text" style="width:100%;margin-bottom:10px;" value="${defaultTitle}" />
                <textarea id="trainee-message" style="white-space:pre-wrap;text-align:left;width:100%;height:260px;">${msg}</textarea>
                <button id="trainee-test-send" class="confirm-button enabled" style="min-width:360px;margin-top:10px;">Test to: ${escapeHTML(data.trainer_contact_email)}</button>
                <button id="trainee-all-send" class="confirm-button enabled" style="min-width:360px;margin-top:10px;">Send Email to All</button>
                <div id="trainee-send-status" style="margin-top:10px;text-align:center;"></div>
            `;

            document.getElementById('trainee-test-send').addEventListener('click', () => sendTraineeEmails(trainingId, true));
            document.getElementById('trainee-all-send').addEventListener('click', () => sendTraineeEmails(trainingId, false));
        })
        .catch(err => {
            modalBox.innerHTML = `<p style="color:red;">Error loading training: ${escapeHTML(err.message)}</p>`;
        });

    modal.classList.remove('modal-hidden');
}

function sendTraineeEmails(trainingId, isTest) {
    const statusDiv = document.getElementById('trainee-send-status');
    const btn = isTest ? document.getElementById('trainee-test-send') : document.getElementById('trainee-all-send');
    btn.innerHTML = '<div class="spinner-photo-loading"></div>';

    fetch('../processes/trainee_sender.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({
            training_id: trainingId,
            test: isTest ? 1 : 0,
            title: document.getElementById('trainee-title').value,
            message: document.getElementById('trainee-message').value
        })
    })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                btn.textContent = '✅ Message sent!';
                btn.style.background = 'green';
            } else {
                btn.textContent = '🙄Message failed to send';
                btn.style.background = 'red';
            }
            if (data.message) {
                statusDiv.innerHTML += `<p>${escapeHTML(data.message)}</p>`;
            }
        })
        .catch(err => {
            btn.textContent = '🙄Message failed to send';
            btn.style.background = 'red';
            statusDiv.innerHTML += `<p>${escapeHTML(err.message)}</p>`;
        });
}


$(document).ready(function() {
    $("#trainer-trainings").DataTable({
        "pageLength": 10,
        "searching": false,
        "lengthChange": false,
        "language": {
            "emptyTable": "You are not a trainer for any trainings yet.",
            "lengthMenu": "Show _MENU_ trainings",
            "info": "Showing _START_ to _END_ of _TOTAL_ trainings",
            "infoEmpty": "No trainings available",
            "loadingRecords": "Loading trainings...",
            "processing": "Processing...",
            "paginate": {
                "first": "First",
                "last": "Last",
                "next": "Next",
                "previous": "Previous"
            }
        },
        "columnDefs": [
            { "orderable": false, "targets": [2, 3] }
        ]
    });
});





function openTraineesModal(trainingId, trainingTitle) {
    const modal = document.getElementById('form-modal-message');
    const modalBox = document.getElementById('modal-content-box');

    // Show the modal
    modal.style.display = 'flex';
    modalBox.style.flexFlow = 'column';

    // Lock scrolling for the body and blur background
    document.getElementById('page-content')?.classList.add('blurred');
    document.getElementById('footer-full')?.classList.add('blurred');
    document.body.classList.add('modal-open');

    // Set up modal structure
    modalBox.innerHTML = `
        <h4 style="text-align:center;">Registered Trainees for <br> ${escapeHTML(trainingTitle)}</h4>
        <div id="trainee-table-container" style="max-height: 100%; overflow-y: auto; margin-bottom: 20px;"></div>
        <button id="message-participants-btn" class="confirm-button enabled" style="margin-top:10px;">Message Participants...</button>
    `;

    document.getElementById('message-participants-btn').addEventListener('click', () => {
        closeInfoModal();
        openTraineeSender(trainingId);
    });

    // Fetch trainees via AJAX
    fetch(`../api/fetch_training_trainees.php?training_id=${trainingId}`)
        .then(response => response.json())
        .then(data => {
            if (!data || data.length === 0) {
                document.getElementById('trainee-table-container').innerHTML = `<p style="text-align:center;">No trainees registered yet.</p>`;
                return;
            }

            if (data.error) {
                document.getElementById('trainee-table-container').innerHTML = `<p style="text-align:center; color: red;">${escapeHTML(data.error)}</p>`;
                return;
            }

            // Build the DataTable HTML
            let tableHTML = `
                <table id="trainees-table" class="display" style="width:100%;">
                    <thead>
                        <tr>
                            <th>First Name</th>
                            <th>Email</th>
                            <th>GEA Status</th>
                            <th>RSVP Status</th>
                            <th>Date Registered</th>
                        </tr>
                    </thead>
                    <tbody>
            `;

            data.forEach(trainee => {
                tableHTML += `
                    <tr>
                        <td>${escapeHTML(trainee.first_name || '-')}</td>
                        <td>${escapeHTML(trainee.email_addr || '-')}</td>
                        <td>${escapeHTML(trainee.gea_status || '-')}</td>
                        <td>${escapeHTML(trainee.rsvp_status || '-')}</td>
                        <td>${escapeHTML(trainee.date_registered || '-')}</td>
                    </tr>
                `;
            });

            tableHTML += '</tbody></table>';

            // Insert the table into the modal
            document.getElementById('trainee-table-container').innerHTML = tableHTML;

            // Initialize the DataTable with scrollability
            $('#trainees-table').DataTable({
                paging: true,
                searching: true,
                info: true,
                scrollX: true,
                scrollY: "100%", // Makes it scrollable within the modal
                scrollCollapse: true
            });
        })
        .catch(error => {
            document.getElementById('trainee-table-container').innerHTML = `<p style="text-align:center; color: red;">Error loading trainees: ${escapeHTML(error.message)}</p>`;
        });

    // Show the modal
    modal.classList.remove('modal-hidden');
}



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
    const modalBox = document.getElementById('modal-content-box');
    let messageContainer = document.querySelector('.modal-message');
    if (!messageContainer) {
        messageContainer = document.createElement('div');
        messageContainer.className = 'modal-message';
        modalBox.appendChild(messageContainer);
    }

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

function actionsTrainingModal(buttonElem, trainingId) {
    const modal = document.getElementById('form-modal-message');
    const modalBox = document.getElementById('modal-content-box');
    modalBox.innerHTML = '';
    const messageContainer = document.createElement('div');
    messageContainer.className = 'modal-message';
    modalBox.appendChild(messageContainer);

    let url = `register.php?id=${trainingId}`;
    let content = '';
    content += `<a class="ecobrick-action-button" href="${url}" target="_blank">🔍 View Course Listing</a>`;
    content += `<a class="ecobrick-action-button" href="launch-training.php?id=${trainingId}">✏️ Edit Course</a>`;
    content += `<a class="ecobrick-action-button" href="training-report.php?training_id=${trainingId}">📝 Edit Report</a>`;
    content += `<a class="ecobrick-action-button" href="javascript:void(0);" onclick="copyCourseListingURL(${trainingId}, this)">🔗 Copy Course Listing URL</a>`;

    const showReport = buttonElem.getAttribute('data-show-report') === '1';
    const readyToShow = buttonElem.getAttribute('data-ready-to-show') === '1';
    const reportChecked = showReport ? 'checked' : '';
    const listingChecked = readyToShow ? 'checked' : '';

    content += `<div class="training-toggle-row">
                    <span class="training-toggle-title">Publish report on ecobricks.org:</span>
                    <label class="toggle-switch">
                        <input type="checkbox" class="training-report-toggle" data-training-id="${trainingId}" ${reportChecked}>
                        <span class="slider"></span>
                    </label>
                </div>`;
    content += `<div class="training-toggle-row">
                    <span class="training-toggle-title">List on GoBrik courses:</span>
                    <label class="toggle-switch">
                        <input type="checkbox" class="training-listing-toggle" data-training-id="${trainingId}" ${listingChecked}>
                        <span class="slider"></span>
                    </label>
                </div>`;

    content += `<a class="ecobrick-action-button deleter-button" href="javascript:void(0);" onclick="deleteTraining(${trainingId})">❌ Delete Training</a>`;

    messageContainer.innerHTML = content;
    const newReportToggle = messageContainer.querySelector('.training-report-toggle');
    const newListingToggle = messageContainer.querySelector('.training-listing-toggle');
    if (newReportToggle) addReportToggleListener(newReportToggle, buttonElem);
    if (newListingToggle) addListingToggleListener(newListingToggle, buttonElem);
    modal.style.display = 'flex';
    modalBox.style.background = 'none';
    document.getElementById('page-content').classList.add('blurred');
    document.getElementById('footer-full').classList.add('blurred');
    document.body.classList.add('modal-open');
}

function copyCourseListingURL(trainingId, button) {
    const url = `https://gobrik.com/en/register.php?id=${trainingId}`;
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(url).then(() => {
            button.innerHTML = 'URL Copied!';
            setTimeout(closeInfoModal, 1000);
        }).catch(err => {
            console.error('Failed to copy: ', err);
            alert('Error copying URL. Please try again.');
        });
    } else {
        const tempInput = document.createElement('input');
        tempInput.value = url;
        document.body.appendChild(tempInput);
        tempInput.select();
        document.execCommand('copy');
        document.body.removeChild(tempInput);
        button.innerHTML = 'URL Copied!';
        setTimeout(closeInfoModal, 1000);
    }
}

function deleteTraining(trainingId) {
    if (confirm('Are you sure you want to delete this training? This cannot be undone.')) {
        fetch('../processes/delete_training.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                'training_id': trainingId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Training deleted successfully.');
                window.location.reload();
            } else {
                alert('Error deleting training: ' + data.error);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('There was an error processing your request.');
        });
    }
}

function addReportToggleListener(toggle, buttonElem) {
    toggle.addEventListener('change', function() {
        const trainingId = this.dataset.trainingId;
        const showReport = this.checked ? 1 : 0;
        fetch('../api/toggle_training_report.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                'training_id': trainingId,
                'show_report': showReport
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                buttonElem.setAttribute('data-show-report', showReport);
            } else {
                alert('Error updating status: ' + data.error);
                toggle.checked = !toggle.checked; // revert on failure
            }
        })
        .catch(() => {
            alert('There was an error processing your request.');
            toggle.checked = !toggle.checked;
        });
    });
}

function addListingToggleListener(toggle, buttonElem) {
    toggle.addEventListener('change', function() {
        const trainingId = this.dataset.trainingId;
        const readyToShow = this.checked ? 1 : 0;
        fetch('../api/toggle_training_listing.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                'training_id': trainingId,
                'ready_to_show': readyToShow
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                buttonElem.setAttribute('data-ready-to-show', readyToShow);
            } else {
                alert('Error updating listing status: ' + data.error);
                toggle.checked = !toggle.checked; // revert on failure
            }
        })
        .catch(() => {
            alert('There was an error processing your request.');
            toggle.checked = !toggle.checked;
        });
    });
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const slides = document.querySelectorAll('#ecobrick-slider .slide');
    const dots = document.querySelectorAll('#slider-dots .dot');
    let currentSlide = 0;

    function showSlide(index) {
        console.log('Showing slide', index);
        slides[currentSlide].classList.remove('active');
        dots[currentSlide].classList.remove('active');
        currentSlide = (index + slides.length) % slides.length;
        slides[currentSlide].classList.add('active');
        dots[currentSlide].classList.add('active');
    }

    function nextSlide() {
        console.log('Auto advancing to next slide');
        showSlide(currentSlide + 1);
    }

    let interval = setInterval(nextSlide, 10000);

    function resetInterval() {
        clearInterval(interval);
        interval = setInterval(nextSlide, 10000);
    }

    dots.forEach((dot, idx) => {
        dot.addEventListener('click', () => {
            showSlide(idx);
            resetInterval();
        });
    });

    let startX = 0;
    const sliderBox = document.getElementById('slider-box');
    sliderBox.addEventListener('touchstart', e => {
        startX = e.touches[0].clientX;
    });
    sliderBox.addEventListener('touchend', e => {
        const diff = e.changedTouches[0].clientX - startX;
        if (diff < -50) {
            showSlide(currentSlide + 1);
            resetInterval();
        } else if (diff > 50) {
            showSlide(currentSlide - 1);
            resetInterval();
        }
    });
});
</script>

<script>
function trainerReportAlert(firstName, trainingName, trainingDate, geaStatus, trainingId) {
    const notice = document.getElementById('registered-notice');
    if (!notice) return;
    const icon = document.getElementById('notice-icon');
    const textSpan = document.getElementById('notice-text');
    const closeBtn = notice.querySelector('.notice-close');

    if (!notice.dataset.originalBg) {
        notice.dataset.originalBg = notice.style.backgroundColor || window.getComputedStyle(notice).backgroundColor;
        notice.dataset.originalIcon = icon.textContent;
        notice.dataset.originalText = textSpan.innerHTML;
    }

    notice.style.backgroundColor = 'orange';
    icon.textContent = '⚠️';
    textSpan.innerHTML = `${firstName}! It looks like your course ${trainingName} is complete as of ${trainingDate}! As a GEA ${geaStatus} its important to complete and publish your Training Report. ` +
        `<a href="training-report.php?training_id=${trainingId}" style="margin-left:5px;text-decoration:underline;color:white;font-weight:bold;">Complete Report</a>`;

    closeBtn.addEventListener('click', function(e) {
        e.stopImmediatePropagation();
        notice.style.display = 'flex';
        notice.style.backgroundColor = notice.dataset.originalBg;
        icon.textContent = notice.dataset.originalIcon;
        textSpan.innerHTML = notice.dataset.originalText;
    }, { once: true });
}
</script>

<?php if (isset($pendingReport)): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    trainerReportAlert(
        <?php echo json_encode($first_name); ?>,
        <?php echo json_encode($pendingReport['title']); ?>,
        <?php echo json_encode($pendingReport['date']); ?>,
        <?php echo json_encode($gea_status); ?>,
        <?php echo json_encode($pendingReport['id']); ?>
    );
});
</script>
<?php endif; ?>

</body>
</html>
