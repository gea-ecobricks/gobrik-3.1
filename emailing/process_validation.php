<?php
require_once '../gobrikconn_env.php'; // Gobrik database (for Mailgun logging)
require_once '../buwanaconn_env.php'; // Buwana database
require_once 'validate_functions.php'; // Include validation functions

function logValidationFailure(mysqli $conn, string $email, string $reason): void
{
    $event_timestamp = date('Y-m-d H:i:s');
    $stmt = $conn->prepare(
        "INSERT INTO earthen_mailgun_events_tb (
            recipient_email,
            event_type,
            event_timestamp,
            severity,
            reason,
            error_message
        ) VALUES (?, 'validation_failed', ?, 'temporary', 'validation_failed', ?)"
    );

    if (!$stmt) {
        die(json_encode(["error" => "Insert failed: " . $conn->error]));
    }

    $stmt->bind_param("sss", $email, $event_timestamp, $reason);

    if (!$stmt->execute()) {
        die(json_encode(["error" => "Insert failed: " . $stmt->error]));
    }

    $stmt->close();
}

// Ensure database connections exist
if (!isset($gobrik_conn) || !isset($buwana_conn)) {
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

// Insert failed email into earthen_mailgun_events_tb (if invalid)
if ($failed_reason) {
    logValidationFailure($gobrik_conn, $email, $failed_reason);

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
