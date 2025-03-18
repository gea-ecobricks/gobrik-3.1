<?php
require_once '../buwanaconn_env.php'; // Load database credentials
require_once 'validate_functions.php'; // Include validation functions

$conn = connect_db();
$failed_emails = [];
$invalid_email_ids = [];

// Fetch emails from ghost_test_email_tb
$sql = "SELECT id, email FROM ghost_test_email_tb";  // Assuming 'id' is the primary key
$result = $conn->query($sql);

while ($row = $result->fetch_assoc()) {
    $email = $row['email'];
    $email_id = $row['id'];  // Store ID for deletion
    $validation = is_valid_email($email);

    if (!$validation['valid']) {
        $failed_emails[] = [$email, $validation['reason']];
        $invalid_email_ids[] = $email_id;
        continue;
    }

    if (!domain_exists($email)) {
        $failed_emails[] = [$email, "Nonexistent domain"];
        $invalid_email_ids[] = $email_id;
    }
}

// Insert failed emails into failed_emails_tb
$failed_count = count($failed_emails);
if ($failed_count > 0) {
    $stmt = $conn->prepare("INSERT INTO failed_emails_tb (email, reason) VALUES (?, ?)");
    foreach ($failed_emails as $failed) {
        $stmt->bind_param("ss", $failed[0], $failed[1]);
        $stmt->execute();
    }
    $stmt->close();
}

// Delete invalid emails from ghost_test_email_tb
if (!empty($invalid_email_ids)) {
    $ids_to_delete = implode(",", $invalid_email_ids);
    $delete_sql = "DELETE FROM ghost_test_email_tb WHERE id IN ($ids_to_delete)";
    $conn->query($delete_sql);
}

$conn->close();

// Return JSON response
echo json_encode(["failed_count" => $failed_count, "deleted_count" => count($invalid_email_ids)]);
?>
