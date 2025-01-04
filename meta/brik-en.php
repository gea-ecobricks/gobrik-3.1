<?php

// Ensure the 'serial_no' parameter exists in the URL
if (isset($_GET['serial_no']) && !empty($_GET['serial_no'])) {
    // Sanitize the input
    $serialNo = intval($_GET['serial_no']); // Convert to an integer for safety

    // Use a prepared statement to fetch the ecobrick details
    $sql = "SELECT * FROM tb_ecobricks WHERE serial_no = ?";
    $stmt = $gobrik_conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("i", $serialNo);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            while ($array = $result->fetch_assoc()) {
                // Generate meta tags and title dynamically
                echo '<title>Ecobrick '. htmlspecialchars($array["serial_no"]) .' | '. htmlspecialchars($array["weight_g"]) .'g of plastic sequestered by '. htmlspecialchars($array["owner"]) .' in '. htmlspecialchars($array["location_full"]) .'.</title>';
                echo '<meta name="description" content="An authenticated ecobrick that was published and archived on the brikcoin manual blockchain on ' . htmlspecialchars($array["date_logged_ts"]) .'">';

                if (isset($array["vision"]) && $array["vision"] != '') {
                    echo '<meta name="description" content="'. htmlspecialchars($array["vision"]) .'">';
                }

                echo '<meta name="keywords" content="ecobrick, serial '. htmlspecialchars($array["owner"]) .', '. htmlspecialchars($array["owner"]) .', brikchain, brikcoin, brik record, plastic sequestration, recycling, alternative, sequestration of plastic, plastic offsetting, aes plastic, carbon sequestration. '. htmlspecialchars($array["location_full"]) .'">';

                echo '<meta property="og:url"           content="https://gobrik.com/en/briks.php?serial_no='. htmlspecialchars($array["serial_no"]) .'"/>' ;
                echo '<meta property="og:title"         content="Ecobrick '. htmlspecialchars($array["serial_no"]) .' | '. htmlspecialchars($array["weight_g"]) .'g of plastic sequestered by '. htmlspecialchars($array["owner"]) .' in '. htmlspecialchars($array["location_full"]) .'.">';
                echo '<meta property="og:description"   content="An authenticated ecobrick that was published and archived on the brikcoin manual blockchain on ' . htmlspecialchars($array["date_logged_ts"]) .'"/>';
                echo '<meta property="og:image"         content="'. htmlspecialchars($array["ecobrick_full_photo_url"]) .'"/>';
                echo '<meta property="og:image:alt"     content="The brikchain record of an authenticated ecobrick on the brikchain"/>';
                echo '<meta property="og:locale" content="en_GB" />';
                echo '<meta property="og:type"          content="website">';
                echo '<meta property="og:type" content="article" />
                    <meta property="og:site_name" content="GoBrik.com" />
                    <meta property="article:publisher" content="https://web.facebook.com/ecobricks.org" />
                    <meta property="article:modified_time" content="'. htmlspecialchars($array["date_logged_ts"]) .'" />
                    <meta property="og:image:type" content="image/png" />
                    <meta name="author" content="Global Ecobrick Alliance" />';
            }
        } else {
            echo '<META NAME="robots" CONTENT="noindex">';
            echo '<title>No Ecobrick Found | GoBrik.com</title>';
            echo '<meta name="description" content="No data found for this ecobrick serial number.  Most likely this is because the brikchain data is still in migration."> ';
        }

        $stmt->close();
    } else {
        echo '<p>Error preparing the SQL statement.</p>';
    }
} else {
    // Handle the case where 'serial_no' is not provided
    echo '<META NAME="robots" CONTENT="noindex">';
    echo '<title>No Serial Number Provided | GoBrik.com</title>';
    echo '<meta name="description" content="No serial number provided in the URL.">';
}
?>
