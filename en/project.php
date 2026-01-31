<?php
require_once '../earthenAuth_helper.php'; // Include the authentication helper functions

// Set page variables
$lang = basename(dirname($_SERVER['SCRIPT_NAME']));
$version = '1.52';
$page = 'project';
$lastModified = date("Y-m-d\TH:i:s\Z", filemtime(__FILE__));
$is_logged_in = isLoggedIn(); // Check if the user is logged in using the helper function

// Check if the user is logged in
if ($is_logged_in) {

    $buwana_id = $_SESSION['buwana_id'];

    // Include database connections
    require_once '../gobrikconn_env.php';
    require_once '../buwanaconn_env.php';

    // Fetch user data
    $user_continent_icon = getUserContinent($buwana_conn, $buwana_id);
$earthling_emoji = getUserEarthlingEmoji($buwana_conn, $buwana_id);
    $user_location_watershed = getWatershedName($buwana_conn, $buwana_id);
    $user_location_full = getUserFullLocation($buwana_conn, $buwana_id);
    $gea_status = getGEA_status($buwana_id);
    $user_community_name = getCommunityName($buwana_conn, $buwana_id);
    $first_name = getFirstName($buwana_conn, $buwana_id);

    $buwana_conn->close();  // Close the database connection
}

// Include main database connection
require_once '../gobrikconn_env.php';

echo '<!DOCTYPE html>
<html lang="' . htmlspecialchars($lang, ENT_QUOTES, 'UTF-8') . '">
<head>
<meta charset="UTF-8">
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.6.0/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.6.0/dist/leaflet.js"></script>
';

require_once ("../includes/project-inc.php");

echo '

    <div id="form-submission-box" style="margin-top:80px;">
        <div class="form-container-v2" style="padding-top:0px !important">';

$conn = $gobrik_conn;
$conn->set_charset('utf8mb4');

$projectId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if (!$projectId && isset($_GET['project_id'])) {
    $projectId = (int) $_GET['project_id'];
}

$project = null;
if ($projectId > 0) {
    $sql = "SELECT * FROM tb_projects WHERE project_id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("i", $projectId);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $project = $result->fetch_assoc();
        }
        $stmt->close();
    }
}

$ecobricks = [];
if ($project && !empty($project['connected_ecobricks'])) {
    $serial_numbers = array_filter(array_map('trim', explode(',', $project['connected_ecobricks'])));
    if ($serial_numbers) {
        $placeholders = implode(',', array_fill(0, count($serial_numbers), '?'));
        $sql_ecobricks = "SELECT ecobrick_unique_id, ecobrick_thumb_photo_url, owner, weight_g, location_full FROM tb_ecobricks WHERE ecobrick_unique_id IN ({$placeholders})";
        $stmt_ecobricks = $conn->prepare($sql_ecobricks);
        if ($stmt_ecobricks) {
            $stmt_ecobricks->bind_param(str_repeat('s', count($serial_numbers)), ...$serial_numbers);
            $stmt_ecobricks->execute();
            $result_ecobricks = $stmt_ecobricks->get_result();
            if ($result_ecobricks->num_rows > 0) {
                while ($row = $result_ecobricks->fetch_assoc()) {
                    $ecobricks[] = $row;
                }
            }
            $stmt_ecobricks->close();
        }
    }
}

?>

<div class="project-content-block project-content-blok">
    <div class="project-info-box">
        <div class="project-title">
            <?php echo $project ? htmlspecialchars($project['project_name'], ENT_QUOTES, 'UTF-8') : 'Project Not Found'; ?>
        </div>
        <div class="project-sub-title">
            <?php if ($project): ?>
                <?php echo htmlspecialchars($project['description_short'], ENT_QUOTES, 'UTF-8'); ?>
            <?php else: ?>
                There are no results for project <?php echo htmlspecialchars((string) $projectId, ENT_QUOTES, 'UTF-8'); ?> in our database.
            <?php endif; ?>
        </div>
        <?php if ($project): ?>
            <?php
            $project_phase = strtolower(trim($project['project_phase'] ?? ''));
            $status_class = '';
            if ($project_phase === 'completed') {
                $status_class = 'project-status-completed';
            } elseif ($project_phase === 'in progress') {
                $status_class = 'project-status-in-progress';
            } elseif ($project_phase === 'incomplete') {
                $status_class = 'project-status-incomplete';
            }
            ?>
            <div class="project-status project-status-pill <?php echo htmlspecialchars($status_class, ENT_QUOTES, 'UTF-8'); ?>">
                <?php echo htmlspecialchars($project['project_phase'] ?? 'Project', ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>
    </div>
    <div class="project-image">
        <?php if ($project && !empty($project['photo1_main'])): ?>
            <a class="project-image-link" href="javascript:void(0);" onclick="viewGalleryImage('<?php echo htmlspecialchars($project['photo1_main'], ENT_QUOTES, 'UTF-8'); ?>', 'Project <?php echo htmlspecialchars($project['project_id'], ENT_QUOTES, 'UTF-8'); ?> was made in <?php echo htmlspecialchars($project['location_full'], ENT_QUOTES, 'UTF-8'); ?> and started on <?php echo htmlspecialchars($project['start_dt'], ENT_QUOTES, 'UTF-8'); ?>')">
                <img src="../<?php echo htmlspecialchars($project['photo1_main'], ENT_QUOTES, 'UTF-8'); ?>" alt="Project <?php echo htmlspecialchars($project['project_id'], ENT_QUOTES, 'UTF-8'); ?> was made in <?php echo htmlspecialchars($project['location_full'], ENT_QUOTES, 'UTF-8'); ?> and started on <?php echo htmlspecialchars($project['start_dt'], ENT_QUOTES, 'UTF-8'); ?>">
            </a>
        <?php else: ?>
            <img src="../webps/empty-ecobrick-450px.webp" alt="empty ecobrick">
        <?php endif; ?>
    </div>
</div>

<div id="main-content-v2">
    <?php if ($project): ?>

                    <div class="lead-page-paragraph">
                        <p>
                            <?php echo htmlspecialchars($project['project_name'], ENT_QUOTES, 'UTF-8'); ?>
                            <span data-lang-id="110"> is a </span>
                            <?php echo htmlspecialchars($project['construction_type'], ENT_QUOTES, 'UTF-8'); ?>
                            <?php echo htmlspecialchars($project['project_type'], ENT_QUOTES, 'UTF-8'); ?>
                            <span data-lang-id="111">project in </span>
                            <?php echo htmlspecialchars($project['location_full'], ENT_QUOTES, 'UTF-8'); ?>
                            <span data-lang-id="112">. The project is made from </span>
                            <?php echo htmlspecialchars($project['briks_used'], ENT_QUOTES, 'UTF-8'); ?>
                            <span data-lang-id="113"> ecobricks, resulting in the sequestration of approximately </span>
                            <?php echo htmlspecialchars($project['est_total_weight'], ENT_QUOTES, 'UTF-8'); ?>&#8202;kg
                            <span data-lang-id="114">of plastic.</span>
                        </p>
                    </div>

                    <div id="three-column-gal" class="three-column-gal" style="margin-top:40px">
                        <?php for ($i = 1; $i <= 6; $i++): ?>
                            <?php
                            $photo_main = $project["photo{$i}_main"] ?? '';
                            $photo_tmb = $project["photo{$i}_tmb"] ?? '';
                            ?>
                            <?php if (!empty($photo_main) && !empty($photo_tmb)): ?>
                                <div class="gal-photo" onclick="viewGalleryImage('../<?php echo htmlspecialchars($photo_main, ENT_QUOTES, 'UTF-8'); ?>', 'Project photo <?php echo $i; ?> | <?php echo htmlspecialchars($project['project_name'], ENT_QUOTES, 'UTF-8'); ?>')">
                                    <img src="../<?php echo htmlspecialchars($photo_tmb, ENT_QUOTES, 'UTF-8'); ?>" alt="Project photo <?php echo $i; ?>">
                                </div>
                            <?php endif; ?>
                        <?php endfor; ?>
                    </div>

                    <div class="main-details">
                        <div class="page-paragraph">
                            <p><?php echo nl2br(htmlspecialchars($project['description_long'], ENT_QUOTES, 'UTF-8')); ?></p>
                            <br>
                        </div>
                    </div>


                <?php if ($ecobricks): ?>
                    <div class="featured-content-gallery" style="width:100%;">
                        <div class="feed-live">
                            <p>Ecobricks used in project. Click to view.</p>
                        </div>
                        <div class="gallery-flex-container">
                            <?php foreach ($ecobricks as $row): ?>
                                <div class="gal-photo">
                                    <div class="photo-box">
                                        <img src="<?php echo htmlspecialchars($row['ecobrick_thumb_photo_url'], ENT_QUOTES, 'UTF-8'); ?>?v=1" alt="Ecobrick <?php echo htmlspecialchars($row['ecobrick_unique_id'], ENT_QUOTES, 'UTF-8'); ?> by <?php echo htmlspecialchars($row['owner'], ENT_QUOTES, 'UTF-8'); ?>" title="Ecobrick <?php echo htmlspecialchars($row['ecobrick_unique_id'], ENT_QUOTES, 'UTF-8'); ?> by <?php echo htmlspecialchars($row['owner'], ENT_QUOTES, 'UTF-8'); ?>" loading="lazy" onclick="ecobrickPreview('<?php echo htmlspecialchars($row['ecobrick_unique_id'], ENT_QUOTES, 'UTF-8'); ?>', '<?php echo htmlspecialchars($row['weight_g'], ENT_QUOTES, 'UTF-8'); ?>', '<?php echo htmlspecialchars($row['owner'], ENT_QUOTES, 'UTF-8'); ?>', '<?php echo htmlspecialchars($row['location_full'], ENT_QUOTES, 'UTF-8'); ?>')" />
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <div id="data-chunk">
                    <div class="ecobrick-data">
                        <p style="margin-left: -32px;font-weight: bold;" data-lang-id="125"> +++ Raw Data Record</p><br>
                        <p>--------------------</p>
                        <p data-lang-id="126">BEGIN PROJECT RECORD ></p>
                        <p><b data-lang-id="128">Project name:</b> <?php echo htmlspecialchars($project['project_name'], ENT_QUOTES, 'UTF-8'); ?></p>
                        <p><b data-lang-id="127">Started:</b> <?php echo htmlspecialchars($project['start_dt'], ENT_QUOTES, 'UTF-8'); ?></p>
                        <p><b data-lang-id="129">Short Description:</b> <?php echo htmlspecialchars($project['description_short'], ENT_QUOTES, 'UTF-8'); ?></p>
                        <p><b data-lang-id="132">Project Phase:</b> <?php echo htmlspecialchars($project['project_phase'], ENT_QUOTES, 'UTF-8'); ?></p>
                        <p><b data-lang-id="133">Percent Complete:</b> <i><?php echo htmlspecialchars($project['project_perc_complete'], ENT_QUOTES, 'UTF-8'); ?></i>&#8202;%</p>
                        <p><b data-lang-id="134">Community:</b> <?php echo htmlspecialchars($project['community'], ENT_QUOTES, 'UTF-8'); ?></p>
                        <p><b data-lang-id="135">Project type:</b> <?php echo htmlspecialchars($project['project_type'], ENT_QUOTES, 'UTF-8'); ?></p>
                        <p><b data-lang-id="136">Construction Type:</b> <?php echo htmlspecialchars($project['construction_type'], ENT_QUOTES, 'UTF-8'); ?></p>
                        <p><b data-lang-id="137">No. of Ecobricks Used:</b> <?php echo htmlspecialchars($project['briks_used'], ENT_QUOTES, 'UTF-8'); ?></p>
                        <p><b data-lang-id="137">Ecobricks Used:</b> <?php echo htmlspecialchars($project['connected_ecobricks'], ENT_QUOTES, 'UTF-8'); ?></p>
                        <p><b data-lang-id="138">Average Brik Weight:</b> <?php echo htmlspecialchars($project['est_avg_brik_weight'], ENT_QUOTES, 'UTF-8'); ?>&#8202;g</p>
                        <p><b data-lang-id="139">Location:</b> <?php echo htmlspecialchars($project['location_full'], ENT_QUOTES, 'UTF-8'); ?></p>
                        <p><b data-lang-id="140">Latitude:</b> <?php echo htmlspecialchars($project['location_lat'], ENT_QUOTES, 'UTF-8'); ?></p>
                        <p><b data-lang-id="140b">Longitude:</b> <?php echo htmlspecialchars($project['location_long'], ENT_QUOTES, 'UTF-8'); ?></p>
                        <p><b data-lang-id="141">Project URL:</b> <?php echo htmlspecialchars($project['project_url'], ENT_QUOTES, 'UTF-8'); ?></p>
                        <p><b data-lang-id="142">Admins:</b> <?php echo htmlspecialchars($project['project_admins'], ENT_QUOTES, 'UTF-8'); ?></p>
                        <p><b data-lang-id="143">Feature Photo URL:</b> <?php echo htmlspecialchars($project['photo1_main'] ?? '', ENT_QUOTES, 'UTF-8'); ?></p>
                        <p><b data-lang-id="144">Photo 2:</b> <?php echo htmlspecialchars($project['photo2_main'] ?? '', ENT_QUOTES, 'UTF-8'); ?></p>
                        <p><b data-lang-id="145">Photo 3:</b> <?php echo htmlspecialchars($project['photo3_main'] ?? '', ENT_QUOTES, 'UTF-8'); ?></p>
                        <p><b data-lang-id="146">Photo 4:</b> <?php echo htmlspecialchars($project['photo4_main'] ?? '', ENT_QUOTES, 'UTF-8'); ?></p>
                        <p><b data-lang-id="147">Photo 5:</b> <?php echo htmlspecialchars($project['photo5_main'] ?? '', ENT_QUOTES, 'UTF-8'); ?></p>
                        <p><b data-lang-id="148">Plastic Sequestered:</b> <?php echo htmlspecialchars($project['est_total_weight'], ENT_QUOTES, 'UTF-8'); ?>&#8202;kg</p>
                        <p><b data-lang-id="149">Logged:</b> <?php echo htmlspecialchars($project['logged_ts'], ENT_QUOTES, 'UTF-8'); ?></p>
                        <p><b data-lang-id="149b">Ready to Show:</b> <?php echo htmlspecialchars($project['ready_to_show'], ENT_QUOTES, 'UTF-8'); ?> | <a href="edit-project.php?project_id=<?php echo htmlspecialchars($project['project_id'], ENT_QUOTES, 'UTF-8'); ?>">e</a></p>
                        <p data-lang-id="150">||| END RECORD.</p>
                    </div>
                </div>

                <?php if (!empty($project['location_lat']) && !empty($project['location_long'])): ?>
                    <div id="map" style="width: 100%; height: 300px;padding:10px;"></div>
                    <script>
                        var map = L.map('map').setView([<?php echo htmlspecialchars($project['location_lat'], ENT_QUOTES, 'UTF-8'); ?>, <?php echo htmlspecialchars($project['location_long'], ENT_QUOTES, 'UTF-8'); ?>], 13);
                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            maxZoom: 19,
                            attribution: '© OpenStreetMap'
                        }).addTo(map);
                        L.marker([<?php echo htmlspecialchars($project['location_lat'], ENT_QUOTES, 'UTF-8'); ?>, <?php echo htmlspecialchars($project['location_long'], ENT_QUOTES, 'UTF-8'); ?>]).addTo(map)
                            .bindPopup('<?php echo htmlspecialchars($project['project_name'], ENT_QUOTES, 'UTF-8'); ?>')
                            .openPopup();
                    </script>
                <?php else: ?>
                    <p>Project not found or no location data available.</p>
                <?php endif; ?>

                <p style="font-size:smaller">Project Location:</p>
                <p style="font-size:normal"><?php echo htmlspecialchars($project['location_full'], ENT_QUOTES, 'UTF-8'); ?></p><br>
                <br><hr><br>

                <div class="page-paragraph">
                                    <h3><p data-lang-id="151">Ecobrick Applications</p></h3>
                                    <p data-lang-id="152">There are all sorts of ways to build with ecobricks! We invite you to get inspired by our searchable archive of ecobrick projects. Search by location, project type, construction type and location.</p>
                                    <p data-lang-id="153">Then, when you're ready to go, check out our ecobrick building guidelines. We've got in-depth resources on general building, modules, open space, earth building and earth building techniques.</p>
                                    <br>
                                    <!-- <p><a class="action-btn-blue" href="featured-projects.php" data-lang-id="154">🔎 Featured Projects</a></p>
                                    <p style="font-size: 0.85em; margin-top:20px;" data-lang-id="155">The live archive of ecobrick projects around the world.</p>
                                    <br> -->
                                    <p><a class="action-btn-blue" href="https://ecobricks.org/build.php" target="_blank" rel="noopener" data-lang-id="154">🔎 Learn about Ecobrick Building</a></p>
                                    <p style="font-size: 0.85em; margin-top:20px;" data-lang-id="155">An overview of ecobrick applications.</p>
                                </div>
    <?php else: ?>
        <div class="lead-page-paragraph">
            <p data-lang-id="152">🚧 It seems project <?php echo htmlspecialchars((string) $projectId, ENT_QUOTES, 'UTF-8'); ?> has not been published to the ecobricks.org database. This could be because of a publication error. It could also be because the project ID is mis-entered in the URL.</p>
        </div>
        <br><hr><br>
        <div class="page-paragraph">
                            <h3><p data-lang-id="151">Ecobrick Applications</p></h3>
                            <p data-lang-id="152">There are all sorts of ways to build with ecobricks! We invite you to get inspired by our searchable archive of ecobrick projects. Search by location, project type, construction type and location.</p>
                            <p data-lang-id="153">Then, when you're ready to go, check out our ecobrick building guidelines. We've got in-depth resources on general building, modules, open space, earth building and earth building techniques.</p>
                            <br>
                            <!-- <p><a class="action-btn-blue" href="featured-projects.php" data-lang-id="154">🔎 Featured Projects</a></p>
                            <p style="font-size: 0.85em; margin-top:20px;" data-lang-id="155">The live archive of ecobrick projects around the world.</p>
                            <br> -->
                            <p><a class="action-btn-blue" href="https://ecobricks.org/build.php" target="_blank" rel="noopener" data-lang-id="154">🔎 Learn about Ecobrick Building</a></p>
                            <p style="font-size: 0.85em; margin-top:20px;" data-lang-id="155">An overview of ecobrick applications.</p>
                        </div>

    <?php endif; ?>

</div>
</div>
</div>
</div>

<!--FOOTER STARTS HERE-->
<?php require_once "../footer-2026.php"; ?>



<script>
function ecobrickPreview(brik_serial, weight, owner, location) {
    var imageUrl = 'https://ecobricks.org/briks/ecobrick-' + brik_serial + '-file.jpeg';

    const modal = document.getElementById('form-modal-message');
    const contentBox = modal.querySelector('.modal-content-box');
    const photoBox = modal.querySelector('.modal-photo-box');
    const photoContainer = modal.querySelector('.modal-photo');

    contentBox.style.display = 'none';
    photoBox.style.display = 'block';

    photoContainer.innerHTML = '';

    var img = document.createElement('img');
    img.src = imageUrl;
    img.alt = "Ecobrick " + brik_serial;
    img.style.maxWidth = '90%';
    img.style.maxHeight = '75vh';
    img.style.minHeight = "400px";
    img.style.minWidth = "400px";
    img.style.margin = 'auto';
    photoContainer.appendChild(img);

    var details = document.createElement('div');
    details.className = 'ecobrick-details';
    details.innerHTML = '<p>Ecobrick ' + brik_serial + ' | ' + weight + 'g of plastic sequestered by ' + owner + ' in ' + location + '.</p>' +
                        '<a href="details-ecobrick-page.php?serial_no=' + brik_serial + '" class="btn featured-gallery-button" style="margin-bottom: 50px;height: 25px;padding: 5px;border: none;padding: 5px 12px;">ℹ️ View Full Details</a>';
    photoContainer.appendChild(details);

    modal.querySelector('.modal-content-box').style.display = 'none';

    modal.style.display = 'flex';

    document.getElementById('page-content')?.classList.add('blurred');
    document.getElementById('footer-full')?.classList.add('blurred');
    document.body.classList.add('modal-open');
}
</script>

</body>
</html>
<?php
$conn->close();
?>
