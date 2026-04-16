<?php
require_once '../earthenAuth_helper.php'; // Include the authentication helper functions

// Set page variables
$lang = basename(dirname($_SERVER['SCRIPT_NAME']));
$version = '1.0';
$page = 'projects';
$lastModified = date("Y-m-d\TH:i:s\Z", filemtime(__FILE__));
$is_logged_in = isLoggedIn();

// Check if the user is logged in
if (isLoggedIn()) {
    $buwana_id = $_SESSION['buwana_id'];
    require_once '../gobrikconn_env.php';
    require_once '../buwanaconn_env.php';

    $user_continent_icon = getUserContinent($buwana_conn, $buwana_id);
    $earthling_emoji = getUserEarthlingEmoji($buwana_conn, $buwana_id);
    $user_location_watershed = getWatershedName($buwana_conn, $buwana_id);
    $user_location_full = getUserFullLocation($buwana_conn, $buwana_id);
    $gea_status = getGEA_status($buwana_id);
    $user_community_name = getCommunityName($buwana_conn, $buwana_id);
    $first_name = getFirstName($buwana_conn, $buwana_id);

    $buwana_conn->close();
}

// Fetch up to 100 published projects with photos
require_once '../gobrikconn_env.php';

$sql_projects = "SELECT project_id, project_name, description_short, briks_used, photo1_main, photo1_tmb
                 FROM tb_projects
                 WHERE ready_to_show = 1
                   AND photo1_tmb IS NOT NULL
                   AND photo1_tmb != ''
                 ORDER BY project_id DESC
                 LIMIT 100";
$result_projects = $gobrik_conn->query($sql_projects);
$gallery_projects = [];
if ($result_projects && $result_projects->num_rows > 0) {
    while ($row = $result_projects->fetch_assoc()) {
        $gallery_projects[] = [
            'project_id'        => (int)($row['project_id']        ?? 0),
            'project_name'      => $row['project_name']             ?? '',
            'description_short' => $row['description_short']        ?? '',
            'briks_used'        => (int)($row['briks_used']         ?? 0),
            'photo1_main'       => $row['photo1_main']              ?? '',
            'photo1_tmb'        => $row['photo1_tmb']               ?? '',
        ];
    }
}
$gobrik_conn->close();

echo '<!DOCTYPE html>
<html lang="' . htmlspecialchars($lang, ENT_QUOTES, 'UTF-8') . '">
<head>
<meta charset="UTF-8">
';
?>

<?php require_once ("../includes/projects-inc.php"); ?>

<!-- PAGE CONTENT -->
<div id="top-page-image" class="credentials-banner top-page-image"></div>
<div id="form-submission-box" class="landing-page-form">
    <div class="form-container" style="padding-top: 108px;">

        <!-- PAGE HEADER -->
        <div class="projects-header">
            <h2 class="projects-title" data-lang-id="000-page-title">Community Ecobrick Projects</h2>
            <p class="projects-description" data-lang-id="001-page-description">
                Communities around the world are using ecobricks to build green spaces, furniture, and structures.
                Browse the latest projects posted on GoBrik and discover what is possible when we put plastic to good use.
            </p>
        </div>

        <!-- PROJECTS GALLERY -->
        <div id="project-gallery-grid" class="landing-photo-grid">
            <?php if (!empty($gallery_projects)): ?>
                <?php foreach ($gallery_projects as $pidx => $proj): ?>
                    <button class="landing-grid-item landing-project-item" type="button"
                            title="<?php echo htmlspecialchars($proj['project_name'], ENT_QUOTES, 'UTF-8'); ?>"
                            data-pidx="<?php echo (int)$pidx; ?>">
                        <img src="<?php echo htmlspecialchars($proj['photo1_tmb'] ?: $proj['photo1_main'], ENT_QUOTES, 'UTF-8'); ?>"
                             alt="<?php echo htmlspecialchars($proj['project_name'], ENT_QUOTES, 'UTF-8'); ?>"
                             loading="lazy">
                        <span class="landing-project-title"><?php echo htmlspecialchars($proj['project_name'], ENT_QUOTES, 'UTF-8'); ?></span>
                    </button>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="gallery-empty" data-lang-id="002-gallery-empty">No projects to display at this time.</p>
            <?php endif; ?>
        </div>

    </div>
</div>

<script>
const GALLERY_PROJECTS = <?php echo json_encode($gallery_projects, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES) ?: '[]'; ?>;

document.getElementById('project-gallery-grid')?.addEventListener('click', function(e) {
    const item = e.target.closest('.landing-project-item');
    if (!item) return;
    const project = GALLERY_PROJECTS[parseInt(item.dataset.pidx, 10)];
    if (project) projectPreview(project);
});
</script>

</div><!--closes page-content-->

<?php require_once ("../footer-2026.php"); ?>

</body>
</html>
