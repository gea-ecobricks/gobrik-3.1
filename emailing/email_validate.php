<?php
require_once '../buwanaconn_env.php'; // Load database credentials

// Function to connect to the database
function connect_db() {
    global $buwana_conn;

    // Ensure the database connection exists
    if (!$buwana_conn) {
        die("Database connection failed: " . mysqli_connect_error());
    }

    return $buwana_conn;
}

// Function to get the total email count
function get_total_email_count() {
    $conn = connect_db();

    $sql = "SELECT COUNT(*) AS total FROM ghost_test_email_tb";
    $result = $conn->query($sql);

    if (!$result) {
        die("Query failed: " . $conn->error); // Error handling for query failure
    }

    $row = $result->fetch_assoc();
    return $row['total'] ?? 0; // Return 0 if no result
}

$total_emails = get_total_email_count();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Validator</title>
    <script>
        function validateEmails() {
            let progressDiv = document.getElementById("progress");
            let failedCountDiv = document.getElementById("failed-count");
            
            progressDiv.innerHTML = "Validating emails... Please wait.";
            failedCountDiv.innerHTML = "";

            fetch("process_validation.php")
            .then(response => response.json())
            .then(data => {
                progressDiv.innerHTML = "Validation Completed!";
                failedCountDiv.innerHTML = "Failed Emails: " + (data.failed_count || 0);
            })
            .catch(error => {
                progressDiv.innerHTML = "Error occurred during validation!";
                console.error("Fetch error:", error);
            });
        }
    </script>
</head>
<body>
    <h2>Email Validator</h2>
    <p>Total Emails in Database: <strong><?php echo htmlspecialchars($total_emails, ENT_QUOTES, 'UTF-8'); ?></strong></p>

    <button onclick="validateEmails()">Validate Emails</button>

    <p id="progress"></p>
    <p id="failed-count"></p>
</body>
</html>
