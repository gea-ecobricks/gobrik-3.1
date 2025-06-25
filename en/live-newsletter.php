<?php

// Build the unsubscribe link using the recipient's email if available.
$unsubscribe_link = isset($recipient_email)
    ? "https://gobrik.com/emailing/unsubscribe.php?email=" . urlencode($recipient_email)
    : "https://earthen.io/unsubscribe/?uuid=611f7d90-e87c-4c43-ab51-0772a7883703&key=c8c3faf87323b6ad7a8b96bcc9f9d742316e82dc604c69de46e524bcb11e3104&newsletter=7bbd5ff6-f69e-4ff0-a9d3-67963d85410b";

$email_template = <<<HTML

<!doctype html>
<html>
<head>
    <meta name="viewport" content="width=device-width">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>Solstice, Ayyew &amp; Earthen</title>
    <style>
        .post-title-link { display: block; margin-top: 32px; color: #15212A; text-align: center; line-height: 1.1em; }
        .post-title-link-left { text-align: left; }
        .view-online-link { white-space: nowrap; color: #73818c; text-decoration: underline !important; }
        .kg-nft-link, .kg-twitter-link, .kg-cta-link-accent .kg-cta-sponsor-label a, .kg-cta-text a, .kg-cta-minimal .kg-cta-button {
            color: #15212A !important;
            text-decoration: none !important;
            font-family: inherit !important;
        }
        .kg-audio-link { color: #73818c !important; }
        @media only screen and (max-width: 620px) {
            table.body { width: 100% !important; }
            .hide-mobile { display: none !important; }
            .mobile-only { display: block !important; }
            .desktop-only { display: none !important; }
            table.body p, table.body ul, table.body ol, table.body td { font-size: 16px !important; }
            table.body .post-title a { font-size: 26px !important; line-height: 1.1em !important; }
            table.feedback-buttons { width: 100% !important; max-width: 390px; }
            .feedback-button-text { display: none !important; }
            .feature-image-caption, .kg-card-figcaption, .kg-card-figcaption p { font-size: 13px !important; }
        }
        @media all {
            .ExternalClass { width: 100%; line-height: 100%; }
            .apple-link a { color: inherit !important; text-decoration: none !important; }
            #MessageViewBody a { color: inherit; text-decoration: none; }
        }
    </style>
</head>
<body style="background-color: #fff; font-family: -apple-system, BlinkMacSystemFont, Roboto, Helvetica, Arial, sans-serif; font-size: 18px; line-height: 1.4; margin: 0; padding: 0; color: #15212A;">
<span class="preheader" style="display:none; visibility:hidden; mso-hide:all; opacity:0;">With the coming of this year's Solstice, it is most apropos to launch a course that celebrates cyclocentric culture and concepts.</span>

<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #fff;">
<tr><td></td>
<td class="container" style="max-width: 600px; margin: 0 auto; padding: 0;">
<div class="content">
<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background: #ffffff; border-radius: 3px;">

<tr>
<td class="wrapper" style="padding: 20px;">
<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
<tr class="header-image-row">
<td align="center" style="padding-top: 24px; padding-bottom: 16px;">
<a href="https://earthen.io/r/ff19a55c?m=611f7d90-e87c-4c43-ab51-0772a7883703" target="_blank">
<img src="https://earthen.io/content/images/size/w1200/2022/08/email-cover-1200px.jpg" width="600" style="border: none; max-width: 100%;">
</a>
</td>
</tr>
<tr class="site-info-row">
<td align="center" style="padding-top: 32px;">
<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
<tr>
<td class="site-icon" align="center" style="padding: 8px;">
<a href="https://earthen.io/r/5adad2dd?m=611f7d90-e87c-4c43-ab51-0772a7883703" target="_blank">
<img src="https://earthen.io/content/images/2022/07/page-logo.png" alt="Earthen" width="44" height="44" style="border: none; border-radius: 3px;">
</a>
</td>
</tr>
<tr>
<td class="site-url" align="center" style="font-size: 16px; font-weight: 700; text-transform: uppercase; padding-bottom: 12px;">
<a href="https://earthen.io/r/0a0409ba?m=611f7d90-e87c-4c43-ab51-0772a7883703" class="site-title" style="text-decoration: none; color: #15212A;">Earthen</a>
</td>
</tr>
</table>
</td>
</tr>
<tr>
<td class="post-title" align="center" style="font-size: 36px; font-weight: 700; line-height: 1.1em; padding-bottom: 16px;">
<a href="https://earthen.io/r/08f20575?m=611f7d90-e87c-4c43-ab51-0772a7883703" class="post-title-link" style="text-decoration: none; color: #15212A;">Solstice, Ayyew &amp; Earthen</a>
</td>
</tr>

<tr>
<td style="color: #15212A; font-size: 18px;">
<table class="post-meta-wrapper" role="presentation" width="100%" style="padding-bottom: 32px;">
<tr>
<td class="post-meta" align="center" style="font-size: 13px; color: #73818c;">By GEA Center Circle • <span class="post-meta-date">25 Jun 2025</span></td>
</tr>
<tr class="post-meta view-online-mobile" style="display: none;">
<td class="view-online" align="center" style="font-size: 13px; color: #73818c; text-decoration: underline;">
<a href="https://earthen.io/r/6847d69e?m=611f7d90-e87c-4c43-ab51-0772a7883703" target="_blank" class="view-online-link">View in browser</a>
</td>
</tr>
</table>
</td>
</tr>

<tr class="feature-image-row">
<td align="center" style="padding-bottom: 30px;">
<img src="https://earthen.io/content/images/size/w1200/2025/06/earthen-ethics-launch-1-1.png" width="600" style="border: none; max-width: 100%;">
</td>
</tr>

<tr>
<td align="center" style="font-size: 13px; color: #73818c; padding-bottom: 32px;">
<div class="feature-image-caption">The land of the Igorot people in the Northern Philippines where the Ayyew concept originates</div>
</td>
</tr>

<tr class="post-content-row">
<td class="post-content" style="font-family: Georgia, serif; font-size: 18px; line-height: 1.5em; color: #15212A; padding-bottom: 20px; border-bottom: 1px solid #e0e7eb;">
<p>With the coming of this year's Solstice (the other day!), it is most apropos to launch a course that celebrates cyclocentric culture and concepts.</p>
<p>What is a <em>cyclocentric</em> culture you ask? It is a culture that centers around its sync and celebration of Earthen cycles – such the migration of animals, the position of Venus, the phases of the moon, or... the Solstice!</p>
<p>The term is coined by Russell and Irene who we will be interviewing about their theory of <em>Earthen Ethics</em> on June 29th. We'll talk about Irene's cyclocentric Igorot culture and her people's concept of Ayyew. Russell and Irene argue that concept of Ayyew (and the cyclocentric paradigm) are crucial for transitioning humanity to deep ecological integration.</p>
<p>Course registration on GoBrik is now open and free:</p>

<!-- Start GoBrik Card -->
<div class="kg-card kg-bookmark-card" style="margin: 0 0 1.5em; padding: 0; width: 100%; background: #ffffff; border: 1px solid #e0e7eb; border-radius: 3px;">
  <a class="kg-bookmark-container" href="https://earthen.io/r/db2774f1?m=611f7d90-e87c-4c43-ab51-0772a7883703" target="_blank" style="display: flex; min-height: 148px; font-family: -apple-system, BlinkMacSystemFont, Roboto, Helvetica, Arial, sans-serif; border-radius: 3px; overflow-wrap: anywhere; color: #4B8501; text-decoration: none;">

    <!-- Left Content -->
    <div class="kg-bookmark-content" style="width: 100%; padding: 20px;">
      <div class="kg-bookmark-title" style="color: #15212A; font-size: 15px; line-height: 1.5em; font-weight: 600;">An Intro to Ayyew &amp; Earthen Ethics</div>
      <div class="kg-bookmark-description" style="margin-top: 12px; color: #73818c; font-size: 13px; line-height: 1.5em;">Register for our GEA Community Event led by Ani Himawati and Lucie Mann on June 29, 2025</div>
      <div class="kg-bookmark-metadata" style="display: flex; align-items: center; margin-top: 14px; color: #15212A; font-size: 13px;">
        <img class="kg-bookmark-icon" src="https://earthen.io/content/images/icon/apple-icon-180x180-13.png" alt="" width="22" height="22" style="margin-right: 8px;">
        <span class="kg-bookmark-author">GoBrik.com</span>
      </div>
    </div>

    <!-- Right Thumbnail -->
    <div class="kg-bookmark-thumbnail" style="min-width: 140px; max-width: 180px; border-radius: 0 3px 3px 0;">
      <img src="https://earthen.io/content/images/thumbnail/earthen-ethics-launch-1.web" alt="Earthen Ethics Launch" width="180" height="148" style="width: 100%; height: auto; border-radius: 0 3px 3px 0; display: block;">
    </div>

  </a>
</div>

<!-- End GoBrik Card -->

<p>We’re also pleased to announce another introduction to Ecobrick course which will run on July 19:</p>
<!-- Start Ecobricks Card -->
<div class="kg-card kg-bookmark-card" style="background: #ffffff; border: 1px solid #e0e7eb; border-radius: 3px; margin: 0 0 1.5em; overflow: hidden;">
  <a class="kg-bookmark-container" href="https://earthen.io/r/33183cd9?m=611f7d90-e87c-4c43-ab51-0772a7883703" target="_blank" style="display: flex; text-decoration: none; color: #15212A; font-family: -apple-system, BlinkMacSystemFont, Roboto, Helvetica, Arial, sans-serif;">

    <!-- Left Content -->
    <div class="kg-bookmark-content" style="width: 100%; padding: 20px; box-sizing: border-box;">
      <div class="kg-bookmark-title" style="font-weight: 600; font-size: 15px; color: #15212A;">Plastic, the Biosphere and Ecobricks</div>
      <div class="kg-bookmark-description" style="margin-top: 12px; font-size: 13px; color: #73818c;">Register for our Online Starter Workshop led by Paula Apollonia and Russell Maier on July 19, 2025</div>
      <div class="kg-bookmark-metadata" style="margin-top: 14px; font-size: 13px; color: #15212A; display: flex; align-items: center;">
        <img class="kg-bookmark-icon" src="https://earthen.io/content/images/icon/apple-icon-180x180-14.png" alt="" width="22" height="22" style="margin-right: 8px;">
        <span class="kg-bookmark-author">GoBrik.com</span>
      </div>
    </div>

    <!-- Right Thumbnail -->
    <div class="kg-bookmark-thumbnail" style="min-width: 140px; max-width: 180px;">
      <img src="https://earthen.io/content/images/thumbnail/starter-workshop-feature-1-en-2.webp" alt="Plastic, the Biosphere and Ecobricks" width="180" height="148" style="width: 100%; height: auto; display: block; border-left: 1px solid #e0e7eb;">
    </div>

  </a>
</div>
<!-- End Ecobricks Card -->


<p>Hope to see you there.<br><br>The Earthen Team</p>
</td>
</tr>


<tr>
<td align="center" style="font-size: 13px; color: #73818c; padding: 20px 30px;">
<p><em>Together we can be the transition to ever increasing harmony with the cycles of life.</em></p>
</td>
</tr>

            <!-- Subscription footer -->
            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse: separate; width: 100%;">
                <tr>
                    <td class="footer" align="center" style="font-family: -apple-system, BlinkMacSystemFont, Roboto, Helvetica, Arial, sans-serif; font-size: 13px; color: #73818c; padding: 20px 30px;">
                        Earthen © 2025 – <a href="$unsubscribe_link" style="color: #73818c; text-decoration: underline;">Unsubscribe</a>
                    </td>
                </tr>
            </table>

            </div>
        </td>
        <td>&nbsp;</td>
    </tr>
</table>

</body>
</html>
HTML;

?>
