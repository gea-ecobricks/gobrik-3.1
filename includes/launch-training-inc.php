

<?php require_once ("../meta/launch-training-$lang.php");?>


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
    opacity:1;
}

/* ===================================================================== */
/* ========================== RESPONSIVENESS ============================ */
/* ===================================================================== */

@media (max-width: 900px) {
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








.form-item input {
  background: var(--input-background) !important;
  font-size: 1.5em;
}

.form-field-style {
  width: 100%;
  padding: 9px 11px !important;
  margin: 4px 0;
  font-size: 20px !important;
  box-sizing: border-box;
  border: 3px solid var(--button-2-1);
  border-radius: 5px;
  background-color: var(--top-header);
   border: 1px solid var(--button-2-1) !important;
}


.advanced-box-content {
    padding: 2px 15px 15px 15px;
    max-height: 0;  /* Initially set to 0 */
    overflow: hidden;  /* Hide any overflowing content */
    transition: max-height 0.5s ease-in-out;  /* Transition effect */
	font-size:smaller;
	margin-top:-10px;
}


.dropdown {
  float: right;
  overflow: hidden;
  margin-bottom: -10px;
}

#registration-footer {

  display:none !important;
}

#serial-select ul {
  list-style: none;
  padding: 0;
}


.form-item li:hover {
  background: var(--emblem-blue);
  cursor: pointer;
  padding:3px;
}

#serial-select {
  background: var(--advanced-background);
  width: 130px;
  margin-top: -10px;
  padding: 10px 10px 10px 20px;
  border-radius: 0px 0px 10px 10px;
  position: absolute;
  z-index: 100;
  margin-left: 15px;
  display: none;
}

.splash-image {display:flex;
  margin-right: auto !important;
  margin-left: auto;}

.splash-image img {margin-right: auto; margin-left: 0px;}


@media screen and (max-width: 700px) { 
	.splash-content-block {  
        background-color: var(--top-header);
        filter: none !important;
        min-height: 20vh !important;
        height: 20vh !important;
        
	}

  .splash-image {display: none !important;}

  /* .splash-image img  {height: 200px;} */
}


@media screen and (min-width: 700px) { 
	.splash-content-block {
        background-color: var(--top-header);
        filter: none !important;
        min-height: 20vh !important;
}
} 

@media screen and (max-width: 700px) {
.splash-heading {
	font-size: 2.5em !important;
	line-height: 1.1;
	margin: 10px 0px;
	text-align: center;
}
}

@media screen and (min-width: 700px) {
.splash-heading {
	font-size: 3.1em !important;
}
}

#splash-bar {

    background-color: var(--top-header);
    filter: none !important;
    margin-bottom: -200px !important;

}

#main-background {
  background-size: cover;

}

/*  
#form-submission-box {
  font-family: "Mulish", sans-serif;
} */

/* h2 {
  font-family: "Arvo", serif;
  color: var(--h1);
} */

.form-item {
    margin-top: 10px;
    margin-bottom: 10px;
}
.form-caption {
  font-family: "Mulish", sans-serif;
  font-weight: 300;
  color: var(--text-color);
  font-size: 1.0em;
  margin-top: -5px;
}



label {
  font-family: "Mulish", sans-serif;
  font-weight: 500;
  color: var(--text-color);
  font-size: 1.3em;
}


.form-item input {
  background: var(--input-background);
  font-size: 1.3em;
}

.form-item textarea {
  background: var(--input-background);
  font-size: 1.3em;
}

.form-item select {
  background: var(--input-background);
  font-size: 1.2em;
  padding: 5px;
  border-radius: 5px;
  margin-top: 9px;
  margin-bottom: 10px;
}

input[type="text"],
input[type="number"],
textarea,
input[type="date"] {
  font-family: "Mulish", sans-serif;
  font-weight: 300;
  width: 100%;
  padding: 8px;
  margin-bottom: 10px;
  border: 1px solid var(--divider-line);
  border-radius: 5px;
  box-sizing: border-box;
  margin-top: 8px;

}

input[type="submit"] {
  color: white;
  padding: 10px 20px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  background-color: #12b712; /* Initial background color */
  background-size: 0% 100%; /* Initial background size (progress bar) */
  transition: background-size 0.5s ease; /* Transition effect for smooth progress */
  font-size: 1.3em;
  width: 100%;
  margin-top: 30px;
}

/* Specify the progress bar color */
input[type="submit"].progress-bar {
  background: url(../svgs/square-upload-progress.svg) left center repeat-y, gray; /* Combined background */
  background-size: auto; /* Auto size for image background */
}


input[type="submit"]:hover {
  background-color: green;
} 

.spinner-photo-loading {
    border: 4px solid rgba(0, 0, 0, 0.1);
    border-left-color: #ffffff;
    border-radius: 50%;
    width: 20px;
    height: 20px;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}



.form-field-error {
  color: red;
  margin-top: -15px;
  margin-bottom: 20px;
  padding-left: 10px;
  padding-bottom: 15px;
  display: none;
}

.form-container {

  width: 80%;
  background-color: var(--form-background);
  border: 1px solid var(--divider-line);
  border-radius: 15px;
  margin: 0 auto;
  max-width: 1000px;
  z-index: 20;
  font-family: "Mulish", sans-serif;
  position: relative;
  padding-top: 0px !important;

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
    margin-top: 60px;

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
    margin-top: 100px;

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
	
.confirm-button {
  color: white;
  padding: 10px 20px;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  background: var(--emblem-green);
  font-size: 1.3em;
  margin: auto;
  justify-content: center;
  text-align: center;
  text-decoration: none;
  margin-top: 10px;
  display: flex;
}

.confirm-button:hover {
  background: var(--emblem-green-over);


}


/*upload*/

.form-item {
    border-radius: 5px;
    padding-left: 10px;
    padding-right: 10px;
    padding-top: 10px;
    background-color: #00000015;
}

.form-item label,
.form-item input,
.form-item .form-caption {
    padding: 10px;
}

#photos-submission-box .form-item input {
    font-size: 1.2em;
    color: var(--text-color);
    border-radius: 5px;
    background-color: #ffffff35;
    margin-top: 10px;
    cursor: pointer;
}

.form-item .form-caption {
    font-size: 1.0;


}
.input-container {
    position: relative;
    display: inline-block;
    width: 100%
}

#location_address {
    width: 100%;
    padding-right: 30px; /* Make space for the spinner */
}

.spinner {
    display: none;
    position: absolute;
    top: 30%;  /* Center vertically in the input field */
    right: 15px; /* Distance from the right edge of the input field */
    transform: translateY(-50%); /* Ensures the spinner is exactly centered vertically */
    width: 20px;
    height: 20px;
    border: 4px solid rgba(0,0,0,0.1);
    border-top: 4px solid var(--emblem-pink);
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

.trainer-tag-container {
    display: flex;
    flex-wrap: wrap;
    margin-top: 5px;
}

.trainer-tag-box {
    display: flex;
    align-items: center;
    background: #ccc;
    border-radius: 15px;
    padding: 2px 8px;
    margin: 2px;
    font-size: 0.9em;
}

.trainer-tag-box .remove-trainer {
    margin-right: 6px;
    cursor: pointer;
    color: #fff;
    font-weight: bold;
}

@keyframes spin {
    0% { transform: rotate(0deg); translateY(-50%); }
    100% { transform: rotate(360deg); translateY(-50%); }
}



</style>	

<?php require_once ("../header-2026.php");?>
