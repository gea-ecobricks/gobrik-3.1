<?php
require_once '../buwanaconn_env.php';
require_once '../ghostconn_env.php';
header('Content-Type: application/json');

try {
    $status_limit = 20;
    $sent_limit = 4;

    $sent_label_slug = 'sent-001';

    $sent_sql = "SELECT m.id, m.email, m.name, ml.created_at AS test_sent_date_time, 1 AS test_sent
                  FROM members m
                  INNER JOIN members_newsletters mn ON mn.member_id = m.id AND mn.subscribed = 1
                  INNER JOIN members_labels ml ON ml.member_id = m.id
                  INNER JOIN labels l ON l.id = ml.label_id AND (l.slug = ? OR l.name = ?)
                  ORDER BY ml.created_at DESC
                  LIMIT {$sent_limit}";
    $sent_stmt = $ghost_conn->prepare($sent_sql);
    $sent_stmt->bind_param('ss', $sent_label_slug, $sent_label_slug);
    $sent_stmt->execute();
    $sent_res = $sent_stmt->get_result();
    $sent_members = $sent_res ? $sent_res->fetch_all(MYSQLI_ASSOC) : [];
    $sent_stmt->close();
    $sent_count = count($sent_members);

    $pending_limit = $status_limit - $sent_count;

    $pending_sql = "SELECT m.id, m.email, m.name, NULL AS test_sent_date_time, 0 AS test_sent
                     FROM members m
                     INNER JOIN members_newsletters mn ON mn.member_id = m.id AND mn.subscribed = 1
                     WHERE NOT EXISTS (
                        SELECT 1 FROM members_labels ml
                        INNER JOIN labels l ON l.id = ml.label_id
                        WHERE ml.member_id = m.id AND (l.slug = ? OR l.name = ?)
                     )
                     ORDER BY m.created_at ASC
                     LIMIT {$pending_limit}";
    $pending_stmt = $ghost_conn->prepare($pending_sql);
    $pending_stmt->bind_param('ss', $sent_label_slug, $sent_label_slug);
    $pending_stmt->execute();
    $pending_res = $pending_stmt->get_result();
    $pending_members = $pending_res ? $pending_res->fetch_all(MYSQLI_ASSOC) : [];
    $pending_stmt->close();

    $all_members = array_merge($sent_members, $pending_members);

    echo json_encode(['success' => true, 'members' => $all_members]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
