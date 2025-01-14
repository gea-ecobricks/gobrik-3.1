<?php
require_once '../buwanaconn_env.php'; // Database connection

header('Content-Type: application/json');
$response = ['success' => false];

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    $response['message'] = 'Invalid request method. Use POST.';
    echo json_encode($response);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
$buwana_id = $input['buwana_id'] ?? null;

if (empty($buwana_id) || !is_numeric($buwana_id)) {
    $response['message'] = 'Invalid or missing Buwana ID.';
    echo json_encode($response);
    exit();
}

try {
    // Fetch user data from users_tb
    $sqlUser = "SELECT first_name, last_synk_ts, continent_code, location_full FROM users_tb WHERE buwana_id = ?";
    $stmtUser = $conn->prepare($sqlUser);
    $stmtUser->bind_param("i", $buwana_id);
    $stmtUser->execute();
    $userData = $stmtUser->get_result()->fetch_assoc();
    $stmtUser->close();

    if (!$userData) {
        throw new Exception("User not found.");
    }

    // Fetch personal calendars
    $sqlPersonalCalendars = "SELECT calendar_id, calendar_name FROM calendars_tb WHERE buwana_id = ?";
    $stmtPersonal = $conn->prepare($sqlPersonalCalendars);
    $stmtPersonal->bind_param("i", $buwana_id);
    $stmtPersonal->execute();
    $personalCalendars = $stmtPersonal->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmtPersonal->close();

    // Fetch subscribed calendars
    $sqlSubscribedCalendars = "SELECT c.calendar_id, c.calendar_name FROM cal_subscriptions_tb s
                               JOIN calendars_tb c ON s.calendar_id = c.calendar_id
                               WHERE s.buwana_id = ?";
    $stmtSubscribed = $conn->prepare($sqlSubscribedCalendars);
    $stmtSubscribed->bind_param("i", $buwana_id);
    $stmtSubscribed->execute();
    $subscribedCalendars = $stmtSubscribed->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmtSubscribed->close();

    // Prepare response
    $response['success'] = true;
    $response['user'] = $userData;
    $response['personal_calendars'] = $personalCalendars;
    $response['subscribed_calendars'] = $subscribedCalendars;
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
} finally {
    $conn->close();
}

echo json_encode($response);
exit();
?>
