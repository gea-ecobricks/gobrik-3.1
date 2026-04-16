<?php
require_once '../earthenAuth_helper.php'; // Include the authentication helper functions

// Set page variables
$lang = basename(dirname($_SERVER['SCRIPT_NAME']));
$version = '1.0';
$page = 'newest-briks';
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

// Include database connection
require_once '../gobrikconn_env.php';

// Fetch the count of ecobricks and the total weight in kg
$sql = "SELECT COUNT(*) as ecobrick_count, SUM(weight_g) / 1000 as total_weight FROM tb_ecobricks WHERE status != 'not ready'";
$result = $gobrik_conn->query($sql);

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $ecobrick_count = number_format($row['ecobrick_count'] ?? 0);
    $total_weight = number_format(round($row['total_weight'] ?? 0));
} else {
    $ecobrick_count = '0';
    $total_weight = '0';
}

// Fetch the 50 latest authenticated ecobricks for the gallery
$sql_gallery = "SELECT ecobrick_full_photo_url, ecobrick_thumb_photo_url, serial_no,
                       photo_version, weight_g, ecobricker_maker, location_full, vision, status
                FROM tb_ecobricks
                WHERE ecobrick_thumb_photo_url IS NOT NULL
                  AND ecobrick_thumb_photo_url != ''
                  AND status = 'authenticated'
                ORDER BY date_logged_ts DESC
                LIMIT 50";
$result_gallery = $gobrik_conn->query($sql_gallery);
$gallery_ecobricks = [];
if ($result_gallery && $result_gallery->num_rows > 0) {
    while ($row = $result_gallery->fetch_assoc()) {
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

$gobrik_conn->close();

echo '<!DOCTYPE html>
<html lang="' . htmlspecialchars($lang, ENT_QUOTES, 'UTF-8') . '">
<head>
<meta charset="UTF-8">
';
?>

<!-- Page CSS & JS Initialization -->
<?php require_once("../includes/newest-briks-inc.php"); ?>

<!-- PAGE CONTENT -->
<div id="top-page-image" class="credentials-banner top-page-image"></div>

<div id="form-submission-box" class="landing-page-form">
    <div class="form-container" style="padding-top: 108px;">

        <div class="newest-briks-header">
            <h2 data-lang-id="001-latest-ecobricks">Newest Ecobricks</h2>
            <p>
                <span data-lang-id="002-as-of-today">As of today, </span><?php echo $ecobrick_count; ?> <span data-lang-id="002b-have-been">ecobricks have been logged on GoBrik,
                representing over </span><?php echo $total_weight; ?> kg <span data-lang-id="002c-of-seq-plastic">of sequestered plastic!</span>
            </p>
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
                <p class="gallery-empty" data-lang-id="003-gallery-empty">No authenticated ecobricks to display at this time.</p>
            <?php endif; ?>
        </div>

    </div>
</div>

</div><!--closes page-content-->

<!-- FOOTER -->
<?php require_once("../footer-2026.php"); ?>

<script>
const GALLERY_ECOBRICKS = <?php echo json_encode($gallery_ecobricks, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES) ?: '[]'; ?>;

document.getElementById('ecobrick-gallery-grid')?.addEventListener('click', function(e) {
    const item = e.target.closest('.landing-brik-item');
    if (!item) return;
    const brick = GALLERY_ECOBRICKS[parseInt(item.dataset.idx, 10)];
    if (brick) openViewEcobricV2(brick);
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

    const photoWrapper = document.createElement('div');
    photoWrapper.className = 'ecobrick-photo-wrapper';
    const img = document.createElement('img');
    img.src = (brickData.ecobrick_full_photo_url || '') + (brickData.photo_version ? '?v=' + brickData.photo_version : '');
    img.alt = 'Ecobrick ' + (brickData.serial_no || '');
    photoWrapper.appendChild(img);
    photoContainer.appendChild(photoWrapper);

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

    if (modalViewButton) {
        modalViewButton.href = 'brik.php?serial_no=' + encodeURIComponent(brickData.serial_no || '');
        modalViewButton.textContent = 'View Ecobrick';
        modalViewButton.setAttribute('aria-label', 'Open ecobrick ' + (brickData.serial_no || '') + ' details');
        modalViewButton.style.display = 'inline-flex';
    }

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

</body>
</html>
