
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

.register-page-shell {
    margin-top:108px;
}

.register-page-container{
    padding-top:0;
}

.register-content-wrap{
    width:100%;
    margin:auto;
    margin-top:5px;
}

.register-intro-card{
    width:100%;
    background:var(--course-module);
    border-radius:15px;
    padding:10px;
}

.register-lead-photo{
    width:100%;
    border-radius:10px;
}

.register-subtitle{
    margin:10px 0;
}

.register-meta-line{
    font-size:1em;
}

.register-main-button{
    margin-top:20px;
    font-size:1.3em;
    padding:10px 20px;
    cursor:pointer;
}

.register-bottom-button{
    margin-top:20px;
    margin-bottom:75px;
    font-size:1.3em;
    padding:10px 20px;
    cursor:pointer;
    width:100%;
}

.register-featured-description{
    margin-top:20px;
    font-size:1.5em;
    padding:15px;
}

.register-agenda{
    font-size:1.23em;
    padding:15px;
    margin-top:0;
}

/* ===================================================================== */
/* STATUS PANEL */
/* ===================================================================== */

.register-status-panel{
    margin-top:16px;
    width:100%;
    background:rgba(255,255,255,0.16);
    border:1px solid rgba(0,0,0,0.08);
    border-radius:14px;
    padding:14px;
}

.register-progress-bar{
    position:relative;
    width:100%;
    height:22px;
    border-radius:999px;
    overflow:hidden;
    background:#e3e3e3;
}

.register-progress-fill{
    position:absolute;
    top:0;
    bottom:0;
    left:0;
    min-width:8px;
    border-radius:999px;
}

/* ===================================================================== */
/* 3P MODAL */
/* ===================================================================== */

.threep-modal-wrap{
    display:flex;
    flex-direction:column;
    text-align:center;
}

.threep-training-kicker-pill{
    display:inline-block;
    align-self:center;
    background:#4ea05a;
    color:#fff;
    border-radius:999px;
    padding:8px 16px;
    font-size:0.95em;
    font-weight:600;
    margin-bottom:12px;
}

/* ===================================================================== */
/* SLIDER */
/* ===================================================================== */

#threep_pledge_slider{
    -webkit-appearance:none;
    appearance:none;
    width:100%;
    height:14px;
    border-radius:999px;
    outline:none;
    cursor:pointer;

    background:linear-gradient(
        90deg,
        #93d86c 0%,
        #93d86c 50%,
        #d9e6d6 50%,
        #d9e6d6 100%
    );

    transition:
        background 120ms linear,
        transform 140ms cubic-bezier(.34,1.56,.64,1);
}

/* elastic easing */

#threep_pledge_slider:active{
    transform:scaleY(1.12);
}

/* thumb */

#threep_pledge_slider::-webkit-slider-thumb{
    -webkit-appearance:none;
    appearance:none;
    width:26px;
    height:26px;
    border-radius:50%;
    background:#fff;
    border:3px solid var(--pledge-color,#7ccf7a);
    box-shadow:0 2px 7px rgba(0,0,0,0.18);
    cursor:pointer;

    transition:
        border-color .12s ease,
        transform .12s ease;
}

#threep_pledge_slider::-webkit-slider-thumb:hover{
    transform:scale(1.08);
}

#threep_pledge_slider::-moz-range-thumb{
    width:26px;
    height:26px;
    border-radius:50%;
    background:#fff;
    border:3px solid var(--pledge-color,#7ccf7a);
}

/* glow animation */

@keyframes pledgeGlow{
    0%{ box-shadow:0 0 0 rgba(80,200,120,0);}
    50%{ box-shadow:0 0 12px rgba(80,200,120,.45);}
    100%{ box-shadow:0 0 0 rgba(80,200,120,0);}
}

#threep_pledge_slider{
    animation:pledgeGlow 3s infinite;
}

/* ===================================================================== */
/* CONFIRM BUTTON */
/* ===================================================================== */

.threep-confirm-button{
    transition:
        background .18s ease,
        border-color .18s ease,
        transform .12s ease;
}

/* soft pulse */

@keyframes pledgePulse{
    0%{transform:scale(1)}
    50%{transform:scale(1.04)}
    100%{transform:scale(1)}
}

.threep-confirm-button.enabled{
    animation:pledgePulse 2.6s infinite;
}

.threep-confirm-button:hover{
    transform:scale(1.04);
}

/* ===================================================================== */
/* TOOLTIP */
/* ===================================================================== */

.custom-tooltip-bubble{
    position:absolute;
    z-index:99999;
    max-width:320px;
    background:#2b2b2b;
    color:#fff;
    padding:10px 12px;
    border-radius:10px;
    font-size:.92rem;
    line-height:1.45;
    box-shadow:0 10px 26px rgba(0,0,0,0.22);
    opacity:0;
    transform:translateY(3px);
    transition:opacity .12s ease,transform .12s ease;
}

.custom-tooltip-bubble.visible{
    opacity:1;
    transform:translateY(0);
}

/* ===================================================================== */
/* RESPONSIVE */
/* ===================================================================== */

@media (max-width:900px){

    .threep-modal-head{
        flex-direction:column;
        align-items:center;
    }

    .register-modal-actions{
        flex-direction:column;
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



