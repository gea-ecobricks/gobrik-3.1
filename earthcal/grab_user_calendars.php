try {
    // Log incoming request
    error_log('Incoming request: ' . file_get_contents('php://input'));

    // Fetch all calendars associated with the user
    $sqlAllCalendars = "SELECT calendar_id AS id, calendar_name AS name, calendar_color AS color
                        FROM calendars_tb
                        WHERE buwana_id = ?";
    $stmtAll = $cal_conn->prepare($sqlAllCalendars);
    $stmtAll->bind_param("i", $buwana_id);
    $stmtAll->execute();
    $allCalendars = $stmtAll->get_result()->fetch_all(MYSQLI_ASSOC);

    // Log query results
    error_log('Fetched calendars: ' . print_r($allCalendars, true));

    $stmtAll->close();

    // Prepare response
    $response['success'] = true;
    $response['calendars'] = $allCalendars;

    if (empty($allCalendars)) {
        $response['message'] = 'No calendars found for this user.';
    }
} catch (Exception $e) {
    error_log('Error fetching calendars: ' . $e->getMessage());
    $response['message'] = $e->getMessage();
}
