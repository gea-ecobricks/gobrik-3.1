<?php
require_once '../buwanaconn_env.php'; // Load database credentials
require_once 'validate_functions.php'; // Include validation functions

// Ensure database connection exists
if (!isset($buwana_conn)) {
    die(json_encode(["error" => "Database connection not established"]));
}

// Fetch the next unvalidated email
$sql = "SELECT id, email FROM ghost_test_email_tb WHERE validated = 0 LIMIT 1";
$result = $buwana_conn->query($sql);

if ($result->num_rows === 0) {
    echo json_encode(["message" => "Validation complete!", "email" => "", "status" => "done"]);
    exit;
}

$row = $result->fetch_assoc();
$email = $row['email'];
$email_id = $row['id'];

$validation = is_valid_email($email);
$failed_reason = null;

if (!$validation['valid']) {
    $failed_reason = $validation['reason'];
} elseif (!domain_exists($email)) {
    $failed_reason = "Nonexistent domain";
}

// Insert failed email into failed_emails_tb (if invalid)
if ($failed_reason) {
    $stmt = $buwana_conn->prepare("INSERT INTO failed_emails_tb (email_addr, fail_reason) VALUES (?, ?)");
    $stmt->bind_param("ss", $email, $failed_reason);
    if (!$stmt->execute()) {
        die(json_encode(["error" => "Insert failed: " . $stmt->error]));
    }
    $stmt->close();

    // Delete invalid email from ghost_test_email_tb
    $delete_sql = "DELETE FROM ghost_test_email_tb WHERE id = ?";
    $delete_stmt = $buwana_conn->prepare($delete_sql);
    $delete_stmt->bind_param("i", $email_id);
    if (!$delete_stmt->execute()) {
        die(json_encode(["error" => "Delete failed: " . $delete_stmt->error]));
    }
    $delete_stmt->close();
} else {
    // Mark email as validated
    $update_sql = "UPDATE ghost_test_email_tb SET validated = 1 WHERE id = ?";
    $update_stmt = $buwana_conn->prepare($update_sql);
    $update_stmt->bind_param("i", $email_id);
    if (!$update_stmt->execute()) {
        die(json_encode(["error" => "Update failed: " . $update_stmt->error]));
    }
    $update_stmt->close();
}

$buwana_conn->close();

// Return JSON response with processed email details
echo json_encode([
    "email" => $email,
    "status" => $failed_reason ? "Failed - $failed_reason" : "Valid",
    "next" => true
]);
?>
