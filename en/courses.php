<?php
require_once '../earthenAuth_helper.php';

$lang = basename(dirname($_SERVER['SCRIPT_NAME']));
$version = '0.01';
$page = 'courses';
$lastModified = date("Y-m-d\TH:i:s\Z", filemtime(__FILE__));
$is_logged_in = isLoggedIn();

if ($is_logged_in) {
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
}

require_once '../gobrikconn_env.php';

$courses = [];
$sql = "SELECT training_id, training_title, training_subtitle, lead_trainer, featured_description, training_type, training_location, training_language, training_date, training_time_txt, registration_scope, display_cost, feature_photo1_main, display_cost FROM tb_trainings WHERE ready_to_show = 1 AND training_date >= CURDATE() ORDER BY training_date ASC";
$result = $gobrik_conn->query($sql);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $courses[] = $row;
    }
}
$gobrik_conn->close();
?>
<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars($lang, ENT_QUOTES, 'UTF-8'); ?>">
<head>
<meta charset="UTF-8">
<?php require_once("../includes/courses-inc.php"); ?>
    <div class="splash-title-block"></div>
    <div id="splash-bar"></div>
    <!-- PAGE CONTENT -->
    <div id="top-page-image" class="credentials-banner top-page-image"></div>
    <div id="form-submission-box" class="landing-page-form">
        <div class="form-container">
            <div style="text-align:center;width:100%;margin:auto;">
                <h2 data-lang-id="001-current-courses">Current Courses</h2>
                <p style="margin-bottom: 20px;"><span data-lang-id="002-course-selection">Select from our list of ongoing trainings, workshops and community events.</span></p>
            </div>
            <div class="course-grid">
                <?php foreach ($courses as $course): ?>
                    <a class="course-module-box" href="register.php?id=<?php echo $course['training_id']; ?>">
                        <img src="<?php echo htmlspecialchars($course['feature_photo1_main']); ?>" alt="">
                        <div class="course-date-lang-bar">
                            <?php echo date('M j', strtotime($course['training_date'])); ?> |
                            🌐<?php echo strtoupper($course['training_language']); ?>
                        </div>
                        <div class="course-module-info">
                            <h3><?php echo htmlspecialchars($course['training_title']); ?></h3>
                            <h4><?php echo htmlspecialchars($course['training_subtitle']); ?></h4>
                            <div class="training-leaders">Led by <?php echo htmlspecialchars($course['lead_trainer']); ?></div>
                            <?php $desc = strip_tags($course['featured_description']); if(strlen($desc)>255) $desc = substr($desc,0,255).'...'; ?>
                            <div class="course-description"><?php echo htmlspecialchars($desc); ?></div>
                            <div class="module-caption-item">
                                <?php echo htmlspecialchars($course['training_type']); ?> |
                                <?php echo htmlspecialchars($course['training_location']); ?>
                            </div>
                            <div class="module-caption-item">
                                Open to: <?php echo htmlspecialchars($course['registration_scope']); ?>
                            </div>
                        </div>
                        <div id="learn-more-button-wrapper" style="background:var(--darker);text-align: center; color: var(--text-color);   font-weight: 500; border-radius:10px;padding:8px;">
                            <div class="display-cost"><?php echo htmlspecialchars($course['display_cost']); ?></div>
                            <div class="learn-more-btn">ℹ️ Learn More</div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div> <!--closing #main as opened in header-->

<!--FOOTER STARTS HERE-->
<?php require_once ("../footer-2025.php");?>

</div> <!--Closes main-->
</body>
</html>

