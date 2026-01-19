<!-- ENGLISH ECOBRICK DETAILS PAGE -->

<?php

if (isset($_GET['project_id']) && !empty($_GET['project_id'])) {
    $projectId = intval($_GET['project_id']);

    $sql = "SELECT * FROM tb_projects WHERE project_id = ?";
    $stmt = $gobrik_conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("i", $projectId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            while ($array = $result->fetch_assoc()) {

                echo '<title>' . htmlspecialchars($array["project_name"], ENT_QUOTES, 'UTF-8') . ' |  ' . htmlspecialchars($array["briks_used"], ENT_QUOTES, 'UTF-8') . ' ecobricks</title>';

                echo '<meta name="description" content="' . htmlspecialchars($array["est_total_weight"], ENT_QUOTES, 'UTF-8') . '&#8202;kg plastik telah diamankan di ' . htmlspecialchars($array["location_full"], ENT_QUOTES, 'UTF-8') . ' dalam kreasi ' . htmlspecialchars($array["project_type"], ENT_QUOTES, 'UTF-8') . ' .">';


                echo '<meta name="keywords" content="ecobrick, serial ' . htmlspecialchars($array["project_admins"], ENT_QUOTES, 'UTF-8') . ', ' . htmlspecialchars($array["construction_type"], ENT_QUOTES, 'UTF-8') . ',' . htmlspecialchars($array["project_type"], ENT_QUOTES, 'UTF-8') . ', sekuestrasi plastik, daur ulang, alternatif, sekuestrasi plastik, penggantian kerugian plastik, plastik aes, sekuestrasi karbon. ' . htmlspecialchars($array["location_full"], ENT_QUOTES, 'UTF-8') . '">';

                echo '<meta property="og:url"           content="https://ecobricks.org/' . htmlspecialchars($lang, ENT_QUOTES, 'UTF-8') . '/project.php?project_id=' . htmlspecialchars($array["project_id"], ENT_QUOTES, 'UTF-8') . '">' ;
                echo '<meta property="og:title"         content="' . htmlspecialchars($array["project_name"], ENT_QUOTES, 'UTF-8') . ' |  ' . htmlspecialchars($array["briks_used"], ENT_QUOTES, 'UTF-8') . ' ecobricks">';
                echo '<meta property="og:description"   content="' . htmlspecialchars($array["est_total_weight"], ENT_QUOTES, 'UTF-8') . '&#8202;kg plastik telah diamankan di ' . htmlspecialchars($array["location_full"], ENT_QUOTES, 'UTF-8') . ' dalam kreasi ' . htmlspecialchars($array["project_type"], ENT_QUOTES, 'UTF-8') . '.">';
                echo '<meta property="og:image"         content="https://ecobricks.org/projects/photos/project-' . htmlspecialchars($array["project_id"], ENT_QUOTES, 'UTF-8') . '-1.webp?v=3">';
                echo '<meta property="og:image:alt"     content="Foto di proyek ecobrick">';
                echo '<meta property="og:locale" content="id_ID" >';
                echo '<meta property="og:type"          content="website">';


                echo '<meta property="og:type" content="article" >
                <meta property="og:site_name" content="Ecobricks.org" >
                <meta property="article:publisher" content="https://web.facebook.com/ecobricks.org" >
                <meta property="article:modified_time" content="' . htmlspecialchars($array["logged_ts"], ENT_QUOTES, 'UTF-8') . '" >
                <meta property="og:image:type" content="image/webp" >
                <meta name="author" content="Global Ecobrick Alliance" >
                <meta name="twitter:card" content="summary" >
                <meta name="twitter:label1" content="Est. reading time" >
                <meta name="twitter:data1" content="15 minutes" > ';
            }
        } else {
            echo '<META NAME="robots" CONTENT="noindex">';
            echo '<title>Ecobrick Tidak Ditemukan | Ecobricks.org</title>';
            echo '<meta name="description" content="Tidak ada data yang ditemukan untuk proyek ini. Mungkin sudah dihapus atau dipindahkan."> ';
        }

        $stmt->close();
    } else {
        echo '<p>Error preparing the SQL statement.</p>';
    }
} else {
    echo '<META NAME="robots" CONTENT="noindex">';
    echo '<title>ID Proyek Tidak Tersedia | Ecobricks.org</title>';
    echo '<meta name="description" content="Tidak ada ID proyek yang disediakan di URL.">';
}

?>
