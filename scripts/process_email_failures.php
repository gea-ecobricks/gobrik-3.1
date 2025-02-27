<?php
require_once '../scripts/earthen_subscribe_functions.php';
require_once '../buwanaconn_env.php'; // Use the Buwana database connection

define('BATCH_SIZE', 50);

// Fetch failed email records for the table
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

// Handle AJAX request
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

            $response[] = ["id" => $id, "status" => "Success ✅"];
        } else {
            // If email doesn't exist in Earthen Ghost, remove it from failures table
            $response[] = ["id" => $id, "status" => "No matching earthen account. Removed from failures table"];

            $sql_delete = "DELETE FROM failed_emails_tb WHERE id = ?";
            $stmt_delete = $buwana_conn->prepare($sql_delete);
            $stmt_delete->bind_param('i', $id);
            $stmt_delete->execute();
            $stmt_delete->close();
        }
    }

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
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; padding: 20px; }
        button { padding: 10px 15px; font-size: 16px; cursor: pointer; background-color: #28a745; color: white; border: none; border-radius: 5px; }
        button:hover { background-color: #218838; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f4f4f4; }
        .success { color: green; }
        .error { color: red; }
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

    <script>
        document.getElementById('processButton').addEventListener('click', function() {
            fetch('', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'process=true'
            })
            .then(response => response.json())
            .then(data => {
                data.forEach(item => {
                    let row = document.getElementById('row-' + item.id);
                    if (row) {
                        let statusCell = row.querySelector('.deletion-status');
                        statusCell.textContent = item.status;
                        statusCell.classList.add(item.status.includes('Success') ? 'success' : 'error');

                        // Remove the row if the email was deleted
                        if (item.status.includes("Removed from failures table")) {
                            setTimeout(() => row.remove(), 1000);
                        }
                    }
                });
            })
            .catch(error => console.error('Error:', error));
        });
    </script>

</body>
</html>
