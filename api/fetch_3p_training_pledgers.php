<?php
require_once '../gobrikconn_env.php';

header('Content-Type: application/json');

$training_id = isset($_GET['training_id']) ? intval($_GET['training_id']) : 0;

if ($training_id <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid training ID.']);
    exit();
}

// Join registrations with ecobrickers (for name/email/gea_status) and pledges (for amounts/status)
$sql = "SELECT
    e.first_name,
    e.email_addr,
    e.gea_status,
    r.status         AS registration_status,
    p.pledge_status,
    p.pledged_amount_idr,
    p.display_currency,
    p.display_amount,
    p.confirmed_at   AS pledged_at
FROM training_registrations_tb r
LEFT JOIN tb_ecobrickers e
       ON e.buwana_id = r.buwana_id
LEFT JOIN training_pledges_tb p
       ON p.training_id   = r.training_id
      AND p.buwana_id     = r.buwana_id
      AND p.pledge_status NOT IN ('cancelled','expired','failed')
WHERE r.training_id = ?
  AND r.status NOT IN ('cancelled','expired')
ORDER BY p.confirmed_at ASC";

$stmt = $gobrik_conn->prepare($sql);

if (!$stmt) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $gobrik_conn->error]);
    exit();
}

$stmt->bind_param('i', $training_id);
$stmt->execute();
$result = $stmt->get_result();

$pledgers = [];
while ($row = $result->fetch_assoc()) {
    $pledgers[] = $row;
}

$stmt->close();
$gobrik_conn->close();

echo json_encode($pledgers);
?>
