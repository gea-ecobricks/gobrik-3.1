<?php
require_once '../earthenAuth_helper.php'; // Include the authentication helper functions
require_once '../gobrikconn_env.php'; // Include the GoBrik database connection

// Set response headers for JSON response
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if the request method is POST and the required fields are set
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ecobrick_unique_id'])) {

    // Sanitize and assign POST variables
    $vision = isset($_POST['vision_message']) ? trim($_POST['vision_message']) : null; // Allow vision to be blank
    $ecobrick_unique_id = (int)$_POST['ecobrick_unique_id'];

    // Validate the ecobrick_unique_id
    if ($ecobrick_unique_id > 0) {

        // Prepare the base SQL query
        $sql = "UPDATE tb_ecobricks SET ";
        $params = [];
        $types = '';

        // Add the vision field only if it is not empty
        if (!empty($vision)) {
            $sql .= "vision = ?, ";
            $params[] = $vision;
            $types .= 's'; // 's' for string
        }

        // Add the status field to the update
        $sql .= "status = 'Awaiting validation' WHERE ecobrick_unique_id = ?";
        $params[] = $ecobrick_unique_id;
        $types .= 'i'; // 'i' for integer

        // Prepare the statement
        if ($stmt = $gobrik_conn->prepare($sql)) {

            // Bind the parameters dynamically
            $stmt->bind_param($types, ...$params);

            // Execute the statement
            if ($stmt->execute()) {
                // Check if any rows were affected
                if ($stmt->affected_rows > 0) {
                    // Success response
                    echo json_encode([
                        'success' => true,
                        'message' => 'Record successfully updated.'
                    ]);
                } else {
                    // No rows were affected
                    echo json_encode([
                        'success' => false,
                        'message' => 'No matching ecobrick found to update.'
                    ]);
                }
            } else {
                // Execution failed
                echo json_encode([
                    'success' => false,
                    'message' => 'Error executing SQL statement: ' . $stmt->error
                ]);
            }

            // Close the prepared statement
            $stmt->close();
        } else {
            // SQL preparation failed
            echo json_encode([
                'success' => false,
                'message' => 'Failed to prepare SQL statement: ' . $gobrik_conn->error
            ]);
        }

    } else {
        // Invalid ecobrick_unique_id
        echo json_encode([
            'success' => false,
            'message' => 'Invalid ecobrick ID provided.'
        ]);
    }

    // Close the database connection
    $gobrik_conn->close();

} else {
    // Invalid request method or missing required fields
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request. Please submit the form correctly.'
    ]);
}
?>