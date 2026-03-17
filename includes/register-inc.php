
<!--  Set any page specific graphics to preload-->

<!--  Set any page specific graphics to preload
<link rel="preload" as="image" href="../webps/ecobrick-team-blank.webp" media="(max-width: 699px)">
<link rel="preload" as="image" href="../svgs/richard-and-team-day.svg">
<link rel="preload" as="image" href="../svgs/richard-and-team-night.svg">
<link rel="preload" as="image" href="../webps/biosphere2.webp">
<link rel="preload" as="image" href="../webps/biosphere-day.webp">-->

<?php require_once ("../meta/register-$lang.php");?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">



<STYLE>
/* ===================================================================== */
/* ======================== REGISTER PAGE: 3P UI ======================== */
/* ===================================================================== */

/* page wrappers */
.register-page-shell {
    margin-top: 108px;
}

.register-page-container {
    padding-top: 0;
}

.register-content-wrap {
    width: 100%;
    margin: auto;
    margin-top: 5px;
}

.register-intro-card {
    width: 100%;
    background: var(--course-module);
    border-radius: 15px;
    padding: 10px;
}

.register-lead-photo {
    width: 100%;
    border-radius: 10px;
}

.register-subtitle {
    margin: 10px 0;
}

.register-meta-line {
    font-size: 1em;
}

.register-profile-line {
    margin-bottom: 10px;
}

.register-profile-line-small {
    font-size: 1em;
}

.register-signup-line {
    margin-bottom: 10px;
}

.register-main-button {
    margin-top: 20px;
    font-size: 1.3em;
    padding: 10px 20px;
    cursor: pointer;
}

.register-bottom-button {
    margin-top: 20px;
    margin-bottom: 75px;
    font-size: 1.3em;
    padding: 10px 20px;
    cursor: pointer;
    width: 100%;
}

.register-featured-description {
    margin-top: 20px;
    font-size: 1.5em;
    padding: 15px;
}

.register-agenda {
    font-size: 1.23em;
    padding: 15px;
    margin-top: 0;
}

.register-details-panel {
    margin-top: 20px;
    font-size: small;
}

.register-details-photo {
    width: 100%;
    padding: 10px;
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
    padding: 12px 16px;
    margin-bottom: 16px;
    font-size: 1rem;
    line-height: 1.45;
}

#pledged-notice {
    background: linear-gradient(90deg, rgba(46, 130, 50, 0.94) 0%, rgba(56, 151, 61, 0.96) 100%);
    color: #fff;
    border-left-color: #f0d85c;
}

#pledged-notice a {
    color: #fff;
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
/* ============================ STATUS PANEL ============================ */
/* ===================================================================== */

.register-status-panel {
    margin-top: 16px;
    width: 100%;
    background: rgba(255,255,255,0.16);
    border: 1px solid rgba(0,0,0,0.08);
    border-radius: 14px;
    padding: 14px;
    box-shadow: inset 0 1px 0 rgba(255,255,255,0.14);
}

.register-status-title {
    font-size: 1.04em;
    font-weight: 600;
    margin-bottom: 14px;
}

.register-progress-block {
    margin-bottom: 16px;
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
}

.register-progress-bar {
    position: relative;
    width: 100%;
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
/* ============================ SHARED MODALS =========================== */
/* ===================================================================== */

.register-modal-stack {
    display: flex;
    flex-direction: column;
    height: 100%;
    justify-content: space-between;
}

.register-modal-centered {
    text-align: center;
}

.register-modal-actions {
    display: flex;
    gap: 10px;
    width: 100%;
    margin-top: 20px;
}

.register-modal-actions-column {
    flex-flow: column;
    align-items: center;
}

.register-modal-actions-centered {
    align-items: center;
}

.register-modal-action-wide {
    width: 80%;
}

.register-modal-action-half {
    flex: 1;
}

.register-modal-footnote {
    font-size: 1em;
    color: grey;
    margin-top: 12px;
}

.register-button-muted {
    background: grey !important;
}

.register-button-danger {
    background: red !important;
    color: white !important;
}

.register-success-modal {
    text-align: center;
    width: 100%;
    margin: auto;
    margin-top: 10px;
    margin-bottom: 10px;
}

.register-success-image {
    width: 50%;
    max-width: 400px;
}

.preview-title {
    font-size: 1rem;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    color: #777;
    margin-bottom: 6px;
}

/* ===================================================================== */
/* ============================ 3P CORE UI ============================== */
/* ===================================================================== */

.threep-box {
    margin-top: 20px;
}

.threep-hr {
    margin: 18px 0 22px 0;
    border: 0;
    border-top: 1px solid rgba(0,0,0,0.18);
}

.threep-subtitle {
    font-size: 1.55em;
    font-weight: 600;
    margin-bottom: 10px;
    color: var(--text);
    letter-spacing: 0.01em;
}

.threep-section-divider {
    border-top: 2px dotted rgba(0,0,0,0.22);
    margin: 12px 0 18px 0;
}

.threep-field-card {
    background: rgba(255,255,255,0.20);
    padding: 16px 16px 14px 16px;
    border-radius: 10px;
    margin-bottom: 14px;
    border: 1px solid rgba(0,0,0,0.04);
    box-shadow: inset 0 1px 0 rgba(255,255,255,0.15);
}

.currency-locked {
    padding: 10px 14px;
    background: rgba(0,0,0,0.06);
    border-radius: 8px;
    font-weight: 500;
    display: inline-block;
    border: 1px solid rgba(0,0,0,0.06);
}

.currency-input-wrap {
    display: grid;
    grid-template-columns: 1fr 56px;
    align-items: center;
    gap: 10px;
}

.currency-display-input {
    text-align: left;
    letter-spacing: 0.01em;
    font-variant-numeric: tabular-nums;
}

.currency-suffix {
    text-align: left;
    font-weight: 600;
    opacity: 0.72;
    font-size: 0.96em;
}

/* ===================================================================== */
/* ========================== LIVE CALCULATOR =========================== */
/* ===================================================================== */

.threep-calculator {
    margin-top: 24px;
    background: linear-gradient(180deg, rgba(255,255,255,0.14) 0%, rgba(255,255,255,0.08) 100%);
    padding: 22px 18px 20px 18px;
    border-radius: 14px;
    border: 1px solid rgba(0,0,0,0.07);
    box-shadow:
        inset 0 1px 0 rgba(255,255,255,0.18),
        0 1px 2px rgba(0,0,0,0.04);
}

.threep-calculator-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px;
    margin-top: 14px;
}

.calc-card {
    background: rgba(255,255,255,0.92);
    padding: 18px 16px 16px 16px;
    border-radius: 10px;
    border: 1px solid rgba(0,0,0,0.08);
    min-height: 104px;
    display: flex;
    flex-direction: column;
    justify-content: flex-start;
    box-shadow:
        0 1px 1px rgba(0,0,0,0.03),
        inset 0 1px 0 rgba(255,255,255,0.5);
}

.calc-title {
    font-size: 0.95em;
    opacity: 0.78;
    margin-bottom: 10px;
    line-height: 1.35;
}

.calc-value {
    font-size: 2.05em;
    font-weight: 700;
    line-height: 1.1;
    letter-spacing: 0.01em;
    font-variant-numeric: tabular-nums;
}

.calc-value.is-green {
    color: #2f7d32;
}

.calc-value.is-earth {
    color: #8a4b32;
}

.threep-calculator .form-caption {
    margin-top: 16px;
}

/* ===================================================================== */
/* ======================= STATUS + PROGRESS AREA ======================= */
/* ===================================================================== */

.threep-status-grid {
    display: grid;
    grid-template-columns: 1fr 1.1fr;
    gap: 22px;
    align-items: start;
    margin-top: 10px;
}

.threep-status-textbox {
    background: rgba(255,255,255,0.22);
    padding: 16px;
    border-radius: 10px;
    border: 1px solid rgba(0,0,0,0.06);
    line-height: 1.95;
    box-shadow: inset 0 1px 0 rgba(255,255,255,0.15);
}

/* ===================================================================== */
/* =========================== 3P PROGRESS BARS ========================= */
/* ===================================================================== */

.threep-progress-block {
    background: rgba(255,255,255,0.22);
    padding: 16px 16px 14px 16px;
    border-radius: 10px;
    margin-bottom: 16px;
    border: 1px solid rgba(0,0,0,0.06);
    box-shadow: inset 0 1px 0 rgba(255,255,255,0.15);
}

.threep-progress-label {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    gap: 10px;
    font-weight: 600;
    margin-bottom: 12px;
    font-size: 1em;
    line-height: 1.2;
}

.threep-progress-label span {
    font-weight: 500;
    opacity: 0.78;
    font-size: 0.93em;
    font-variant-numeric: tabular-nums;
}

.threep-progress-bar {
    position: relative;
    width: 100%;
    height: 24px;
    border-radius: 999px;
    overflow: hidden;
    background: #e4e4e4;
    box-shadow:
        inset 0 1px 3px rgba(0,0,0,0.10),
        inset 0 -1px 0 rgba(255,255,255,0.16);
}

.threep-progress-zone-before {
    position: absolute;
    top: 0;
    bottom: 0;
    left: 0;
    background: linear-gradient(180deg, #c7c7c7 0%, #bbbbbb 100%);
}

.threep-progress-zone-after {
    position: absolute;
    top: 0;
    bottom: 0;
    right: 0;
    background: linear-gradient(180deg, rgba(112,176,120,0.34) 0%, rgba(89,155,96,0.28) 100%);
}

.threep-progress-fill {
    position: absolute;
    top: 0;
    bottom: 0;
    left: 0;
    min-width: 8px;
    background: linear-gradient(90deg, #d85050 0%, #e56868 100%);
    border-radius: 999px;
    box-shadow:
        inset 0 1px 0 rgba(255,255,255,0.20),
        0 0 0 1px rgba(0,0,0,0.03);
}

.threep-progress-threshold {
    position: absolute;
    top: -2px;
    bottom: -2px;
    width: 0;
    border-left: 3px solid rgba(24,24,24,0.88);
    box-shadow: 0 0 0 1px rgba(255,255,255,0.20);
    z-index: 5;
}

.threep-progress-meta {
    margin-top: 10px;
    font-size: 0.94em;
    line-height: 1.55;
    opacity: 0.84;
    font-variant-numeric: tabular-nums;
}

.threep-progress-meta strong {
    font-weight: 600;
}

/* ===================================================================== */
/* ============================ 3P MODAL UI ============================= */
/* ===================================================================== */

.threep-modal-wrap {
    display: flex;
    flex-direction: column;
    text-align: center;
}

.threep-modal-head {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 16px;
    margin-bottom: 8px;
}

.threep-modal-head-main {
    width: 100%;
}

.threep-modal-title {
    margin-top: 0;
    margin-bottom: 6px;
}

.threep-modal-copy {
    font-size: 1rem;
    line-height: 1.58;
    margin: 10px 0 18px 0;
    color: #333;
}

/* training title pill: supports all prior class names */
.threep-training-pill,
.threep-training-kicker,
.threep-training-kicker-pill {
    display: inline-flex;
    align-self: center;
    justify-content: center;
    align-items: center;
    width: fit-content;
    max-width: 100%;
    background: var(--subdued-text);
    color: var(--h1);
    border-radius: 999px;
    padding: 8px 16px;
    font-size: 0.95em;
    font-weight: 600;
    margin: 0 auto 12px auto;
    box-shadow:
        0 2px 6px rgba(0,0,0,0.10),
        inset 0 1px 0 rgba(255,255,255,0.12);
    line-height: 1.25;
    white-space: normal;
    text-align: center;
}

.threep-modal-slider-block {
    background: linear-gradient(180deg, rgba(0,0,0,0.038) 0%, rgba(0,0,0,0.028) 100%);
    border: 1px solid rgba(0,0,0,0.07);
    border-radius: 14px;
    padding: 18px 16px 16px 16px;
    margin-top: 6px;
    box-shadow:
        inset 0 1px 0 rgba(255,255,255,0.18),
        0 2px 8px rgba(0,0,0,0.04);
}

.threep-amount-readout {
    font-size: 2.15em;
    font-weight: 700;
    line-height: 1.1;
    margin-bottom: 14px;
    letter-spacing: 0.01em;
    font-variant-numeric: tabular-nums;
}

.threep-slider-row {
    display: grid;
    grid-template-columns: minmax(72px,auto) 1fr minmax(72px,auto);
    gap: 12px;
    align-items: center;
}

.threep-slider-edge {
    font-size: 0.88em;
    font-variant-numeric: tabular-nums;
    line-height: 1.3;
}

.threep-slider-pill,
.threep-edge-pill {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 999px;
    padding: 7px 10px;
    color: #fff;
    font-weight: 600;
    box-shadow:
        0 2px 5px rgba(0,0,0,0.12),
        inset 0 1px 0 rgba(255,255,255,0.14);
    white-space: nowrap;
}

.pill-zero,
.threep-edge-pill-zero {
    background: linear-gradient(180deg, #f0a343 0%, #de7f1e 100%);
}

.pill-max,
.threep-edge-pill-max {
    background: linear-gradient(180deg, #2f8c3b 0%, #21672a 100%);
}

#threep_pledge_slider {
    --pledge-color: #7ed957;
    -webkit-appearance: none;
    appearance: none;
    width: 100%;
    height: 14px;
    border-radius: 999px;
    outline: none;
    cursor: pointer;
    background: linear-gradient(90deg, #7ed957 0%, #7ed957 50%, #e4efe3 50%, #e4efe3 100%);
    box-shadow:
        inset 0 1px 3px rgba(0,0,0,0.16),
        inset 0 -1px 0 rgba(255,255,255,0.16),
        0 0 0 rgba(126, 217, 87, 0);
    transition:
        background 180ms cubic-bezier(0.22, 1, 0.36, 1),
        box-shadow 220ms cubic-bezier(0.22, 1, 0.36, 1),
        transform 220ms cubic-bezier(0.22, 1, 0.36, 1);
    will-change: background, box-shadow, transform;
    transform: translateZ(0);
}

#threep_pledge_slider:hover,
#threep_pledge_slider:focus {
    box-shadow:
        inset 0 1px 3px rgba(0,0,0,0.16),
        inset 0 -1px 0 rgba(255,255,255,0.16),
        0 0 16px color-mix(in srgb, var(--pledge-color) 28%, transparent);
}

#threep_pledge_slider:active {
    transform: scaleY(1.06) translateZ(0);
    transition:
        background 120ms cubic-bezier(0.34, 1.56, 0.64, 1),
        box-shadow 120ms cubic-bezier(0.34, 1.56, 0.64, 1),
        transform 120ms cubic-bezier(0.34, 1.56, 0.64, 1);
}

#threep_pledge_slider::-webkit-slider-runnable-track {
    height: 14px;
    border-radius: 999px;
    background: transparent;
}

#threep_pledge_slider::-webkit-slider-thumb {
    -webkit-appearance: none;
    appearance: none;
    width: 26px;
    height: 26px;
    margin-top: -6px;
    border-radius: 50%;
    background:
        radial-gradient(circle at 35% 30%, rgba(255,255,255,0.98) 0%, rgba(255,255,255,0.92) 42%, rgba(247,247,247,0.96) 100%);
    border: 3px solid var(--pledge-color, #7ed957);
    box-shadow:
        0 2px 8px rgba(0,0,0,0.18),
        0 0 0 6px color-mix(in srgb, var(--pledge-color) 20%, transparent),
        inset 0 1px 0 rgba(255,255,255,0.82);
    cursor: pointer;
    transition:
        border-color 160ms cubic-bezier(0.22, 1, 0.36, 1),
        box-shadow 160ms cubic-bezier(0.22, 1, 0.36, 1),
        transform 180ms cubic-bezier(0.34, 1.56, 0.64, 1);
    will-change: transform, box-shadow, border-color;
    transform: translateZ(0);
}

#threep_pledge_slider:hover::-webkit-slider-thumb,
#threep_pledge_slider:focus::-webkit-slider-thumb {
    transform: scale(1.06) translateZ(0);
    box-shadow:
        0 3px 10px rgba(0,0,0,0.22),
        0 0 0 8px color-mix(in srgb, var(--pledge-color) 26%, transparent),
        inset 0 1px 0 rgba(255,255,255,0.88);
}

#threep_pledge_slider:active::-webkit-slider-thumb {
    transform: scale(1.1) translateZ(0);
}

#threep_pledge_slider::-moz-range-track {
    height: 14px;
    border-radius: 999px;
    background: transparent;
}

#threep_pledge_slider::-moz-range-thumb {
    width: 26px;
    height: 26px;
    border-radius: 50%;
    background:
        radial-gradient(circle at 35% 30%, rgba(255,255,255,0.98) 0%, rgba(255,255,255,0.92) 42%, rgba(247,247,247,0.96) 100%);
    border: 3px solid var(--pledge-color, #7ed957);
    box-shadow:
        0 2px 8px rgba(0,0,0,0.18),
        0 0 0 6px color-mix(in srgb, var(--pledge-color) 20%, transparent),
        inset 0 1px 0 rgba(255,255,255,0.82);
    cursor: pointer;
    transition:
        border-color 160ms cubic-bezier(0.22, 1, 0.36, 1),
        box-shadow 160ms cubic-bezier(0.22, 1, 0.36, 1),
        transform 180ms cubic-bezier(0.34, 1.56, 0.64, 1);
    will-change: transform, box-shadow, border-color;
    transform: translateZ(0);
}

#threep_pledge_slider:hover::-moz-range-thumb,
#threep_pledge_slider:focus::-moz-range-thumb {
    transform: scale(1.06) translateZ(0);
    box-shadow:
        0 3px 10px rgba(0,0,0,0.22),
        0 0 0 8px color-mix(in srgb, var(--pledge-color) 26%, transparent),
        inset 0 1px 0 rgba(255,255,255,0.88);
}

#threep_pledge_slider:active::-moz-range-thumb {
    transform: scale(1.1) translateZ(0);
}

.threep-suggested-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
    margin-top: 14px;
    flex-wrap: wrap;
    padding-top: 12px;
    border-top: 1px dotted rgba(0,0,0,0.14);
}

.threep-suggested-copy {
    font-size: 0.95em;
}

.threep-suggested-row strong {
    font-variant-numeric: tabular-nums;
}

.threep-currency-switcher {
    display: flex;
    align-items: center;
    gap: 8px;
}

.threep-currency-switch-label {
    font-size: 0.84em;
    opacity: 0.8;
}

.threep-currency-select {
    padding: 4px 8px;
    font-size: 0.84em;
    min-width: 88px;
    max-width: 96px;
    line-height: 1.2;
}

.threep-confirm-button {
    position: relative;
    overflow: hidden;
    transition:
        background 180ms cubic-bezier(0.22, 1, 0.36, 1),
        border-color 180ms cubic-bezier(0.22, 1, 0.36, 1),
        box-shadow 220ms cubic-bezier(0.22, 1, 0.36, 1),
        transform 180ms cubic-bezier(0.34, 1.56, 0.64, 1);
    will-change: background, border-color, box-shadow, transform;
    animation: threep-confirm-soft-pulse 2.8s ease-in-out infinite;
    transform: translateZ(0);
}

.threep-confirm-button:hover,
.threep-confirm-button:focus {
    transform: translateY(-1px) scale(1.01) translateZ(0);
    box-shadow:
        0 8px 20px rgba(0,0,0,0.14),
        0 0 0 8px color-mix(in srgb, var(--pledge-color) 18%, transparent);
}

.threep-confirm-button::after {
    content: "";
    position: absolute;
    top: -60%;
    left: -25%;
    width: 22%;
    height: 220%;
    background: linear-gradient(180deg, rgba(255,255,255,0) 0%, rgba(255,255,255,0.28) 50%, rgba(255,255,255,0) 100%);
    transform: rotate(18deg);
    pointer-events: none;
    opacity: 0.66;
    animation: threep-button-shimmer 3.6s ease-in-out infinite;
}

.threep-confirm-footnote,
.subdued {
    color: var(--subdued-text);
    font-size: 0.95em;
    margin-top: 12px;
    max-width: 80%;
}

/* ===================================================================== */
/* ============================== TOOLTIPS ============================== */
/* ===================================================================== */

.threep-help-underline {
    text-decoration: underline dotted;
    text-underline-offset: 3px;
    cursor: help;
}

.custom-tooltip-bubble {
    position: absolute;
    z-index: 99999;
    max-width: 320px;
    background: #2b2b2b;
    color: #fff;
    padding: 10px 12px;
    border-radius: 10px;
    font-size: 0.92rem;
    line-height: 1.45;
    box-shadow: 0 10px 26px rgba(0,0,0,0.22);
    opacity: 0;
    transform: translateY(3px);
    transition: opacity 0.12s ease, transform 0.12s ease;
    pointer-events: none;
    border: 1px solid rgba(255,255,255,0.08);
}

.custom-tooltip-bubble.visible {
    opacity: 1;
    transform: translateY(0);
}

/* ===================================================================== */
/* ============================= ANIMATIONS ============================= */
/* ===================================================================== */

@keyframes threep-confirm-soft-pulse {
    0%, 100% {
        box-shadow:
            0 0 0 0 rgba(126, 217, 87, 0.00),
            0 2px 6px rgba(0,0,0,0.10);
    }
    50% {
        box-shadow:
            0 0 0 6px rgba(126, 217, 87, 0.10),
            0 6px 16px rgba(0,0,0,0.14);
    }
}

@keyframes threep-button-shimmer {
    0% {
        left: -30%;
        opacity: 0;
    }
    12% {
        opacity: 0.48;
    }
    28% {
        left: 108%;
        opacity: 0.18;
    }
    100% {
        left: 108%;
        opacity: 0;
    }
}

/* ===================================================================== */
/* ============================ RESPONSIVE ============================== */
/* ===================================================================== */

@media (max-width: 900px) {
    .threep-status-grid {
        grid-template-columns: 1fr;
    }

    .threep-calculator-grid {
        grid-template-columns: 1fr;
    }

    .currency-input-wrap {
        grid-template-columns: 1fr 52px;
    }

    .calc-value {
        font-size: 1.8em;
    }

    .threep-progress-bar {
        height: 22px;
    }

    .threep-modal-head {
        flex-direction: column;
        align-items: center;
    }

    .threep-slider-row {
        grid-template-columns: 1fr;
    }

    .threep-slider-edge {
        justify-self: center;
    }

    .threep-suggested-row {
        flex-direction: column;
        align-items: flex-start;
    }

    .threep-currency-switcher {
        width: 100%;
        justify-content: space-between;
    }

    .register-progress-label {
        flex-direction: column;
        align-items: flex-start;
    }

    .register-modal-actions {
        flex-direction: column;
    }

    .register-modal-action-half,
    .register-modal-action-wide {
        width: 100%;
    }

    .threep-confirm-footnote,
    .subdued {
        max-width: 100%;
    }

    .threep-training-pill,
    .threep-training-kicker,
    .threep-training-kicker-pill {
        max-width: 92%;
    }
}








#rsvp-register-button-desktop,
#rsvp-register-button-mobile  {
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    background: var(--emblem-green);
    font-size: 1.3em;
    justify-content: center;
    text-align: center;
    text-decoration: none;
    margin-top: 10px;
    display: flex;
    width: 80%;
}

@media screen and (max-width: 768px) {
    #rsvp-register-button-desktop { display:none; }
    #rsvp-register-button-mobile { display:flex;width:100% !important; }
    .training-title-box { flex-flow: column;}

}

@media screen and (min-width: 769px) {
    #rsvp-register-button-mobile { display:none; }
    #rsvp-register-button-desktop { display:flex; align-self:flex-start; }
    .training-title-box { flex-flow: row;}
}


      #main {
        height: fit-content !important;
        padding-bottom: 100px;
    }

    .preview-text {
        font-family: 'Mulish', Arial, Helvetica, sans-serif;
        font-weight: 300;
        -webkit-font-smoothing: antialiased;
        color: var(--text-color);
        margin-top: 15px;
        margin-bottom: 15px;
    }

.profile-names {
text-align:center;
margin-top: 0px;
margin-bottom: auto;"
}


.profile-images {
display: flex;
flex-flow:column;
}

@media screen and (max-width: 769px) {

.the-titles {
padding: 7px;
}

.profile-images img {
width:177px;
margin-left:auto;
margin-right: 10px;
}

.profile-names {
width:250px;
text-align:right;
margin-left:auto;
margin-right: 10px;
margin-top: 10px;
}

.training-title-box {
width:100%;
display:flex;


}
}

@media screen and (min-width: 770px) {

.the-titles {
width:80%;
padding: 20px;
margin-top: -5px;
}
.profile-images {
width:100%;
}

.profile-images img {
width:250px;
padding:15px;
margin-top:-20px;
margin-left:auto;
margin-right: 10px;

}

.profile-names {
width:250px;
margin-right: 10px;
margin-left:auto;
margin-right: 10px;
margin-top: 10px;

}

.training-title-box {
width:100%;
display:flex;
flex-flow:row;
margin-top: 20px;

}

}



#main-background {
  background-size: cover;

}



/* Media Query for screens under 700px */
@media screen and (max-width: 700px) {
  .form-container {
    width: calc(100% - 40px);
    margin: 0;
    /* border: none; */
    padding: 20px 20px 0 20px;
    max-width: 600px;
    padding: 20px;
    position: relative;
    margin-top: 80px;

  }
}


/* Centering the form vertically on larger screens */
@media screen and (min-width: 701px) {
  /* #form-submission-box {
    display: flex;
    align-items: center;
    justify-content: center;

  } */

  .form-container {
    margin-top: auto;
    margin-bottom: auto;
    padding: 30px;
    margin-top: 110px;

  }
}

.module-btn {
  background: var(--emblem-green);
  width: 100%;
  display: flex;
}

.module-btn:hover {
  background: var(--emblem-green-over);
}


.go-button {
    padding: 10px 20px;
    border: none;
    color: white;
    transition: background-color 0.3s, cursor 0.3s;
     padding: 10px 20px;
  border: none;
  border-radius: 6px;
   font-size: 1.3em;

    background-color: var(--button-2-1);
    cursor: pointer;
    margin: 10px;
}

/* Hover effect for enabled state */
.go-button:hover {
    background-color: var(--button-2-1-over);
}


#splash-bar {
  background-color: var(--top-header);
  filter: none !important;
  margin-bottom: -200px !important;
}


.ecobrick-action-button {
    width: 100%;           /* Ensures the button takes the full width */
    display: block;        /* Ensures the button behaves as a block element */
    text-align: center;    /* Centers the text inside the button */
    padding: 10px;         /* Add some padding for better button appearance */
    margin-bottom: 10px;   /* Margin to create space between buttons */
    border: none;          /* Remove default borders */
    cursor: pointer;       /* Show pointer on hover */
    font-size: 1em;        /* Consistent font size */
    padding: 10px 20px;
  border: none;
  border-radius: 10px;
  cursor: pointer;
  background: #00800094;
  font-size: 1.3em;
  text-align: center;
  text-decoration: none;
  color:white;
}


.ecobrick-action-button:hover {
  background: var(--emblem-green);
}


/* Special styling for delete button */
.ecobrick-action-button.deleter-button {
    background-color: #ff000094; /* Red background for the delete button */
    color: white;          /* White text for contrast */
}

.ecobrick-action-button.deleter-button:hover {
    background-color: red; /* Red background for the delete button */
    color: white;          /* White text for contrast */
}

/* Signup count styles */
.signup-count-box {
    background: grey;
    border-radius: 10px;
    padding: 5px 10px;
    text-align: center;
    margin-top: 10px;
}

.signup-count-number {
    font-family: 'Arvo', serif;
    font-size: 1.5em;
    color: white;
}

.signup-count-text {
    font-family: 'Mulish', sans-serif;
    font-size: 0.9em;
    color: white;
}

</style>





<?php require_once ("../header-2026.php");?>



