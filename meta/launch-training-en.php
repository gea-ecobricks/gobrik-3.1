
<title><?php echo !empty($training_title) ? $training_title : 'Launch your GEA training!'; ?></title>
<meta name="keywords" content="GEA Registration, Community, Event, Webinar, Course">
<meta name="description" content="<?php echo !empty($training_type) && !empty($lead_trainer) && !empty($training_date)
    ? "Launch, list and manage the $training_type led by $lead_trainer on $training_date on GoBrik. Trainings will be featuring publicly for user registration once launched."
    : "Launch your GEA training, event, webinar or workshop. Trainings will be featured on the front page of Ecobricks.org and shareable on social media."; ?>">

<!-- Facebook Open Graph Tags for social sharing -->
<meta property="og:url" content="https://www.gobrik.com/<?php echo $lang; ?>/launch-training.php">
<meta property="og:type" content="website">
<meta property="og:title" content="<?php echo !empty($training_title) ? $training_title : 'Launch your GEA training!'; ?>">
<meta property="og:description" content="<?php echo !empty($training_type) && !empty($lead_trainer) && !empty($training_date)
                                             ? "Launch, list and manage the $training_type led by $lead_trainer on $training_date on GoBrik. Trainings will be featuring publicly for user registration once launched."
                                             : "Launch your GEA training, event, webinar or workshop. Trainings will be featured on the front page of Ecobricks.org and shareable on social media."; ?>">

<!-- Default image in case no feature image is available -->
<?php
$og_image = !empty($feature_photo1_main) ? $feature_photo1_main : "https://gobrik.com/svgs/shanti.svg";
?>
<meta property="og:image" content="<?php echo $og_image; ?>">
<meta property="fb:app_id" content="1781710898523821">
<meta property="og:image:width" content="1000">
<meta property="og:image:height" content="1000">
<meta property="og:image:alt" content="<?php echo !empty($training_title) ? $training_title : 'GEA Trainer in action'; ?>">
<meta property="og:locale" content="en_GB">

<meta property="article:modified_time" content="<?php echo date("c"); ?>">

<meta name="author" content="GoBrik.com">
<meta property="og:type" content="page">
<meta property="og:site_name" content="GoBrik.com">
<meta property="article:publisher" content="https://web.facebook.com/ecobricks.org">
<meta property="og:image:type" content="image/png">
