<?php
require_once '../scripts/earthen_subscribe_functions.php';
require_once '../buwanaconn_env.php'; // Use the Buwana database connection

define('BATCH_SIZE', 50); // Limit number of emails processed at a time

// Fetch failed email records for the chart
function getFailedEmails($buwana_conn) {
    $sql_fetch = "SELECT id, email_addr FROM failed_emails_tb ORDER BY created_at ASC LIMIT ?";
    $stmt_fetch = $buwana_conn->prepare($sql_fetch);

    $batch_size = BATCH_SIZE;
    $stmt_fetch->bind_param('i', $batch_size);
    $stmt_fetch->execute();
    $result = $stmt_fetch->get_result();

    $emails = [];
    while ($row = $result->fetch_assoc()) {
        $emails[] = $row;
    }
    $stmt_fetch->close();

    return $emails;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['process'])) {
    $emails_to_process = getFailedEmails($buwana_conn);
    $response = [];

    foreach ($emails_to_process as $email_data) {
        $id = $email_data['id'];
        $email_addr = $email_data['email_addr'];

        // Attempt to unsubscribe
        $success = earthenUnsubscribe($email_addr);

        if ($success) {
            // Remove successfully unsubscribed emails from the queue
            $sql_delete = "DELETE FROM failed_emails_tb WHERE id = ?";
            $stmt_delete = $buwana_conn->prepare($sql_delete);
            $stmt_delete->bind_param('i', $id);
            $stmt_delete->execute();
            $stmt_delete->close();

            $response[] = ["email" => $email_addr, "status" => "Success ✅"];
        } else {
            // If the email doesn't exist in Earthen Ghost, remove it from the failures table
            $response[] = ["email" => $email_addr, "status" => "No matching earthen account. Removed from failures table"];

            $sql_delete = "DELETE FROM failed_emails_tb WHERE id = ?";
            $stmt_delete = $buwana_conn->prepare($sql_delete);
            $stmt_delete->bind_param('i', $id);
            $stmt_delete->execute();
            $stmt_delete->close();
        }
    }

    // Return JSON response
    echo json_encode($response);
    exit();
}

$failedEmails = getFailedEmails($buwana_conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Process Failed Email Unsubscribes</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; padding: 20px; }
        button { padding: 10px 15px; font-size: 16px; cursor: pointer; background-color: #28a745; color: white; border: none; border-radius: 5px; }
        button:hover { background-color: #218838; }
        canvas { max-width: 600px; }
    </style>
</head>
<body>

    <h1>Manual Email Unsubscribe Processing</h1>
    <p>Click the button below to process the next <strong><?php echo BATCH_SIZE; ?></strong> failed email unsubscribes.</p>
    <button id="processButton">Process Failed Emails</button>

    <h2>Emails Ready for Deletion</h2>
    <canvas id="emailChart"></canvas>

    <h2>Processing Results</h2>
    <ul id="results"></ul>

    <script>
        let failedEmails = <?php echo json_encode($failedEmails); ?>;
        let ctx = document.getElementById('emailChart').getContext('2d');

        // Function to update chart
        function updateChart() {
            let labels = failedEmails.map(e => e.email_addr);
            let data = Array(labels.length).fill(1); // Just a placeholder for bar height

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Failed Emails',
                        data: data,
                        backgroundColor: 'rgba(255, 99, 132, 0.6)'
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: { beginAtZero: true, ticks: { display: false } }
                    }
                }
            });
        }

        updateChart();

        document.getElementById('processButton').addEventListener('click', function() {
            fetch('', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'process=true'
            })
            .then(response => response.json())
            .then(data => {
                let resultsList = document.getElementById('results');
                resultsList.innerHTML = ''; // Clear old results
                failedEmails = failedEmails.filter(e => !data.some(d => d.email === e.email_addr)); // Remove processed emails

                data.forEach(item => {
                    let li = document.createElement('li');
                    li.textContent = `${item.email}: ${item.status}`;
                    li.style.color = item.status.includes('Success') ? 'green' : 'red';
                    resultsList.appendChild(li);
                });

                updateChart(); // Refresh the chart with new data
            })
            .catch(error => console.error('Error:', error));
        });
    </script>

</body>
</html>
