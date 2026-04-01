
<?php require_once ("../meta/community-3p-$lang.php");?>

<style>

/* ===================================================================== */
/* =================== COMMUNITY-3P PAGE LAYOUT ======================== */
/* ===================================================================== */

.community-3p-page-shell {
    margin-top: 108px;
}

.community-3p-container {
    padding-top: 0;
    max-width: 760px;
}

.community-3p-content-wrap {
    width: 100%;
    margin: auto;
    margin-top: 5px;
}

/* ===================================================================== */
/* ========================== TRAINING HEADER CARD ====================== */
/* ===================================================================== */

.community-3p-header-card {
    width: 100%;
    background: var(--course-module);
    border-radius: 15px;
    overflow: hidden;
    margin-bottom: 20px;
}

.community-3p-lead-photo {
    width: 100%;
    display: block;
    border-radius: 10px 10px 0 0;
}

.community-3p-training-info {
    padding: 18px 20px 20px 20px;
}

.community-3p-training-info h3 {
    margin: 0 0 8px 0;
    font-size: 1.45em;
    line-height: 1.25;
}

.community-3p-training-info p {
    margin: 0 0 5px 0;
    font-size: 1em;
    opacity: 0.82;
}

/* ===================================================================== */
/* ========================== COMMITMENT SUMMARY CARD =================== */
/* ===================================================================== */

.community-3p-commitment-card {
    background: var(--course-module);
    border: 1px solid rgba(0,0,0,0.08);
    border-top: 3px solid #1e8c40;
    border-radius: 14px;
    padding: 20px 22px;
    margin-bottom: 20px;
    display: flex;
    gap: 24px;
    flex-wrap: wrap;
    align-items: flex-start;
}

.community-3p-commitment-item {
    flex: 1;
    min-width: 160px;
}

.community-3p-commitment-label {
    font-size: 0.88em;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    opacity: 0.65;
    margin-bottom: 6px;
}

.community-3p-commitment-value {
    font-size: 1.65em;
    font-weight: 700;
    font-variant-numeric: tabular-nums;
    line-height: 1.1;
    color: var(--h1);
}

.community-3p-commitment-sub {
    font-size: 0.88em;
    opacity: 0.70;
    margin-top: 4px;
}

/* ===================================================================== */
/* ========================== FORM CARD ================================= */
/* ===================================================================== */

.community-3p-form-card {
    background: var(--course-module);
    border-radius: 14px;
    padding: 24px 22px;
    margin-bottom: 20px;
}

.community-3p-form-card h3 {
    margin: 0 0 6px 0;
    font-size: 1.25em;
    color: var(--h1);
}

.community-3p-form-card > p {
    font-size: 1em;
    opacity: 0.82;
    margin: 0 0 20px 0;
    line-height: 1.5;
}

.c3p-form-row {
    margin-bottom: 18px;
}

.c3p-form-row label {
    display: block;
    font-size: 0.95em;
    font-weight: 600;
    margin-bottom: 6px;
    color: var(--h1);
}

.c3p-form-row input[type="text"],
.c3p-form-row input[type="datetime-local"],
.c3p-form-row select,
.c3p-form-row textarea {
    width: 100%;
    box-sizing: border-box;
    padding: 10px 14px;
    border: 1px solid rgba(0,0,0,0.18);
    border-radius: 8px;
    font-size: 1em;
    background: rgba(255,255,255,0.08);
    color: inherit;
    font-family: inherit;
}

.c3p-form-row input:focus,
.c3p-form-row select:focus,
.c3p-form-row textarea:focus {
    outline: none;
    border-color: #1a56a0;
    box-shadow: 0 0 0 3px rgba(26,86,160,0.12);
}

.c3p-form-caption {
    font-size: 0.82em;
    opacity: 0.65;
    margin-top: 5px;
    line-height: 1.4;
}

.c3p-autocomplete-results {
    position: absolute;
    background: var(--course-module);
    border: 1px solid rgba(0,0,0,0.15);
    border-radius: 8px;
    z-index: 100;
    max-height: 220px;
    overflow-y: auto;
    width: 100%;
    box-shadow: 0 4px 16px rgba(0,0,0,0.12);
    left: 0;
    top: 100%;
    margin-top: 2px;
}

.c3p-autocomplete-wrap {
    position: relative;
}

.c3p-autocomplete-results .autocomplete-item {
    padding: 10px 14px;
    cursor: pointer;
    font-size: 0.97em;
    border-bottom: 1px solid rgba(0,0,0,0.05);
}

.c3p-autocomplete-results .autocomplete-item:last-child {
    border-bottom: none;
}

.c3p-autocomplete-results .autocomplete-item:hover {
    background: rgba(26,86,160,0.10);
}

.c3p-submit-btn {
    width: 100%;
    margin-top: 8px;
    font-size: 1.1em;
    padding: 14px;
    background: #1e8c40 !important;
}

.c3p-submit-btn:hover {
    background: #177233 !important;
}

/* ===================================================================== */
/* ========================== SUCCESS CARD ============================== */
/* ===================================================================== */

.community-3p-success-card {
    background: var(--course-module);
    border: 1px solid rgba(0,0,0,0.08);
    border-top: 4px solid #1e8c40;
    border-radius: 14px;
    padding: 30px 24px;
    margin-bottom: 20px;
    text-align: center;
}

.community-3p-success-card h3 {
    margin: 14px 0 10px 0;
    font-size: 1.5em;
    color: var(--h1);
}

.community-3p-success-card p {
    font-size: 1.05em;
    line-height: 1.6;
    margin: 0 auto 14px auto;
    max-width: 520px;
    opacity: 0.88;
}

.community-3p-success-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 6px 18px;
    border-radius: 999px;
    font-size: 0.9em;
    font-weight: 700;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    background: #1e8c40;
    color: #fff;
    margin-bottom: 4px;
    box-shadow: 0 2px 8px rgba(30,140,64,0.22);
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
    .community-3p-commitment-card {
        flex-direction: column;
    }
}

</style>


<?php require_once ("../header-2026b.php");?>


