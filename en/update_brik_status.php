<?php
require_once '../earthenAuth_helper.php'; // Include the authentication helper functions
require_once '../gobrikconn_env.php'; // Include the GoBrik database connection


// Function to update the status of the ecobrick
function setEcobrickStatus($status, $ecobrick_unique_id) {
    global $gobrik_conn;

    // Prepare the SQL query to update the status of the ecobrick
    $sql = "UPDATE tb_ecobricks SET status = ? WHERE ecobrick_unique_id = ?";
    if ($stmt = $gobrik_conn->prepare($sql)) {
        $stmt->bind_param('si', $status, $ecobrick_unique_id);
        $stmt->execute();
        $stmt->close();
        return true;
    } else {
        error_log('Failed to update ecobrick status: ' . $gobrik_conn->error);
        return false;
    }
}


// Set response headers for JSON response
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if the request method is POST and the action is skip
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'skip' && isset($_POST['ecobrick_unique_id'])) {
    $ecobrick_unique_id = (int)$_POST['ecobrick_unique_id'];

    // Update the status of the ecobrick to 'Awaiting validation'
    if (setEcobrickStatus('Awaiting validation', $ecobrick_unique_id)) {
        echo json_encode(['success' => true, 'message' => 'Status updated to Awaiting validation.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update status.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
}
?>
