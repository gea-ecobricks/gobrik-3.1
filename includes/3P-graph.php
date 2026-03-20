<?php
/*
 * 3P-graph.php — Reusable 3P threshold progress graph partial
 *
 * Include this file AFTER setting the following PHP variables:
 *
 *   $status_participants_current  — int, current registration count
 *   $status_participants_max      — int, display max for participant bar
 *   $status_participants_threshold — int, threshold line position
 *   $status_pledged_current       — int, current total pledged (IDR)
 *   $status_pledged_max           — int, display max for pledge bar (IDR)
 *   $status_pledged_threshold     — int, threshold line position (IDR)
 *   $participants_fill_pct        — float, fill % for participant bar
 *   $participants_threshold_pct   — float, threshold marker % for participant bar
 *   $pledges_fill_pct             — float, fill % for pledge bar
 *   $pledges_threshold_pct        — float, threshold marker % for pledge bar
 *
 * Optional:
 *   $graph_title  — string, panel heading (defaults to "Course Progress")
 *   $threshold_status — 'open' | 'reached' | 'cancelled'  (changes bar color)
 */

$graph_title = $graph_title ?? 'Course Progress';
$graph_threshold_status = $threshold_status ?? 'open';

// When threshold has been reached, show green bars instead of red
$bar_color_class = ($graph_threshold_status === 'reached') ? 'is-green' : 'is-red';
?>

<div class="register-status-panel">
    <div class="register-status-title"><?php echo htmlspecialchars($graph_title, ENT_QUOTES, 'UTF-8'); ?></div>

    <div class="register-progress-block">
        <div class="register-progress-label">
            Participant Threshold
            <span><?php echo (int)$status_participants_current; ?> / <?php echo (int)$status_participants_max; ?></span>
        </div>
        <div class="register-progress-bar">
            <div class="register-progress-zone-before" style="width: <?php echo $participants_threshold_pct; ?>%;"></div>
            <div class="register-progress-zone-after" style="left: <?php echo $participants_threshold_pct; ?>%; width: <?php echo max(0, 100 - $participants_threshold_pct); ?>%;"></div>
            <div class="register-progress-fill <?php echo $bar_color_class; ?>" style="width: <?php echo $participants_fill_pct; ?>%;"></div>
            <div class="register-progress-threshold" style="left: <?php echo $participants_threshold_pct; ?>%;"></div>
        </div>
        <div class="register-progress-meta">
            <strong>Current:</strong> <?php echo (int)$status_participants_current; ?> &nbsp;|&nbsp;
            <strong>Threshold:</strong> <?php echo (int)$status_participants_threshold; ?>
        </div>
    </div>

    <div class="register-progress-block">
        <div class="register-progress-label">
            Pledge Threshold
            <span><?php echo number_format((int)$status_pledged_current); ?> / <?php echo number_format((int)$status_pledged_max); ?> IDR</span>
        </div>
        <div class="register-progress-bar">
            <div class="register-progress-zone-before" style="width: <?php echo $pledges_threshold_pct; ?>%;"></div>
            <div class="register-progress-zone-after" style="left: <?php echo $pledges_threshold_pct; ?>%; width: <?php echo max(0, 100 - $pledges_threshold_pct); ?>%;"></div>
            <div class="register-progress-fill <?php echo $bar_color_class; ?>" style="width: <?php echo $pledges_fill_pct; ?>%;"></div>
            <div class="register-progress-threshold" style="left: <?php echo $pledges_threshold_pct; ?>%;"></div>
        </div>
        <div class="register-progress-meta">
            <strong>Current:</strong> <?php echo number_format((int)$status_pledged_current); ?> IDR &nbsp;|&nbsp;
            <strong>Threshold:</strong> <?php echo number_format((int)$status_pledged_threshold); ?> IDR
        </div>
    </div>
</div>
