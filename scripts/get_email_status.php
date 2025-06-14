<?php
require_once '../buwanaconn_env.php';
header('Content-Type: application/json');

try {
    $sent_sql = "SELECT id, email, name, email_open_rate, test_sent, test_sent_date_time
                  FROM earthen_members_tb
                  WHERE test_sent = 1
                  ORDER BY test_sent_date_time DESC
                  LIMIT 4";
    $sent_res = $buwana_conn->query($sent_sql);
    $sent_members = $sent_res ? $sent_res->fetch_all(MYSQLI_ASSOC) : [];

    $pending_sql = "SELECT id, email, name, email_open_rate, test_sent, test_sent_date_time
                     FROM earthen_members_tb
                     WHERE test_sent = 0
                     ORDER BY id ASC";
    $pending_res = $buwana_conn->query($pending_sql);
    $pending_members = $pending_res ? $pending_res->fetch_all(MYSQLI_ASSOC) : [];

    $all_members = array_merge($sent_members, $pending_members);

    echo json_encode(['success' => true, 'members' => $all_members]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
