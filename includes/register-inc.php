
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

/* Top notices */
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
    background: #f6f3ea;
    border-left-color: #9a6a3a;
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
}

.notice-close:hover {
    opacity: 1;
}

/* ===================================================================== */
/* ===================== REGISTRATION STATUS PANEL ====================== */
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
/* ======================== SHARED MODAL STYLING ======================== */
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
/* ======================== 3P MODAL STYLING ============================ */
/* ===================================================================== */

.threep-modal-wrap {
    display: flex;
    flex-direction: column;
    text-align: center;
}

.threep-training-kicker {
    font-size: 0.95em;
    color: #888;
    margin-bottom: 8px;
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

.threep-modal-slider-block {
    background: rgba(0,0,0,0.035);
    border: 1px solid rgba(0,0,0,0.07);
    border-radius: 14px;
    padding: 18px 16px 16px 16px;
    margin-top: 6px;
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
    grid-template-columns: minmax(58px,auto) 1fr minmax(58px,auto);
    gap: 12px;
    align-items: center;
}

.threep-slider-edge {
    font-size: 0.9em;
    opacity: 0.75;
    font-variant-numeric: tabular-nums;
    line-height: 1.3;
}

#threep_pledge_slider {
    width: 100%;
    accent-color: #5c8f5c;
    cursor: pointer;
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
    font-size: 0.9em;
    opacity: 0.8;
}

.threep-currency-select {
    padding: 5px 8px;
    font-size: 0.9em;
    min-width: 76px;
    max-width: 90px;
}

.threep-confirm-footnote {
    font-size: 0.95em;
    color: grey;
    margin-top: 12px;
    max-width: 80%;
}

/* ===================================================================== */
/* ======================== CUSTOM TOOLTIP UI =========================== */
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
/* ============================ RESPONSIVE ============================== */
/* ===================================================================== */

@media (max-width: 900px) {
    .threep-modal-head {
        flex-direction: column;
        align-items: center;
    }

    .threep-slider-row {
        grid-template-columns: 1fr;
    }

    .threep-slider-edge {
        display: none;
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

    .threep-confirm-footnote {
        max-width: 100%;
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



