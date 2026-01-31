
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


<style>
th.metric-column,
td.metric-column {
    max-width: 125px;
    white-space: normal;
    overflow-wrap: anywhere;
}

.location-column {
    max-width: 220px;
    white-space: normal;
    overflow-wrap: anywhere;
}

    #latest-ecobricks_wrapper {
        width: 100%;
        overflow-x: auto;
    }


        #main {
        height: fit-content !important;
        padding-bottom: 100px;
    }



    #main {
        height: fit-content;
    }


.modal-message {
    margin: auto;
    width: 100%;
}

.ecobrick-action-button {
    width: 100%;           /* Ensures the button takes the full width */
    display: block;        /* Ensures the button behaves as a block element */
    text-align: center;    /* Centers the text inside the button */
    padding: 10px 20px;    /* Add padding for better button appearance */
    margin: 0 auto 10px;   /* Margin to create space between buttons and center them */
    border: none;          /* Remove default borders */
    border-radius: 10px;
    cursor: pointer;       /* Show pointer on hover */
    background: #00800094;
    font-size: 1.3em;      /* Consistent font size */
    text-decoration: none;
    color: white;
    box-sizing: border-box;
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



#splash-bar {
  background-color: var(--top-header);
  filter: none !important;
  margin-bottom: -200px !important;
}

.status-cell {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
}

.status-emoji {
    font-size: 1.1em;
}

@media screen and (max-width: 768px) {
    .modal-message {
        padding: 0 15px;
        box-sizing: border-box;
    }

    .ecobrick-action-button {
        padding-left: 15px;
        padding-right: 15px;
    }

    .status-cell {
        justify-content: center;
        gap: 0;
    }

    .status-text {
        display: none;
    }
}




    </style>





<?php require_once ("../header-2026.php");?>



