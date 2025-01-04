<!-- PÁGINA DE DETALLES DE ECOBRICK EN ESPAÑOL -->

<?php

// Obtiene el contenido de la tabla de Ecobrick como una vista ordenada, usando el serial_no desde la URL.
$serialNo = $_GET['serial_no'];
$sql = "SELECT * FROM tb_ecobricks WHERE serial_no = " . $serialNo;

$result = $gobrik_conn->query($sql);
if ($result->num_rows > 0) {
    while($array = $result->fetch_assoc()) {

        echo '<title>Ecobrick '. $array["serial_no"] .' | '. $array["weight_g"] .'g de plástico secuestrado por '. $array["owner"] .' en '. $array["location_full"] .'.</title>';

        echo '<meta name="description" content="Un ecobrick autenticado que fue publicado y archivado en la blockchain manual de brikcoin el ' . $array["date_logged_ts"] .'">';

        if ( isset($array["vision"]) && $array["vision"] != '' ) {
            echo '<meta name="description" content="'. $array["vision"] .'">';
        }

        echo '<meta name="keywords" content="ecobrick, número de serie '. $array["owner"] .', '. $array["owner"] .', brikchain, brikcoin, registro brik, secuestro de plástico, reciclaje, alternativa, secuestro de plástico, compensación de plástico, plástico AES, secuestro de carbono. '. $array["location_full"] .'">';

        echo '<meta property="og:url"           content="https://gobrik.com/es/brik.php?serial_no='. $array["serial_no"] .'"/>' ;
        echo '<meta property="og:title"         content="Ecobrick '. $array["serial_no"] .' | '. $array["weight_g"] .'g de plástico secuestrado por '. $array["owner"] .' en '. $array["location_full"] .'.">';
        echo '<meta property="og:description"   content="Un ecobrick autenticado que fue publicado y archivado en la blockchain manual de brikcoin el ' . $array["date_logged_ts"] .'"/>';
        echo '<meta property="og:image"         content="'. $array["ecobrick_full_photo_url"] .'"/>';
        echo '<meta property="og:image:alt"     content="El registro brikchain de un ecobrick autenticado en el brikchain"/>';
        echo '<meta property="og:locale" content="es_ES" />';
        echo '<meta property="og:type"          content="website">';

        echo '<meta property="og:type" content="article" />
        <meta property="og:site_name" content="GoBrik.com" />
        <meta property="article:publisher" content="https://web.facebook.com/ecobricks.org" />
        <meta property="article:modified_time" content="'. $array["date_logged_ts"] .'" />
        <meta property="og:image:type" content="image/png" />
        <meta name="author" content="Alianza Global de Ecobricks" />
        ';

    }

} else {
    echo '<META NAME="robots" CONTENT="noindex">';
    echo '<title>No se encontró el Ecobrick | GoBrik.com</title>';
    echo '<meta name="description" content="No se encontraron datos para este número de serie de ecobrick. Lo más probable es que los datos de brikchain aún estén en migración."> ';
}

?>
