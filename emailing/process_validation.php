<?php
require_once '../buwanaconn_env.php'; // Load database credentials
require_once 'validate_functions.php'; // Include validation functions

$conn = connect_db();
$failed_emails = [];

// Fetch emails from ghost_test_email_tb
$sql = "SELECT email FROM ghost_test_email_tb";
$result = $conn->query($sql);

while ($row = $result->fetch_assoc()) {
    $email = $row['email'];
    $validation = is_valid_email($email);

    if (!$validation['valid']) {
        $failed_emails[] = [$email, $validation['reason']];
        continue;
    }

    if (!domain_exists($email)) {
        $failed_emails[] = [$email, "Nonexistent domain"];
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

$conn->close();

// Return JSON response
echo json_encode(["failed_count" => $failed_count]);
?>
