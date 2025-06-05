<title><?php echo !empty($training_title) ? $training_title : 'Lanzar una Capacitación GEA'; ?></title>
<meta name="keywords" content="GEA, capacitación, lanzar, evento, taller">
<meta name="description" content="<?php echo !empty($training_type) && !empty($lead_trainer) && !empty($training_date)
    ? "Lanza, lista y administra el $training_type dirigido por $lead_trainer el $training_date en GoBrik. Las capacitaciones se mostrarán públicamente para el registro de usuarios una vez lanzadas."
    : "Usa GoBrik para lanzar una capacitación, taller o evento comunitario de la GEA."; ?>">

<!-- Facebook Open Graph Tags for social sharing -->
<meta property="og:url" content="https://www.gobrik.com/<?php echo $lang; ?>/launch-training.php">
<meta property="og:type" content="website">
<meta property="og:title" content="<?php echo !empty($training_title) ? $training_title : 'Lanzar una Capacitación GEA'; ?>">
<meta property="og:description" content="<?php echo !empty($training_type) && !empty($lead_trainer) && !empty($training_date)
                                             ? "Lanza, lista y administra el $training_type dirigido por $lead_trainer el $training_date en GoBrik. Las capacitaciones se mostrarán públicamente para el registro de usuarios una vez lanzadas."
                                             : "Usa GoBrik para lanzar una capacitación, taller o evento comunitario de la GEA."; ?>">

<?php
$og_image = !empty($feature_photo1_main) ? $feature_photo1_main : "https://gobrik.com/svgs/shanti.svg";
?>
<meta property="og:image" content="<?php echo $og_image; ?>">
<meta property="fb:app_id" content="1781710898523821">
<meta property="og:image:width" content="1000">
<meta property="og:image:height" content="1000">
<meta property="og:image:alt" content="<?php echo !empty($training_title) ? $training_title : 'Entrenador de GEA en acción'; ?>">
<meta property="og:locale" content="es_ES">
<meta property="article:modified_time" content="<?php echo date('c'); ?>">
<meta name="author" content="GoBrik.com">
<meta property="og:type" content="page">
<meta property="og:site_name" content="GoBrik.com">
<meta property="article:publisher" content="https://web.facebook.com/ecobricks.org">
<meta property="og:image:type" content="image/png">
