

<?php require_once ("../meta/launch-training-$lang.php");?>


<STYLE>

/* ========================================================= */
/* 3P SYSTEM */
/* ========================================================= */

.threep-box{
background:var(--lighter);
padding:22px;
border-radius:12px;
border:1px solid #ccc;
}

.threep-toggle-row{
display:flex;
align-items:center;
justify-content:space-between;
gap:20px;
}

.threep-toggle-title{
font-size:1.1em;
font-weight:600;
}

.threep-hr{
margin:18px 0;
border:0;
border-top:1px solid rgba(0,0,0,0.2);
}

.threep-subtitle{
font-size:1.4em;
font-weight:600;
margin-top:12px;
}

.threep-section-divider{
border-top:2px dotted rgba(0,0,0,0.25);
margin:10px 0 16px 0;
}

.threep-field-card{
background:rgba(255,255,255,0.25);
padding:14px;
border-radius:8px;
margin-bottom:12px;
}

.currency-input-wrap{
display:grid;
grid-template-columns:1fr 50px;
align-items:center;
gap:10px;
}

.currency-display-input{
text-align:left;
}

.currency-suffix{
font-weight:500;
opacity:.7;
}

.currency-locked{
padding:10px;
background:#eee;
border-radius:6px;
display:inline-block;
font-weight:500;
}

/* ========================================================= */
/* CALCULATOR */
/* ========================================================= */

.threep-calculator{
margin-top:20px;
background:rgba(0,0,0,0.04);
padding:16px;
border-radius:10px;
}

.threep-calculator-grid{
display:grid;
grid-template-columns:1fr 1fr;
gap:14px;
margin-top:10px;
}

.calc-card{
background:white;
padding:14px;
border-radius:8px;
border:1px solid rgba(0,0,0,0.1);
}

.calc-title{
font-size:.9em;
opacity:.7;
margin-bottom:6px;
}

.calc-value{
font-size:1.6em;
font-weight:600;
}

/* ========================================================= */
/* STATUS */
/* ========================================================= */

.threep-status-grid{
display:grid;
grid-template-columns:1fr 1fr;
gap:20px;
}

.threep-status-textbox{
background:rgba(255,255,255,0.25);
padding:14px;
border-radius:8px;
line-height:1.8;
}

/* ========================================================= */
/* PROGRESS BARS */
/* ========================================================= */

.threep-progress-bar{
position:relative;
height:18px;
background:#e6e6e6;
border-radius:999px;
overflow:hidden;
}

.threep-progress-zone-before{
position:absolute;
left:0;
top:0;
bottom:0;
width:60%;
background:#cfcfcf;
}

.threep-progress-zone-after{
position:absolute;
right:0;
top:0;
bottom:0;
width:40%;
background:rgba(60,170,90,0.25);
}

.threep-progress-fill{
position:absolute;
left:0;
top:0;
bottom:0;
width:2%;
background:#d9534f;
}

.threep-progress-threshold{
position:absolute;
left:60%;
top:-3px;
bottom:-3px;
border-left:3px solid #222;
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
