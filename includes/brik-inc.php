

<!--Image files to preload that are unique to this page-->

<link rel="preload" as="image" href="../webps/brikmarket-400px.webp?v1.3">
<link rel="preload" as="image" href="../svgs/brik-market-banner3.svg">

<?php require_once ("../meta/brik-$lang.php");?>
 <!-- Include DataTables CSS and JavaScript files -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.css">
    <script type="text/javascript" charset="utf8" src="https://code.jquery.com/jquery-3.5.1.js"></script>
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.js"></script>


<STYLE>


#main {
        height: fit-content !important;
        padding-bottom: 100px;
    }




.brik-content-block {
    display: flex;
    width: 100%;
    padding: 10px;
    background: var(--darker);
    border-radius: 0px 0px 10px 10px;
    }

.brik-image {
padding 15px;

}

@media screen and (max-width: 768px) {
	.brik-content-block {
		flex-flow: column;
		flex-direction: column-reverse;
	}

    .brik-image img {
	  border-width: 1px;
      border-color: gray;
      width: 90%;
      box-shadow: 0 0px 10px rgba(85, 84, 84, 0.4);
      border-style: solid;
      border-radius: 10px;
      margin: auto;
      display: block;
      margin-top: 0px;
      background: grey;
    }

    .brik-info-box {
     text-align: center;
     width: 90%;
    }

    .brik-status {
    margin: auto;
    }
}



@media screen and (min-width: 769px) {
	.brik-content-block {
		flex-flow:row;

	}

    .brik-image {
        width:50%;
     }


    .brik-image img {
        border-width: 1px;
        border-color: gray;
      width: 100%;
      margin-top: -20px;
      box-shadow: 0 0px 10px rgba(85, 84, 84, 0.4);
      border-style: solid;
      border-radius: 10px;
      margin-top: 20px;
    }

    .brik-info-box {
     text-align: right;
     width: 50%;
    }

    .brik-status {
     margin-left: auto;
     }

}

.brik-info-box {
 display: flex;
 flex-flow:column;
  padding: 20px;
  justify-content: center;
 }

 .brik-serial-no {
 font-family: 'Arvo', serif;
 font-size: 3em;
 padding: 20px 10px 0px 10px;
 color: var(--text-color);
 }

  .brik-sub-title {
 font-family: 'Mulish', sans-serif;
 font-size: 1.2em;
 padding: 10px;
 color: var(--subdued-text);
 }



.brik-status {
padding: 5px 8px 5px 8px;
  border-radius: 5px;
  width: fit-content;
  color: var(--main-background);
}

.waiting {
  background: gray;
  }

.authenticated {
  background: green;
  }

.vision-quote {
font-size: 1.4em;
}


#splash-bar {
  background-color: var(--top-header);
  filter: none !important;
  margin-bottom: -200px !important;
}


/* hr {border: gray;
border-style: dashed;
border-width: 1px;
margin-top: 31px;
margin-bottom: 31px;}
 */


.module-btn {
  background: var(--emblem-blue);
}

.module-btn:hover {
  background: var(--emblem-blue-over);
}


</style>

<?php require_once ("../header-2024.php");?>



