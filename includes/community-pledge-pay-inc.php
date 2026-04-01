
<?php require_once ("../meta/community-pledge-pay-$lang.php");?>

<style>

/* ===================================================================== */
/* ======================== PLEDGE-PAY PAGE LAYOUT ====================== */
/* ===================================================================== */

.pledge-pay-page-shell {
    margin-top: 108px;
}

.pledge-pay-container {
    padding-top: 0;
    max-width: 760px;
}

.pledge-pay-content-wrap {
    width: 100%;
    margin: auto;
    margin-top: 5px;
}

/* ===================================================================== */
/* ========================== TRAINING HEADER CARD ====================== */
/* ===================================================================== */

.pledge-pay-header-card {
    width: 100%;
    background: var(--course-module);
    border-radius: 15px;
    overflow: hidden;
    margin-bottom: 20px;
}

.pledge-pay-lead-photo {
    width: 100%;
    display: block;
    border-radius: 10px 10px 0 0;
}

.pledge-pay-training-info {
    padding: 18px 20px 20px 20px;
}

.pledge-pay-training-info h3 {
    margin: 0 0 8px 0;
    font-size: 1.45em;
    line-height: 1.25;
}

.pledge-pay-training-info p {
    margin: 0 0 5px 0;
    font-size: 1em;
    opacity: 0.82;
}

/* ===================================================================== */
/* ========================== YOUR PLEDGE CARD ========================== */
/* ===================================================================== */

.pledge-pay-pledge-card {
    background: var(--course-module);
    border-radius: 14px;
    padding: 18px 20px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    flex-wrap: wrap;
}

.pledge-pay-pledge-label {
    font-size: 0.95em;
    font-weight: 600;
    opacity: 0.72;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-bottom: 6px;
}

.pledge-pay-amount-main {
    font-size: 2em;
    font-weight: 700;
    font-variant-numeric: tabular-nums;
    line-height: 1.1;
    display: block;
    color: var(--h1);
}

.pledge-pay-amount-idr {
    font-size: 1em;
    opacity: 0.65;
    font-variant-numeric: tabular-nums;
    margin-top: 4px;
    display: block;
}

.pledge-pay-pledge-meta {
    font-size: 0.9em;
    opacity: 0.85;
    margin-top: 6px;
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}

/* Inline pledge status pill (inside the pledge card) */
.pledge-status-inline-pill {
    display: inline-flex;
    align-items: center;
    padding: 2px 11px;
    border-radius: 999px;
    font-size: 0.88em;
    font-weight: 700;
    letter-spacing: 0.02em;
    color: #fff;
}

.pledge-status-active    { background: #1e8c40; }
.pledge-status-invited   { background: #d04b00; }
.pledge-status-paid      { background: #1e8c40; }
.pledge-status-cancelled { background: #666; }
.pledge-status-expired   { background: #888; }
.pledge-status-failed    { background: #c00; }

/* ===================================================================== */
/* ========================== 3P GRAPH SECTION ========================== */
/* ===================================================================== */

.pledge-pay-graph-section {
    margin-bottom: 20px;
}

.pledge-pay-graph-section .register-status-panel {
    margin-top: 0;
}

/* ===================================================================== */
/* ========================== PAYMENT STATE CARDS ======================= */
/* ===================================================================== */

/* Base card — uses theme-adaptive panel background */
.pledge-pay-state-card {
    background: var(--course-module);
    border: 1px solid rgba(0,0,0,0.08);
    border-top-width: 3px;
    border-radius: 14px;
    padding: 24px 22px;
    margin-bottom: 20px;
    text-align: center;
}

.pledge-pay-state-card h3 {
    margin: 14px 0 10px 0;
    font-size: 1.4em;
    color: var(--h1);
}

.pledge-pay-state-card p {
    font-size: 1.1em;
    line-height: 1.55;
    margin: 0 0 12px 0;
    max-width: 560px;
    margin-left: auto;
    margin-right: auto;
    color: var(--text-color);
}

/* Pending */
.state-pending {
    border-top-color: #3b73d4;
}

/* Payment Due */
.state-payment-due {
    border-top-color: #e02020;
}

/* Paid */
.state-paid {
    border-top-color: #1e8c40;
}

/* Expired / Cancelled */
.state-cancelled {
    border-top-color: #888;
}

/* ===================================================================== */
/* ========================== STATE BADGES ============================== */
/* ===================================================================== */

.pledge-pay-state-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 5px 16px;
    border-radius: 999px;
    font-size: 0.9em;
    font-weight: 700;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    margin-bottom: 4px;
}

.badge-pending {
    background: #3b73d4;
    color: #fff;
    border: none;
    box-shadow: 0 2px 8px rgba(60,110,210,0.22);
}

.badge-payment-due {
    background: #e02020;
    color: #fff;
    border: none;
    box-shadow: 0 2px 8px rgba(220,40,40,0.22);
}

.badge-paid {
    background: #1e8c40;
    color: #fff;
    border: none;
    box-shadow: 0 2px 8px rgba(30,140,64,0.22);
}

.badge-cancelled {
    background: #666;
    color: #fff;
    border: none;
}

/* ===================================================================== */
/* ========================== PAYMENT BUTTONS =========================== */
/* ===================================================================== */

.pledge-pay-buttons {
    display: flex;
    gap: 16px;
    justify-content: center;
    flex-wrap: wrap;
    margin-top: 22px;
}

.pledge-pay-button-group {
    display: flex;
    flex-direction: column;
    align-items: center;
    flex: 1;
    min-width: 220px;
    max-width: 300px;
}

.pledge-pay-idn-btn {
    background: #d04b00 !important;
    width: 100%;
}

.pledge-pay-idn-btn:hover {
    background: #b53e00 !important;
}

.pledge-pay-intl-btn {
    background: #1a56a0 !important;
    width: 100%;
}

.pledge-pay-intl-btn:hover {
    background: #144584 !important;
}

.pledge-pay-button-note {
    font-size: 0.82em;
    opacity: 0.65;
    margin-top: 7px !important;
    text-align: center;
    line-height: 1.3;
}

.pledge-pay-back-btn {
    margin-top: 16px;
    display: inline-block;
}

/* ===================================================================== */
/* ======================== PROGRESS BAR STYLES ========================= */
/* ===================================================================== */
/* (Shared with register.php — duplicated here so pledge-pay is self-contained) */

.register-status-panel {
    margin-top: 14px;
    width: 100%;
    max-width: 100%;
    background: rgba(255,255,255,0.16);
    border: 1px solid rgba(0,0,0,0.08);
    border-radius: 14px;
    padding: 14px;
    box-shadow: inset 0 1px 0 rgba(255,255,255,0.14);
    box-sizing: border-box;
    overflow: hidden;
}

.register-status-title {
    font-size: 1.04em;
    font-weight: 600;
    margin-bottom: 14px;
}

.register-progress-block {
    margin-bottom: 16px;
    width: 100%;
    min-width: 0;
}

.register-progress-block:last-child {
    margin-bottom: 0;
}

.register-progress-label {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    gap: 10px;
    font-size: 0.95em;
    font-weight: 600;
    margin-bottom: 8px;
    line-height: 1.3;
}

.register-progress-label span {
    font-weight: 500;
    opacity: 0.78;
    font-variant-numeric: tabular-nums;
    text-align: right;
}

.register-progress-bar {
    position: relative;
    width: 100%;
    max-width: 100%;
    height: 22px;
    border-radius: 999px;
    overflow: hidden;
    background: #e3e3e3;
    box-shadow:
        inset 0 1px 3px rgba(0,0,0,0.10),
        inset 0 -1px 0 rgba(255,255,255,0.16);
}

.register-progress-zone-before {
    position: absolute;
    top: 0;
    bottom: 0;
    left: 0;
    background: linear-gradient(180deg, #c6c6c6 0%, #b8b8b8 100%);
}

.register-progress-zone-after {
    position: absolute;
    top: 0;
    bottom: 0;
    background: linear-gradient(180deg, rgba(105,176,115,0.35) 0%, rgba(88,153,96,0.28) 100%);
}

.register-progress-fill {
    position: absolute;
    top: 0;
    bottom: 0;
    left: 0;
    min-width: 8px;
    border-radius: 999px;
    box-shadow:
        inset 0 1px 0 rgba(255,255,255,0.18),
        0 0 0 1px rgba(0,0,0,0.03);
}

.register-progress-fill.is-red {
    background: linear-gradient(90deg, #d65050 0%, #e46d6d 100%);
}

.register-progress-fill.is-green {
    background: linear-gradient(90deg, #2f9e50 0%, #44be6a 100%);
}

.register-progress-threshold {
    position: absolute;
    top: -2px;
    bottom: -2px;
    width: 0;
    border-left: 3px solid rgba(20,20,20,0.9);
    z-index: 4;
    box-shadow: 0 0 0 1px rgba(255,255,255,0.16);
}

.register-progress-meta {
    margin-top: 8px;
    font-size: 0.87em;
    line-height: 1.45;
    opacity: 0.86;
    font-variant-numeric: tabular-nums;
}

/* ===================================================================== */
/* ============================== NOTICES =============================== */
/* ===================================================================== */

.top-container-notice {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    background: #f3f8f2;
    border: 1px solid rgba(0,0,0,0.08);
    border-left: 6px solid #6aa56a;
    border-radius: 10px;
    padding: 12px 16px !important;
    margin-bottom: 16px !important;
    font-size: 1rem;
    line-height: 1.45;
}

.notice-icon {
    margin-right: 10px;
    flex-shrink: 0;
}

.notice-close {
    background: transparent;
    border: none;
    font-size: 1.2rem;
    line-height: 1;
    cursor: pointer;
    opacity: 0.7;
    color: inherit;
}

.notice-close:hover {
    opacity: 1;
}

/* ===================================================================== */
/* ============================== LAYOUT ================================ */
/* ===================================================================== */

#main {
    height: fit-content !important;
    padding-bottom: 100px;
}

@media screen and (max-width: 769px) {
    .form-container {
        width: calc(100% - 40px);
        margin: 0;
        padding: 20px;
        max-width: 600px;
        position: relative;
        margin-top: 80px;
        padding-top: 0px !important;
        margin-top: 80px !important;
    }
}

@media screen and (min-width: 769px) {
    .form-container {
        margin-top: auto;
        margin-bottom: auto;
        padding: 30px;
    }
}

@media (max-width: 600px) {
    .pledge-pay-buttons {
        flex-direction: column;
        align-items: center;
    }

    .pledge-pay-button-group {
        max-width: 100%;
        width: 100%;
    }

    .register-progress-label {
        flex-direction: column;
        align-items: flex-start;
    }

    .pledge-pay-pledge-card {
        flex-direction: column;
        align-items: flex-start;
    }
}

.pledge-pay-beta-row {
    margin-top: 20px;
    padding-top: 16px;
    border-top: 1px solid rgba(0,0,0,0.10);
}

.pledge-pay-beta-label {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 0.9em;
    opacity: 0.70;
    cursor: pointer;
}

.pledge-pay-beta-label input[type="checkbox"] {
    width: 16px;
    height: 16px;
    flex-shrink: 0;
    cursor: pointer;
    margin: 0;
}

</style>


<?php require_once ("../header-2026b.php");?>


