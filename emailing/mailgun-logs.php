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

function respondJson(array $data): void
{
    header('Content-Type: application/json');
    echo json_encode($data);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'remove_event') {
        $event_id = isset($_POST['event_id']) ? (int) $_POST['event_id'] : 0;
        $email = trim($_POST['recipient_email'] ?? '');

        if ($event_id <= 0 || $email === '') {
            respondJson(['success' => false, 'message' => 'Missing event details.']);
        }

        try {
            earthenUnsubscribe($email);
        } catch (Exception $e) {
            // Log unsubscribe issues but continue with deletion
            error_log('[EARTHEN] Remove event unsubscribe failed: ' . $e->getMessage());
        }

        $delete_stmt = $gobrik_conn->prepare('DELETE FROM earthen_mailgun_events_tb WHERE event_id = ?');

        if (!$delete_stmt) {
            respondJson(['success' => false, 'message' => 'Unable to prepare deletion.']);
        }

        $delete_stmt->bind_param('i', $event_id);
        $delete_stmt->execute();
        $deleted_rows = $delete_stmt->affected_rows;
        $delete_stmt->close();

        if ($deleted_rows > 0) {
            respondJson(['success' => true, 'message' => 'Record removed.']);
        }

        respondJson(['success' => false, 'message' => 'No record deleted.']);
    }

    if ($action === 'ignore_event') {
        $event_id = isset($_POST['event_id']) ? (int) $_POST['event_id'] : 0;

        if ($event_id <= 0) {
            respondJson(['success' => false, 'message' => 'Missing event id.']);
        }

        $update_stmt = $gobrik_conn->prepare('UPDATE earthen_mailgun_events_tb SET event_type = "ignored", severity = "ignored" WHERE event_id = ?');

        if (!$update_stmt) {
            respondJson(['success' => false, 'message' => 'Unable to prepare ignore update.']);
        }

        $update_stmt->bind_param('i', $event_id);
        $update_stmt->execute();
        $updated_rows = $update_stmt->affected_rows;
        $update_stmt->close();

        if ($updated_rows > 0) {
            respondJson(['success' => true, 'message' => 'Event ignored.']);
        }

        respondJson(['success' => false, 'message' => 'No changes made.']);
    }

    respondJson(['success' => false, 'message' => 'Unknown action.']);
}

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

$events_query = "SELECT event_id, event_timestamp, recipient_email, COALESCE(event_type, 'unknown') AS event_type, COALESCE(severity, 'unknown') AS severity, COALESCE(NULLIF(error_message, ''), reason, '—') AS details FROM earthen_mailgun_events_tb";

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

$mailgun_status_counts = [];
$mailgun_total_events = 0;
$status_query = "SELECT COALESCE(event_type, 'unknown') AS status, COUNT(*) AS count FROM earthen_mailgun_events_tb WHERE LOWER(COALESCE(event_type, '')) <> 'accepted' GROUP BY status ORDER BY status";
$status_result = $gobrik_conn->query($status_query);

if ($status_result) {
    while ($row = $status_result->fetch_assoc()) {
        $status = $row['status'] ?: 'unknown';
        $count = (int) ($row['count'] ?? 0);
        $mailgun_status_counts[$status] = $count;
        $mailgun_total_events += $count;
    }
    $status_result->free();
}

if (empty($mailgun_status_counts)) {
    $mailgun_status_counts = ['no data' => 0];
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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

        .overview-bar {
            display: flex;
            align-items: center;
            gap: 20px;
            flex-wrap: wrap;
            margin-bottom: 10px;
        }

        .chart-card {
            position: relative;
            width: 220px;
            height: 220px;
            background: #f8f9ff;
            border: 1px solid #d0d7de;
            border-radius: 14px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 10px;
        }

        .chart-card canvas {
            width: 100% !important;
            height: 100% !important;
        }

        .chart-center-label {
            position: absolute;
            text-align: center;
            font-weight: 700;
            color: #0d6efd;
            pointer-events: none;
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

        .header-controls {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
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

        .button.danger {
            background: #e55353;
            border-color: #d62c2c;
            box-shadow: 0 2px 4px rgba(214, 44, 44, 0.2);
        }

        .button.danger:hover {
            background: #d62c2c;
            box-shadow: 0 4px 10px rgba(214, 44, 44, 0.25);
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
            width: auto;
        }

        .danger-button:hover {
            background: #d62c2c;
            box-shadow: 0 3px 8px rgba(214, 44, 44, 0.3);
        }

        .process-cell {
            text-align: center;
            min-width: 120px;
        }

        .process-actions {
            display: inline-flex;
            gap: 8px;
            align-items: center;
            justify-content: center;
            flex-wrap: wrap;
        }

        .skip-button {
            padding: 8px 12px;
            border-radius: 6px;
            border: 1px solid #d0d7de;
            background: #fff;
            color: #0d6efd;
            font-weight: 700;
            cursor: pointer;
            transition: background 0.15s ease, box-shadow 0.15s ease;
        }

        .skip-button:hover {
            background: #e9f2ff;
            box-shadow: 0 3px 8px rgba(13, 110, 253, 0.2);
        }

        .skip-button.loading {
            opacity: 0.8;
            pointer-events: none;
        }

        .details-cell {
            max-width: 300px;
            white-space: normal;
            word-break: break-word;
            overflow-wrap: anywhere;
        }

        table.dataTable tbody td.details-cell {
            white-space: normal;
        }

        .danger-button .spinner {
            display: none;
            width: 16px;
            height: 16px;
            border: 2px solid rgba(255, 255, 255, 0.6);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        .danger-button.loading {
            opacity: 0.9;
            pointer-events: none;
        }

        .danger-button.loading .spinner {
            display: inline-block;
        }

        .danger-button.loading .button-label {
            display: none;
        }

        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
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

        .purge-status {
            display: none;
            align-items: center;
            gap: 10px;
            padding: 10px 14px;
            margin-top: 8px;
            border-radius: 8px;
            background: #f0f4ff;
            border: 1px solid #d0d7de;
        }

        .purge-status.visible {
            display: inline-flex;
        }

        .status-spinner {
            width: 16px;
            height: 16px;
            border: 2px solid rgba(13, 110, 253, 0.3);
            border-top-color: #0d6efd;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        .status-text {
            font-weight: 600;
            color: #0d6efd;
        }

        .status-text.success {
            color: #2d9f49;
        }

        .status-text.error {
            color: #d62c2c;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="overview-bar">
            <div class="chart-card" aria-label="Mailgun event breakdown" role="img">
                <canvas id="mailgun-status-chart"></canvas>
                <div class="chart-center-label" id="mailgun-status-total"><?php echo number_format((int) $mailgun_total_events); ?><br>events</div>
            </div>
            <div style="flex:1 1 320px;">
                <div class="header-bar">
                    <h1>Mailgun Logs</h1>
                    <div class="header-controls">
                        <span class="count-badge">
                            <?php echo count($mailgun_events); ?> events retrieved
                        </span>
                        <div class="actions">
                            <?php if ($show_failed_only): ?>
                                <button type="button" class="button danger" id="start-purge">Start purge</button>
                                <a class="button secondary" href="mailgun-logs.php">View All Events</a>
                            <?php else: ?>
                                <a class="button" href="mailgun-logs.php?failed=1">View Failed Events</a>
                            <?php endif; ?>
                        </div>
                        <?php if ($show_failed_only): ?>
                            <div class="purge-status" id="purge-status" aria-live="polite">
                                <span class="status-spinner" aria-hidden="true"></span>
                                <span class="status-text" id="purge-status-text">Waiting to start purge…</span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
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
                    <?php
                        $event_type   = strtolower($event['event_type'] ?? '');
                        $event_sev    = strtolower($event['severity'] ?? '');
                        $is_failure   = ($event_type === 'failed') || (strpos($event_sev, 'failure') !== false);
                        $recipient    = $event['recipient_email'] ?? '';
                        $event_id     = (int) ($event['event_id'] ?? 0);
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($event['event_timestamp'] ?? '—'); ?></td>
                        <td><?php echo htmlspecialchars($recipient ?: '—'); ?></td>
                        <td><?php echo htmlspecialchars($event['event_type'] ?? '—'); ?></td>
                        <td><?php echo htmlspecialchars($event['severity'] ?? '—'); ?></td>
                        <td class="details-cell"><?php echo htmlspecialchars($event['details'] ?? '—'); ?></td>
                        <td class="process-cell">
                            <?php if ($is_failure && $recipient && $event_id): ?>
                                <div class="process-actions">
                                    <button type="button" class="skip-button" data-event-id="<?php echo $event_id; ?>" data-recipient="<?php echo htmlspecialchars($recipient, ENT_QUOTES); ?>">
                                        <span class="button-label">Skip</span>
                                    </button>
                                    <button type="button" class="danger-button remove-button" data-event-id="<?php echo $event_id; ?>" data-recipient="<?php echo htmlspecialchars($recipient, ENT_QUOTES); ?>">
                                        <span class="button-label">Remove</span>
                                        <span class="spinner" aria-hidden="true"></span>
                                    </button>
                                </div>
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
            const mailgunStatusLabels = <?php echo json_encode(array_keys($mailgun_status_counts)); ?>;
            const mailgunStatusCounts = <?php echo json_encode(array_values($mailgun_status_counts)); ?>;
            const mailgunTotalEvents = <?php echo json_encode((int) $mailgun_total_events); ?>;

            const chartCanvas = document.getElementById('mailgun-status-chart');
            const chartLabel = document.getElementById('mailgun-status-total');

            if (chartCanvas && typeof Chart !== 'undefined') {
                const palette = ['#0d6efd', '#4caf50', '#ff9800', '#9c27b0', '#f44336', '#03a9f4', '#8bc34a', '#ffeb3b'];
                const backgroundColors = mailgunStatusLabels.map((_, idx) => palette[idx % palette.length]);

                new Chart(chartCanvas.getContext('2d'), {
                    type: 'doughnut',
                    data: {
                        labels: mailgunStatusLabels,
                        datasets: [{
                            data: mailgunStatusCounts,
                            backgroundColor: backgroundColors,
                            borderColor: 'transparent',
                            borderWidth: 0
                        }]
                    },
                    options: {
                        plugins: {
                            legend: { position: 'bottom' }
                        }
                    }
                });

                if (chartLabel) {
                    chartLabel.textContent = `${mailgunTotalEvents.toLocaleString()}\nevents`;
                }
            }

            const table = $('#mailgun-log-table').DataTable({
                order: [[0, 'desc']],
                pageLength: 25,
                columnDefs: [
                    { targets: 4, width: '300px' }
                ]
            });

            function handleAction(button, action) {
                const $button = $(button);
                const eventId = $button.data('event-id');
                const recipient = $button.data('recipient');
                const $row = $button.closest('tr');

                if (!eventId) {
                    alert('Missing event details.');
                    return;
                }

                $button.addClass('loading');
                const labelText = action === 'remove_event' ? 'Removing...' : 'Skipping...';
                const $label = $button.find('.button-label');
                if ($label.length) {
                    $label.data('original-text', $label.text());
                    $label.text(labelText);
                }

                $.ajax({
                    method: 'POST',
                    url: 'mailgun-logs.php',
                    data: {
                        action: action,
                        event_id: eventId,
                        recipient_email: recipient
                    },
                    dataType: 'json'
                }).done(function(response) {
                    if (response.success) {
                        table.row($row).remove().draw();
                    } else if (response.message) {
                        alert(response.message);
                    }
                }).fail(function() {
                    alert('Action could not be completed.');
                }).always(function() {
                    $button.removeClass('loading');
                    if ($label && $label.length) {
                        const originalText = $label.data('original-text') || (action === 'remove_event' ? 'Remove' : 'Skip');
                        $label.text(originalText);
                    }
                });
            }

            $(document).on('click', '.remove-button', function() {
                handleAction(this, 'remove_event');
            });

            $(document).on('click', '.skip-button', function() {
                handleAction(this, 'ignore_event');
            });

            const $purgeStatus = $('#purge-status');
            const $purgeStatusText = $('#purge-status-text');
            const $startPurgeButton = $('#start-purge');

            function updatePurgeStatus(message, state = 'info') {
                if (!$purgeStatus.length || !$purgeStatusText.length) return;

                $purgeStatus.addClass('visible');
                $purgeStatusText.removeClass('success error');

                if (state === 'success') {
                    $purgeStatusText.addClass('success');
                } else if (state === 'error') {
                    $purgeStatusText.addClass('error');
                }

                $purgeStatusText.text(message);
            }

            function processPurgeQueue(queue, index, removedCount, failureCount) {
                if (index >= queue.length) {
                    const summaryParts = [];
                    if (removedCount > 0) {
                        summaryParts.push(`${removedCount} event${removedCount === 1 ? '' : 's'} removed`);
                    }
                    if (failureCount > 0) {
                        summaryParts.push(`${failureCount} event${failureCount === 1 ? '' : 's'} failed`);
                    }

                    const summaryMessage = summaryParts.length ? summaryParts.join(', ') : 'No events processed.';

                    updatePurgeStatus(`Purge completed: ${summaryMessage}.`, failureCount > 0 ? 'error' : 'success');
                    $startPurgeButton.removeClass('loading').prop('disabled', false);
                    return;
                }

                const $row = $(queue[index]);
                const $button = $row.find('.remove-button');

                if (!$button.length) {
                    processPurgeQueue(queue, index + 1, removedCount, failureCount);
                    return;
                }

                const eventId = $button.data('event-id');
                const recipient = $button.data('recipient');

                if (!eventId || !recipient) {
                    processPurgeQueue(queue, index + 1, removedCount, failureCount + 1);
                    return;
                }

                updatePurgeStatus(`Removing ${recipient}…`, 'info');
                $button.addClass('loading');

                $.ajax({
                    method: 'POST',
                    url: 'mailgun-logs.php',
                    data: {
                        action: 'remove_event',
                        event_id: eventId,
                        recipient_email: recipient
                    },
                    dataType: 'json'
                }).done(function(response) {
                    if (response.success) {
                        table.row($row).remove().draw();
                        updatePurgeStatus(`Removed ${recipient}.`, 'success');
                        removedCount += 1;
                    } else {
                        updatePurgeStatus(response.message || `Unable to remove ${recipient}.`, 'error');
                        failureCount += 1;
                    }
                }).fail(function() {
                    updatePurgeStatus(`Request failed for ${recipient}.`, 'error');
                    failureCount += 1;
                }).always(function() {
                    $button.removeClass('loading');
                    setTimeout(function() {
                        processPurgeQueue(queue, index + 1, removedCount, failureCount);
                    }, 1000);
                });
            }

            $startPurgeButton.on('click', function() {
                if (!$startPurgeButton.length || $startPurgeButton.prop('disabled')) return;

                const queue = table.rows({ order: 'applied' }).nodes().toArray();
                const processableQueue = queue.filter(function(node) {
                    return $(node).find('.remove-button').length > 0;
                });

                if (!processableQueue.length) {
                    updatePurgeStatus('No failed events available to purge.', 'info');
                    return;
                }

                $startPurgeButton.addClass('loading').prop('disabled', true);
                updatePurgeStatus('Starting purge…', 'info');
                processPurgeQueue(processableQueue, 0, 0, 0);
            });
        });
    </script>
</body>
</html>
