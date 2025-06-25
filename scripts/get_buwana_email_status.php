<?php
require_once '../buwanaconn_env.php';
header('Content-Type: application/json');

try {
    $status_limit = 20;
    $sent_limit = 4;

    $sent_sql = "SELECT buwana_id, email, full_name, bot_score, test_sent, test_sent_date_time
                   FROM users_tb
                   WHERE test_sent = 1
                   ORDER BY test_sent_date_time DESC
                   LIMIT {$sent_limit}";
    $sent_res = $buwana_conn->query($sent_sql);
    $sent_members = $sent_res ? $sent_res->fetch_all(MYSQLI_ASSOC) : [];
    $sent_count = count($sent_members);

    $pending_limit = $status_limit - $sent_count;

    $pending_sql = "SELECT buwana_id, email, full_name, bot_score, test_sent, test_sent_date_time
                    FROM users_tb
                    WHERE test_sent = 0 AND processing IS NULL
                    ORDER BY created_at DESC
                    LIMIT {$pending_limit}";
    $pending_res = $buwana_conn->query($pending_sql);
    $pending_members = $pending_res ? $pending_res->fetch_all(MYSQLI_ASSOC) : [];

    $all_members = array_merge($sent_members, $pending_members);

    echo json_encode(['success' => true, 'members' => $all_members]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
