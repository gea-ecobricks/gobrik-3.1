<?php
require_once("../buwanaconn_env.php"); // Include the database connection file

header('Content-Type: application/json'); // Set header for JSON response

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['continent_code'])) {
    $continent_code = $_POST['continent_code'];

    // Prepare and execute query
    $stmt = $buwana_conn->prepare("SELECT country_id, country_name FROM countries_tb WHERE continent_code = ? ORDER BY country_name");
    $stmt->bind_param('s', $continent_code);
    $stmt->execute();

    // Bind the results
    $stmt->bind_result($country_id, $country_name);

    $countries = [];
    // Fetch values and store in array
    while ($stmt->fetch()) {
        $countries[] = [
            'country_id' => $country_id,
            'country_name' => $country_name
        ];
    }

    $stmt->close();
    echo json_encode($countries); // Encode the result as JSON
} else {
    // If the request is invalid, return an empty array
    echo json_encode([]);
}
?>
