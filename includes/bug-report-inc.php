
<!--  Set any page specific graphics to preload-->

<?php require_once ("../meta/$page-$lang.php");?>


<!-- Include DataTables and jQuery Libraries -->
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.5/css/jquery.dataTables.min.css">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.dataTables.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/responsive/2.4.1/js/dataTables.responsive.min.js"></script>



<STYLE>

/*         #main { */
/*         height: fit-content !important; */
/*         padding-bottom: 100px; */
/*     } */


/* MESSENGER CSS */
/* .hidden { */
/*     display: none; */
/* } */

#bugReportInput {
    width: 100%; /* Ensure the input fills the wrapper */
    padding: 10px;
    border-radius: 5px;
    border: 1px solid grey;
    background: var(--darker);
    color: var(--text-color);
    font-size: 1.1em;
    margin-bottom: 10px;
    resize: vertical;
    box-sizing: border-box; /* Include padding and border in width calculations */
}

#bugReportSubmit {
    background: var(--emblem-green);
    color: white;
    padding: 10px 15px;
    border-radius: 5px;
    border: 1px solid var(--emblem-green);
    cursor: pointer;
    font-size: 1em;
    transition: background 0.3s ease, border 0.3s ease;
    margin: auto;
    display: block;
    min-width:100px;
    min-height: 42px;
}

#bugReportSubmit:hover {
    background: var(--emblem-green-over);
    border-color: var(--emblem-green-over);
}

#feedbackMessage {
    margin-top: 10px;
    font-size: 1em;
    color: var(--emblem-green);
    text-align: center;
}

.bug-report-input-wrapper {
    position: relative;
    width: 100%; /* Ensure the wrapper takes up the full available width */
    box-sizing: border-box; /* Include padding and border in width calculations */
}

#uploadPhotoButton {
    position: absolute;
    bottom: 20px; /* Adjusted to be 10px above the bottom of the form field */
    right: 10px;
    width: 40px;
    height: 40px;
    background: grey;
    color: white;
    border: none;
    border-radius: 50%;
    font-size: 1.2em;
    display: flex;
    align-items: center; /* Center the icon vertically */
    justify-content: center; /* Center the icon horizontally */
    cursor: pointer;
    transition: background 0.3s ease;
}

#uploadPhotoButton:hover {
    background: var(--emblem-blue);
}

#uploadPhotoButton.uploading::after {
    content: '';
    position: absolute;
    top: 6px; /* Center the spinner vertically */
    left: 6px; /* Center the spinner horizontally */
    transform: translate(-50%, -50%); /* Adjusts for perfect centering */
    width: 20px;
    height: 20px;
    border: 4px solid rgba(0, 0, 0, 0.5);
    border-top: 4px solid var(--emblem-pink);
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

.image-file-name {
    position: absolute;
    bottom: 25px; /* Adjusted position for better alignment with the upload button */
    right: 60px; /* Position to the left of the button */
    font-size: 0.8em;
    color: var(--subdued-text);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 150px; /* Adjust as needed */
}

#bugReportInput {
    width: 100%;
    padding: 10px;
    border-radius: 5px;
    border: 1px solid grey;
    background: var(--darker);
    color: var(--text-color);
    font-size: 1.1em;
    margin-bottom: 10px;
    resize: vertical;
}

#bugReportSubmit {
    background: var(--emblem-green);
    color: white;
    padding: 10px 15px;
    border-radius: 5px;
    border: 1px solid var(--emblem-green);
    cursor: pointer;
    font-size: 1em;
    transition: background 0.3s ease, border 0.3s ease;
    margin: auto;
    display: block;
    position: relative;
}

#submitSpinner {
    width: 20px;
    height: 20px;
    border: 3px solid rgba(255, 255, 255, 0.8);
    border-top: 3px solid white;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    position: absolute;
   margin-top: -33px;
    left: 48%;
    transform: translate(-50%, -50%);
}

#bugReportSubmit.loading {
    color: transparent; /* Hide the text */
}


#feedbackMessage {
    margin-top: 10px;
    font-size: 1em;
    text-align: center;
}

#feedbackMessage.success {
    color: var(--emblem-green);
}

#feedbackMessage.error {
    color: red;
}


@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}





#splash-bar {
  background-color: var(--top-header);
  filter: none !important;
  margin-bottom: -200px !important;
}




    </style>





<?php require_once ("../header-2024.php");?>



