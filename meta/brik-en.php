<!-- ENGLISH ECOBRICK DETAILS PAGE -->

<?php


// Get the contents from the Ecobrick table as an ordered View, using the serial_no from the URL.
$serialNo = $_GET['serial_no'];
$sql = "SELECT * FROM tb_ecobricks WHERE serial_no = " . $serialNo;

$result = $gobrik_conn->query($sql);
if ($result->num_rows > 0) {
    while($array = $result->fetch_assoc()) {

		echo '<title>Ecobrick '. $array["serial_no"] .' | '. $array["weight_g"] .'g of plastic sequestered by '. $array["owner"] .' in '. $array["location_full"] .'.</title>';

        echo '<meta name="description" content="An authenticated ecobrick that was published and archived on the brikcoin manual blockchain on ' . $array["date_logged_ts"] .'">';

        if ( isset($array["vision"]) && $array["vision"] != '' ) {
            echo '<meta name="description" content="'. $array["vision"] .'">';

        }

		echo '<meta name="keywords" content="ecobrick, serial '. $array["owner"] .', '. $array["owner"] .', brikchain, brikcoin, brik record, plastic sequestration, recycling, alternative, sequestration of plastic, plastic offsetting, aes plastic, carbon sequestration. '. $array["location_full"] .'">';

        echo '<meta property="og:url"           content="https://gobrik.com/en/details-ecobrick-page.php?serial_no='. $array["serial_no"] .'"/>' ;
        echo '<meta property="og:title"         content="Ecobrick '. $array["serial_no"] .' | '. $array["weight_g"] .'g of plastic sequestered by '. $array["owner"] .' in '. $array["location_full"] .'.">';
        echo '<meta property="og:description"   content="An authenticated ecobrick that was published and archived on the brikcoin manual blockchain on ' . $array["date_logged_ts"] .'"/>';
        echo '<meta property="og:image"         content="'. $array["ecobrick_full_photo_url"] .'"/>';
        echo '<meta property="og:image:alt"     content="The brikchain record of an authenticated ecobrick on the brikchain"/>';
        echo '<meta property="og:locale" content="en_GB" />';
        echo '<meta property="og:type"          content="website">';


        echo '<meta property="og:type" content="article" />
        <meta property="og:site_name" content="GoBrik.com" />
        <meta property="article:publisher" content="https://web.facebook.com/ecobricks.org" />
        <meta property="article:modified_time" content="'. $array["date_logged_ts"] .'" />
        <meta property="og:image:type" content="image/png" />
        <meta name="author" content="Global Ecobrick Alliance" />
';

    }

} else {
    echo '<META NAME="robots" CONTENT="noindex">';
    echo '<title>No Ecobrick Found | GoBrik.com</title>';
    echo '<meta name="description" content="No data found for this ecobrick serial number.  Most likely this is because the brikchain data is still in migration."> ';
}

?>