<?php
require_once '../scripts/earthen_subscribe_functions.php';
require_once '../buwanaconn_env.php'; // Use the Buwana database connection

define('BATCH_SIZE', 50);

// Fetch failed emails
function getFailedEmails($buwana_conn) {
    $sql_fetch = "SELECT id, email_addr, fail_reason FROM failed_emails_tb ORDER BY created_at ASC LIMIT ?";
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

// Handle AJAX request for processing emails
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['process'])) {
    $emails_to_process = getFailedEmails($buwana_conn);
    $response = [];

    foreach ($emails_to_process as $email_data) {
        $id = $email_data['id'];
        $email_addr = $email_data['email_addr'];

        // Attempt to unsubscribe in Earthen
        $success = earthenUnsubscribe($email_addr);

        if ($success) {
            $sql_delete = "DELETE FROM failed_emails_tb WHERE id = ?";
            $stmt_delete = $buwana_conn->prepare($sql_delete);
            $stmt_delete->bind_param('i', $id);
            $stmt_delete->execute();
            $stmt_delete->close();

            $response[] = ["id" => $id, "status" => "User found in Earthen. Unsubscribed.", "success" => true];
        } else {
            // Assume user not found and remove from failures table
            $sql_delete = "DELETE FROM failed_emails_tb WHERE id = ?";
            $stmt_delete = $buwana_conn->prepare($sql_delete);
            $stmt_delete->bind_param('i', $id);
            $stmt_delete->execute();
            $stmt_delete->close();

            $response[] = ["id" => $id, "status" => "User not found in Earthen. Removed from failed list.", "success" => false];
        }
    }

    echo json_encode($response);
    exit();
}

// Handle AJAX request for loading the next batch
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['load_next'])) {
    echo json_encode(getFailedEmails($buwana_conn));
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
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; padding: 20px; }
        button { padding: 10px 15px; font-size: 16px; cursor: pointer; background-color: #28a745; color: white; border: none; border-radius: 5px; margin: 10px 0; }
        button:hover { background-color: #218838; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f4f4f4; }
        .success { color: green; }
        .error { color: red; }
        .hidden { display: none; }
    </style>
</head>
<body>

    <h1>Manual Email Unsubscribe Processing</h1>
    <p>Click the button below to process the next <strong><?php echo BATCH_SIZE; ?></strong> failed email unsubscribes.</p>
    <button id="processButton">Process Failed Emails</button>

    <h2>Emails Ready for Deletion</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Email</th>
                <th>Failed Reason</th>
                <th>Deletion Report</th>
            </tr>
        </thead>
        <tbody id="emailTable">
            <?php foreach ($failedEmails as $email): ?>
                <tr id="row-<?php echo $email['id']; ?>">
                    <td><?php echo $email['id']; ?></td>
                    <td><?php echo $email['email_addr']; ?></td>
                    <td><?php echo $email['fail_reason']; ?></td>
                    <td class="deletion-status"></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <button id="loadNextButton" class="hidden">Load Next 50 Failed Emails</button>

    <script>
        document.getElementById('processButton').addEventListener('click', function() {
            fetch('', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'process=true'
            })
            .then(response => response.json())
            .then(data => {
                let allProcessed = true;

                data.forEach(item => {
                    let row = document.getElementById('row-' + item.id);
                    if (row) {
                        let statusCell = row.querySelector('.deletion-status');
                        statusCell.textContent = item.status;
                        statusCell.classList.add(item.success ? 'success' : 'error');

                        // Remove row if the email was deleted from failures
                        if (!item.success) {
                            setTimeout(() => row.remove(), 1000);
                        }
                    } else {
                        allProcessed = false;
                    }
                });

                // Show "Load Next 50 Failed Emails" button if all emails are processed
                if (allProcessed) {
                    document.getElementById('loadNextButton').classList.remove('hidden');
                }
            })
            .catch(error => console.error('Error:', error));
        });

        document.getElementById('loadNextButton').addEventListener('click', function() {
            fetch('', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'load_next=true'
            })
            .then(response => response.json())
            .then(data => {
                let tableBody = document.getElementById('emailTable');
                tableBody.innerHTML = ''; // Clear table

                if (data.length === 0) {
                    alert("No more failed emails to process.");
                } else {
                    data.forEach(email => {
                        let row = document.createElement('tr');
                        row.id = "row-" + email.id;

                        row.innerHTML = `
                            <td>${email.id}</td>
                            <td>${email.email_addr}</td>
                            <td>${email.fail_reason}</td>
                            <td class="deletion-status"></td>
                        `;
                        tableBody.appendChild(row);
                    });

                    document.getElementById('loadNextButton').classList.add('hidden');
                }
            })
            .catch(error => console.error('Error:', error));
        });
    </script>

</body>
</html>
