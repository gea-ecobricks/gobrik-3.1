<?php
// Include database connection file
require_once 'db_connection.php';

// Set headers for JSON response
header('Content-Type: application/json');

try {
    // Ensure the request method is POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405); // Method Not Allowed
        echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
        exit;
    }

    // Get the raw input data
    $inputData = file_get_contents('php://input');
    $dateCycle = json_decode($inputData, true);

    // Validate required fields
    $requiredFields = ['user_id', 'calendar_id', 'event_name', 'date'];
    foreach ($requiredFields as $field) {
        if (empty($dateCycle[$field])) {
            http_response_code(400); // Bad Request
            echo json_encode(['success' => false, 'message' => "Missing required field: $field"]);
            exit;
        }
    }

    // Extract data from the request
    $userId = intval($dateCycle['user_id']);
    $calendarId = intval($dateCycle['calendar_id']);
    $eventName = htmlspecialchars($dateCycle['event_name']);
    $date = $dateCycle['date']; // Assume date is already in YYYY-MM-DD format
    $frequency = $dateCycle['Frequency'] ?? 'One-time';
    $completed = $dateCycle['Completed'] ?? 'No';
    $pinned = $dateCycle['Pinned'] ?? 'No';
    $public = $dateCycle['public'] ?? 'No';
    $comment = htmlspecialchars($dateCycle['comment'] ?? '');
    $color = htmlspecialchars($dateCycle['calendar_color'] ?? '');
    $toDelete = $dateCycle['Delete'] ?? 'No';
    $lastEdited = date('Y-m-d H:i:s'); // Current timestamp
    $synked = 'Yes'; // API always marks records as synced

    // Prepare SQL to insert a new record
    $sql = "INSERT INTO datecycles_tb (
                user_id, calendar_id, event_name, date, frequency, completed, pinned, public,
                comment, color, delete, last_edited, synked
            ) VALUES (
                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
            )";

    // Prepare and execute the statement
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $userId, $calendarId, $eventName, $date, $frequency, $completed, $pinned, $public,
        $comment, $color, $toDelete, $lastEdited, $synked
    ]);

    // Get the last inserted ID
    $lastInsertId = $pdo->lastInsertId();

    // Respond with success and the new ID
    http_response_code(201); // Created
    echo json_encode([
        'success' => true,
        'message' => 'DateCycle added successfully.',
        'id' => $lastInsertId
    ]);
} catch (PDOException $e) {
    // Database error
    http_response_code(500); // Internal Server Error
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    // General error
    http_response_code(500); // Internal Server Error
    echo json_encode(['success' => false, 'message' => 'An unexpected error occurred: ' . $e->getMessage()]);
}
?>
