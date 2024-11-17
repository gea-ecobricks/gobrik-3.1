<?php
require_once '../earthenAuth_helper.php'; // Include the authentication helper functions
require_once '../gobrikconn_env.php'; // Include the GoBrik database connection

// Set response headers for JSON response
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if the request method is POST and the required fields are set
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['vision_message']) && isset($_POST['ecobrick_unique_id'])) {

    // Sanitize and assign POST variables
    $vision = trim($_POST['vision_message']); // Use 'vision_message' from the form, mapped to 'vision' in the DB
    $ecobrick_unique_id = (int)$_POST['ecobrick_unique_id'];

    // Validate that the vision message isn't empty and the ecobrick_unique_id is a valid integer
    if (!empty($vision) && $ecobrick_unique_id > 0) {

        // Prepare SQL query to update the ecobrick record and set status to 'Awaiting validation'
        $sql = "UPDATE tb_ecobricks SET vision = ?, status = 'Awaiting validation' WHERE ecobrick_unique_id = ?";

        // Prepare the statement
        if ($stmt = $gobrik_conn->prepare($sql)) {

            // Bind parameters (s = string, i = integer)
            $stmt->bind_param('si', $vision, $ecobrick_unique_id);

            // Execute the statement
            if ($stmt->execute()) {
                // Check if any rows were affected (if the ecobrick exists and was updated)
                if ($stmt->affected_rows > 0) {
                    // Success response
                    echo json_encode([
                        'success' => true,
                        'message' => 'Vision successfully added and status updated.'
                    ]);
                } else {
                    // No rows were affected, meaning ecobrick_unique_id may not exist
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
        // Invalid input data
        echo json_encode([
            'success' => false,
            'message' => 'Invalid input data. Please provide a valid vision and ecobrick ID.'
        ]);
    }

    // Close the database connection
    $gobrik_conn->close();

} else {
    // Invalid request method or missing fields
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request. Please submit the form correctly.'
    ]);
}

