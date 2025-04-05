<?php
require_once '../buwanaconn_env.php';
header('Content-Type: application/json');

// Only handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form values
    $com_name = trim($_POST['newCommunityName']);
    $com_type = trim($_POST['newCommunityType']);
    $country_id = intval($_POST['communityCountry']);
    $com_lang = intval($_POST['communityLanguage']); // Now directly using language_id

    // Fetch country name from countries_tb using the provided country_id
    $sql_get_country = "SELECT country_name FROM countries_tb WHERE country_id = ?";
    $stmt_country = $buwana_conn->prepare($sql_get_country);
    $stmt_country->bind_param("i", $country_id);
    $stmt_country->execute();
    $stmt_country->bind_result($com_country);
    $stmt_country->fetch();
    $stmt_country->close();

    // Debug log
    error_log("Received: Name=$com_name, Type=$com_type, Country ID=$country_id, Country=$com_country, Lang ID=$com_lang");

    // Validate inputs
    if (empty($com_name) || empty($com_type) || empty($com_country) || empty($com_lang)) {
        echo json_encode(["success" => false, "message" => "All fields are required."]);
        exit;
    }

    // Insert the new community into communities_tb
    $sql_insert = "INSERT INTO communities_tb (com_name, com_country, com_type, com_lang, country_id, created_at, updated_at)
                   VALUES (?, ?, ?, ?, ?, NOW(), NOW())";

    $stmt_insert = $buwana_conn->prepare($sql_insert);
    $stmt_insert->bind_param("sssii", $com_name, $com_country, $com_type, $com_lang, $country_id);

    if ($stmt_insert->execute()) {
        echo json_encode([
            "success" => true,
            "message" => "✅ Community added successfully!",
            "community_name" => $com_name
        ]);
    } else {
        error_log("Insert failed: " . $stmt_insert->error);
        echo json_encode(["success" => false, "message" => "Error adding community."]);
    }

    $stmt_insert->close();
    $buwana_conn->close();
} else {
    echo json_encode(["success" => false, "message" => "Invalid request."]);
}
?>
