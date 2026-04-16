<?php
require_once '../earthenAuth_helper.php'; // Include the authentication helper functions

// Set page variables
$lang = basename(dirname($_SERVER['SCRIPT_NAME']));
$version = '7.77';
$page = 'index';
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
$earthling_emoji = getUserEarthlingEmoji($buwana_conn, $buwana_id);
    $user_location_watershed = getWatershedName($buwana_conn, $buwana_id);
    $user_location_full = getUserFullLocation($buwana_conn, $buwana_id);
    $gea_status = getGEA_status($buwana_id);
    $user_community_name = getCommunityName($buwana_conn, $buwana_id);
    $first_name = getFirstName($buwana_conn, $buwana_id);

    $buwana_conn->close();  // Close the database connection
} else {

}

// // Determine if the user is logged in for dynamic content handling later
// $is_logged_in = isset($buwana_id) && !empty($first_name);

echo '<!DOCTYPE html>
<html lang="' . htmlspecialchars($lang, ENT_QUOTES, 'UTF-8') . '">
<head>
<meta charset="UTF-8">
';
?>



<!--
Deploy code
cd repositories/gobrik-3-0-2
cp -R en/* id/

GoBrik.com site version 3.1
Developed and made open source by the Global Ecobrick Alliance
See our git hub repository for the full code and to help out:
https://github.com/gea-ecobricks/gobrik-3.0/tree/main/en-->



<?php require_once ("../includes/index-inc.php");?>


<div class="clouds-new2" style="margin-bottom:25px; margin-top:36px">
    <div class="biosphere">
            <img src="../webps/biosphere-blanked.webp" width="400" height="400" alt="biosphere">
    </div>
    <div class="landing-content" style="height:100vh;justify-content: center;
  display: flex;
  flex-flow: column;">

        <div class="main-landing-graphic">
            <img src="../webps/ecobrick-team-blanked.webp" style="width:100%;height:43%;" alt="Unite with ecobrickers around the world">
        </div>
        <div class="big-header" data-lang-id="000-lead-header">Together we can keep our plastic out of the biosphere and out of industry.</div>
        <div class="welcome-text" data-lang-id="001-welcome-text">GoBrik helps manage your ecobricks, plastic and projects so that together we can build our greenest visions.</div>
        <div class="sign-buttons" style="display:flex;flex-flow:row;justify-content: center;">
            <div>
                <button type="button" aria-label="sign in" class="sign-innn" onclick="location.href='login.php'" title="Click here to sign in" style="cursor:pointer;">
                    <i style="background: url(../svgs/bottle-icon.svg) no-repeat; width:20px; height:26px; display: inline-block; background-size:contain; margin-bottom:-5px; margin-right:4px;"></i>
                    <span data-lang-id="002-sign-in">Log in</span>
                </button>

            </div>

            <div>
                <button type="button" aria-label="Sign up" onclick="location.href='https://buwana.ecobricks.org/en/signup-1.php?app=gbrk_f2c61a85a4cd4b8b89a7'" class="sign-uppp" style="cursor:pointer;">
                <i style="background: url(../svgs/strike-icon.svg) no-repeat; width:20px; height:26px;display: inline-block;background-size:contain;margin-bottom: -5px;margin-left:4px;"></i><span data-lang-id="003-sign-up">Sign up</span></button>
            </div>

        </div>

        <div class="tree-text" style="padding-bottom:15px;" data-lang-id="004-account-options">
        Use your GoBrik or Buwana account to sign in.
        No account? Sign up for free!
        </div>

    </div>  <!--  landing-content-->
</div> <!-- clouds-->


<!-- FULL ECOBRICK FLOW GALLERY -->

<?php
include '../gobrikconn_env.php';

// Fetch the 10 latest authenticated ecobricks for the gallery
$sql_ecobricks = "SELECT ecobrick_full_photo_url, ecobrick_thumb_photo_url, serial_no,
                         photo_version, weight_g, ecobricker_maker, location_full, vision, status
                  FROM tb_ecobricks
                  WHERE ecobrick_thumb_photo_url IS NOT NULL
                    AND ecobrick_thumb_photo_url != ''
                    AND status NOT IN ('not ready', 'deleted')
                  ORDER BY ecobrick_id DESC
                  LIMIT 10";
$result_ecobricks = $gobrik_conn->query($sql_ecobricks);
$gallery_ecobricks = [];
if ($result_ecobricks && $result_ecobricks->num_rows > 0) {
    while ($row = $result_ecobricks->fetch_assoc()) {
        $location_parts   = array_filter(array_map('trim', explode(',', $row['location_full'] ?? '')));
        $location_display = implode(', ', array_slice($location_parts, -2));
        $gallery_ecobricks[] = [
            'ecobrick_full_photo_url'  => $row['ecobrick_full_photo_url']  ?? '',
            'ecobrick_thumb_photo_url' => $row['ecobrick_thumb_photo_url'] ?? '',
            'serial_no'                => $row['serial_no']                ?? '',
            'photo_version'            => $row['photo_version']            ?? '',
            'weight_g'                 => $row['weight_g']                 ?? '',
            'ecobricker_maker'         => $row['ecobricker_maker']         ?? '',
            'location_display'         => $location_display,
            'vision'                   => $row['vision']                   ?? '',
            'status'                   => $row['status']                   ?? '',
        ];
    }
}
?>

<!-- LATEST ECOBRICKS GALLERY -->
<div class="featured-content-gallery">
    <div class="feed-live">
        <p data-lang-id="005-featured-live-brikchain"><span class="blink">⬤  </span>Live brikchain feed of authenticated ecobricks.  Click to preview.</p>
    </div>

    <div id="ecobrick-gallery-grid" class="landing-photo-grid">
        <?php if (!empty($gallery_ecobricks)): ?>
            <?php foreach ($gallery_ecobricks as $idx => $brick): ?>
                <button class="landing-grid-item landing-brik-item" type="button"
                        title="Ecobrick <?php echo htmlspecialchars($brick['serial_no'], ENT_QUOTES, 'UTF-8'); ?> by <?php echo htmlspecialchars($brick['ecobricker_maker'], ENT_QUOTES, 'UTF-8'); ?>"
                        data-idx="<?php echo (int)$idx; ?>">
                    <img src="<?php echo htmlspecialchars($brick['ecobrick_thumb_photo_url'], ENT_QUOTES, 'UTF-8'); ?>"
                         alt="Ecobrick <?php echo htmlspecialchars($brick['serial_no'], ENT_QUOTES, 'UTF-8'); ?>"
                         loading="lazy">
                </button>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="gallery-empty">No ecobricks to display at this time.</p>
        <?php endif; ?>
    </div>

    <div class="feature-content-box">
        <div class="big-header" data-lang-id="006-featured-live-heading">Ecobricking.  Live.</div>
        <div class="welcome-text" data-lang-id="007-featured-live-subheading">Ecobricks are being made, logged and validated around the world right this moment.</div>
        <div class="landing-button" style="margin:5px auto 5px auto;justify-content: center; display: flex">
            <a href="brikchain.php" class="feature-button" data-lang-id="008-featured-live-button" aria-label="view brikchain">⛓️ The Brikchain</a>
        </div>
        <div class="tree-text" data-lang-id="009-featured-live-links">A feed & archive of authenticated ecobricks</div>
    </div>
</div>







    <div class="bottom-scope">
         <div class="landing-content">
            <div class="tree-coins" data-lang-id="010-second-feature-img" ><img src="../webps/2023-tree-blank.webp" style="width:100%;" alt="Build your greenest visions with ecobricks">


            </div>

            <div class="welcome-text" data-lang-id="011-second-text">
                Together we're securing plastic out of the biosphere to make building blocks, generate brikcoins and co-create green spaces.
               <br><br>
               <img src="../svgs/aes-brk.svg" style="width:200px;" width="200" height="77" alt="Introducing Brikcoins and AES Plastic Offsetting">
            </div>

            <div class="tree-text" data-lang-id="012-gobrik-sub-text">
                GoBrik provides ecobrickers and their communities with the tools to manage their ecobricking and to quantify its ecological value.
            </div>

        <br><br>


        </div><!--closes Landing content-->
    </div><!-- closes bottom-scope -->


<!-- LATEST PROJECTS GALLERY -->
<?php
$sql_projects = "SELECT project_id, project_name, description_short, briks_used, photo1_main, photo1_tmb
                 FROM tb_projects
                 WHERE ready_to_show = 1
                   AND photo1_tmb IS NOT NULL
                   AND photo1_tmb != ''
                 ORDER BY project_id DESC
                 LIMIT 10";
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
?>

<div class="featured-content-gallery">
    <div class="feed-live">
        <p><span class="blink">⬤  </span>Latest community ecobrick projects.  Click to preview.</p>
    </div>

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
            <p class="gallery-empty">No projects to display at this time.</p>
        <?php endif; ?>
    </div>

    <div class="feature-content-box">
        <div class="big-header">Building Together.</div>
        <div class="welcome-text">Communities around the world are using ecobricks to build green spaces, furniture and structures.</div>
        <div class="landing-button" style="margin:5px auto 5px auto;justify-content: center; display: flex">
            <a href="projects.php" class="feature-button" aria-label="browse projects">🌿 Browse Projects</a>
        </div>
        <div class="tree-text">A community of builders putting plastic to use</div>
    </div>
</div>

<script>
const GALLERY_ECOBRICKS = <?php echo json_encode($gallery_ecobricks, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES) ?: '[]'; ?>;
const GALLERY_PROJECTS  = <?php echo json_encode($gallery_projects,  JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES) ?: '[]'; ?>;

document.getElementById('ecobrick-gallery-grid')?.addEventListener('click', function(e) {
    const item = e.target.closest('.landing-brik-item');
    if (!item) return;
    const brick = GALLERY_ECOBRICKS[parseInt(item.dataset.idx, 10)];
    if (brick) openViewEcobricV2(brick);
});

document.getElementById('project-gallery-grid')?.addEventListener('click', function(e) {
    const item = e.target.closest('.landing-project-item');
    if (!item) return;
    const project = GALLERY_PROJECTS[parseInt(item.dataset.pidx, 10)];
    if (project) projectPreview(project);
});

function getEcobrickStatusClass(statusText) {
    const s = (statusText || '').toLowerCase();
    if (s.includes('auth'))   return 'status-authenticated';
    if (s.includes('await'))  return 'status-awaiting';
    if (s.includes('reject')) return 'status-rejected';
    return 'status-default';
}

function openViewEcobricV2(brickData) {
    if (!brickData) return;
    const modal            = document.getElementById('form-modal-message-v2');
    const photoContainer   = modal?.querySelector('.modal-photo-v2');
    const messageContainer = modal?.querySelector('.modal-message-v2');
    const modalStatusPill  = modal?.querySelector('.modal-status-pill');
    const modalViewButton  = modal?.querySelector('.modal-view-button');
    if (!modal || !photoContainer || !messageContainer) return;

    photoContainer.replaceChildren();
    messageContainer.replaceChildren();

    // Photo
    const photoWrapper = document.createElement('div');
    photoWrapper.className = 'ecobrick-photo-wrapper';
    const img = document.createElement('img');
    img.src = (brickData.ecobrick_full_photo_url || '') + (brickData.photo_version ? '?v=' + brickData.photo_version : '');
    img.alt = 'Ecobrick ' + (brickData.serial_no || '');
    photoWrapper.appendChild(img);
    photoContainer.appendChild(photoWrapper);

    // Meta text
    const metaWrapper = document.createElement('div');
    metaWrapper.className = 'ecobrick-meta-v2';
    const details    = document.createElement('p');
    const weightTxt  = brickData.weight_g         ? Number(brickData.weight_g).toLocaleString() + ' gram' : 'an unknown weight';
    const makerTxt   = brickData.ecobricker_maker  || 'an unknown maker';
    const locationTxt= brickData.location_display  || 'an undisclosed location';
    const serialTxt  = brickData.serial_no         || 'an unlisted serial';
    details.textContent = 'This ' + weightTxt + ' ecobrick ' + serialTxt + ' was made by ' + makerTxt + ' in ' + locationTxt + '.';
    metaWrapper.appendChild(details);
    if (brickData.vision) {
        const visionEl = document.createElement('div');
        visionEl.className = 'ecobrick-vision-v2';
        visionEl.textContent = brickData.vision;
        metaWrapper.appendChild(visionEl);
    }
    photoContainer.appendChild(metaWrapper);

    // View button
    if (modalViewButton) {
        modalViewButton.href = 'brik.php?serial_no=' + encodeURIComponent(brickData.serial_no || '');
        modalViewButton.textContent = 'View Ecobrick';
        modalViewButton.setAttribute('aria-label', 'Open ecobrick ' + (brickData.serial_no || '') + ' details');
        modalViewButton.style.display = 'inline-flex';
    }

    // Status pill
    if (modalStatusPill) {
        modalStatusPill.className   = 'modal-status-pill status-pill ' + getEcobrickStatusClass(brickData.status);
        modalStatusPill.textContent = brickData.status || 'Status unknown';
        modalStatusPill.style.display = 'inline-flex';
    }

    modal.classList.remove('modal-hidden');
    modal.classList.add('modal-shown');
    document.getElementById('page-content')?.classList.add('blurred');
    document.getElementById('footer-full')?.classList.add('blurred');
    document.body.classList.add('modal-open');
}
window.openViewEcobricV2 = openViewEcobricV2;
</script>


</div><!--closes main and starry background-->

	<!--FOOTER STARTS HERE-->

	<?php require_once ("../footer-2026.php");?>

<!--close page content-->


</body>

</html>
