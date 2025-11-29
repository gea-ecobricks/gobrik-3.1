<?php
require_once '../earthenAuth_helper.php';
require_once '../auth/session_start.php';
require_once '../gobrikconn_env.php';

if (!$is_logged_in) {
    header('Location: ../login.php?redirect=emailing/mailgun-logs.php');
    exit();
}

$buwana_id = $_SESSION['buwana_id'] ?? 0;

$roles_query = "SELECT user_roles FROM tb_ecobrickers WHERE buwana_id = ?";
if ($stmt = $gobrik_conn->prepare($roles_query)) {
    $stmt->bind_param('i', $buwana_id);
    $stmt->execute();
    $stmt->bind_result($user_roles);
    if (!$stmt->fetch() || stripos($user_roles, 'admin') === false) {
        echo "<script>
            alert('Sorry, only admins can see this page.');
            window.location.href = '../en/dashboard.php';
        </script>";
        exit();
    }
    $stmt->close();
} else {
    echo "<script>
        alert('Unable to verify your account right now.');
        window.location.href = '../en/dashboard.php';
    </script>";
    exit();
}

$mailgun_events = [];
$events_query = "SELECT event_timestamp, recipient_email, COALESCE(event_type, 'unknown') AS event_type, COALESCE(severity, 'unknown') AS severity, COALESCE(NULLIF(error_message, ''), reason, '—') AS details FROM earthen_mailgun_events_tb ORDER BY event_timestamp DESC LIMIT 200";

if ($result = $gobrik_conn->query($events_query)) {
    while ($row = $result->fetch_assoc()) {
        $mailgun_events[] = $row;
    }
    $result->free();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mailgun Logs</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/normalize/8.0.1/normalize.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            color: #333;
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 1100px;
            margin: 0 auto;
            background: #fff;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        h1 {
            margin-top: 0;
        }

        table.dataTable thead th {
            background: #f0f0f0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Mailgun Logs</h1>
        <p>Latest Mailgun events for the Earthen sender (most recent 200 entries).</p>
        <table id="mailgun-log-table" class="display" style="width:100%">
            <thead>
                <tr>
                    <th>Timestamp</th>
                    <th>Recipient</th>
                    <th>Event</th>
                    <th>Severity</th>
                    <th>Details</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($mailgun_events as $event): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($event['event_timestamp'] ?? '—'); ?></td>
                        <td><?php echo htmlspecialchars($event['recipient_email'] ?? '—'); ?></td>
                        <td><?php echo htmlspecialchars($event['event_type'] ?? '—'); ?></td>
                        <td><?php echo htmlspecialchars($event['severity'] ?? '—'); ?></td>
                        <td><?php echo htmlspecialchars($event['details'] ?? '—'); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#mailgun-log-table').DataTable({
                order: [[0, 'desc']],
                pageLength: 25
            });
        });
    </script>
</body>
</html>
