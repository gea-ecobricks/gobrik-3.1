<title><?php echo $training_title; ?></title>
<meta name="keywords" content="Registro GEA, Evento comunitario, Webinar, Curso">
<meta name="description" content="Regístrese para nuestro <?php echo $training_type; ?> dirigido por <?php echo $lead_trainer; ?> el <?php echo date('F j, Y', strtotime($training_date)); ?>">

<!-- Facebook Open Graph Tags for social sharing -->
<meta property="og:url" content="https://www.gobrik.com/<?php echo $lang; ?>/register.php?id=<?php echo $training_id; ?>">
<meta property="og:type" content="website">
<meta property="og:title" content="<?php echo $training_title; ?>">
<meta property="og:description" content="Regístrese para nuestro <?php echo $training_type; ?> dirigido por <?php echo $lead_trainer; ?> el <?php echo date('F j, Y', strtotime($training_date)); ?>">
<?php $og_image = !empty($feature_photo1_main) ? $feature_photo1_main : 'https://www.gobrik.com/photos/events/terraces-forests-gladys.jpg'; ?>
<meta property="og:image" content="<?php echo $og_image; ?>">
<meta property="fb:app_id" content="1781710898523821">
<meta property="og:image:width" content="1000">
<meta property="og:image:height" content="500">
<meta property="og:image:alt" content="<?php echo $training_title; ?>">
<meta property="og:locale" content="es_ES">
<meta property="article:modified_time" content="<?php echo date('c'); ?>">
<meta name="author" content="GoBrik.com">
<meta property="og:type" content="page">
<meta property="og:site_name" content="GoBrik.com">
<meta property="article:publisher" content="https://web.facebook.com/ecobricks.org">
<meta property="og:image:type" content="image/png">
