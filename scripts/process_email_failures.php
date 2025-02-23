<?php
require_once '../scripts/earthen_subscribe_functions.php';
require_once '../buwanaconn_env.php'; // Use the Buwana database connection

define('BATCH_SIZE', 50); // Limit number of emails processed at a time

// Process emails if the button is clicked
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<h2>Processing Failed Email Unsubscribes...</h2>";

    try {
        // Fetch a batch of failed email addresses
        $sql_fetch = "SELECT id, email_addr FROM tb_failed_unsubscribes ORDER BY created_at ASC LIMIT ?";
        $stmt_fetch = $buwana_conn->prepare($sql_fetch);
        $stmt_fetch->bind_param('i', BATCH_SIZE);
        $stmt_fetch->execute();
        $result = $stmt_fetch->get_result();

        if ($result->num_rows === 0) {
            echo "<p>No failed email unsubscribes to process.</p>";
            exit();
        }

        echo "<ul>";
        $emails_to_process = [];
        while ($row = $result->fetch_assoc()) {
            $emails_to_process[$row['id']] = $row['email_addr'];
        }
        $stmt_fetch->close();

        // Process each email in batch
        foreach ($emails_to_process as $id => $email_addr) {
            echo "<li>Processing: <strong>$email_addr</strong>... ";

            $success = earthenUnsubscribe($email_addr);

            if ($success) {
                // Remove successfully unsubscribed emails from the queue
                $sql_delete = "DELETE FROM tb_failed_unsubscribes WHERE id = ?";
                $stmt_delete = $buwana_conn->prepare($sql_delete);
                $stmt_delete->bind_param('i', $id);
                $stmt_delete->execute();
                $stmt_delete->close();

                echo "<span style='color: green;'>Success ✅</span></li>";
            } else {
                echo "<span style='color: red;'>Failed ❌ (Kept in queue)</span></li>";
            }
        }
        echo "</ul>";

    } catch (Exception $e) {
        echo "<p style='color: red;'>Error processing failed unsubscribes: " . $e->getMessage() . "</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Process Failed Email Unsubscribes</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; padding: 20px; }
        button { padding: 10px 15px; font-size: 16px; cursor: pointer; background-color: #28a745; color: white; border: none; border-radius: 5px; }
        button:hover { background-color: #218838; }
    </style>
</head>
<body>
    <h1>Manual Email Unsubscribe Processing</h1>
    <p>Click the button below to process the next <strong><?php echo BATCH_SIZE; ?></strong> failed email unsubscribes.</p>
    <form method="post">
        <button type="submit">Process Failed Emails</button>
    </form>
</body>
</html>
