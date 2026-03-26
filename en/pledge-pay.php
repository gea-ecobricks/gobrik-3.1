<?php
require_once '../auth/session_start.php';
require_once '../earthenAuth_helper.php';

// Set page variables
$lang = basename(dirname($_SERVER['SCRIPT_NAME']));
$version = '0.1';
$page = 'pledge-pay';
$lastModified = date("Y-m-d\TH:i:s\Z", filemtime(__FILE__));

// Must be logged in — session_start.php handles redirect if not
$buwana_id = $_SESSION['buwana_id'];

// Get training ID from the URL
$training_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($training_id <= 0) {
    header('Location: courses.php');
    exit();
}

require_once '../gobrikconn_env.php';
require_once '../buwanaconn_env.php';

// Fetch user info
$first_name = getFirstName($buwana_conn, $buwana_id);

// 🌎 Fetch user meta from Buwana database
$user_continent_icon = getUserContinent($buwana_conn, $buwana_id);
$earthling_emoji = getUserEarthlingEmoji($buwana_conn, $buwana_id);
$user_location_watershed = getWatershedName($buwana_conn, $buwana_id);
$user_location_full = getUserFullLocation($buwana_conn, $buwana_id);
$gea_status = getGEA_status($buwana_id);
$user_roles = getUser_Role($buwana_id);
$user_community_name = getCommunityName($buwana_conn, $buwana_id);

// 👤 Look up user's GoBrik account info
$sql_lookup_user = "SELECT first_name, ecobricks_made, ecobricker_id, location_full_txt, user_capabilities, full_name FROM tb_ecobrickers WHERE buwana_id = ?";
$stmt_lookup_user = $gobrik_conn->prepare($sql_lookup_user);
if ($stmt_lookup_user) {
    $stmt_lookup_user->bind_param("i", $buwana_id);
    $stmt_lookup_user->execute();
    $stmt_lookup_user->bind_result($first_name, $ecobricks_made, $ecobricker_id, $location_full_txt, $user_capabilities_raw, $full_name);
    $stmt_lookup_user->fetch();
    $stmt_lookup_user->close();
} else {
    die("Error preparing statement for tb_ecobrickers: " . $gobrik_conn->error);
}


$ecobricker_id = null;
$users_email_address = '';
$stmt = $gobrik_conn->prepare("SELECT ecobricker_id, email_addr FROM tb_ecobrickers WHERE buwana_id = ?");
$stmt->bind_param("i", $buwana_id);
$stmt->execute();
$stmt->bind_result($ecobricker_id, $users_email_address);
$stmt->fetch();
$stmt->close();

// Initialize training variables
$training_title = $training_subtitle = $training_date = $lead_trainer = '';
$training_type = $training_location = $training_time_txt = '';
$payment_mode = 'free';
$base_currency = 'IDR';
$default_price_idr = 0;
$funding_goal_idr = 0;
$min_participants_required = 0;
$pledge_deadline = '';
$payment_deadline = '';
$threshold_status = 'open';
$feature_photo1_main = '';
$ready_to_show = 0;

// Fetch training details
$sql = "SELECT * FROM tb_trainings WHERE training_id = ?";
$stmt = $gobrik_conn->prepare($sql);
$stmt->bind_param("i", $training_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $training_title    = htmlspecialchars($row['training_title']    ?? '', ENT_QUOTES, 'UTF-8');
    $training_subtitle = htmlspecialchars($row['training_subtitle'] ?? '', ENT_QUOTES, 'UTF-8');
    $training_date     = htmlspecialchars($row['training_date']     ?? '', ENT_QUOTES, 'UTF-8');
    $training_time_txt = htmlspecialchars($row['training_time_txt'] ?? '', ENT_QUOTES, 'UTF-8');
    $lead_trainer      = htmlspecialchars($row['lead_trainer']      ?? '', ENT_QUOTES, 'UTF-8');
    $training_type     = htmlspecialchars($row['training_type']     ?? '', ENT_QUOTES, 'UTF-8');
    $training_location = htmlspecialchars($row['training_location'] ?? '', ENT_QUOTES, 'UTF-8');

    $payment_mode            = htmlspecialchars($row['payment_mode']            ?? 'free', ENT_QUOTES, 'UTF-8');
    $base_currency           = htmlspecialchars($row['base_currency']           ?? 'IDR',  ENT_QUOTES, 'UTF-8');
    $default_price_idr       = intval($row['default_price_idr']       ?? 0);
    $funding_goal_idr        = intval($row['funding_goal_idr']        ?? 0);
    $min_participants_required = intval($row['min_participants_required'] ?? 0);
    $pledge_deadline         = htmlspecialchars($row['pledge_deadline']         ?? '', ENT_QUOTES, 'UTF-8');
    $payment_deadline        = htmlspecialchars($row['payment_deadline']        ?? '', ENT_QUOTES, 'UTF-8');
    $threshold_status        = htmlspecialchars($row['threshold_status']        ?? 'open', ENT_QUOTES, 'UTF-8');
    $feature_photo1_main     = htmlspecialchars($row['feature_photo1_main']     ?? '', ENT_QUOTES, 'UTF-8');
    $ready_to_show           = intval($row['ready_to_show'] ?? 0);
} else {
    header('Location: courses.php');
    exit();
}
$stmt->close();

if ($ready_to_show === 0 || $payment_mode !== 'pledge_threshold') {
    header("Location: register.php?id=$training_id");
    exit();
}

// Fetch this user's pledge record for this training
$pledge_id       = null;
$pledged_amount_idr  = 0;
$display_currency    = 'IDR';
$display_amount      = 0;
$pledge_status       = null;
$invited_to_pay_at   = null;
$payment_due_at      = null;

$sql_pledge = "SELECT pledge_id, pledged_amount_idr, display_currency, display_amount,
                      pledge_status, invited_to_pay_at, payment_due_at
               FROM training_pledges_tb
               WHERE training_id = ? AND buwana_id = ?
                 AND pledge_status NOT IN ('cancelled', 'expired', 'failed')
               ORDER BY created_at DESC
               LIMIT 1";
$stmt_pledge = $gobrik_conn->prepare($sql_pledge);
$stmt_pledge->bind_param("ii", $training_id, $buwana_id);
$stmt_pledge->execute();
$stmt_pledge->bind_result($pledge_id, $pledged_amount_idr, $display_currency, $display_amount,
                           $pledge_status, $invited_to_pay_at, $payment_due_at);
$stmt_pledge->fetch();
$stmt_pledge->close();

if (!$pledge_id) {
    // No active pledge — send back to register page
    header("Location: register.php?id=$training_id");
    exit();
}

// Fetch live progress stats for the 3P graph
$total_registrations_count = 0;
$total_pledges_count       = 0;
$total_amount_pledged      = 0;

$count_stmt = $gobrik_conn->prepare("
    SELECT
        (SELECT COUNT(*) FROM training_registrations_tb WHERE training_id = ?) AS total_regs,
        (SELECT COUNT(*) FROM training_pledges_tb WHERE training_id = ? AND pledge_status IN ('active','invited','paid')) AS total_pledges,
        (SELECT COALESCE(SUM(pledged_amount_idr),0) FROM training_pledges_tb WHERE training_id = ? AND pledge_status IN ('active','invited','paid')) AS total_amount
");
if ($count_stmt) {
    $count_stmt->bind_param("iii", $training_id, $training_id, $training_id);
    $count_stmt->execute();
    $count_stmt->bind_result($total_registrations_count, $total_pledges_count, $total_amount_pledged);
    $count_stmt->fetch();
    $count_stmt->close();
}

// Progress bar percentages (same logic as register.php)
$status_participants_current   = (int)$total_registrations_count;
$status_participants_threshold = ($min_participants_required > 0) ? $min_participants_required : 12;
$status_participants_max       = max(25, $status_participants_threshold);

$status_pledged_current   = (int)$total_amount_pledged;
$status_pledged_threshold = ($funding_goal_idr > 0) ? $funding_goal_idr : 700000;
$status_pledged_max       = max(1500000, $status_pledged_threshold);

$participants_fill_pct      = max(2, round(($status_participants_current / max(1, $status_participants_max)) * 100, 2));
$participants_threshold_pct = min(100, round(($status_participants_threshold / max(1, $status_participants_max)) * 100, 2));
$pledges_fill_pct           = max(2, round(($status_pledged_current / max(1, $status_pledged_max)) * 100, 2));
$pledges_threshold_pct      = min(100, round(($status_pledged_threshold / max(1, $status_pledged_max)) * 100, 2));

// Determine payment state
$now                  = time();
$payment_deadline_ts  = !empty($payment_deadline)  ? strtotime($payment_deadline)  : 0;
$pledge_deadline_ts   = !empty($pledge_deadline)    ? strtotime($pledge_deadline)   : 0;
$invited_to_pay_ts    = !empty($invited_to_pay_at)  ? strtotime($invited_to_pay_at) : 0;

// payment_state values: 'pending' | 'payment_due' | 'paid' | 'expired' | 'cancelled'
$payment_state = 'pending';

if ($pledge_status === 'paid') {
    $payment_state = 'paid';
} elseif ($threshold_status === 'cancelled') {
    $payment_state = 'cancelled';
} elseif ($pledge_status === 'invited') {
    if ($payment_deadline_ts > 0 && $now > $payment_deadline_ts) {
        $payment_state = 'expired';
    } else {
        $payment_state = 'payment_due';
    }
} elseif ($pledge_deadline_ts > 0 && $now > $pledge_deadline_ts && $threshold_status === 'open') {
    // Pledge deadline passed without threshold being reached
    $payment_state = 'expired';
}

// Format display strings
$pledge_deadline_display   = !empty($pledge_deadline)   ? date("F j, Y", strtotime($pledge_deadline))   : '';
$payment_deadline_display  = !empty($payment_deadline)  ? date("F j, Y", strtotime($payment_deadline))  : '';
$training_date_display     = !empty($training_date)     ? date("F j, Y", strtotime($training_date))      : $training_date;

// Format the pledge amount for display
if ($display_currency !== 'IDR' && $display_amount > 0) {
    $pledge_display_main = number_format((float)$display_amount, 2) . ' ' . htmlspecialchars($display_currency, ENT_QUOTES, 'UTF-8');
    $pledge_display_idr  = '(' . number_format((int)$pledged_amount_idr) . ' IDR)';
} else {
    $pledge_display_main = number_format((int)$pledged_amount_idr) . ' IDR';
    $pledge_display_idr  = '';
}

$gobrik_conn->close();

echo '<!DOCTYPE html>
<html lang="' . $lang . '">
<head>
<meta charset="UTF-8">
';
?>

<?php require_once("../includes/pledge-pay-inc.php"); ?>


<div id="form-submission-box" class="pledge-pay-page-shell">
    <div class="form-container pledge-pay-container">
        <div class="pledge-pay-content-wrap">

            <!-- Training header card -->
            <div class="pledge-pay-header-card">
                <?php if (!empty($feature_photo1_main)): ?>
                    <img src="<?php echo $feature_photo1_main; ?>" class="pledge-pay-lead-photo" alt="<?php echo $training_title; ?>">
                <?php endif; ?>
                <div class="pledge-pay-training-info">
                    <h3><?php echo $training_title; ?></h3>
                    <?php if (!empty($training_subtitle)): ?>
                        <p><?php echo $training_subtitle; ?></p>
                    <?php endif; ?>
                    <p><?php echo $training_date_display; ?><?php if (!empty($training_time_txt)): ?> | <?php echo $training_time_txt; ?><?php endif; ?></p>
                    <p><?php echo $training_type; ?><?php if (!empty($lead_trainer)): ?> · Led by <?php echo $lead_trainer; ?><?php endif; ?></p>
                    <?php if (!empty($training_location)): ?>
                        <p><?php echo $training_location; ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Your Pledge summary -->
            <div class="pledge-pay-pledge-card">
                <div>
                    <div class="pledge-pay-pledge-label">Your Pledge</div>
                    <span class="pledge-pay-amount-main"><?php echo $pledge_display_main; ?></span>
                    <?php if (!empty($pledge_display_idr)): ?>
                        <span class="pledge-pay-amount-idr"><?php echo $pledge_display_idr; ?></span>
                    <?php endif; ?>
                </div>
                <div class="pledge-pay-pledge-meta">
                    Pledge status:
                    <span class="pledge-status-inline-pill pledge-status-<?php echo htmlspecialchars($pledge_status, ENT_QUOTES, 'UTF-8'); ?>">
                        <?php echo htmlspecialchars(ucfirst($pledge_status), ENT_QUOTES, 'UTF-8'); ?>
                    </span>
                </div>
            </div>

            <!-- 3P progress graph -->
            <div class="pledge-pay-graph-section">
                <?php
                $graph_title = 'Course Progress';
                require_once '../includes/3P-graph.php';
                ?>
            </div>

            <!-- ============================================================ -->
            <!-- Conditional payment state section                             -->
            <!-- ============================================================ -->

            <?php if ($payment_state === 'pending'): ?>

                <div class="pledge-pay-state-card state-pending">
                    <div class="pledge-pay-state-badge badge-pending">⏳ Pending</div>
                    <h3>Your Pledge is Active</h3>
                    <p>
                        This course hasn't yet reached its go-ahead threshold.
                        Your pledge is counted and helping the course move forward —
                        but it's not yet time to pay.
                    </p>
                    <?php if (!empty($pledge_deadline_display)): ?>
                        <p>The pledge window closes on <strong><?php echo $pledge_deadline_display; ?></strong>. We'll email you at <strong><?php echo htmlspecialchars($users_email_address, ENT_QUOTES, 'UTF-8'); ?></strong> to let you know whether the course is going ahead and when payment is due.</p>
                    <?php endif; ?>
                    <a href="register.php?id=<?php echo $training_id; ?>" class="confirm-button pledge-pay-back-btn">↩ Back to Course</a>

                    <div class="pledge-pay-beta-row">
                        <label class="pledge-pay-beta-label">
                            <input type="checkbox" id="beta-pay-checkbox">
                            I'd like to go ahead and pay anyway (i.e. beta testing!)
                        </label>
                    </div>

                    <div id="beta-pay-buttons" class="pledge-pay-buttons" style="display:none;">
                        <div class="pledge-pay-button-group">
                            <a href="../api/create_midtrans_payment.php?pledge_id=<?php echo (int)$pledge_id; ?>"
                               class="confirm-button enabled pledge-pay-idn-btn">
                                🇮🇩 Indonesian Payment
                            </a>
                            <p class="pledge-pay-button-note">Indonesian local payments via Midtrans gateway</p>
                        </div>
                        <div class="pledge-pay-button-group">
                            <a href="../api/create_stripe_payment.php?pledge_id=<?php echo (int)$pledge_id; ?>"
                               class="confirm-button enabled pledge-pay-intl-btn">
                                🌍 International Payment
                            </a>
                            <p class="pledge-pay-button-note">International payments via Stripe gateway</p>
                        </div>
                    </div>
                </div>

            <?php elseif ($payment_state === 'payment_due'): ?>

                <div class="pledge-pay-state-card state-payment-due">
                    <div class="pledge-pay-state-badge badge-payment-due">💳 Payment Due</div>
                    <h3>This Course is Happening!</h3>
                    <p>
                        Great news — this course has reached its go-ahead thresholds.
                        Your pledge payment of <strong><?php echo $pledge_display_main; ?></strong>
                        is now due.
                    </p>
                    <?php if (!empty($payment_deadline_display)): ?>
                        <p>Please complete payment by <strong><?php echo $payment_deadline_display; ?></strong>. We'll send you the final course access details once your payment is confirmed.</p>
                    <?php else: ?>
                        <p>We'll send you the final course access details once your payment is confirmed.</p>
                    <?php endif; ?>

                    <div class="pledge-pay-buttons">

                        <div class="pledge-pay-button-group">
                            <a href="../api/create_midtrans_payment.php?pledge_id=<?php echo (int)$pledge_id; ?>"
                               class="confirm-button enabled pledge-pay-idn-btn">
                                🇮🇩 Indonesian Payment
                            </a>
                            <p class="pledge-pay-button-note">Indonesian local payments via Midtrans gateway</p>
                        </div>

                        <div class="pledge-pay-button-group">
                            <a href="../api/create_stripe_payment.php?pledge_id=<?php echo (int)$pledge_id; ?>"
                               class="confirm-button enabled pledge-pay-intl-btn">
                                🌍 International Payment
                            </a>
                            <p class="pledge-pay-button-note">International payments via Stripe gateway</p>
                        </div>

                    </div>
                </div>

            <?php elseif ($payment_state === 'paid'): ?>

                <div class="pledge-pay-state-card state-paid">
                    <div class="pledge-pay-state-badge badge-paid">✅ Payment Confirmed</div>
                    <h3>You're all set!</h3>
                    <p>
                        Your payment of <strong><?php echo $pledge_display_main; ?></strong> for
                        <strong><?php echo $training_title; ?></strong> has been received.
                        Check your email at <strong><?php echo htmlspecialchars($users_email_address, ENT_QUOTES, 'UTF-8'); ?></strong> for your final course access details and joining instructions.
                    </p>
                    <a href="dashboard.php" class="confirm-button enabled pledge-pay-back-btn">Go to Dashboard</a>
                </div>

            <?php else: /* expired or cancelled */ ?>

                <div class="pledge-pay-state-card state-cancelled">
                    <div class="pledge-pay-state-badge badge-cancelled">
                        <?php echo ($payment_state === 'expired') ? '⌛ Expired' : '❌ Cancelled'; ?>
                    </div>
                    <h3>
                        <?php echo ($payment_state === 'expired')
                            ? 'This Course Didn\'t Reach its Threshold'
                            : 'This Course Has Been Cancelled'; ?>
                    </h3>
                    <p>
                        <?php if ($payment_state === 'expired'): ?>
                            Unfortunately, this course did not reach its go-ahead threshold before the pledge deadline.
                            Your pledge has been voided and no payment has been collected.
                        <?php else: ?>
                            This course has been cancelled. Your pledge has been voided and no payment has been collected.
                        <?php endif; ?>
                    </p>
                    <a href="courses.php" class="confirm-button enabled pledge-pay-back-btn">Browse Other Courses</a>
                </div>

            <?php endif; ?>

        </div>
    </div>
</div>

</div>

<script>
const TRAINING_ID     = <?php echo (int)$training_id; ?>;
const PLEDGE_ID       = <?php echo (int)$pledge_id; ?>;
const ECOBRICKER_ID   = <?php echo json_encode($ecobricker_id, JSON_HEX_TAG) ?: 'null'; ?>;
const PAYMENT_STATE   = <?php echo json_encode($payment_state, JSON_HEX_TAG) ?: '""'; ?>;
const PLEDGE_STATUS   = <?php echo json_encode($pledge_status, JSON_HEX_TAG) ?: '""'; ?>;
const TRAINING_NAME   = <?php echo json_encode($training_title, JSON_HEX_TAG) ?: '""'; ?>;
const PLEDGED_AMOUNT_IDR  = <?php echo (int)$pledged_amount_idr; ?>;
const DISPLAY_CURRENCY    = <?php echo json_encode($display_currency, JSON_HEX_TAG) ?: '"IDR"'; ?>;
const DISPLAY_AMOUNT      = <?php echo json_encode($display_amount, JSON_HEX_TAG) ?: '0'; ?>;

const CURRENCY_RATES = {
    IDR: 1,
    USD: 0.000064,
    EUR: 0.000059,
    CAD: 0.000087,
    GBP: 0.000050,
    MYR: 0.00030
};

function formatCurrencyFromIdr(idrAmount, currency) {
    const safeIdr = Number(idrAmount || 0);
    if (currency === 'IDR') {
        return new Intl.NumberFormat('en-US', { maximumFractionDigits: 0 }).format(safeIdr) + ' IDR';
    }
    const converted = safeIdr * (CURRENCY_RATES[currency] || 1);
    return new Intl.NumberFormat('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 2 }).format(converted) + ' ' + currency;
}

function escapeHtml(str) {
    return String(str)
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}

document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.notice-close').forEach(btn => {
        btn.addEventListener('click', function() {
            const notice = this.closest('.top-container-notice');
            if (notice) notice.style.display = 'none';
        });
    });

    const betaCheckbox = document.getElementById('beta-pay-checkbox');
    const betaButtons = document.getElementById('beta-pay-buttons');
    if (betaCheckbox && betaButtons) {
        betaCheckbox.addEventListener('change', function() {
            betaButtons.style.display = this.checked ? 'flex' : 'none';
        });
    }
});
</script>

<?php require_once("../footer-2026.php"); ?>

</body>
</html>
