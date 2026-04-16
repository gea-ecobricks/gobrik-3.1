
<!--  Set any page specific graphics to preload
<link rel="preload" as="image" href="../webps/ecobrick-team-blank.webp" media="(max-width: 699px)">
<link rel="preload" as="image" href="../svgs/richard-and-team-day.svg">
<link rel="preload" as="image" href="../svgs/richard-and-team-night.svg">
<link rel="preload" as="image" href="../webps/biosphere2.webp">
<link rel="preload" as="image" href="../webps/biosphere-day.webp">-->

<?php require_once ("../meta/$page-$lang.php");?>


<style>

@-webkit-keyframes fadeIn { from { opacity:0; } to { opacity:1; } }
@-moz-keyframes fadeIn { from { opacity:0; } to { opacity:1; } }
@keyframes fadeIn { from { opacity:0; } to { opacity:1; } }

/* -------------------------------------------------------
   1. ROOT & DOCUMENT SETUP
   ------------------------------------------------------- */

/* Force white logo on the landing page regardless of colour scheme */
:root {
    --logo-color: rgb(213, 213, 213);
    --unit-100vh: 100vh;
}
@supports (height: 100dvh) {
    :root { --unit-100vh: 100dvh; }
}

/* Allow the page to grow beyond the viewport — do NOT constrain html to 100%
   (doing so would prevent scrolling once galleries push the page taller than 1vh). */
html {
    min-height: 100%;
}

body {
    z-index: 0;
    position: relative;
    margin: 0;
    padding: 0;
    background: var(--dashboard-page-background, #f5f5f5);
    /* clip is safer than hidden: it clips visual overflow without creating a
       scroll container, so the document scroll chain stays intact. */
    overflow-x: clip;
}

/* -------------------------------------------------------
   2. HEADER OVERRIDES (landing-page specific)
   The global #header is position:fixed; on the landing
   page we want it to sit in normal flow with no blur.
   ------------------------------------------------------- */

/* Remove fixed-position lock and backdrop blur on the landing page */
#header {
    position: relative !important;
    backdrop-filter: none !important;
    -webkit-backdrop-filter: none !important;
}

@media screen and (min-width: 701px) {
    #header { height: 70px; }
}
@media screen and (max-width: 700px) {
    #header { height: 70px; }
}

.gobrik-logo {
    margin: auto;
    position: absolute;
    width: 165px;
    height: 47px;
    border: none;
    right: 0;
    left: 0;
    margin-top: 13px;
    cursor: pointer;
}

.main-menu-button {
    position: absolute;
    left: 0;
    margin-top: 22px;
    margin-left: 25px;
    border: none;
    cursor: pointer;
    height: 30px;
    width: 30px;
}

.top-menu-login-button {
    position: absolute;
    left: 0;
    margin-top: 22px;
    margin-left: 22px;
    font-family: 'Mulish';
    background: none;
    border: 0.5px solid;
    border-radius: 5px;
    font-size: 0.9em;
    padding: 3px 14px;
    cursor: pointer;
    color: var(--header-accent);
    border-color: var(--header-accent);
}
.top-menu-login-button:hover {
    color: var(--top-header);
    background-color: var(--header-accent);
}
@media screen and (max-width: 700px) {
    .top-menu-login-button { display: none; }
}

.top-settings-button {
    border: none;
    cursor: pointer;
    height: 30px;
    width: 30px;
}

#lang-button {
    position: relative !important;
    transition: 0.3s;
}

/* -------------------------------------------------------
   3. LANDING INTRO — CLOUDS / BIOSPHERE / HERO
   ------------------------------------------------------- */

.biosphere {
    position: relative;
    z-index: 0;
    height: 50vh;
    width: 100%;
    text-align: center;
    margin: auto;
    margin-bottom: -50vh;
}

.main-landing-graphic {
    margin: auto;
    position: relative;
    z-index: 11;
}
.main-landing-graphic img {
    height: auto;
    width: 100%;
}
@media screen and (max-width: 700px) {
    .main-landing-graphic {
        width: 93%;
        margin-top: 70px;
        margin-bottom: -5px;
        min-width: 250px;
        min-height: 200px;
    }
}
@media screen and (min-width: 701px) and (max-width: 1300px) {
    .main-landing-graphic {
        width: 75%;
        margin-bottom: -5px;
        margin-top: 110px;
    }
}
@media screen and (min-width: 1301px) {
    .main-landing-graphic {
        width: 66%;
        margin-bottom: -5px;
        margin-top: 70px;
    }
}

/* -------------------------------------------------------
   4. TYPOGRAPHY — LANDING HEADINGS & BODY COPY
   ------------------------------------------------------- */

.big-header {
    font-family: 'Arvo', serif;
    margin-top: 20px;
    text-align: center !important;
    line-height: 1.2 !important;
    color: var(--h1);
    z-index: 10;
}
@media screen and (max-width: 700px) {
    .big-header { font-size: 1.95em !important; margin-bottom: 14px; }
}
@media screen and (min-width: 701px) and (max-width: 1300px) {
    .big-header { font-size: 2.3em !important; margin-bottom: 16px; }
}
@media screen and (min-width: 1301px) {
    .big-header { font-size: 2.6em !important; margin-bottom: 19px; }
}

.welcome-text {
    font-size: 1.6em !important;
    font-family: 'Mulish', sans-serif;
    text-align: center !important;
    color: var(--text-color);
}
@media screen and (max-width: 700px) {
    .welcome-text { font-size: 1.11em !important; margin-bottom: 26px; }
}
@media screen and (min-width: 701px) and (max-width: 1300px) {
    .welcome-text { font-size: 1.5em !important; margin-bottom: 28px; }
}
@media screen and (min-width: 1301px) {
    .welcome-text { font-size: 1.7em !important; margin-bottom: 30px; }
}

.tree-text {
    font-size: 0.83em;
    font-family: 'Mulish', sans-serif;
    width: 85%;
    text-align: center;
    line-height: 1.4;
    position: relative;
    z-index: 5;
    font-weight: 300;
    margin: 5px auto;
    color: var(--text-color);
}

p a   { color: var(--text-color) !important; }
p a:hover { color: var(--h1) !important; }

/* -------------------------------------------------------
   5. LANDING CONTENT LAYOUT
   ------------------------------------------------------- */

@media screen and (min-width: 1200px) {
    .landing-content { width: 70%; margin: auto; }
}
@media screen and (min-width: 700px) and (max-width: 1200px) {
    .landing-content { width: 80%; margin: auto; }
}
@media screen and (max-width: 700px) {
    .landing-content { background: none; max-width: 90%; margin: auto; }
}

.tree-coins {
    position: relative;
    z-index: 0;
    text-align: center;
    margin: 15px auto 10px auto;
}
@media screen and (max-width: 700px) { .tree-coins { width: 90%; } }
@media screen and (min-width: 700px) { .tree-coins { width: 60%; } }

/* -------------------------------------------------------
   6. LOGIN / SIGN-UP BUTTONS
   ------------------------------------------------------- */

.sign-innn {
    font-family: 'Mulish', sans-serif !important;
    display: block;
    margin: auto;
    background: var(--button-2-1);
    background-image: linear-gradient(to bottom, var(--button-2-1), var(--button-2-2));
    border-radius: 8px 0 0 8px;
    color: #fff;
    font-size: 1.3em;
    font-weight: 700;
    padding: 9px 18px;
    text-decoration: none;
    margin-bottom: 12px;
    border: none;
    margin-right: 3px;
    cursor: pointer;
}
.sign-innn:hover {
    background: var(--button-2-1-over);
    background-image: linear-gradient(to bottom, var(--button-2-1-over), var(--button-2-2-over));
    text-decoration: none;
}

.sign-uppp {
    font-family: 'Mulish', sans-serif !important;
    display: block;
    margin: auto;
    background: var(--button-1-1);
    background-image: linear-gradient(to bottom, var(--button-1-1), var(--button-1-2));
    border-radius: 0 8px 8px 0;
    color: #fff;
    font-size: 1.3em;
    font-weight: 700;
    padding: 9px 18px 9px 9px;
    text-decoration: none;
    margin-bottom: 12px;
    border: none;
    margin-left: 3px;
    cursor: pointer;
}
.sign-uppp:hover {
    background: var(--button-1-1-over);
    background-image: linear-gradient(to bottom, var(--button-1-1-over), var(--button-1-2-over));
    text-decoration: none;
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

.overlay-content-settings {
    position: initial;
    text-align: center;
    font-family: "Mulish";
    display: flex;
    justify-content: center;
    flex-flow: column;
    height: 100%;
    margin: auto;
}
@media screen and (max-width: 700px) {
    .overlay-content-settings { width: 77%; font-size: 0.9em; }
}
@media screen and (min-width: 700px) and (max-width: 1324px) {
    .overlay-content-settings { width: 65%; font-size: 0.9em; }
}
@media screen and (min-width: 1325px) {
    .overlay-content-settings { width: 69%; margin: auto; }
}

.settings-label {
    font-family: 'Mulish';
    font-size: 1.2em;
    margin: 18px 0 8px 0;
}

.language-box {
    display: flex;
    margin: 10px auto;
    justify-content: center;
    padding: 5px 30px;
    background: var(--slide-highlight);
    border-radius: 55px;
    width: fit-content;
}

.language-selector {
    font-family: 'Mulish';
    padding: 10px 20px;
    background: var(--side-overlays);
    border-radius: 10px;
    margin: 10px;
    font-size: 1.1em;
    cursor: pointer;
    color: var(--text-color);
    border-width: 0.5px;
}
.language-selector:hover {
    background: var(--header-accent);
    border-width: 1px;
    color: var(--top-header);
}
.language-selector a { color: var(--side-overlays); }

.menu-page-item {
    padding: 10px;
    font-family: 'Mulish';
    font-size: 1.4em;
    color: var(--text-color);
    border-bottom: 1px solid var(--subdued-text);
    cursor: pointer;
}
.menu-page-item:hover {
    border-bottom: 2px solid var(--text-color);
    color: var(--header-accent);
}
.menu-page-item a { text-decoration: none; color: var(--subdued-text); }
.menu-page-item a:hover { text-decoration: none; color: var(--h1); }

[part="darkLabel"], [part="lightLabel"], [part="toggleLabel"] {
    font-size: 22px !important;
}

#right-close-button {
    position: absolute;
    right: 0;
    top: 0;
    transition: 0.3s;
    height: 75px;
    width: 75px;
    padding-right: 30px;
    padding-top: 30px;
}

.x-button {
    background: url('../svgs/right-x.svg') no-repeat;
    padding: 10px;
    background-size: contain;
    width: 75px;
    height: 75px;
    border: none;
}
.x-button:hover {
    background: url('../svgs/x-over.svg') no-repeat;
    padding: 10px;
    background-size: contain;
}

/* -------------------------------------------------------
   8. CARBON BADGE (rendered in footer)
   ------------------------------------------------------- */

#wcb.wcb-d #wcb_a {
    color: #2e2e2e !important;
    background: #27ad37 !important;
    border-color: #00a112 !important;
}
#wcb #wcb_a, #wcb #wcb_g { border: 0.2em solid #2cb03c !important; }
#wcb.wcb-d #wcb_2 { color: var(--footer-text) !important; }

/* -------------------------------------------------------
   9. GALLERY SECTION — LIVE FEED HEADER BAR
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
   10. GALLERY FEATURE BOX (below each grid)
   ------------------------------------------------------- */

.feature-button {
    font-family: 'Mulish', sans-serif;
    display: inline-block;
    background: #00a1f2;
    background-image: linear-gradient(to bottom, #00a1f2, #008ad4);
    border-radius: 8px;
    color: #fff;
    font-size: 1.4em;
    padding: 8px 18px;
    text-decoration: none !important;
    margin: 18px auto 16px auto;
    font-weight: 500;
    cursor: pointer;
    border: none;
    text-align: center;
}
.feature-button:hover {
    background: #3cb0fd;
    background-image: linear-gradient(to bottom, #3cb0fd, #3498db);
    text-decoration: underline;
}

/* -------------------------------------------------------
   11. GALLERY SECTIONS — BACKGROUND BANDS
   Each gallery section (grid + feature-content-box) gets
   a distinct subtle tint to visually group them together.
   ------------------------------------------------------- */

.featured-content-gallery {
    padding: 30px 0 40px 0;
    border-radius: 18px;
    margin: 18px auto;
    max-width: 1500px;
}

.gallery-section-ecobricks {
    background: rgba(34, 180, 100, 0.07);
}

.gallery-section-projects {
    background: rgba(0, 140, 220, 0.07);
}

/* -------------------------------------------------------
   12. LANDING PAGE — PHOTO GRID GALLERIES
   Shared by the ecobrick gallery and the projects gallery.
   Tiles are ~213 px wide (15 % smaller than the 250 px
   original); hover scale(1.18) restores them to ~250 px.
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
    transform: scale(1.18);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.30);
    z-index: 2;
}
.landing-grid-item img {
    width: 213px;
    height: 255px;
    object-fit: cover;
    display: block;
}

/* Project name overlay at the bottom of each project tile */
.landing-project-title {
    position: absolute;
    bottom: 0;
    left: 0;
    width: 100%;
    padding: 6px 8px;
    box-sizing: border-box;
    background: rgba(0, 0, 0, 0.50);
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

/* Smaller tiles on narrow phones — two columns still fit */
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

</style>




<?php require_once ("../header-2026b.php");?>

