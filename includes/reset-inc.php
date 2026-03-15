
<!--  Set any page specific graphics to preload-->

<!--  Set any page specific graphics to preload
<link rel="preload" as="image" href="../webps/ecobrick-team-blank.webp" media="(max-width: 699px)">
<link rel="preload" as="image" href="../svgs/richard-and-team-day.svg">
<link rel="preload" as="image" href="../svgs/richard-and-team-night.svg">
<link rel="preload" as="image" href="../webps/biosphere2.webp">
<link rel="preload" as="image" href="../webps/biosphere-day.webp">-->

<?php require_once ("../meta/$page-$lang.php");?>




<STYLE>


/* ===================================================================== */
/* ======================= REGISTER 3P STATUS PANEL ===================== */
/* ===================================================================== */

.register-status-panel{
    margin-top:16px;
    width:100%;
    background:rgba(255,255,255,0.14);
    border:1px solid rgba(0,0,0,0.08);
    border-radius:12px;
    padding:14px;
}

.register-status-title{
    font-size:1.02em;
    font-weight:600;
    margin-bottom:12px;
}

.register-progress-block{
    margin-bottom:14px;
}

.register-progress-block:last-child{
    margin-bottom:0;
}

.register-progress-label{
    display:flex;
    justify-content:space-between;
    gap:8px;
    font-size:0.92em;
    font-weight:600;
    margin-bottom:8px;
    line-height:1.3;
}

.register-progress-label span{
    font-weight:500;
    opacity:0.8;
    font-variant-numeric:tabular-nums;
}

.register-progress-bar{
    position:relative;
    width:100%;
    height:18px;
    border-radius:999px;
    overflow:hidden;
    background:#e4e4e4;
    box-shadow: inset 0 1px 3px rgba(0,0,0,0.10);
}

.register-progress-zone-before{
    position:absolute;
    top:0;
    bottom:0;
    left:0;
    background:linear-gradient(180deg, #c7c7c7 0%, #bbbbbb 100%);
}

.register-progress-zone-after{
    position:absolute;
    top:0;
    bottom:0;
    background:linear-gradient(180deg, rgba(112,176,120,0.34) 0%, rgba(89,155,96,0.28) 100%);
}

.register-progress-fill{
    position:absolute;
    top:0;
    bottom:0;
    left:0;
    min-width:8px;
    border-radius:999px;
}

.register-progress-fill.is-red{
    background:linear-gradient(90deg, #d85050 0%, #e56868 100%);
}

.register-progress-threshold{
    position:absolute;
    top:-2px;
    bottom:-2px;
    width:0;
    border-left:3px solid rgba(24,24,24,0.88);
    z-index:5;
}

.register-progress-meta{
    margin-top:8px;
    font-size:0.86em;
    line-height:1.45;
    opacity:0.84;
    font-variant-numeric:tabular-nums;
}

/* ===================================================================== */
/* ========================= REGISTER 3P MODAL ========================== */
/* ===================================================================== */

.threep-modal-wrap{
    display:flex;
    flex-direction:column;
    text-align:center;
}

.threep-modal-head{
    display:flex;
    justify-content:space-between;
    align-items:flex-start;
    gap:16px;
    margin-bottom:10px;
}

.threep-modal-currency{
    min-width:120px;
    text-align:left;
}

.threep-modal-copy{
    font-size:1.02em;
    line-height:1.55;
    margin:8px 0 18px 0;
}

.threep-modal-slider-block{
    background:rgba(0,0,0,0.04);
    border:1px solid rgba(0,0,0,0.06);
    border-radius:12px;
    padding:18px 16px;
    margin-top:6px;
}

.threep-amount-readout{
    font-size:2.1em;
    font-weight:700;
    margin-bottom:14px;
    line-height:1.15;
}

.threep-slider-row{
    display:grid;
    grid-template-columns:minmax(50px,auto) 1fr minmax(50px,auto);
    gap:12px;
    align-items:center;
}

.threep-slider-edge{
    font-size:0.9em;
    opacity:0.75;
    font-variant-numeric:tabular-nums;
}

#threep_pledge_slider{
    width:100%;
}

.threep-slider-caption{
    margin-top:12px;
    font-size:0.95em;
    opacity:0.88;
}

@media (max-width: 900px){
    .threep-modal-head{
        flex-direction:column;
        align-items:center;
    }

    .threep-modal-currency{
        width:100%;
        text-align:center;
    }

    .threep-slider-row{
        grid-template-columns:1fr;
    }

    .threep-slider-edge{
        display:none;
    }
}







    #main {
        height: fit-content;
    }


.module-btn {
  background: var(--emblem-green);
  width: 100%;
  display: flex;
}

.module-btn:hover {
  background: var(--emblem-green-over);
}

#splash-bar {
  background-color: var(--top-header);
  filter: none !important;
  margin-bottom: -200px !important;
}


</style>





<?php require_once ("../header-2026.php");?>



