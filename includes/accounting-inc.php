
<!--  Set any page specific graphics to preload-->

<!--  Set any page specific graphics to preload
<link rel="preload" as="image" href="../webps/ecobrick-team-blank.webp" media="(max-width: 699px)">
<link rel="preload" as="image" href="../svgs/richard-and-team-day.svg">
<link rel="preload" as="image" href="../svgs/richard-and-team-night.svg">
<link rel="preload" as="image" href="../webps/biosphere2.webp">
<link rel="preload" as="image" href="../webps/biosphere-day.webp">-->

<?php require_once ("../meta/$page-$lang.php");?>



<!-- Include DataTables and jQuery Libraries -->
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.5/css/jquery.dataTables.min.css">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.dataTables.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/responsive/2.4.1/js/dataTables.responsive.min.js"></script>



<STYLE>
#main {
    height: auto;
    overflow: visible;
    padding-bottom: 100px; /* Keep this for footer spacing */
    display: flex;
    flex-direction: column;

        padding-bottom: 100px;
    }


.page-button {
    background: var(--darker);
    color: var(--text-color);
    text-decoration: none;
    border-radius: 10px;
    padding: 10px 20px;
    text-align: center;
    flex: 1 1 90%; /* Make each button take 90% of the width of the container */
    max-width: 200px; /* Prevent buttons from becoming too wide */
    cursor: pointer;
    font-size:1.3em;
}

.page-button:hover {
    background: var(--form-background); /* Adjust hover color if needed */
    text-decoration: none;
}

.page-button.disabled {
    pointer-events: none;
    opacity: 0.6;
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


.dashboard-wrapper {
    padding: 40px 20px 60px;
}

.dashboard-v2-panel.form-container {
    width: 100%;
    box-sizing: border-box;
    padding: 28px;
}

.dashboard-v2-panel.form-container .menu-buttons-row {
    justify-content: center;
    gap: 12px;
}

.dashboard-v2-panel.form-container .menu-buttons-row .page-button {
    flex: 0 1 auto;
}


.ecobrick-action-button {
    width: 100%;           /* Ensures the button takes the full width */
    display: block;        /* Ensures the button behaves as a block element */
    text-align: center;    /* Centers the text inside the button */
    padding: 10px 0px 10px 0px;         /* Add some padding for better button appearance */
    margin-bottom: 10px;   /* Margin to create space between buttons */
    border: none;          /* Remove default borders */
    cursor: pointer;       /* Show pointer on hover */
    font-size: 1em;        /* Consistent font size */

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

/* Styling for cancel button */
.ecobrick-action-button.cancel-button {
    background-color: grey;
    color: white;
}

.ecobrick-action-button.cancel-button:hover {
    background-color: #6e6e6e;
    color: white;
}



#splash-bar {
  background-color: var(--top-header);
  filter: none !important;
  margin-bottom: -200px !important;
}




    </style>





<?php require_once ("../header-2025.php");?>



