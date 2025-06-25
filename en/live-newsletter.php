<?php

// Build the unsubscribe link dynamically
$unsubscribe_link = isset($recipient_email)
    ? "https://gobrik.com/emailing/unsubscribe.php?email=" . urlencode($recipient_email)
    : "https://earthen.io/unsubscribe/?uuid=611f7d90-e87c-4c43-ab51-0772a7883703&key=c8c3faf87323b6ad7a8b96bcc9f9d742316e82dc604c69de46e524bcb11e3104&newsletter=7bbd5ff6-f69e-4ff0-a9d3-67963d85410b";

$email_template = <<<HTML
<!doctype html>
<html>
<head>
    <meta name="viewport" content="width=device-width">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>Solstice, Ayyew & Earthen</title>
    <style>
        body {
            background-color: #fff;
            font-family: -apple-system, BlinkMacSystemFont, Roboto, Helvetica, Arial, sans-serif;
            -webkit-font-smoothing: antialiased;
            font-size: 18px;
            line-height: 1.4;
            margin: 0;
            padding: 0;
            -ms-text-size-adjust: 100%;
            -webkit-text-size-adjust: 100%;
            color: #15212A;
        }
        .post-title-link { display: block; margin-top: 32px; color: #15212A; text-align: center; line-height: 1.1em; }
        .view-online-link { word-wrap: none; white-space: nowrap; color: #73818c; text-decoration: underline !important; }
        .kg-bookmark-container { display: flex; border: 1px solid #e0e7eb; border-radius: 3px; text-decoration: none; color: #15212A; }
        .kg-bookmark-content { padding: 20px; width: 100%; }
        .kg-bookmark-title { font-weight: 600; font-size: 15px; line-height: 1.5em; }
        .kg-bookmark-description { font-size: 13px; color: #73818c; margin-top: 12px; }
        .kg-bookmark-metadata { display: flex; margin-top: 14px; font-size: 13px; }
        .kg-bookmark-thumbnail { min-width: 140px; max-width: 180px; background-size: cover; background-position: center; border-radius: 0 2px 2px 0; }
        .feature-image-caption { font-size: 13px; color: #73818c; text-align: center; padding-top: 5px; padding-bottom: 32px; }
        @media only screen and (max-width: 620px) {
            .container { width: 100% !important; padding: 0 !important; }
            .post-title-link { margin-top: 24px !important; }
            .site-icon img { width: 36px !important; height: 36px !important; }
        }
    </style>
</head>
<body>
<span class="preheader" style="display:none;">With the coming of this year’s Solstice, it is most apropos to launch a course that celebrates cyclocentric culture and concepts...</span>

<table role="presentation" width="100%" bgcolor="#fff" class="body">
    <tr>
        <td>&nbsp;</td>
        <td class="container" style="max-width: 600px; margin: auto;">
            <div class="content">

                <!-- HEADER IMAGE -->
                <table role="presentation" class="main" width="100%" style="border-spacing: 20px 0;">
                    <tr>
                        <td align="center" style="padding-top: 24px;">
                            <a href="https://earthen.io/r/ff19a55c?m=611f7d90-e87c-4c43-ab51-0772a7883703" target="_blank">
                                <img src="https://earthen.io/content/images/size/w1200/2022/08/email-cover-1200px.jpg" width="600" style="max-width: 100%; border: none;">
                            </a>
                        </td>
                    </tr>

                    <!-- SITE INFO -->
                    <tr>
                        <td align="center" style="padding-top: 32px;">
                            <a href="https://earthen.io/r/5adad2dd?m=611f7d90-e87c-4c43-ab51-0772a7883703" target="_blank">
                                <img src="https://earthen.io/content/images/2022/07/page-logo.png" alt="Earthen" width="44" height="44" style="border-radius: 3px;">
                            </a>
                            <div style="font-weight: 700; text-transform: uppercase; font-size: 16px; padding-top: 12px;">
                                <a href="https://earthen.io/r/0a0409ba?m=611f7d90-e87c-4c43-ab51-0772a7883703" style="color:#15212A; text-decoration:none;">Earthen</a>
                            </div>
                        </td>
                    </tr>

                    <!-- TITLE -->
                    <tr>
                        <td align="center" style="padding-bottom: 16px;">
                            <a href="https://earthen.io/r/08f20575?m=611f7d90-e87c-4c43-ab51-0772a7883703" class="post-title-link">Solstice, Ayyew & Earthen</a>
                        </td>
                    </tr>

                    <!-- POST DATE -->
                    <tr>
                        <td align="center" style="color: #73818c; font-size: 13px;">By GEA Center Circle • 25 Jun 2025</td>
                    </tr>

                    <!-- FEATURE IMAGE -->
                    <tr>
                        <td align="center">
                            <img src="https://earthen.io/content/images/size/w1200/2025/06/earthen-ethics-launch-1-1.png" width="600" style="max-width: 100%;">
                            <div class="feature-image-caption">The land of the Igorot people in the Northern Philippines where the Ayyew concept originates</div>
                        </td>
                    </tr>

                    <!-- MAIN CONTENT -->
                    <tr>
                        <td style="font-family: Georgia, serif; font-size: 18px; line-height: 1.5em; padding-bottom: 20px; border-bottom: 1px solid #e0e7eb;">
                            <p>With the coming of this year’s Solstice (the other day!), it is most apropos to launch a course that celebrates cyclocentric culture and concepts.</p>

                            <p>What is a <em>cyclocentric</em> culture you ask? It is a culture that centers around its sync and celebration of Earthen cycles — such as the migration of animals, the position of Venus, the phases of the moon, or... the Solstice!</p>

                            <p>The term is coined by Russell and Irene who we will be interviewing about their theory of <em>Earthen Ethics</em> on June 29th. We'll talk about Irene's cyclocentric Igorot culture and her people’s concept of Ayyew. Russell and Irene argue that the concept of Ayyew (and the cyclocentric paradigm) are crucial for transitioning humanity to deep ecological integration.</p>

                            <p>Course registration on GoBrik is now open and free:</p>

                            <!-- BOOKMARK CARD 1 -->
                            <div class="kg-card kg-bookmark-card">
                                <a class="kg-bookmark-container" href="https://earthen.io/r/db2774f1?m=611f7d90-e87c-4c43-ab51-0772a7883703" target="_blank">
                                    <div class="kg-bookmark-content">
                                        <div class="kg-bookmark-title">An Intro to Ayyew & Earthen Ethics</div>
                                        <div class="kg-bookmark-description">Register for our GEA Community Event led by Ani Himawati and Lucie Mann on June 29, 2025</div>
                                        <div class="kg-bookmark-metadata">
                                            <img src="https://earthen.io/content/images/icon/apple-icon-180x180-13.png" width="22" height="22">
                                            <span style="margin-left:8px;">GoBrik.com</span>
                                        </div>
                                    </div>
                                    <div class="kg-bookmark-thumbnail" style="background-image: url('https://earthen.io/content/images/thumbnail/earthen-ethics-launch-1.webp');"></div>
                                </a>
                            </div>

                            <p>We're also pleased to announce another introduction to Ecobrick course which will run on July 19.</p>

                            <!-- BOOKMARK CARD 2 -->
                            <div class="kg-card kg-bookmark-card">
                                <a class="kg-bookmark-container" href="https://earthen.io/r/33183cd9?m=611f7d90-e87c-4c43-ab51-0772a7883703" target="_blank">
                                    <div class="kg-bookmark-content">
                                        <div class="kg-bookmark-title">Plastic, the Biosphere and Ecobricks</div>
                                        <div class="kg-bookmark-description">Register for our Online Starter Workshop led by Paula Apollonia and Russell Maier on July 19, 2025</div>
                                        <div class="kg-bookmark-metadata">
                                            <img src="https://earthen.io/content/images/icon/apple-icon-180x180-14.png" width="22" height="22">
                                            <span style="margin-left:8px;">GoBrik.com</span>
                                        </div>
                                    </div>
                                    <div class="kg-bookmark-thumbnail" style="background-image: url('https://earthen.io/content/images/thumbnail/starter-workshop-feature-1-en-2.webp');"></div>
                                </a>
                            </div>

                            <p>Hope to see you there.<br><br>The Earthen Team</p>
                        </td>
                    </tr>

                    <!-- FOOTER -->
                    <tr>
                        <td align="center" style="padding-top: 40px; font-size: 13px; color: #73818c;">
                            <p><em>Together we can be the transition to ever increasing harmony with the cycles of life.</em></p>
                            <p>Earthen © 2025 – <a href="$unsubscribe_link" style="color: #73818c; text-decoration: underline;">Unsubscribe</a></p>
                            <p><a href="https://ghost.org/?via=pbg-newsletter" target="_blank"><img src="https://static.ghost.org/v4.0.0/images/powered.png" width="142" height="30" alt="Powered by Ghost" style="border: none;"></a></p>
                        </td>
                    </tr>
                </table>
            </div>
        </td>
        <td>&nbsp;</td>
    </tr>
</table>

<img width="1" height="1" alt="" src="http://email.earthen.ecobricks.org/o/eJwszkGOgzAMQNHTNLsi2zghWfgwTuxC1DJIgc75Rx11-RdfeiZEKdXggkuETAsgBd-1v-7dJOVYMwByZqIInGlpJbmFTWaoj-qGPDcsRWcmmxHMcvFYvFroQkAR0ueLgDShPVpzT6rq6sY3Btdxbf4zeTvq6O15TsdYw5DxPs9du48bw_rBTO3YwyX1_Xre_3XhknU7zutbv0J_AQAA__9X-j1j">
</body>
</html>
HTML;

?>
