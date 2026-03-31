
<!--  Set any page specific graphics to preload-->

<!--  Set any page specific graphics to preload
<link rel="preload" as="image" href="../webps/ecobrick-team-blank.webp" media="(max-width: 699px)">
<link rel="preload" as="image" href="../svgs/richard-and-team-day.svg">
<link rel="preload" as="image" href="../svgs/richard-and-team-night.svg">
<link rel="preload" as="image" href="../webps/biosphere2.webp">
<link rel="preload" as="image" href="../webps/biosphere-day.webp">-->

<?php require_once ("../meta/$page-$lang.php");?>



<STYLE>

.form-container {
padding-top: 0px !important;
background: none !important;
border: none !important;
margin-top:-100px !important;
}


.log-report-btn {
    display: inline-block;
    padding: 5px 10px;
    background-color: #e0e0e0;
    color: black;
    border: 1px solid grey;
    text-decoration: none;
    border-radius: 4px;
    font-size: 14px;
}

.log-report-btn:hover {
    background-color: darkgrey;
    color: white;
}

.signup-btn .hover-emoji {
    display: none;
}
.signup-btn:hover .signup-count {
    display: none;
}
.signup-btn:hover .hover-emoji {
    display: inline;
}




//Databables


   #ecobrick-search-return_info {
   color: grey !important;
   font-family: 'Mulish', sans-serif !important;
   }

   #latest-ecobricks_info {
   color: grey !important;
   font-family: 'Mulish', sans-serif !important;
   }

/* Media query for screens less than 769px wide */
@media screen and (max-width: 768px) {
    /* Hide the "Location" and "Weight" table headers */
    #latest-ecobricks th:nth-child(2), /* Weight column header */
    #latest-ecobricks th:nth-child(3)  /* Location column header */ {
        display: none;
    }

    /* Hide the "Location" and "Weight" table cells */
    #latest-ecobricks td:nth-child(2), /* Weight column cell */
    #latest-ecobricks td:nth-child(3)  /* Location column cell */ {
        display: none;
    }
}

    #main {
        height: fit-content;
    }

    .preview-text {
        font-family: 'Mulish', Arial, Helvetica, sans-serif;
        font-weight: 300;
        -webkit-font-smoothing: antialiased;
        color: var(--text-color);
        margin-top: 15px;
        margin-bottom: 15px;
    }






#main-background {
  background-size: cover;

}

/* #main { */
/*   background-color: #0003 !important; */
/* } */

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

#featured_image {
  margin-bottom: 8px;
  margin-top: 8px;
  padding: 5px;
  font-size: 1em;
}

#tmb_featured_image {
  margin-bottom: 8px;
  margin-top: 8px;
  padding: 5px;
  font-size: 1em;
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

/* Toggle rows inside the training actions modal */
.training-toggle-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: var(--same);
    padding: 10px 20px;
    border-radius: 10px;
    margin-bottom: 10px;
    width: 100%;
}

.training-toggle-title {
    font-size: 1em;
    color: var(--text-color);
}

/* Notice button inside header notice */
.notice-button {
    margin-left: 10px;
    padding: 4px 8px;
    background: #fff;
    color: #000;
    border-radius: 4px;
    text-decoration: none;
    font-size: 0.9em;
}

#my-trainings-panel {
    box-sizing: border-box;
}

/* Trainer trainings table tweaks */
#trainer-trainings td:first-child {
    white-space: normal;
}
#trainer-trainings td:nth-child(3) a {
    padding: 10px;
    min-width: 20px;
}

#trainer-trainings td:nth-child(3),
#trainer-trainings th:nth-child(3),
#trainer-trainings td:nth-child(4),
#trainer-trainings th:nth-child(4) {
    max-width: 50px;
}
@media screen and (max-width: 768px) {
    #trainer-trainings th:nth-child(2),
    #trainer-trainings td:nth-child(2) {
        display: none;
    }
}

/* Ensure the trainer trainings table has equal margins on mobile */
@media screen and (max-width: 700px) {
    #trainer-trainings_wrapper {
        padding: 0 7px;
        box-sizing: border-box;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    #trainer-trainings {
        margin: 0;
        width: 100%;
    }
}

/* ===================================================================== */
/* ====================== PLEDGE REGISTRATIONS LIST ==================== */
/* ===================================================================== */

.pledge-reg-list {
    display: flex;
    flex-direction: column;
    gap: 0;
    margin-top: 6px;
    margin-bottom: 10px;
}

.pledge-reg-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 14px;
    padding: 11px 0 11px 10px;
    border-bottom: 1px solid rgba(0,0,0,0.07);
    position: relative;
}

.pledge-reg-row:last-child {
    border-bottom: none;
}

.pledge-reg-row.reg-row-upcoming::before {
    content: '';
    position: absolute;
    left: 0;
    top: 6px;
    bottom: 6px;
    width: 3px;
    background: #22c55e;
    border-radius: 999px;
}

.pledge-reg-info {
    display: flex;
    flex-direction: column;
    gap: 3px;
    min-width: 0;
    flex: 1;
}

.pledge-reg-title {
    font-weight: 600;
    font-size: 0.97em;
    line-height: 1.3;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.pledge-reg-meta {
    font-size: 0.82em;
    opacity: 0.60;
    line-height: 1.2;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* ------------------------------------------------------------------ */
/* Pledge status pill — a bar graph wrapped in a rounded pill shape    */
/* ------------------------------------------------------------------ */

.pledge-reg-pill {
    position: relative;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    min-width: 128px;
    height: 30px;
    border-radius: 999px;
    overflow: hidden;
    text-decoration: none;
    font-size: 0.76em;
    font-weight: 700;
    letter-spacing: 0.015em;
    border: 1.5px solid rgba(0,0,0,0.10);
    background: #e5e5e5;
    cursor: pointer;
    transition: filter 0.15s ease, transform 0.15s ease;
}

.pledge-reg-pill:hover {
    filter: brightness(0.94);
    transform: translateY(-1px);
}

/* Fill bar — absolute, grows left-to-right based on funding % */
.pledge-pill-bar {
    position: absolute;
    top: 0;
    left: 0;
    height: 100%;
    border-radius: 999px;
    transition: width 0.5s cubic-bezier(0.25, 1, 0.5, 1);
    pointer-events: none;
}

/* Status label on top of fill */
.pledge-pill-label {
    position: relative;
    z-index: 1;
    white-space: nowrap;
    padding: 0 10px;
}

/* ---- State: pending (threshold not yet met) ---- */
.pill-state-pending {
    border-color: rgba(60,110,210,0.28);
}
.pill-state-pending .pledge-pill-bar {
    background: linear-gradient(90deg, #5b8fe8 0%, #78aaf0 100%);
    opacity: 0.38;
}
.pill-state-pending .pledge-pill-label {
    color: #1a50b0;
}

/* ---- State: payment-due (threshold met, awaiting payment) ---- */
.pill-state-payment-due {
    border-color: rgba(190,20,20,0.30);
}
.pill-state-payment-due .pledge-pill-bar {
    background: linear-gradient(90deg, #29a654 0%, #42c46c 100%);
    opacity: 0.55;
}
.pill-state-payment-due .pledge-pill-label {
    color: #ae0000;
}

/* ---- State: paid ---- */
.pill-state-paid {
    border-color: rgba(28,130,60,0.35);
}
.pill-state-paid .pledge-pill-bar {
    background: linear-gradient(90deg, #1d8c42 0%, #2eab5a 100%);
}
.pill-state-paid .pledge-pill-label {
    color: #fff;
    text-shadow: 0 1px 2px rgba(0,0,0,0.18);
}

/* ---- State: expired / cancelled ---- */
.pill-state-expired,
.pill-state-cancelled {
    border-color: rgba(0,0,0,0.12);
}
.pill-state-expired .pledge-pill-bar,
.pill-state-cancelled .pledge-pill-bar {
    background: linear-gradient(90deg, #999 0%, #b5b5b5 100%);
    opacity: 0.45;
}
.pill-state-expired .pledge-pill-label,
.pill-state-cancelled .pledge-pill-label {
    color: #555;
}

/* ---- State: confirmed (non-3P legacy registrations) ---- */
.pill-state-confirmed {
    border-color: rgba(60,100,190,0.28);
}
.pill-state-confirmed .pledge-pill-bar {
    background: linear-gradient(90deg, #4e7ec8 0%, #6a96dc 100%);
    opacity: 0.55;
}
.pill-state-confirmed .pledge-pill-label {
    color: #163a82;
}

/* Past confirmed registrations — grey out */
.pill-state-confirmed.pill-is-past .pledge-pill-bar {
    background: linear-gradient(90deg, #888 0%, #aaa 100%);
    opacity: 0.45;
}
.pill-state-confirmed.pill-is-past .pledge-pill-label {
    color: #555;
}

/* ---- Section dividers ---- */
.reg-section-label {
    font-size: 0.80em;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    opacity: 0.50;
    margin: 18px 0 4px 0;
}

.reg-empty-note {
    font-size: 0.92em;
    opacity: 0.65;
    margin: 8px 0 0 0;
}

.reg-empty-note a {
    text-decoration: underline;
}

/* ---- Mobile adjustments (≤480px) ---- */
@media screen and (max-width: 480px) {
    .pledge-reg-title {
        font-size: 0.90em;
    }
    .pledge-reg-pill {
        min-width: 96px;
        font-size: 0.70em;
    }
    /* Ensure pledge-reg row doesn't overflow the panel */
    .pledge-reg-row {
        overflow: hidden;
    }
    .pledge-reg-info {
        min-width: 0;
        overflow: hidden;
    }
}

/* ===================================================================== */
/* ========================= MY PROJECTS PANEL ========================= */
/* ===================================================================== */

.my-project-list {
    display: flex;
    flex-direction: column;
    gap: 0;
    margin-top: 6px;
    margin-bottom: 10px;
}

.my-project-row {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 11px 0;
    border-bottom: 1px solid rgba(0,0,0,0.07);
}

.my-project-row:last-child {
    border-bottom: none;
}

.my-project-tmb {
    flex-shrink: 0;
    width: 58px;
    height: 58px;
    border-radius: 8px;
    object-fit: cover;
    cursor: pointer;
    transition: transform 0.15s ease, filter 0.15s ease;
    border: 1px solid rgba(0,0,0,0.10);
}

.my-project-tmb:hover {
    transform: scale(1.05);
    filter: brightness(0.88);
}

.reg-training-tmb-link {
    display: block;
    flex-shrink: 0;
}

.reg-training-tmb {
    display: block;
    width: 54px;
    height: 54px;
    border-radius: 8px;
    object-fit: cover;
    border: 1px solid rgba(0,0,0,0.10);
    transition: transform 0.15s ease, filter 0.15s ease;
    cursor: pointer;
}

.reg-training-tmb:hover {
    transform: scale(1.05);
    filter: brightness(0.88);
}

.my-project-info {
    display: flex;
    flex-direction: column;
    gap: 3px;
    min-width: 0;
    flex: 1;
}

.my-project-title {
    font-weight: 600;
    font-size: 0.97em;
    line-height: 1.3;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.my-project-meta {
    font-size: 0.82em;
    opacity: 0.60;
    line-height: 1.3;
    overflow-wrap: break-word;
    word-break: break-word;
    min-width: 0;
}

.project-phase-pill {
    position: relative;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    min-width: 104px;
    height: 30px;
    border-radius: 999px;
    font-size: 0.76em;
    font-weight: 700;
    letter-spacing: 0.015em;
    border: 1.5px solid rgba(0,120,0,0.28);
    background: linear-gradient(90deg, #29a654 0%, #42c46c 100%);
    color: #fff;
    cursor: pointer;
    transition: filter 0.15s ease, transform 0.15s ease;
    padding: 0 12px;
    text-shadow: 0 1px 2px rgba(0,0,0,0.18);
    white-space: nowrap;
}

.project-phase-pill:hover {
    filter: brightness(0.90);
    transform: translateY(-1px);
}

@media screen and (max-width: 480px) {
    .my-project-title {
        font-size: 0.90em;
    }

    /* Mobile: hide standalone gear buttons — pill takes over that role */
    .brik-gear-btn,
    .project-gear-btn {
        display: none !important;
    }

    /* Mobile: show gear emoji inside pills */
    .pill-gear-emoji {
        display: inline;
    }

    /* Mobile: project/ecobrick rows stay flex, pill pinned to the right */
    .my-project-row {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .my-project-row > .my-project-tmb {
        width: 46px;
        height: 46px;
        flex-shrink: 0;
    }

    .my-project-row > .my-project-info {
        flex: 1;
        min-width: 0;
    }

    /* Project pill: stays in flex row on the far right */
    .my-project-row > .project-phase-pill {
        flex-shrink: 0;
        min-width: 72px;
        font-size: 0.70em;
        padding: 0 8px;
        pointer-events: auto;
        cursor: pointer;
    }

    /* Ecobrick pill: stays in flex row on the far right, interactive on mobile */
    .my-project-row > .brik-row-pill {
        flex-shrink: 0;
        min-width: 72px;
        font-size: 0.70em;
        height: 26px;
        pointer-events: auto;
        cursor: pointer;
    }

    /* Training v2: pledge-btn + status-pill stay in flex row */
    .my-project-row > .training-v2-pledge-btn {
        flex-shrink: 0;
        font-size: 0.72em;
        min-width: 0;
        padding: 4px 8px;
    }

    .my-project-row > .training-v2-status-pill {
        flex-shrink: 0;
        font-size: 0.68em;
        padding: 4px 8px;
        white-space: normal;
        text-align: center;
    }
}

/* ===================================================================== */
/* ======================== MY ECOBRICKS PANEL ========================= */
/* ===================================================================== */

/* Inline status pill for ecobrick rows — gradient style matching .project-phase-pill.
   Do NOT use .ecobrick-status-pill here — that class is position:absolute in main.css (for photo overlays). */
.brik-row-pill {
    flex-shrink: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 0.76em;
    font-weight: 700;
    letter-spacing: 0.015em;
    padding: 0 10px;
    height: 28px;
    min-width: 80px;
    border-radius: 999px;
    white-space: nowrap;
    pointer-events: none;
    text-shadow: 0 1px 2px rgba(0,0,0,0.18);
    border: 1.5px solid transparent;
    /* override status-pill's loose padding/shadow */
    box-shadow: none !important;
}

/* Gear emoji inside pills — hidden on desktop, revealed on mobile */
.pill-gear-emoji {
    display: none;
}

.brik-row-pill.status-authenticated {
    background: linear-gradient(90deg, #29a654 0%, #42c46c 100%);
    border-color: rgba(0,120,0,0.28);
}

.brik-row-pill.status-awaiting {
    background: linear-gradient(90deg, #c07800 0%, #f59e0b 100%);
    border-color: rgba(180,100,0,0.28);
}

.brik-row-pill.status-rejected {
    background: linear-gradient(90deg, #c62828 0%, #e53935 100%);
    border-color: rgba(180,0,0,0.28);
}

.brik-row-pill.status-default {
    background: linear-gradient(90deg, #374151 0%, #4b5563 100%);
    border-color: rgba(0,0,0,0.20);
}

.brik-gear-btn {
    flex-shrink: 0;
    background: none;
    border: none;
    cursor: pointer;
    font-size: 1.1em;
    padding: 4px 6px;
    border-radius: 6px;
    transition: background 0.15s ease;
    line-height: 1;
}

.brik-gear-btn:hover {
    background: rgba(0,0,0,0.08);
}

.my-ecobricks-loading {
    font-size: 0.88em;
    opacity: 0.55;
    padding: 12px 0;
}

.my-ecobricks-pagination {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 6px;
    margin-top: 10px;
    padding-top: 8px;
    border-top: 1px solid rgba(0,0,0,0.07);
}

.my-ecobricks-page-buttons {
    display: flex;
    gap: 10px;
    width: 100%;
}

.my-ecobricks-page-buttons .page-button {
    flex: 1;
    text-align: center;
    justify-content: center;
}

.my-ecobricks-page-info {
    font-size: 0.82em;
    opacity: 0.60;
}

/* Footer section below ecobrick/project list (log btn + pagination) */
.panel-list-footer {
    display: flex;
    flex-direction: column;
    gap: 8px;
    margin-top: 10px;
    padding-top: 8px;
    border-top: 1px solid rgba(0,0,0,0.07);
}

/* Grey full-width action button used in panel footers */
.panel-grey-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    width: 100%;
    box-sizing: border-box;
    padding: 9px 14px;
    background: #e8e8e8;
    color: #3a3a3a;
    border: 1px solid #ccc;
    border-radius: 8px;
    font-weight: 600;
    font-size: 0.9em;
    text-decoration: none;
    cursor: pointer;
    transition: background 0.15s ease;
}

.panel-grey-btn:hover {
    background: #d4d4d4;
}

/* Gear button for project rows */
.project-gear-btn {
    flex-shrink: 0;
    background: none;
    border: none;
    cursor: pointer;
    font-size: 1.1em;
    padding: 4px 6px;
    border-radius: 6px;
    transition: background 0.15s ease;
    line-height: 1;
}

.project-gear-btn:hover {
    background: rgba(0,0,0,0.08);
}

</style>



 <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.5/css/jquery.dataTables.min.css">
    <!-- Responsive DataTables CSS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.dataTables.min.css">

    <!-- jQuery (required for DataTables) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- DataTables JS -->
    <script type="text/javascript" src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
    <!-- Responsive DataTables JS -->
    <script type="text/javascript" src="https://cdn.datatables.net/responsive/2.4.1/js/dataTables.responsive.min.js"></script>








<?php
    $dashboard_header_version = $dashboard_header_version ?? "../header-2026b.php";
    require_once ($dashboard_header_version);
?>



