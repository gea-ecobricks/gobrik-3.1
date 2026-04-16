
<?php require_once ("../meta/$page-$lang.php"); ?>

<style>

/* -------------------------------------------------------
   1. DOCUMENT SETUP
   ------------------------------------------------------- */

#main {
    height: fit-content !important;
    padding-bottom: 100px;
}

#splash-bar {
    background-color: var(--top-header);
    filter: none !important;
    margin-bottom: -200px !important;
}

/* -------------------------------------------------------
   2. PAGE HEADER — stats block
   ------------------------------------------------------- */

.newest-briks-header {
    text-align: center;
    margin-bottom: 28px;
}

.newest-briks-header h2 {
    font-family: 'Arvo', serif;
    color: var(--h1);
    margin-top: 0;
    margin-bottom: 12px;
}

.newest-briks-header p {
    font-family: 'Mulish', sans-serif;
    color: var(--text-color);
    font-size: 1.1em;
    line-height: 1.5;
    margin: 0 auto;
    max-width: 600px;
}

/* -------------------------------------------------------
   3. PHOTO GRID
   ------------------------------------------------------- */

.landing-photo-grid {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 10px;
    padding: 12px 10px 30px 10px;
    max-width: 1400px;
    margin: 0 auto;
}

.landing-grid-item {
    width: 213px;
    flex: 0 0 213px;
    height: 255px;
    overflow: hidden;
    border: none;
    padding: 0;
    margin: 0;
    background: var(--darker, #d8d8d8);
    cursor: pointer;
    position: relative;
    border-radius: 6px;
    transition: transform 0.18s ease, box-shadow 0.18s ease;
}
.landing-grid-item:hover {
    transform: scale(1.10);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.30);
    z-index: 2;
}
.landing-grid-item img {
    width: 213px;
    height: 255px;
    object-fit: cover;
    display: block;
}

.gallery-empty {
    font-family: 'Mulish', sans-serif;
    color: var(--subdued-text, #999);
    padding: 20px;
    text-align: center;
    width: 100%;
}

/* Smaller tiles on narrow phones */
@media screen and (max-width: 560px) {
    .landing-grid-item {
        width: 136px;
        flex: 0 0 136px;
        height: 162px;
    }
    .landing-grid-item img {
        width: 136px;
        height: 162px;
    }
}

/* -------------------------------------------------------
   4. FORM CONTAINER
   ------------------------------------------------------- */

@media screen and (max-width: 700px) {
    .form-container {
        width: calc(100% - 40px);
        margin: 0;
        padding: 20px;
        max-width: 600px;
        position: relative;
        margin-top: 80px;
    }
}

@media screen and (min-width: 701px) {
    .form-container {
        margin-top: auto;
        margin-bottom: auto;
        padding: 30px;
        margin-top: 110px;
    }
}

</style>


<?php require_once ("../header-2026b.php"); ?>
