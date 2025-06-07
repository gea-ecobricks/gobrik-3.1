
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
padding-top: 0px;
background: none;
border: none;
margin-top:-100px;
}


.log-report-btn {
    display: inline-block;
    padding: 5px 10px;
    background-color: #007BFF;
    color: white;
    text-decoration: none;
    border-radius: 4px;
    font-size: 14px;
}

.log-report-btn:hover {
    background-color: #0056b3;
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








<?php require_once ("../header-2024.php");?>



