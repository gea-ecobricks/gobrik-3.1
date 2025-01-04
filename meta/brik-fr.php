<!-- PAGE DE DÉTAILS DES ÉCOBRIQUES EN FRANÇAIS -->

<?php

// Récupère le contenu de la table des écobriques sous forme de vue ordonnée, en utilisant le numéro de série depuis l'URL.
$serialNo = $_GET['serial_no'];
$sql = "SELECT * FROM tb_ecobricks WHERE serial_no = " . $serialNo;

$result = $gobrik_conn->query($sql);
if ($result->num_rows > 0) {
    while($array = $result->fetch_assoc()) {

        echo '<title>Écobrique '. $array["serial_no"] .' | '. $array["weight_g"] .'g de plastique séquestré par '. $array["owner"] .' à '. $array["location_full"] .'.</title>';

        echo '<meta name="description" content="Une écobrique authentifiée qui a été publiée et archivée sur la blockchain manuelle brikcoin le ' . $array["date_logged_ts"] .'">';

        if ( isset($array["vision"]) && $array["vision"] != '' ) {
            echo '<meta name="description" content="'. $array["vision"] .'">';
        }

        echo '<meta name="keywords" content="écobrique, numéro de série '. $array["owner"] .', '. $array["owner"] .', brikchain, brikcoin, enregistrement brik, séquestration de plastique, recyclage, alternative, séquestration de plastique, compensation plastique, plastique AES, séquestration de carbone. '. $array["location_full"] .'">';

        echo '<meta property="og:url"           content="https://gobrik.com/fr/brik.php?serial_no='. $array["serial_no"] .'"/>';
        echo '<meta property="og:title"         content="Écobrique '. $array["serial_no"] .' | '. $array["weight_g"] .'g de plastique séquestré par '. $array["owner"] .' à '. $array["location_full"] .'.">';
        echo '<meta property="og:description"   content="Une écobrique authentifiée qui a été publiée et archivée sur la blockchain manuelle brikcoin le ' . $array["date_logged_ts"] .'"/>';
        echo '<meta property="og:image"         content="'. $array["ecobrick_full_photo_url"] .'"/>';
        echo '<meta property="og:image:alt"     content="L\'enregistrement brikchain d\'une écobrique authentifiée sur le brikchain"/>';
        echo '<meta property="og:locale" content="fr_FR" />';
        echo '<meta property="og:type"          content="website">';

        echo '<meta property="og:type" content="article" />
        <meta property="og:site_name" content="GoBrik.com" />
        <meta property="article:publisher" content="https://web.facebook.com/ecobricks.org" />
        <meta property="article:modified_time" content="'. $array["date_logged_ts"] .'" />
        <meta property="og:image:type" content="image/png" />
        <meta name="author" content="Alliance Globale d\'Écobriques" />
        <meta name="twitter:card" content="summary" />
        <meta name="twitter:label1" content="Temps de lecture estimé" />
        <meta name="twitter:data1" content="15 minutes" /> ';

    }

} else {
    echo '<META NAME="robots" CONTENT="noindex">';
    echo '<title>Aucune Écobrique Trouvée | GoBrik.com</title>';
    echo '<meta name="description" content="Aucune donnée trouvée pour ce numéro de série d\'écobrique. Cela est probablement dû à la migration des données brikchain."> ';
}

?>
