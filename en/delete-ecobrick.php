<?php
// Start the session and enable error reporting for debugging
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include the database connection
require_once '../gobrikconn_env.php'; // Ensure this file sets up $gobrik_conn correctly

// Ensure proper response headers for JSON response
header('Content-Type: application/json');

// Check if the request is a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve the serial_no from the POST data
    $serial_no = $_POST['serial_no'] ?? null;

    // Check if the serial_no is valid
    if ($serial_no) {
        try {
            // Prepare the DELETE SQL query based on the serial_no
            $sql = "DELETE FROM tb_ecobricks WHERE serial_no = ?";
            $stmt = $gobrik_conn->prepare($sql);

            // Check if the query was successfully prepared
            if ($stmt) {
                // Bind the serial_no to the SQL query
                $stmt->bind_param('s', $serial_no);

                // Execute the query
                if ($stmt->execute()) {
                    // If the deletion was successful, send success response
                    echo json_encode(['success' => true, 'message' => 'Ecobrick deleted successfully']);
                } else {
                    // If there was an error executing the query, send error response
                    echo json_encode(['success' => false, 'error' => 'Failed to delete ecobrick: ' . $stmt->error]);
                }

                // Close the statement
                $stmt->close();
            } else {
                // If preparing the query failed, send error response
                echo json_encode(['success' => false, 'error' => 'Failed to prepare statement: ' . $gobrik_conn->error]);
            }
        } catch (Exception $e) {
            // Catch any exception that occurred and send error response
            echo json_encode(['success' => false, 'error' => 'Exception occurred: ' . $e->getMessage()]);
        }
    } else {
        // If serial_no is invalid or missing, send error response
        echo json_encode(['success' => false, 'error' => 'Invalid ecobrick serial number provided']);
    }
} else {
    // If the request is not POST, send error response
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}

// Close the database connection
if (isset($gobrik_conn)) {
    $gobrik_conn->close();
}
?>
