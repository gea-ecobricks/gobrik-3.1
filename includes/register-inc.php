
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
margin-top:-10px;
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

</style>





<?php require_once ("../header-2024.php");?>



