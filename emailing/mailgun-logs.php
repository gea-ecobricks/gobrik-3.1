<?php
require_once '../earthenAuth_helper.php';
require_once '../auth/session_start.php';
require_once '../gobrikconn_env.php';
require_once 'earthen_helpers.php';

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

$action_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['unsubscribe_email'])) {
    $email = trim($_POST['unsubscribe_email']);

    if ($email !== '') {
        try {
            $unsubscribe_result = earthenUnsubscribe($email);

            if ($unsubscribe_result) {
                $action_message = "Successfully removed $email from Ghost.";
            } else {
                $action_message = "No Ghost member found for $email.";
            }
        } catch (Exception $e) {
            $action_message = 'Unable to process unsubscribe: ' . $e->getMessage();
        }
    }
}

$mailgun_events = [];
$show_failed_only = isset($_GET['failed']) && $_GET['failed'] === '1';

$events_query = "SELECT event_timestamp, recipient_email, COALESCE(event_type, 'unknown') AS event_type, COALESCE(severity, 'unknown') AS severity, COALESCE(NULLIF(error_message, ''), reason, '—') AS details FROM earthen_mailgun_events_tb";

if ($show_failed_only) {
    $events_query .= " WHERE COALESCE(event_type, '') = 'failed' OR COALESCE(severity, '') LIKE '%failure%'";
}

$events_query .= " ORDER BY event_timestamp DESC LIMIT 200";

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

        .header-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
        }

        .header-bar h1 {
            margin: 0;
        }

        .actions {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .count-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 12px;
            border-radius: 20px;
            background: #f0f4ff;
            color: #0d6efd;
            border: 1px solid #d0d7de;
            font-weight: 600;
        }

        .button {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 10px 14px;
            border-radius: 8px;
            border: 1px solid #d0d7de;
            background: #0d6efd;
            color: #fff;
            text-decoration: none;
            font-weight: 600;
            transition: background 0.15s ease, box-shadow 0.15s ease;
            box-shadow: 0 2px 4px rgba(13, 110, 253, 0.2);
        }

        .button.secondary {
            background: #fff;
            color: #0d6efd;
        }

        .button:hover {
            background: #0b5ed7;
            box-shadow: 0 4px 10px rgba(13, 110, 253, 0.25);
        }

        .button.secondary:hover {
            background: #e9f2ff;
        }

        table.dataTable thead th {
            background: #f0f0f0;
        }

        .danger-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 8px 12px;
            border-radius: 6px;
            border: 1px solid #d62c2c;
            background: #e55353;
            color: #fff;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            transition: background 0.15s ease, box-shadow 0.15s ease;
            width: 100%;
        }

        .danger-button:hover {
            background: #d62c2c;
            box-shadow: 0 3px 8px rgba(214, 44, 44, 0.3);
        }

        .process-cell {
            text-align: center;
            min-width: 120px;
        }

        .notice {
            padding: 10px 14px;
            border-radius: 8px;
            background: #f0f9ff;
            border: 1px solid #9ec5fe;
            color: #0c63e4;
            margin-top: 14px;
            margin-bottom: 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header-bar">
            <h1>Mailgun Logs</h1>
            <span class="count-badge">
                <?php echo count($mailgun_events); ?> events retrieved
            </span>
            <div class="actions">
                <?php if ($show_failed_only): ?>
                    <a class="button secondary" href="mailgun-logs.php">View All Events</a>
                <?php else: ?>
                    <a class="button" href="mailgun-logs.php?failed=1">View Failed Events</a>
                <?php endif; ?>
            </div>
        </div>
        <p>
            <?php if ($show_failed_only): ?>
                Showing failed Mailgun events for the Earthen sender (most recent 200 entries).
            <?php else: ?>
                Latest Mailgun events for the Earthen sender (most recent 200 entries).
            <?php endif; ?>
        </p>
        <?php if ($action_message): ?>
            <p class="notice"><?php echo htmlspecialchars($action_message); ?></p>
        <?php endif; ?>
        <table id="mailgun-log-table" class="display" style="width:100%">
            <thead>
                <tr>
                    <th>Timestamp</th>
                    <th>Recipient</th>
                    <th>Event</th>
                    <th>Severity</th>
                    <th>Details</th>
                    <th>Process</th>
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
                        <td class="process-cell">
                            <?php
                                $event_type   = strtolower($event['event_type'] ?? '');
                                $event_sev    = strtolower($event['severity'] ?? '');
                                $is_failure   = ($event_type === 'failed') || (strpos($event_sev, 'failure') !== false);
                                $recipient    = $event['recipient_email'] ?? '';
                            ?>
                            <?php if ($is_failure && $recipient): ?>
                                <form method="POST" onsubmit="return confirm('Remove <?php echo htmlspecialchars($recipient); ?> from Ghost?');">
                                    <input type="hidden" name="unsubscribe_email" value="<?php echo htmlspecialchars($recipient); ?>">
                                    <button type="submit" class="danger-button">Remove</button>
                                </form>
                            <?php else: ?>
                                —
                            <?php endif; ?>
                        </td>
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
