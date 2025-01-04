<!-- HALAMAN DETAIL ECOBRICK DALAM BAHASA INDONESIA -->

<?php

// Mengambil konten dari tabel Ecobrick sebagai tampilan terurut, menggunakan serial_no dari URL.
$serialNo = $_GET['serial_no'];
$sql = "SELECT * FROM tb_ecobricks WHERE serial_no = " . $serialNo;

$result = $gobrik_conn->query($sql);
if ($result->num_rows > 0) {
    while($array = $result->fetch_assoc()) {

        echo '<title>Ecobrick '. $array["serial_no"] .' | '. $array["weight_g"] .'g plastik disekuestrasi oleh '. $array["owner"] .' di '. $array["location_full"] .'.</title>';

        echo '<meta name="description" content="Sebuah ecobrick yang terautentikasi yang diterbitkan dan diarsipkan di blockchain manual brikcoin pada ' . $array["date_logged_ts"] .'">';

        if ( isset($array["vision"]) && $array["vision"] != '' ) {
            echo '<meta name="description" content="'. $array["vision"] .'">';
        }

        echo '<meta name="keywords" content="ecobrick, nomor seri '. $array["owner"] .', '. $array["owner"] .', brikchain, brikcoin, catatan brik, sekuestrasi plastik, daur ulang, alternatif, sekuestrasi plastik, kompensasi plastik, plastik AES, sekuestrasi karbon. '. $array["location_full"] .'">';

        echo '<meta property="og:url"           content="https://gobrik.com/id/brik.php?serial_no='. $array["serial_no"] .'"/>' ;
        echo '<meta property="og:title"         content="Ecobrick '. $array["serial_no"] .' | '. $array["weight_g"] .'g plastik disekuestrasi oleh '. $array["owner"] .' di '. $array["location_full"] .'.">';
        echo '<meta property="og:description"   content="Sebuah ecobrick yang terautentikasi yang diterbitkan dan diarsipkan di blockchain manual brikcoin pada ' . $array["date_logged_ts"] .'"/>';
        echo '<meta property="og:image"         content="'. $array["ecobrick_full_photo_url"] .'"/>';
        echo '<meta property="og:image:alt"     content="Catatan brikchain dari ecobrick yang terautentikasi pada brikchain"/>';
        echo '<meta property="og:locale" content="id_ID" />';
        echo '<meta property="og:type"          content="website">';

        echo '<meta property="og:type" content="article" />
        <meta property="og:site_name" content="GoBrik.com" />
        <meta property="article:publisher" content="https://web.facebook.com/ecobricks.org" />
        <meta property="article:modified_time" content="'. $array["date_logged_ts"] .'" />
        <meta property="og:image:type" content="image/png" />
        <meta name="author" content="Aliansi Global Ecobrick" />
       ';

    }

} else {
    echo '<META NAME="robots" CONTENT="noindex">';
    echo '<title>Ecobrick Tidak Ditemukan | GoBrik.com</title>';
    echo '<meta name="description" content="Tidak ada data ditemukan untuk nomor seri ecobrick ini. Kemungkinan besar karena data brikchain masih dalam migrasi."> ';
}

?>
