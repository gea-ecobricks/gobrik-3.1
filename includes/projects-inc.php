
<?php require_once ("../meta/$page-$lang.php"); ?>

<style>

@-webkit-keyframes fadeIn { from { opacity:0; } to { opacity:1; } }
@-moz-keyframes fadeIn { from { opacity:0; } to { opacity:1; } }
@keyframes fadeIn { from { opacity:0; } to { opacity:1; } }

/* -------------------------------------------------------
   1. DOCUMENT SETUP
   ------------------------------------------------------- */

html {
    min-height: 100%;
}

body {
    z-index: 0;
    position: relative;
    margin: 0;
    padding: 0;
    background: var(--dashboard-page-background, #f5f5f5);
    overflow-x: clip;
}

/* -------------------------------------------------------
   2. PAGE HEADER — title + description hero block
   ------------------------------------------------------- */

.projects-page-header {
    width: 100%;
    padding: 60px 0 20px 0;
    text-align: center;
    background: var(--dashboard-page-background, #f5f5f5);
}

.projects-hero {
    max-width: 780px;
    margin: 0 auto;
    padding: 0 24px;
}

.projects-title {
    font-family: 'Arvo', serif;
    color: var(--h1);
    line-height: 1.2;
    margin-bottom: 16px;
}

@media screen and (max-width: 700px) {
    .projects-title { font-size: 2.0em; }
}
@media screen and (min-width: 701px) and (max-width: 1300px) {
    .projects-title { font-size: 2.4em; }
}
@media screen and (min-width: 1301px) {
    .projects-title { font-size: 2.8em; }
}

.projects-description {
    font-family: 'Mulish', sans-serif;
    color: var(--text-color);
    line-height: 1.55;
    margin: 0 auto 24px auto;
    max-width: 640px;
}

@media screen and (max-width: 700px) {
    .projects-description { font-size: 1.05em; }
}
@media screen and (min-width: 701px) {
    .projects-description { font-size: 1.2em; }
}

.projects-back-link {
    margin: 4px auto 10px auto;
    display: flex;
    justify-content: center;
}

/* -------------------------------------------------------
   3. FEATURE BUTTON
   ------------------------------------------------------- */

.feature-button {
    font-family: 'Mulish', sans-serif;
    display: inline-block;
    background: #00a1f2;
    background-image: linear-gradient(to bottom, #00a1f2, #008ad4);
    border-radius: 8px;
    color: #fff;
    font-size: 1.2em;
    padding: 8px 18px;
    text-decoration: none !important;
    margin: 10px auto;
    font-weight: 500;
    cursor: pointer;
    border: none;
    text-align: center;
}
.feature-button:hover {
    background: #3cb0fd;
    background-image: linear-gradient(to bottom, #3cb0fd, #3498db);
    text-decoration: underline !important;
}

/* -------------------------------------------------------
   4. GALLERY SECTION
   ------------------------------------------------------- */

.featured-content-gallery {
    padding: 30px 0 50px 0;
    border-radius: 18px;
    margin: 18px auto 40px auto;
    max-width: 1500px;
}

.gallery-section-projects {
    background: rgba(0, 140, 220, 0.07);
}

.projects-full-gallery {
    /* Give the full-page gallery a little extra breathing room */
    margin-top: 0;
    border-radius: 0;
}

/* -------------------------------------------------------
   5. LIVE FEED BAR
   ------------------------------------------------------- */

.feed-live {
    text-align: center;
    background: var(--darker);
    border-radius: 15px 15px 0 0;
    font-size: 0.9em;
    margin: 15px auto -10px auto;
    width: 80%;
    padding: 10px;
}
.feed-live p {
    font-family: 'Courier New', monospace !important;
    color: var(--subdued-text);
    line-height: 1.1;
    font-weight: 300;
}
@media screen and (max-width: 700px) {
    .feed-live p { font-size: 0.8em; }
}
@media screen and (min-width: 700px) {
    .feed-live p { font-size: 0.9em; }
}

/* -------------------------------------------------------
   6. PHOTO GRID
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

/* Project name overlay at the bottom of each tile */
.landing-project-title {
    position: absolute;
    bottom: 0;
    left: 0;
    width: 100%;
    padding: 6px 8px;
    box-sizing: border-box;
    background: rgba(0, 0, 0, 0.52);
    color: #fff;
    font-family: 'Mulish', sans-serif;
    font-size: 0.78em;
    line-height: 1.2;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    text-align: center;
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
   7. SIDE-MENU OVERLAY
   ------------------------------------------------------- */

#main-menu-overlay {
    background-color: var(--side-overlays);
    color: var(--text-color);
    z-index: 26;
}

.overlay-settings {
    height: 100%;
    width: 0%;
    position: fixed;
    z-index: 21;
    right: 0;
    top: 0;
    overflow-x: hidden;
    transition: 0.5s;
}

</style>


<?php require_once ("../header-2026b.php"); ?>
