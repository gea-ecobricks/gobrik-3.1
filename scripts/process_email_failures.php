<?php
require_once '../scripts/earthen_subscribe_functions.php';
require_once '../buwanaconn_env.php'; // Use the Buwana database connection

define('BATCH_SIZE', 50);

// Get total count of failed emails
function getFailedEmailsCount($buwana_conn) {
    $sql = "SELECT COUNT(*) AS total FROM failed_emails_tb";
    $result = $buwana_conn->query($sql);
    $row = $result->fetch_assoc();
    return $row['total'];
}

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

    echo json_encode(["processed" => $response, "remaining" => getFailedEmailsCount($buwana_conn)]);
    exit();
}

// Handle AJAX request for loading the next batch
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['load_next'])) {
    echo json_encode(["emails" => getFailedEmails($buwana_conn), "remaining" => getFailedEmailsCount($buwana_conn)]);
    exit();
}

$failedEmails = getFailedEmails($buwana_conn);
$totalFailedEmails = getFailedEmailsCount($buwana_conn);
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
        .processing { color: #ff9800; }
        .hidden { display: none; }
        .spinner {
            display: inline-block;
            width: 14px;
            height: 14px;
            border: 2px solid #ddd;
            border-top: 2px solid #ff9800;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-left: 5px;
        }
        @keyframes spin { 100% { transform: rotate(360deg); } }
    </style>
</head>
<body>

    <h1>Manual Email Unsubscribe Processing</h1>
    <p id="remainingCount">
        Click the button below to process the next <strong><?php echo BATCH_SIZE; ?></strong> failed email unsubscribes
        out of the remaining <strong><?php echo $totalFailedEmails; ?></strong> emails in the failed_emails table.
    </p>
    <button id="processButton">Process Failed Emails</button>

    <h2>Emails Ready for Deletion</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Email</th>
                <th>Fail Reason</th>
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
            let rows = document.querySelectorAll('#emailTable tr');

            rows.forEach(row => {
                let statusCell = row.querySelector('.deletion-status');
                statusCell.innerHTML = 'Processing... <span class="spinner"></span>';
            });

            fetch('', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'process=true'
            })
            .then(response => response.json())
            .then(data => {
                data.processed.forEach(item => {
                    let row = document.getElementById('row-' + item.id);
                    if (row) {
                        let statusCell = row.querySelector('.deletion-status');
                        statusCell.innerHTML = item.status;
                        statusCell.classList.add(item.success ? 'success' : 'error');

                        if (!item.success) {
                            setTimeout(() => row.remove(), 1000);
                        }
                    }
                });

                document.getElementById('remainingCount').innerHTML = `
                    Click the button below to process the next <strong><?php echo BATCH_SIZE; ?></strong> failed email unsubscribes
                    out of the remaining <strong>${data.remaining}</strong> emails in the failed_emails table.
                `;

                document.getElementById('loadNextButton').classList.remove('hidden');
            })
            .catch(error => console.error('Error:', error));
        });
    </script>

</body>
</html>
