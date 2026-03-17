
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
/* ======================== 3P SECTION STYLES =========================== */
/* ===================================================================== */

.threep-box{
    margin-top:20px;
}

.threep-hr{
    margin:18px 0 22px 0;
    border:0;
    border-top:1px solid rgba(0,0,0,0.18);
}

.threep-subtitle{
    font-size:1.55em;
    font-weight:600;
    margin-bottom:10px;
    color:var(--text);
    letter-spacing:0.01em;
}

.threep-section-divider{
    border-top:2px dotted rgba(0,0,0,0.22);
    margin:12px 0 18px 0;
}

.threep-field-card{
    background:rgba(255,255,255,0.20);
    padding:16px 16px 14px 16px;
    border-radius:10px;
    margin-bottom:14px;
    border:1px solid rgba(0,0,0,0.04);
    box-shadow: inset 0 1px 0 rgba(255,255,255,0.15);
}

.currency-locked{
    padding:10px 14px;
    background:rgba(0,0,0,0.06);
    border-radius:8px;
    font-weight:500;
    display:inline-block;
    border:1px solid rgba(0,0,0,0.06);
}

.currency-input-wrap{
    display:grid;
    grid-template-columns:1fr 56px;
    align-items:center;
    gap:10px;
}

.currency-display-input{
    text-align:left;
    letter-spacing:0.01em;
    font-variant-numeric:tabular-nums;
}

.currency-suffix{
    text-align:left;
    font-weight:600;
    opacity:0.72;
    font-size:0.96em;
}

/* ===================================================================== */
/* ========================== LIVE CALCULATOR =========================== */
/* ===================================================================== */

.threep-calculator{
    margin-top:24px;
    background:linear-gradient(180deg, rgba(255,255,255,0.14) 0%, rgba(255,255,255,0.08) 100%);
    padding:22px 18px 20px 18px;
    border-radius:14px;
    border:1px solid rgba(0,0,0,0.07);
    box-shadow:
        inset 0 1px 0 rgba(255,255,255,0.18),
        0 1px 2px rgba(0,0,0,0.04);
}

.threep-calculator-grid{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:14px;
    margin-top:14px;
}

.calc-card{
    background:rgba(255,255,255,0.92);
    padding:18px 16px 16px 16px;
    border-radius:10px;
    border:1px solid rgba(0,0,0,0.08);
    min-height:104px;
    display:flex;
    flex-direction:column;
    justify-content:flex-start;
    box-shadow:
        0 1px 1px rgba(0,0,0,0.03),
        inset 0 1px 0 rgba(255,255,255,0.5);
}

.calc-title{
    font-size:0.95em;
    opacity:0.78;
    margin-bottom:10px;
    line-height:1.35;
}

.calc-value{
    font-size:2.05em;
    font-weight:700;
    line-height:1.1;
    letter-spacing:0.01em;
    font-variant-numeric:tabular-nums;
}

.calc-value.is-green{
    color:#2f7d32;
}

.calc-value.is-earth{
    color:#8a4b32;
}

.threep-calculator .form-caption{
    margin-top:16px;
}

/* ===================================================================== */
/* ======================= STATUS + PROGRESS AREA ======================= */
/* ===================================================================== */

.threep-status-grid{
    display:grid;
    grid-template-columns:1fr 1.1fr;
    gap:22px;
    align-items:start;
    margin-top:10px;
}

.threep-status-textbox{
    background:rgba(255,255,255,0.22);
    padding:16px;
    border-radius:10px;
    border:1px solid rgba(0,0,0,0.06);
    line-height:1.95;
    box-shadow: inset 0 1px 0 rgba(255,255,255,0.15);
}

/* ===================================================================== */
/* =========================== PROGRESS BARS ============================ */
/* ===================================================================== */

.threep-progress-block{
    background:rgba(255,255,255,0.22);
    padding:16px 16px 14px 16px;
    border-radius:10px;
    margin-bottom:16px;
    border:1px solid rgba(0,0,0,0.06);
    box-shadow: inset 0 1px 0 rgba(255,255,255,0.15);
}

.threep-progress-label{
    display:flex;
    justify-content:space-between;
    align-items:flex-end;
    gap:10px;
    font-weight:600;
    margin-bottom:12px;
    font-size:1em;
    line-height:1.2;
}

.threep-progress-label span{
    font-weight:500;
    opacity:0.78;
    font-size:0.93em;
    font-variant-numeric:tabular-nums;
}

.threep-progress-bar{
    position:relative;
    width:100%;
    height:24px;
    border-radius:999px;
    overflow:hidden;
    background:#e4e4e4;
    box-shadow:
        inset 0 1px 3px rgba(0,0,0,0.10),
        inset 0 -1px 0 rgba(255,255,255,0.16);
}

.threep-progress-zone-before{
    position:absolute;
    top:0;
    bottom:0;
    left:0;
    background:linear-gradient(180deg, #c7c7c7 0%, #bbbbbb 100%);
}

.threep-progress-zone-after{
    position:absolute;
    top:0;
    bottom:0;
    right:0;
    background:linear-gradient(180deg, rgba(112,176,120,0.34) 0%, rgba(89,155,96,0.28) 100%);
}

.threep-progress-fill{
    position:absolute;
    top:0;
    bottom:0;
    left:0;
    min-width:8px;
    background:linear-gradient(90deg, #d85050 0%, #e56868 100%);
    border-radius:999px;
    box-shadow:
        inset 0 1px 0 rgba(255,255,255,0.20),
        0 0 0 1px rgba(0,0,0,0.03);
}

.threep-progress-threshold{
    position:absolute;
    top:-2px;
    bottom:-2px;
    width:0;
    border-left:3px solid rgba(24,24,24,0.88);
    box-shadow:
        0 0 0 1px rgba(255,255,255,0.20);
    z-index:5;
}

.threep-progress-meta{
    margin-top:10px;
    font-size:0.94em;
    line-height:1.55;
    opacity:0.84;
    font-variant-numeric:tabular-nums;
}

.threep-progress-meta strong{
    font-weight:600;
}

/* ===================================================================== */
/* =========================== MODAL UI ================================= */
/* ===================================================================== */

.threep-training-pill{
    display:inline-block;
    background:#2e7d32;
    color:white;
    padding:6px 14px;
    border-radius:999px;
    font-weight:600;
    font-size:.95em;
    margin-bottom:14px;
}

/* slider row */

.threep-slider-row{
    display:grid;
    grid-template-columns:auto 1fr auto;
    align-items:center;
    gap:12px;
    margin-top:10px;
}

/* slider track */

#threep_pledge_slider{
    width:100%;
    appearance:none;
    height:10px;
    border-radius:999px;
    background:#b9e4b9;
    outline:none;
    transition:background .15s ease;
}

/* slider thumb */

#threep_pledge_slider::-webkit-slider-thumb{
    appearance:none;
    width:20px;
    height:20px;
    border-radius:50%;
    background:white;
    border:2px solid #666;
    cursor:pointer;
}

#threep_pledge_slider::-moz-range-thumb{
    width:20px;
    height:20px;
    border-radius:50%;
    background:white;
    border:2px solid #666;
}

/* pills */

.threep-slider-pill{
    padding:4px 10px;
    border-radius:999px;
    font-size:.8em;
    font-weight:600;
    white-space:nowrap;
}

.pill-zero{
    background:#f28c28;
    color:white;
}

.pill-max{
    background:#1b5e20;
    color:white;
}

/* readout */

.threep-amount-readout{
    font-size:1.8em;
    font-weight:700;
    margin-bottom:8px;
}

/* currency selector */

.threep-currency-select{
    font-size:.85em;
    padding:6px 10px;
    border-radius:6px;
}

/* subdued explanatory text */

.subdued{
    color:var(--subdued-text);
    font-size:.9em;
    margin-top:6px;
}

/* ===================================================================== */
/* ========================== RESPONSIVENESS ============================ */
/* ===================================================================== */

@media (max-width:900px){

    .threep-status-grid{
        grid-template-columns:1fr;
    }

    .threep-calculator-grid{
        grid-template-columns:1fr;
    }

    .currency-input-wrap{
        grid-template-columns:1fr 52px;
    }

    .calc-value{
        font-size:1.8em;
    }

    .threep-progress-bar{
        height:22px;
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



