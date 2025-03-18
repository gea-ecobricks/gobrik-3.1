<?php
require_once '../buwanaconn_env.php'; // Load database credentials

// Ensure database connection exists
if (!isset($buwana_conn)) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Function to get the total pending email count
function get_total_email_count() {
    global $buwana_conn;

    $sql = "SELECT COUNT(*) AS total FROM ghost_test_email_tb WHERE validated = 0";
    $result = $buwana_conn->query($sql);

    if (!$result) {
        die("Query failed: " . $buwana_conn->error);
    }

    $row = $result->fetch_assoc();
    return $row['total'] ?? 0; // Return 0 if empty
}

$total_emails = get_total_email_count();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Validator</title>
    <style>
        #email-log {
            border: 1px solid #ccc;
            padding: 10px;
            margin-top: 10px;
            max-height: 200px;
            overflow-y: auto;
            background: #f9f9f9;
        }
    </style>
    <script>
        let processing = false;

        function updateEmailCount() {
            fetch("get_email_count.php")
                .then(response => response.json())
                .then(data => {
                    document.getElementById("email-count").innerText = data.total;
                })
                .catch(error => console.error("Error fetching email count:", error));
        }

        function validateEmails() {
            if (processing) return;
            processing = true;

            let progressDiv = document.getElementById("progress");
            let failedCountDiv = document.getElementById("failed-count");
            let emailLogDiv = document.getElementById("email-log");

            progressDiv.innerHTML = "Starting validation...";
            failedCountDiv.innerHTML = "";
            emailLogDiv.innerHTML = "<strong>Processing emails...</strong>";

            function processNextEmail() {
                fetch("process_validation.php")
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === "done") {
                            progressDiv.innerHTML = "Validation Completed!";
                            processing = false;
                            return;
                        }

                        // Display each processed email
                        let emailEntry = document.createElement("p");
                        emailEntry.textContent = `Processed: ${data.email} - ${data.status}`;
                        emailLogDiv.appendChild(emailEntry);

                        // Scroll to the latest entry
                        emailLogDiv.scrollTop = emailLogDiv.scrollHeight;

                        // Update email count
                        updateEmailCount();

                        // Process next email
                        processNextEmail();
                    })
                    .catch(error => {
                        progressDiv.innerHTML = "Error occurred during validation!";
                        console.error("Validation Error:", error);
                        processing = false;
                    });
            }

            processNextEmail();
        }
    </script>
</head>
<body>
    <h2>Email Validator</h2>
    <p>Total Emails Pending Validation: <strong id="email-count"><?php echo htmlspecialchars($total_emails, ENT_QUOTES, 'UTF-8'); ?></strong></p>

    <button onclick="validateEmails()">Validate Emails</button>

    <p id="progress"></p>
    <p id="failed-count"></p>
    <div id="email-log"></div>
</body>
</html>
