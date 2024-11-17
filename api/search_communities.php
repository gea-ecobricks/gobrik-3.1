<?php
require_once '../buwanaconn_env.php'; // Your database connection

header('Content-Type: application/json'); // Set content type to JSON

$communities = []; // Initialize an empty array for communities

if (isset($_POST['query'])) {
    $query = '%' . trim($_POST['query']) . '%';  // Search with wildcards

    // Prepare the SQL query to search for communities by name
    $sql_search = "SELECT com_id, com_name FROM communities_tb WHERE com_name LIKE ?";
    $stmt_search = $buwana_conn->prepare($sql_search);

    if ($stmt_search) {
        $stmt_search->bind_param('s', $query);
        $stmt_search->execute();
        $stmt_search->bind_result($com_id, $com_name);

        while ($stmt_search->fetch()) {
            $communities[] = ['com_id' => $com_id, 'com_name' => $com_name];
        }

        $stmt_search->close();
    } else {
        // Log and handle SQL preparation errors
        error_log("Database query preparation failed: " . $buwana_conn->error);
    }
}

// Return the communities array as JSON, whether empty or populated
echo json_encode($communities);

$buwana_conn->close();
?>
