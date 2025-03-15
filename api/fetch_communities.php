<?php
require_once '../gobrikconn_env.php'; // Database connection

if (isset($_GET['search'])) {
    $search = "%" . $_GET['search'] . "%"; // Wildcard search

    $sql = "SELECT com_id, com_name FROM communities_tb WHERE com_name LIKE ? ORDER BY com_name ASC LIMIT 10";
    $stmt = $gobrik_conn->prepare($sql);
    $stmt->bind_param("s", $search);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        echo "<div class='community-option' data-id='" . $row['com_id'] . "'>" . htmlspecialchars($row['com_name'], ENT_QUOTES, 'UTF-8') . "</div>";
    }

    $stmt->close();
    $gobrik_conn->close();
}
?>
