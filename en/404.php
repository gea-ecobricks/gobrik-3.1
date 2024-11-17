<!DOCTYPE html>

<?php
// Get the current page URL
$current_url = "https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

// Parse the URL and extract the directory name
$url_parts = parse_url($current_url);
$path_segments = explode('/', trim($url_parts['path'], '/'));
$lang = isset($path_segments[0]) ? $path_segments[0] : 'en'; // Default to English if no directory found

?>
<HTML lang="en">
<HEAD>
<META charset="UTF-8">
<?php $version='2.46';?>
<?php $page='404';?>

<?php require_once ("../includes/404-inc.php");?>

<!--PAGE BANNER-->


 <div class="splash-content-block">
	<div class="splash-box">
		<div class="splash-heading" data-lang-id="001-splash-title">Sorry!</div>
	    <div class="splash-sub" data-lang-id="002-splash-subtitle">It seems that the page you're looking for can't be found.</div>
	</div>
	<div class="splash-image" data-lang-id="003-splash-image-alt"><img src="../svgs/question.svg" style="width: 75%" alt="Ecobricks making a question mark">
    </div>
</div>

<!-- PAGE CONTENT-->


        <div id="main-content">

        <!-- The flexible grid (content) -->
            <div class="row">
                <div class="main">

                    <div class="lead-page-paragraph">
                         <p data-lang-id="004-lead-paragraph">You're seeing this page because the url you entered doesn't correspond to a page on our server.</p>
                    </div>

                    <div class="page-paragraph">
                         <p data-lang-id="005-first-page-paragraph">Look carefully at the URL to see if there is a mispelling.  We've just launched a new version of our site, and alas, there could be some bugs!</p>
                    </div>


                    <div class="page-paragraph-reg">

                         <p data-lang-id="007-page-paragraph">If you think there's an error on our site, please let us know at support@ecobricks.org.</p>


                        <p data-lang-id="008-page-paragraph">Meanwhile, use the new search feature on our site (top right menu bar) to look for the content you're trying to find.</p>

                        <button type="button" class="module-btn" style="margin-top:20px;" onclick="openSearch()" data-lang-id="009-search-button">🔎 Search Site</button>
                        <br><br>
                        <a class="module-btn" href="../en/index.php" style="margin-top:50px;" data-lang-id="011-home-button">🏡 Home</a>

                    </div>
                </div>

                <div class="side">

                <?php require_once ("../en/side-modules/spiral-design.php");?>

                </div>
            </div>
            <br><br>
         </div>
     </div>

	<!--FOOTER STARTS HERE-->

	<?php require_once ("../footer-2024.php");?>


</div>
</body>
</html>


