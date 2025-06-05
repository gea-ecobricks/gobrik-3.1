<title><?php echo !empty($training_title) ? $training_title : 'Lancer une Formation GEA'; ?></title>
<meta name="keywords" content="GEA, formation, lancer, événement, atelier">
<meta name="description" content="<?php echo !empty($training_type) && !empty($lead_trainer) && !empty($training_date)
    ? "Lancez, répertoriez et gérez le $training_type dirigé par $lead_trainer le $training_date sur GoBrik. Les formations seront présentées publiquement pour l'inscription une fois lancées."
    : "Utilisez GoBrik pour lancer une formation, un atelier ou un événement communautaire GEA."; ?>">

<!-- Facebook Open Graph Tags for social sharing -->
<meta property="og:url" content="https://www.gobrik.com/<?php echo $lang; ?>/launch-training.php">
<meta property="og:type" content="website">
<meta property="og:title" content="<?php echo !empty($training_title) ? $training_title : 'Lancer une Formation GEA'; ?>">
<meta property="og:description" content="<?php echo !empty($training_type) && !empty($lead_trainer) && !empty($training_date)
                                             ? "Lancez, répertoriez et gérez le $training_type dirigé par $lead_trainer le $training_date sur GoBrik. Les formations seront présentées publiquement pour l'inscription une fois lancées."
                                             : "Utilisez GoBrik pour lancer une formation, un atelier ou un événement communautaire GEA."; ?>">

<?php
$og_image = !empty($feature_photo1_main) ? $feature_photo1_main : "https://gobrik.com/svgs/shanti.svg";
?>
<meta property="og:image" content="<?php echo $og_image; ?>">
<meta property="fb:app_id" content="1781710898523821">
<meta property="og:image:width" content="1000">
<meta property="og:image:height" content="1000">
<meta property="og:image:alt" content="<?php echo !empty($training_title) ? $training_title : 'Formateur GEA en action'; ?>">
<meta property="og:locale" content="fr_FR">
<meta property="article:modified_time" content="<?php echo date('c'); ?>">
<meta name="author" content="GoBrik.com">
<meta property="og:type" content="page">
<meta property="og:site_name" content="GoBrik.com">
<meta property="article:publisher" content="https://web.facebook.com/ecobricks.org">
<meta property="og:image:type" content="image/png">
