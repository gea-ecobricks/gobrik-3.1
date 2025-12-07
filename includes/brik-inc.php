

<!--Image files to preload that are unique to this page-->

<link rel="preload" as="image" href="../webps/brikmarket-400px.webp?v1.3">
<link rel="preload" as="image" href="../svgs/brik-market-banner3.svg">

<?php require_once ("../meta/brik-$lang.php");?>
 <!-- Include DataTables CSS and JavaScript files -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.css">
    <script type="text/javascript" charset="utf8" src="https://code.jquery.com/jquery-3.5.1.js"></script>
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.js"></script>


<STYLE>

    body {
      background: var(--dashboard-page-background, #f5f5f5);
      font-family: 'Mulish', 'Helvetica Neue', Arial, sans-serif;
    }

    :root {
        --brik-panel-bg: #ffffff;
        --brik-panel-border: #d0d7de;
        --brik-panel-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        --brik-pill-blue-bg: #e3f2ff;
        --brik-pill-blue-text: #0b5ed7;
        --brik-pill-green-bg: #e6f4ea;
        --brik-pill-green-text: #1e8e3e;
        --brik-pill-red-bg: #fdecea;
        --brik-pill-red-text: #b3261e;
    }


#main {
        height: fit-content !important;
        padding-bottom: 100px;
    }




.brik-content-block {
    display: flex;
    width: 100%;
    background-color: var(--form-background);
      border: 1px solid var(--divider-line);
    border-radius: 16px;
    box-shadow: var(--brik-panel-shadow);
    gap: 18px;
    }

.brik-image {
padding: 10px;

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



.brik-status-pill {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 6px 14px;
    border-radius: 999px;
    width: fit-content;
    font-weight: 700;
    font-size: 0.95em;
    border: 1px solid transparent;
    box-shadow: 0 1px 4px rgba(0, 0, 0, 0.06);
}

.status-waiting {
    background: var(--brik-pill-blue-bg);
    color: var(--brik-pill-blue-text);
    border-color: rgba(11, 94, 215, 0.12);
}

.status-authenticated {
    background: var(--brik-pill-green-bg);
    color: var(--brik-pill-green-text);
    border-color: rgba(30, 142, 62, 0.16);
}

.status-rejected {
    background: var(--brik-pill-red-bg);
    color: var(--brik-pill-red-text);
    border-color: rgba(179, 38, 30, 0.2);
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

.form-container-v2 {
    background: var(--brik-panel-bg);
    border: 1px solid var(--brik-panel-border);
    border-radius: 18px;
    box-shadow: var(--brik-panel-shadow);
    padding: 24px;
    margin: 0 auto;
    max-width: 1100px;
}

</style>

<?php require_once ("../header-2026.php");?>



