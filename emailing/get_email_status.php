<?php
header('Content-Type: application/json');

require_once __DIR__ . '/earthen_helpers.php';

try {
    $status_limit = 20;
    $sent_limit = 4;

    $members = fetchGhostMembers();
    $summary = summarizeGhostMembers($members, 'sent-001');

    usort($summary['sent'], function ($a, $b) {
        return strcmp($b['updated_at'] ?? '', $a['updated_at'] ?? '');
    });

    usort($summary['pending'], function ($a, $b) {
        return strcmp($a['created_at'] ?? '', $b['created_at'] ?? '');
    });

    $sent_members = array_slice($summary['sent'], 0, $sent_limit);
    $pending_limit = $status_limit - count($sent_members);
    $pending_members = array_slice($summary['pending'], 0, $pending_limit);

    $all_members = array_map(function ($member) {
        $member['email_open_rate'] = calculateOpenRate($member);
        $member['test_sent'] = memberHasLabel($member, 'sent-001');
        $member['test_sent_date_time'] = $member['updated_at'] ?? 'N/A';
        return $member;
    }, array_merge($sent_members, $pending_members));

    echo json_encode(['success' => true, 'members' => $all_members]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
