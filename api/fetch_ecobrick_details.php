<?php
// Include the GoBrik server connection credentials
require_once '../gobrikconn_env.php';

try {
    // Get the serial number from the request
    $serial_number = $_GET['serial_number'] ?? null;

    if (!$serial_number) {
        throw new Exception("Serial number is required.");
    }

    // Query to fetch ecobrick details
    $sql = "SELECT ecobrick_unique_id, full_photo_url, weight_g, volume_ml, ecobrick_maker
            FROM tb_ecobricks
            WHERE ecobrick_unique_id = ?";
    $stmt = $gobrik_conn->prepare($sql);

    if (!$stmt) {
        throw new Exception("Failed to prepare statement: " . $gobrik_conn->error);
    }

    $stmt->bind_param("s", $serial_number);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception("Ecobrick not found.");
    }

    // Return ecobrick details as JSON
    echo json_encode($result->fetch_assoc());
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
