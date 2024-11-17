<?php
require_once("../buwanaconn_env.php"); // Include the database connection file

header('Content-Type: application/json'); // Set header for JSON response

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['country_id'])) {
    $country_id = $_POST['country_id'];

    // Prepare and execute query to fetch watersheds based on the selected country_id
    $stmt = $buwana_conn->prepare("
        SELECT w.watershed_id, w.watershed_name
        FROM watersheds_tb w
        INNER JOIN watersheds_countries wc ON w.watershed_id = wc.watershed_id
        WHERE wc.country_id = ?
        ORDER BY w.watershed_name
    ");

    $stmt->bind_param('i', $country_id);
    $stmt->execute();

    // Bind the results
    $stmt->bind_result($watershed_id, $watershed_name);

    $watersheds = [];
    // Fetch values and store in array
    while ($stmt->fetch()) {
        $watersheds[] = [
            'watershed_id' => $watershed_id,
            'watershed_name' => $watershed_name
        ];
    }

    $stmt->close();
    echo json_encode($watersheds); // Encode the result as JSON
} else {
    // If the request is invalid, return an empty array
    echo json_encode([]);
}
?>

