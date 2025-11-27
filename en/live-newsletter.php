<?php

// Build the unsubscribe link using the recipient's email if available.
$unsubscribe_link = isset($recipient_email)
    ? "https://gobrik.com/emailing/unsubscribe.php?email=" . urlencode($recipient_email)
    : "https://earthen.io/unsubscribe/?uuid=4dbbb711-73e9-4fd0-9056-a7cc1af6a905&key=6c3ffe5e66725cd21a19a3f06a3f9c57d439ef226283a59999acecb11fb087dc&newsletter=1db69ae6-6504-48ba-9fd9-d78b3928071f";

$email_template = <<<HTML
<!doctype html>
<html>
    <head>
        <meta name="viewport" content="width=device-width">
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <!--[if mso]><xml><o:OfficeDocumentSettings><o:PixelsPerInch>96</o:PixelsPerInch><o:AllowPNG/></o:OfficeDocumentSettings></xml><![endif]-->
        <title>Writing Earth Right</title>
        <style>
.post-title-link {
  display: block;
  margin-top: 32px;
  color: #15212A;
  text-align: center;
  line-height: 1.1em;
}
.post-title-link-left {
  text-align: left;
}
.view-online-link {
  word-wrap: none;
  white-space: nowrap;
  color: #15212a;
  color: rgba(0, 0, 0, 0.6);
  text-decoration: underline !important;
}
.kg-nft-link {
  display: block;
  text-decoration: none !important;
  color: #15212A !important;
  font-family: inherit !important;
  font-size: 14px;
  line-height: 1.3em;
  padding-top: 4px;
  padding-right: 20px;
  padding-left: 20px;
  padding-bottom: 4px;
}
.kg-twitter-link {
  display: block;
  text-decoration: none !important;
  color: #15212A !important;
  font-family: inherit !important;
  font-size: 15px;
  padding: 8px;
  line-height: 1.3em;
}
.kg-cta-link-accent .kg-cta-sponsor-label a {
  color: #4B8501 !important;
}
.kg-cta-text a:not(.kg-cta-link-accent .kg-cta-text a) {
  color: #15212A;
  text-decoration: underline;
}
.kg-cta-link-accent .kg-cta-text a {
  color: #4B8501 !important;
}
.kg-audio-link {
  color: #15212a !important;
  color: rgba(0, 0, 0, 0.6) !important;
}
@media only screen and (max-width: 620px) {
  table.body {
    width: 100%;
    min-width: 100%;
  }

  .hide-mobile {
    display: none;
  }

  .mobile-only {
    display: initial !important;
  }

  .hide-desktop {
    display: initial !important;
  }

  .desktop-only {
    display: none !important;
  }

  table.body p,
table.body ul,
table.body ol,
table.body td {
    font-size: 16px;
  }

  table.header .post-excerpt {
    font-size: 16px !important;
  }

  table.body .kg-callout-card {
    padding: 16px 24px !important;
  }

  table.body .kg-callout-text {
    font-size: 16px !important;
    line-height: 1.5em !important;
  }

  table.body pre {
    white-space: pre-wrap !important;
    word-break: break-word !important;
  }

  table.body .content {
    padding: 0 !important;
  }

  table.body .container {
    padding: 0 !important;
    width: 100% !important;
  }

  table.body .main,
table.header .header-main {
    border-spacing: 10px 0 !important;
    border-left-width: 0 !important;
    border-radius: 0 !important;
    border-right-width: 0 !important;
  }

  table.body .img-responsive {
    height: auto !important;
    max-width: 100% !important;
    width: auto !important;
  }

  table.header .site-icon {
    padding-top: 0 !important;
  }

  table.header .site-info {
    padding-top: 24px !important;
  }

  table.header .post-title-link {
    margin-top: 24px !important;
  }

  table.header .post-meta-wrapper {
    padding-bottom: 24px !important;
  }

  table.header .site-icon img {
    width: 36px !important;
    height: 36px !important;
  }

  table.header .site-url a {
    font-size: 13px !important;
    padding-bottom: 16px !important;
  }

  table.header .post-meta,
table.header .post-meta-date {
    white-space: normal !important;
    font-size: 13px !important;
    line-height: 1.2em;
  }

  table.header .post-meta,
table.header .view-online {
    width: 100% !important;
  }

  table.header .post-meta-left,
table.header .post-meta-left.view-online {
    width: 100% !important;
    text-align: left !important;
  }

  table.header .post-meta.view-online-mobile {
    display: table-row !important;
  }

  table.header .post-meta-left.view-online-mobile,
table.header .post-meta-left.view-online-mobile .view-online {
    text-align: left !important;
  }

  table.header .post-meta.view-online.desktop {
    display: none !important;
  }

  table.header .view-online {
    text-decoration: underline;
  }

  table.body .footer p,
table.body .footer p span {
    font-size: 13px !important;
  }

  table.header .view-online-link,
table.body .footer,
table.body .footer a {
    font-size: 13px !important;
  }

  table.header .post-title a {
    font-size: 26px !important;
    line-height: 1.1em !important;
  }

  table.feedback-buttons {
    display: table !important;
    width: 100% !important;
    max-width: 390px;
  }

  table.feedback-buttons img {
    display: inherit !important;
  }

  table.body .feedback-button-text {
    display: none!important;
  }

  table.body .latest-posts-header {
    font-size: 12px !important;
  }

  table.body .latest-post-title {
    padding-right: 8px !important;
  }

  table.body .latest-post h4,
table.body .latest-post h4 span {
    padding: 4px 0 6px !important;
    font-size: 15px !important;
  }

  table.body .latest-post-excerpt,
table.body .latest-post-excerpt a,
table.body .latest-post-excerpt span {
    font-size: 13px !important;
    line-height: 1.2 !important;
  }

  table.body .subscription-box h3 {
    font-size: 14px !important;
  }

  table.body .subscription-box p,
table.body .subscription-box p span {
    font-size: 13px !important;
  }

  table.body .subscription-details,
table.body .manage-subscription {
    display: inline-block;
    width: 100%;
    text-align: left !important;
    font-size: 13px !important;
  }

  table.body .subscription-details {
    padding-bottom: 12px;
  }

  table.body .kg-bookmark-card {
    width: 90vw;
  }

  table.body .kg-bookmark-thumbnail {
    display: none !important;
  }

  table.body .kg-bookmark-metadata span {
    font-size: 13px !important;
  }

  table.body .kg-embed-card {
    max-width: 90vw !important;
  }

  table.body h1 {
    font-size: 32px !important;
    line-height: 1.3em !important;
  }

  table.body h2,
table.body h2 span {
    font-size: 26px !important;
    line-height: 1.22em !important;
  }

  table.body h3 {
    font-size: 21px !important;
    line-height: 1.25em !important;
  }

  table.body h4 {
    font-size: 19px !important;
    line-height: 1.3em !important;
  }

  table.body h5 {
    font-size: 16px !important;
    line-height: 1.4em !important;
  }

  table.body h6 {
    font-size: 16px !important;
    line-height: 1.4em !important;
  }

  table.body blockquote {
    font-size: 16px !important;
    line-height: 1.6em;
    margin-bottom: 0;
  }

  table.body blockquote p {
    margin-right: 15px !important;
    margin-left: 15px !important;
  }

  table.body blockquote.kg-blockquote-alt {
    border-left: 0 none !important;
    margin: 0 !important;
    font-size: 18px !important;
    line-height: 1.4em !important;
  }

  table.body blockquote.kg-blockquote-alt p {
    margin-right: 20px !important;
    margin-left: 20px !important;
  }

  table.body hr {
    margin: 2em 0 !important;
  }

  .feature-image-caption {
    font-size: 13px!important;
  }

  .kg-card-figcaption {
    font-size: 13px!important;
  }

  .kg-card-figcaption p,
.kg-card-figcaption p span {
    font-size: 13px!important;
  }

  table.body .kg-cta-card {
    padding: 0 20px;
  }

  table.body .kg-cta-card.kg-cta-bg-none {
    padding: 0;
  }

  table.body .kg-cta-sponsor-label {
    padding: 10px 0;
  }

  table.body table.kg-cta-content-wrapper:not(.kg-cta-bg-none.kg-cta-no-dividers table.kg-cta-content-wrapper) {
    padding: 20px 0;
  }

  table.body .kg-cta-immersive.kg-cta-has-img:not(.kg-cta-bg-none):not(.kg-cta-no-label) table.kg-cta-content-wrapper {
    padding-top: 0;
  }

  table.body .kg-cta-minimal .kg-cta-image-container {
    padding-right: 20px;
  }

  table.body .kg-cta-immersive .kg-cta-image-container {
    padding-bottom: 20px;
  }

  table.body .kg-cta-immersive.kg-cta-no-text .kg-cta-image-container {
    padding-bottom: 0;
  }

  table.body .kg-cta-button-container {
    padding-top: 16px;
  }

  table.body .kg-cta-minimal .kg-cta-image-container {
    display: inline-block !important;
    width: 100% !important;
    padding: 0 !important;
    padding-bottom: 16px !important;
    padding-right: 0 !important;
  }

  table.body .kg-cta-minimal .kg-cta-content-inner {
    display: inline-block !important;
    width: 100% !important;
    padding: 0 !important;
  }

  table.body .kg-cta-minimal img.kg-cta-image {
    width: 52px !important;
    height: 52px !important;
  }
}
@media all {
  .subscription-details p.hidden {
    display: none !important;
  }

  .ExternalClass {
    width: 100%;
  }

  .ExternalClass,
.ExternalClass p,
.ExternalClass span,
.ExternalClass font,
.ExternalClass td,
.ExternalClass div {
    line-height: 100%;
  }

  .apple-link a {
    color: inherit !important;
    font-family: inherit !important;
    font-size: inherit !important;
    font-weight: inherit !important;
    line-height: inherit !important;
    text-decoration: none !important;
  }

  #MessageViewBody a {
    color: inherit;
    text-decoration: none;
    font-size: inherit;
    font-family: inherit;
    font-weight: inherit;
    line-height: inherit;
  }
}
</style>
        <!--[if mso]>
        <style type="text/css">
            ul, ol { margin-left: 1.5em !important; } 
        </style>
        <![endif]-->
    </head>
    <body data-testid="email-preview-body" style="background-color: #fff; font-family: -apple-system, BlinkMacSystemFont, Roboto, Helvetica, Arial, sans-serif; -webkit-font-smoothing: antialiased; font-size: 18px; line-height: 0; margin: 0; padding: 0; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; color: #15212A;">
        <span class="preheader" style="color: transparent; display: none; height: 0; max-height: 0; max-width: 0; opacity: 0; overflow: hidden; mso-hide: all; visibility: hidden; width: 0;">How we write the name of our home planet unconsciously encodes the relationship we have with it


Today, as we become more and more aware of the ecological crises that beset our planet, many of us are moved to take action. Frankly, it&#39;s pretty inspiring. Over the last two decades, as we&#39;ve all come to observe and experience the effects of our modern industrialized economies, environmental care has become a defining concern. Green is not only cool&#x2014;it&#39;s the color of the new generation. Ecological </span>
        <!-- SPACING TO AVOID BODY TEXT BEING DUPLICATED IN PREVIEW TEXT -->
        <div style="display:none; max-height:0; overflow:hidden; mso-hide: all;" aria-hidden="true" role="presentation">
            &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#x2007;&#x34F; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD; &#xAD;  &#xA0;
        </div>

        <!-- HEADER WITH FULL-WIDTH BACKGROUND -->
            <table role="presentation" border="0" cellpadding="0" cellspacing="0" class="header" width="100%" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; line-height: 1.4; background-color: #ffffff; width: 100%; border-spacing: 0; border-collapse: collapse;" bgcolor="#ffffff">
                <!-- Outlook doesn't respect max-width so we need an extra centered table -->
                <!--[if mso]>
                <tr>
                    <td>
                        <center>
                            <table border="0" cellpadding="0" cellspacing="0" width="600">
                <![endif]-->
                <tr>
                    <td style="font-family: -apple-system, BlinkMacSystemFont, Roboto, Helvetica, Arial, sans-serif; font-size: 18px; vertical-align: top; color: #15212A;" valign="top">&#xA0;</td>
                    <td class="container" align="center" style="font-family: -apple-system, BlinkMacSystemFont, Roboto, Helvetica, Arial, sans-serif; font-size: 18px; vertical-align: top; color: #15212A;" valign="top">
                        <div class="content" style="box-sizing: border-box; display: block; margin: 0 auto; max-width: 600px;">
                        <!-- Header content constrained to 600px -->
                        <table role="presentation" border="0" cellpadding="0" cellspacing="0" class="header-main" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; border-spacing: 20px 0; width: 100%; max-width: 600px; background: #ffffff; background-color: transparent;" width="100%" bgcolor="transparent">
                            <tr>
                                <td class="header-content" style="font-family: -apple-system, BlinkMacSystemFont, Roboto, Helvetica, Arial, sans-serif; font-size: 18px; vertical-align: top; color: #15212A; padding: 0; margin: 0 auto;" valign="top">
                                    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%;">

                                            <tr class="site-info-row">
                                                <td class="site-info" width="100%" align="center" style="font-family: -apple-system, BlinkMacSystemFont, Roboto, Helvetica, Arial, sans-serif; font-size: 18px; vertical-align: top; color: #15212A; padding-top: 32px;" valign="top">
                                                    <table role="presentation" border="0" cellpadding="0" cellspacing="0" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%;" width="100%">
                                                            <tr>
                                                                <td class="site-icon" style="font-family: -apple-system, BlinkMacSystemFont, Roboto, Helvetica, Arial, sans-serif; font-size: 18px; vertical-align: top; color: #15212A; padding-bottom: 8px; padding-top: 8px; text-align: center; border-radius: 3px;" valign="top" align="center"><a href="https://earthen.io/r/8521f669?m=4dbbb711-73e9-4fd0-9056-a7cc1af6a905" style="color: #4B8501; text-decoration: none; overflow-wrap: anywhere;" target="_blank"><img src="https://earthen.io/content/images/2022/07/page-logo.png" alt="Earthen" border="0" width="44" height="44" style="border: none; -ms-interpolation-mode: bicubic; max-width: 100%; width: 44px; height: 44px; border-radius: 3px;"></a></td>
                                                            </tr>
                                                            <tr>
                                                                <td class="site-url " style="font-family: -apple-system, BlinkMacSystemFont, Roboto, Helvetica, Arial, sans-serif; vertical-align: top; color: #15212A; font-size: 16px; letter-spacing: -0.1px; font-weight: 700; text-transform: uppercase; text-align: center;" valign="top" align="center"><div style="width: 100% !important;"><a href="https://earthen.io/r/fb84f209?m=4dbbb711-73e9-4fd0-9056-a7cc1af6a905" class="site-title" style="text-decoration: none; color: #15212A; overflow-wrap: anywhere;" target="_blank">Earthen</a></div></td>
                                                            </tr>
                                                            <tr>
                                                                <td class="site-url site-url-bottom-padding" style="font-family: -apple-system, BlinkMacSystemFont, Roboto, Helvetica, Arial, sans-serif; vertical-align: top; color: #15212A; font-size: 16px; letter-spacing: -0.1px; font-weight: 700; text-transform: uppercase; text-align: center; padding-bottom: 12px;" valign="top" align="center"><div style="width: 100% !important;"><a href="https://earthen.io/r/0d112d8d?m=4dbbb711-73e9-4fd0-9056-a7cc1af6a905" class="site-subtitle" style="text-decoration: none; color: #15212a; color: rgba(0, 0, 0, 0.6); font-size: 13px; font-weight: 400; text-transform: none; overflow-wrap: anywhere;" target="_blank">Ayyew 452</a></div></td>
                                                            </tr>

                                                    </table>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td class="post-title post-title-no-excerpt" style="font-family: -apple-system, BlinkMacSystemFont, Roboto, Helvetica, Arial, sans-serif; font-size: 18px; vertical-align: top; text-align: center; color: #000000; padding-bottom: 16px;" valign="top" align="center">
                                                    <table role="presentation" border="0" cellpadding="0" cellspacing="0" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%;" width="100%">
                                                        <tr>
                                                            <td style="font-family: -apple-system, BlinkMacSystemFont, Roboto, Helvetica, Arial, sans-serif; vertical-align: top; font-size: 36px; line-height: 1.1; font-weight: 700; text-align: center; color: #000000;" valign="top" align="center">
                                                                <a href="https://earthen.io/r/5ac647ee?m=4dbbb711-73e9-4fd0-9056-a7cc1af6a905" class="post-title-link" style="text-decoration: none; display: block; margin-top: 32px; text-align: center; line-height: 1.1em; overflow-wrap: anywhere; color: #000000;" target="_blank">Writing Earth Right</a>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td width="100%" style="font-family: -apple-system, BlinkMacSystemFont, Roboto, Helvetica, Arial, sans-serif; font-size: 18px; vertical-align: top; color: #15212A; width: 100%;" align="center" valign="top">
                                                    <table class="post-meta-wrapper" role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%; padding-bottom: 32px;">
                                                        <tr>
                                                            <td height="20" class="post-meta post-meta-center" style="font-family: -apple-system, BlinkMacSystemFont, Roboto, Helvetica, Arial, sans-serif; vertical-align: top; color: #15212a; color: rgba(0, 0, 0, 0.6); font-size: 13px; font-weight: 400; text-align: center; padding: 0;" valign="top" align="center">
                                                                By Russell Maier &#x2022; <span class="post-meta-date" style="white-space: nowrap;">27 Nov 2025 </span>
                                                            </td>
                                                            <td class="post-meta post-meta-center view-online desktop" style="font-family: -apple-system, BlinkMacSystemFont, Roboto, Helvetica, Arial, sans-serif; vertical-align: top; color: rgba(0, 0, 0, 0.6); font-size: 13px; font-weight: 400; text-align: center; display: none;" valign="top" align="center">
                                                                <a href="https://earthen.io/r/474eda83?m=4dbbb711-73e9-4fd0-9056-a7cc1af6a905" class="view-online-link" style="word-wrap: none; white-space: nowrap; color: #15212a; color: rgba(0, 0, 0, 0.6); overflow-wrap: anywhere; text-decoration: underline;" target="_blank">View in browser</a>
                                                            </td>
                                                        </tr>
                                                        <tr class="post-meta post-meta-center view-online-mobile" style="color: #15212a; color: rgba(0, 0, 0, 0.6); font-size: 13px; font-weight: 400; text-align: center;" align="center">
                                                            <td height="20" class="view-online" style="font-family: -apple-system, BlinkMacSystemFont, Roboto, Helvetica, Arial, sans-serif; vertical-align: top; color: #15212a; color: rgba(0, 0, 0, 0.6); font-size: 13px; font-weight: 400; text-align: center; text-decoration: underline;" valign="top" align="center">
                                                                <a href="https://earthen.io/r/f9900ec1?m=4dbbb711-73e9-4fd0-9056-a7cc1af6a905" class="view-online-link" style="word-wrap: none; white-space: nowrap; color: #15212a; color: rgba(0, 0, 0, 0.6); overflow-wrap: anywhere; text-decoration: underline;" target="_blank">View in browser</a>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>

                                            <tr class="feature-image-row">
                                                <td class="feature-image
                                                        feature-image-with-caption
                                                " align="center" style="font-family: -apple-system, BlinkMacSystemFont, Roboto, Helvetica, Arial, sans-serif; vertical-align: top; color: #15212A; padding-bottom: 30px; text-align: center; width: 100%; padding: 0; font-size: 13px;" width="100%" valign="top"><img src="https://earthen.io/content/images/size/w1200/2025/11/earthrise-cropped-1.webp" width="600" style="border: none; -ms-interpolation-mode: bicubic; max-width: 100%;"></td>
                                            </tr>

                                                <tr>
                                                    <td align="center" style="font-family: -apple-system, BlinkMacSystemFont, Roboto, Helvetica, Arial, sans-serif; font-size: 18px; vertical-align: top; color: #15212A;" valign="top">
                                                        <div class="feature-image-caption" style="width: 100%; padding-top: 5px; padding-bottom: 32px; text-align: center; color: #15212a; color: rgba(0, 0, 0, 0.6); line-height: 1.5em; font-size: 13px;">
                                                            <i><em class="italic" style="white-space: pre-wrap;">Earth Rise</em></i><span style="white-space: pre-wrap;"> - first photo of planet Earth from Luna.</span>
                                                        </div>
                                                    </td>
                                                </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                        </div>
                    </td>
                    <td style="font-family: -apple-system, BlinkMacSystemFont, Roboto, Helvetica, Arial, sans-serif; font-size: 18px; vertical-align: top; color: #15212A;" valign="top">&#xA0;</td>
                </tr>
                <!--[if mso]>
                            </table>
                        </center>
                    </td>
                </tr>
                <![endif]-->
            </table>

        <!-- MAIN CONTENT AREA -->
        <table role="presentation" border="0" cellpadding="0" cellspacing="0" class="body" width="100%" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%; line-height: 1.4; background-color: #ffffff;" bgcolor="#ffffff">
            <!-- Outlook doesn't respect max-width so we need an extra centered table -->
            <!--[if mso]>
            <tr>
                <td>
                    <center>
                        <table border="0" cellpadding="0" cellspacing="0" width="600">
            <![endif]-->
            <tr>
                <td style="font-family: -apple-system, BlinkMacSystemFont, Roboto, Helvetica, Arial, sans-serif; font-size: 18px; vertical-align: top; color: #15212A;" valign="top">&#xA0;</td>
                <td class="container" style="font-family: -apple-system, BlinkMacSystemFont, Roboto, Helvetica, Arial, sans-serif; font-size: 18px; vertical-align: top; color: #15212A; display: block; max-width: 600px; margin: 0 auto;" valign="top">
                    <div class="content" style="box-sizing: border-box; display: block; margin: 0 auto; max-width: 600px;">
                        <table role="presentation" border="0" cellpadding="0" cellspacing="0" class="main" width="100%" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; border-spacing: 20px 0; width: 100%; max-width: 600px; background: #ffffff; background-color: #ffffff;" bgcolor="#ffffff">
                            <tr>
                                <td class="wrapper" style="font-family: -apple-system, BlinkMacSystemFont, Roboto, Helvetica, Arial, sans-serif; font-size: 18px; vertical-align: top; color: #15212A; box-sizing: border-box;" valign="top">
                                    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" data-testid="email-preview-content" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%;">
                                        <tr class="post-content-row">
                                            <td class="post-content-sans-serif" style="font-family: -apple-system, BlinkMacSystemFont, Roboto, Helvetica, Arial, sans-serif; vertical-align: top; font-size: 17px; line-height: 1.5em; color: #15212A; padding-bottom: 20px; border-bottom: 1px solid #e0e7eb; max-width: 600px;" valign="top">
                                                <!-- POST CONTENT START -->
                                                <h3 id="how-we-write-the-name-of-our-home-planet-unconsciously-encodes-the-relationship-we-have-with-it" style="margin-top: 0; font-family: -apple-system, BlinkMacSystemFont, Roboto, Helvetica, Arial, sans-serif; line-height: 1.11em; font-weight: 700; text-rendering: optimizeLegibility; margin: 1.5em 0 0.5em 0; font-size: 26px; color: #15212A;">How we write the name of our home planet unconsciously encodes the relationship we have with it<br></h3><p style="margin: 0 0 1.5em 0; line-height: 1.6em; color: #15212A;">Today, as we become more and more aware of the ecological crises that beset our planet, many of us are moved to take action. Frankly, it&#39;s pretty inspiring. Over the last two decades, as we&#39;ve all come to observe and experience the effects of our modern industrialized economies, environmental care has become a defining concern. Green is not only cool&#x2014;it&#39;s the color of the new generation. Ecological integration is not just a trend; it&#39;s a civilizational shift. And planetary thinking isn&#39;t just for astronauts&#x2014; it&#x2019;s for all of us.</p><p style="margin: 0 0 1.5em 0; line-height: 1.6em; color: #15212A;">However, in the vast task of correcting and regenerating modern depletions, it&#39;s easy to get caught up in superficial actions rather than the fundamental shift our age requires.</p><p style="margin: 0 0 1.5em 0; line-height: 1.6em; color: #15212A;">Forget about recycling, gardening, or permaculture projects.</p><p style="margin: 0 0 1.5em 0; line-height: 1.6em; color: #15212A;">I don&#39;t care how many trees you&#39;re going to plant or how many tons of CO&#x2082; you want to offset.</p><p style="margin: 0 0 1.5em 0; line-height: 1.6em; color: #15212A;">There&#39;s really only one place to start.</p><p style="margin: 0 0 1.5em 0; line-height: 1.6em; color: #15212A;">With our words.</p><p style="margin: 0 0 1.5em 0; line-height: 1.6em; color: #15212A;">In particular, the very word for our planet that we so long to love.</p><p style="margin: 0 0 1.5em 0; line-height: 1.6em; color: #15212A;">Long ago, Plato pointed out, &quot;The corruption of the City begins with the corruption of words.&quot; More poignantly, Albert Camus wrote, &quot;<em>Mal nommer un objet, c&#x2019;est ajouter au malheur du monde</em>&quot;&#x2014;&quot;To misname an object is to add to the misfortune of the world&quot;&#xB9;.</p><p style="margin: 0 0 1.5em 0; line-height: 1.6em; color: #15212A;">What were they getting at?</p><p style="margin: 0 0 1.5em 0; line-height: 1.6em; color: #15212A;">They observed that the more our words and their meanings diverge from the reality they represent, the more society suffers. Indeed, our words&#x2014;the tiny building blocks of sentences, paragraphs, stories, and visions&#x2014;if corrupted, can manifest cities and worlds of great woe.</p><p style="margin: 0 0 1.5em 0; line-height: 1.6em; color: #15212A;">While Plato and Camus had societal woe in mind, as we know too well today, there&#x2019;s also the ecological sort: pollution, ecological degradation, and biological depletion.</p><p style="margin: 0 0 1.5em 0; line-height: 1.6em; color: #15212A;">For the last ten years, my colleague Banayan and I have been reflecting on this kind of woe and where it comes from. We&#39;ve been working hard on plastic pollution from the perspective of her people&#x2014;the Igorots, indigenous people of Northern Luzon in the West Philippine Sea. They have managed to retain their culture despite three hundred years of colonizing attempts by the Spanish, Japanese, and Americans. As we&#39;ve worked on essays together, I&#39;ve come to realize that the Igorots (like so many other kincentric cultures) retain a dazzlingly different view of the world than modern cultures.</p><p style="margin: 0 0 1.5em 0; line-height: 1.6em; color: #15212A;">Working with her on refining paragraphs and sentences, I&#x2019;ve become particularly conscious of the problematic modern words in my own English vocabulary.</p><p style="margin: 0 0 1.5em 0; line-height: 1.6em; color: #15212A;">Perhaps the most egregious is the way many of us write the name of our home planet: Earth.</p><p style="margin: 0 0 1.5em 0; line-height: 1.6em; color: #15212A;">Specifically, the third rock from the Sun.</p><p style="margin: 0 0 1.5em 0; line-height: 1.6em; color: #15212A;">Specificity is key here. The word &#x201C;earth&#x201D; has several disambiguations. For example, when we refer to soil or the ground&#x2014;or even tectonic plates&#x2014;we speak of &quot;the earth&quot;. For example, &quot;the earth beneath my feet.&quot; Notice the article and the lack of capitalization. Here, the sentence is not referring to our planet but rather an aspect of its surface. This use of the word <em>earth</em> is not an issue.</p><p style="margin: 0 0 1.5em 0; line-height: 1.6em; color: #15212A;">Writing of our planet Earth, however, is another matter.</p><p style="margin: 0 0 1.5em 0; line-height: 1.6em; color: #15212A;">Alas, all too often, well-intentioned environmentalists and Earth-lovers refer to &quot;the Earth&quot; when they are talking about the planet.</p><p style="margin: 0 0 1.5em 0; line-height: 1.6em; color: #15212A;">For example, Bill Gates writes: &quot;The earth is warming, it&#x2019;s warming because of human activity, and the impact is bad and will get much worse&quot; (2015, <em>How to Solve a Climate Crisis</em>).</p><p style="margin: 0 0 1.5em 0; line-height: 1.6em; color: #15212A;">Another example: &#x201C;Reconnecting with the Earth and each other&#x201D; (<em>Bioneers slogan</em>).</p><p style="margin: 0 0 1.5em 0; line-height: 1.6em; color: #15212A;">Or: &quot;We would seek to preserve the sanctity of life and replenish the Earth&#x2019;s natural systems...&quot; (Daniel Pinchbeck, <em>Foundation</em>).</p><p style="margin: 0 0 1.5em 0; line-height: 1.6em; color: #15212A;">First, let&#39;s be clear, these are all categorical stylistic mistakes.</p><p style="margin: 0 0 1.5em 0; line-height: 1.6em; color: #15212A;">The <a href="https://earthen.io/r/395d6d0b?m=4dbbb711-73e9-4fd0-9056-a7cc1af6a905" rel="noreferrer" style="overflow-wrap: anywhere; color: #4B8501; text-decoration: underline;" target="_blank">AP Stylebook</a> and the <a href="https://earthen.io/r/c3f5759c?m=4dbbb711-73e9-4fd0-9056-a7cc1af6a905" rel="noreferrer" style="overflow-wrap: anywhere; color: #4B8501; text-decoration: underline;" target="_blank">MLA guide</a> stand against the use of the article <em>the</em> when referring to the planet Earth (though slightly lukewarm in their emphasis*).  However, the <a href="https://earthen.io/r/c490a7c8?m=4dbbb711-73e9-4fd0-9056-a7cc1af6a905" rel="noreferrer" style="overflow-wrap: anywhere; color: #4B8501; text-decoration: underline;" target="_blank">UN style guide</a>, and the <a href="https://earthen.io/r/74258bba?m=4dbbb711-73e9-4fd0-9056-a7cc1af6a905" rel="noreferrer" style="overflow-wrap: anywhere; color: #4B8501; text-decoration: underline;" target="_blank">American Astronomical Society, Space.com</a> are clear.  They instruct capitalization.  The <a href="https://earthen.io/r/6a107103?m=4dbbb711-73e9-4fd0-9056-a7cc1af6a905" rel="noreferrer" style="overflow-wrap: anywhere; color: #4B8501; text-decoration: underline;" target="_blank">NASA&#39;s style guide</a> states&quot;[we] <em>capitalize the first letter in Earth, Moon, and Sun. In addition, do not use the modified &#x2018;the&#x2019; before Earth</em>&quot;).  </p><p style="margin: 0 0 1.5em 0; line-height: 1.6em; color: #15212A;">So, if you write &#x201C;the Earth&#x201D; for the planet, you are already swimming against the current of established English language conventions.</p><p style="margin: 0 0 1.5em 0; line-height: 1.6em; color: #15212A;">But this isn&#39;t just about style&#xB2;.  This is ontological.  The way we write &#39;Earth&#39; betrays and perpetuates a deep corruption of thought with dire ecological consequences.</p><p style="margin: 0 0 1.5em 0; line-height: 1.6em; color: #15212A;">Think for a moment about how we talk about the other planets in our solar system.</p><p style="margin: 0 0 1.5em 0; line-height: 1.6em; color: #15212A;">Do you say, &#x201C;I&#x2019;d love to visit the Mars&#x201D;?</p><p style="margin: 0 0 1.5em 0; line-height: 1.6em; color: #15212A;">Or, &#x201C;Looks like the Jupiter&#x2019;s red spot is acting up today&#x201D;?</p><p style="margin: 0 0 1.5em 0; line-height: 1.6em; color: #15212A;">Or, &#x201C;Let&#x2019;s try to avoid the atmospheric fate of the Venus&#x201D;?</p><p style="margin: 0 0 1.5em 0; line-height: 1.6em; color: #15212A;">Of course not.</p><p style="margin: 0 0 1.5em 0; line-height: 1.6em; color: #15212A;">We intuitively recognize Mars, Jupiter, Venus as proper names. Names that belong to entities, not objects. We don&#x2019;t put an article in front of them because <em>the</em> turns a be-ing into a some-thing.</p><p style="margin: 0 0 1.5em 0; line-height: 1.6em; color: #15212A;">After all, the planets are named after the great gods of antiquity for a reason. As celestial companions to humanity for countless millennia, these beacons of light in the sky are filled with mystery and majesty. Even today, as per the International Astronomical Union&#x2019;s naming conventions, newly discovered planets and moons must be named after gods from one of humanity&#x2019;s religions. As we send probes to planets to learn more about their unique combinations of matter, orbital positions, and chemistries, we increasingly appreciate that each planet has a cosmological character all its own.</p><p style="margin: 0 0 1.5em 0; line-height: 1.6em; color: #15212A;">Earth is of course one of these planets.</p><p style="margin: 0 0 1.5em 0; line-height: 1.6em; color: #15212A;">And yet, over the last two millennia, our view and naming of our home world has become corrupted.</p><p style="margin: 0 0 1.5em 0; line-height: 1.6em; color: #15212A;">One can trace it back to ancient Greek and Roman axioms that put humans on a pedestal. Over time, their glorification of human gods and heroes evolved into an understanding of the world in the which humans were special, separate and central to everything else. This current of anthropocentricism eventually culminated in the Scientific Revolution&#x2019;s materialist worldview. Grounded in Cartesian dualism, matter and mind, humanity and <a href="https://earthen.io/r/b9260512?m=4dbbb711-73e9-4fd0-9056-a7cc1af6a905" rel="noreferrer" style="overflow-wrap: anywhere; color: #4B8501; text-decoration: underline;" target="_blank">nature</a>, were split apart. The physical world&#x2014;<em>res extensa</em>&#x2014;was to be studied, measured, and mastered, while mind and spirit were relegated to the abstract.</p><p style="margin: 0 0 1.5em 0; line-height: 1.6em; color: #15212A;">From this divide, Earth came to be seen not as a living being or a sacred entity, but as an inert object. The shift was subtle yet profound: Earth was no longer an entity we lived with, but what we lived on.</p><p style="margin: 0 0 1.5em 0; line-height: 1.6em; color: #15212A;">Modern ideologies, whether capitalist or socialist, feminist or fascist, inherited this foundational view of Earth as a passive provider. Rather than seeing mountains, rivers, trees, animals&#x2014;each with majesty and character all their own&#x2014;the modern gaze came to see them more and more as objects: Forests became timber, mountains became mines, rivers became water supplies.</p><p style="margin: 0 0 1.5em 0; line-height: 1.6em; color: #15212A;">Numerous words we use today reflects this thinking&#x2014;&#x201C;the Earth,&#x201D;, &quot;nature&quot;, &#x201C;natural resources,&#x201D; &#x201C;the environment&#x201D; (<a href="https://earthen.io/r/6e99c03b?m=4dbbb711-73e9-4fd0-9056-a7cc1af6a905" rel="noreferrer" style="overflow-wrap: anywhere; color: #4B8501; text-decoration: underline;" target="_blank">all terms that are missing in the Igorot language</a>). This linguistic objectification enabled, and indeed justified, the expansion of extractive economies and colonial dominion. The English language has come to quite literally &#39;articulate&#39; the world around us: to divide it into inanimate objects that are separate and self-less.</p><p style="margin: 0 0 1.5em 0; line-height: 1.6em; color: #15212A;">In sharp contrast, kincentric cultures such as the Igorots refer to plants, animals, mountains, and rivers as entities&#x2014;without qualifying articles. Kincentric languages carry a built-in reverence for those with whom they share space. You do not say <em>the Grandmother</em> when you call her by name; you simply say her name. Likewise, a mountain, a river, a forest is addressed as someone, not something.</p><p style="margin: 0 0 1.5em 0; line-height: 1.6em; color: #15212A;">Learning from them, we must revisit not only our actions but our assumptions&#x2013; the integral transition of our age. And that begins with how we write and how we name the unique cosmological entity that is our common home.</p><p style="margin: 0 0 1.5em 0; line-height: 1.6em; color: #15212A;">Perhaps the measure of how important this transition is can be seen in how difficult it is to make.</p><p style="margin: 0 0 1.5em 0; line-height: 1.6em; color: #15212A;">In my own writing, I&#x2019;ve been striving to excise &#x201C;the&#x201D; from <em>Earth</em> for the last year! Despite reflecting on the importance of this change, developing an ethical theory around it, and writing this very essay, I constantly find myself correcting my inadvertent Earth objectifications.</p><p style="margin: 0 0 1.5em 0; line-height: 1.6em; color: #15212A;">I am sure you will too.</p><p style="margin: 0 0 1.5em 0; line-height: 1.6em; color: #15212A;">All the more reason to be gentle with those who make the mistake. The deeply ingrained tendency to write this way points to just how embedded the objectification of Earth is in our modern way of seeing the world.</p><p style="margin: 0 0 1.5em 0; line-height: 1.6em; color: #15212A;">However, there is no room for ambiguity.</p><p style="margin: 0 0 1.5em 0; line-height: 1.6em; color: #15212A;">Not only is the articulation and objectification of Earth stylistically flawed, it belies an insidious misconception. For ancient and ongoing kincentric cultures the objectification of Earth and her entities is both absent in their language and anathema in their ontology.</p><p style="margin: 0 0 1.5em 0; line-height: 1.6em; color: #15212A;">To move forward, we must first thoroughly excise our objectification of planet Earth from our vocabulary. Only then can we shatter the mind-forged manacles of antiquated, materialist cosmology and open the door to the ecological regeneration to which our moment so urgently calls.</p><p style="margin: 0 0 1.5em 0; line-height: 1.6em; color: #15212A;">Here is the door.</p><p style="margin: 0 0 1.5em 0; line-height: 1.6em; color: #15212A;">Once we remove &#x201C;the&#x201D; from &#x201C;Earth&#x201D;, it is that much easier to remove it when we speak of Bear, of Salmon, of Eagle, of Whale, and whatever other kindred creatures share space with you. And once we do, their commodification, exploitation, and depletion become that much more difficult and inconceivable.</p><p style="margin: 0 0 1.5em 0; line-height: 1.6em; color: #15212A;">Furthermore, when we name Earth without an article, we name a presence rather than an object. We honor a cosmological entity that has, for billions of years, concentrated matter, dispersed energy, increased biodiversity, and raised awareness.  And with that, we can we at last see Earth as a teacher&#x2014;an example that we, as Earthlings, can and <a href="https://earthen.io/r/0c4e2d1d?m=4dbbb711-73e9-4fd0-9056-a7cc1af6a905" rel="noreferrer" style="overflow-wrap: anywhere; color: #4B8501; text-decoration: underline;" target="_blank">should follow</a>. </p><p style="margin: 0 0 1.5em 0; line-height: 1.6em; color: #15212A;">Only then, as our language re-aligns and harmonizes with ecological and ontological reality, so too can we.</p><p style="margin: 0 0 1.5em 0; line-height: 1.6em; color: #15212A;">It is synchronization that must begin at the root&#x2013; with our words. In this small but sovereign act of re-naming, our sentences change, our stories change, and with them, our cities and our world.</p><p style="margin: 0 0 1.5em 0; line-height: 1.6em; color: #15212A;"></p><p style="margin: 0 0 1.5em 0; line-height: 1.6em; color: #15212A;">___________________<br>&#xB9; My translation.  From Albert Camus, <em>Sur une philosophie de l&#39;expression</em>, Po&#xE9;sie 44, 1944).<br>&#xB2; Indeed, the <a href="https://earthen.io/r/8c695111?m=4dbbb711-73e9-4fd0-9056-a7cc1af6a905" rel="noreferrer" style="overflow-wrap: anywhere; color: #4B8501; text-decoration: underline;" target="_blank">Chicago Manual of Style</a> doesn&#39;t take a stand on the matter.  They simply urge writers to be consistent with whatever capitalization and articulation they choose.  Of course, this is precisely why I wrote this essay: this needs to be correct.  They need to upgrade their guidelines for the sake of our common home and to align with empirical reality.  <br></p><table class="kg-card kg-hr-card" role="presentation" width="100%" border="0" cellpadding="0" cellspacing="0" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%;"><tbody><tr><td style="font-family: -apple-system, BlinkMacSystemFont, Roboto, Helvetica, Arial, sans-serif; vertical-align: top; color: #15212A; margin: 0; padding-top: 1.5em; padding-bottom: 3em; font-size: 17px;" valign="top"><!--[if !mso]><!-- --><hr style="position: relative; width: 100%; margin: 3em 0; padding: 0; height: 1px; background-color: transparent; border: 0; border-top: 1px solid #e0e7eb; display: none;"><!--<![endif]--><table class="kg-hr" role="presentation" border="0" cellpadding="0" cellspacing="0" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%;" width="100%"><tbody><tr><td style="font-family: -apple-system, BlinkMacSystemFont, Roboto, Helvetica, Arial, sans-serif; vertical-align: top; color: #15212A; padding-top: 1.5em; padding-bottom: 3em; margin: 0; padding: 0; border-top: 1px solid #e0e7eb; font-size: 0; line-height: 0;" valign="top">&#xA0;</td></tr></tbody></table></td></tr></tbody></table><div>
        <!--[if !mso !vml]-->
            <div class="kg-card kg-bookmark-card kg-card-hascaption" style="margin: 0 0 1.5em; padding: 0; width: 100%;">
                <a class="kg-bookmark-container" href="https://earthen.io/r/35b0f387?m=4dbbb711-73e9-4fd0-9056-a7cc1af6a905" style="display: flex; min-height: 148px; font-family: -apple-system, BlinkMacSystemFont, Roboto, Helvetica, Arial, sans-serif; border-radius: 3px; background-color: #ffffff; background-color: rgba(255, 255, 255, 0.25); border: 1px solid #e0e7eb; border: 1px solid rgba(0, 0, 0, 0.12); overflow-wrap: anywhere; color: #4B8501; text-decoration: none;" target="_blank">
                    <div class="kg-bookmark-content" style="display: inline-block; width: 100%; padding: 20px;">
                        <div class="kg-bookmark-title" style="color: #15212A; font-size: 15px; line-height: 1.5em; font-weight: 600;">Plastic, the Biosphere and Ecobricks</div>
                        <div class="kg-bookmark-description" style="display: -webkit-box; overflow-y: hidden; margin-top: 12px; max-height: 40px; color: #15212a; color: rgba(0, 0, 0, 0.6); font-size: 13px; line-height: 1.5em; font-weight: 400; -webkit-line-clamp: 2; -webkit-box-orient: vertical;">Register for our Online Starter Workshop led by Paula Apollonia and Russell Maier on Dece<span class="desktop-only">mber 7, 2025</span><span class="hide-desktop" style="display: none;">&#x2026;</span></div>
                        <div class="kg-bookmark-metadata" style="display: flex; flex-wrap: wrap; align-items: center; margin-top: 14px; color: #15212A; font-size: 13px; font-weight: 400;">
                            <img class="kg-bookmark-icon" src="https://earthen.io/content/images/icon/apple-icon-180x180-21.png" alt style="border: none; -ms-interpolation-mode: bicubic; max-width: 100%; margin-right: 8px; width: 22px; height: 22px;" width="22" height="22">
                            <span class="kg-bookmark-author" src="GoBrik.com" style="line-height: 1.5em;">GoBrik.com</span>
                            
                        </div>
                    </div>
                    <div class="kg-bookmark-thumbnail" style="min-width: 140px; max-width: 180px; background-repeat: no-repeat; background-size: cover; background-position: center; border-radius: 0 2px 2px 0; background-image: url(&#39;https://earthen.io/content/images/thumbnail/starter-workshop-feature-1-en-3.webp&#39;);">
                        <img src="https://earthen.io/content/images/thumbnail/starter-workshop-feature-1-en-3.webp" alt onerror="this.style.display=&#39;none&#39;" style="border: none; -ms-interpolation-mode: bicubic; max-width: 100%; display: none;"></div>
                </a>
                <div class="kg-card-figcaption" style="text-align: center; font-family: -apple-system, BlinkMacSystemFont, Roboto, Helvetica, Arial, sans-serif; padding-top: 10px; padding-bottom: 10px; line-height: 1.5em; color: #15212a; color: rgba(0, 0, 0, 0.6); font-size: 13px;"><p style="margin: 0 0 1.5em 0; line-height: 1.6em; color: inherit;"><span style="white-space: pre-wrap;">Heads up: I will be leading an intro to ecobrick course early December with GEA co-trainer Paula. Registration is free.</span></p></div>
            </div>
        <!--[endif]-->
        <!--[if vml]>
            <table class="kg-card kg-bookmark-card--outlook" style="margin: 0; padding: 0; width: 100%; border: 1px solid #e5eff5; background: #ffffff; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif; border-collapse: collapse; border-spacing: 0;" width="100%">
                <tr>
                    <td width="100%" style="padding: 20px;">
                        <table style="margin: 0; padding: 0; border-collapse: collapse; border-spacing: 0;">
                            <tr>
                                <td class="kg-bookmark-title--outlook">
                                    <a href="https://gobrik.com/en/register.php?id=954" style="text-decoration: none; color: #15212A; font-size: 15px; line-height: 1.5em; font-weight: 600;">
                                        Plastic, the Biosphere and Ecobricks
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="kg-bookmark-description--outlook">
                                        <a href="https://gobrik.com/en/register.php?id=954" style="text-decoration: none; margin-top: 12px; color: #738a94; font-size: 13px; line-height: 1.5em; font-weight: 400;">
                                            Register for our Online Starter Workshop led by Paula Apollonia and Russell Maier on Dece<span class="desktop-only">mber 7, 2025</span><span class="hide-desktop">…</span>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td class="kg-bookmark-metadata--outlook" style="padding-top: 14px; color: #15212A; font-size: 13px; font-weight: 400; line-height: 1.5em;">
                                    <table style="margin: 0; padding: 0; border-collapse: collapse; border-spacing: 0;">
                                        <tr>
                                            
                                                <td valign="middle" class="kg-bookmark-icon--outlook" style="padding-right: 8px; font-size: 0; line-height: 1.5em;">
                                                    <a href="https://gobrik.com/en/register.php?id=954" style="text-decoration: none; color: #15212A;">
                                                        <img src="https://earthen.io/content/images/icon/apple-icon-180x180-21.png" width="22" height="22" alt=" ">
                                                    </a>
                                                </td>
                                            
                                            <td valign="middle" class="kg-bookmark-byline--outlook">
                                                <a href="https://gobrik.com/en/register.php?id=954" style="text-decoration: none; color: #15212A;">
                                                    GoBrik.com
                                                    
                                                    
                                                </a>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
            <div class="kg-bookmark-spacer--outlook" style="height: 1.5em;">&nbsp;</div>
        <![endif]--></div><p style="margin: 0 0 1.5em 0; line-height: 1.6em; color: #15212A;">Last week&#39;s essay:</p><div>
        <!--[if !mso !vml]-->
            <div class="kg-card kg-bookmark-card " style="margin: 0 0 1.5em; padding: 0; width: 100%;">
                <a class="kg-bookmark-container" href="https://earthen.io/r/68c11051?m=4dbbb711-73e9-4fd0-9056-a7cc1af6a905" style="display: flex; min-height: 148px; font-family: -apple-system, BlinkMacSystemFont, Roboto, Helvetica, Arial, sans-serif; border-radius: 3px; background-color: #ffffff; background-color: rgba(255, 255, 255, 0.25); border: 1px solid #e0e7eb; border: 1px solid rgba(0, 0, 0, 0.12); overflow-wrap: anywhere; color: #4B8501; text-decoration: none;" target="_blank">
                    <div class="kg-bookmark-content" style="display: inline-block; width: 100%; padding: 20px;">
                        <div class="kg-bookmark-title" style="color: #15212A; font-size: 15px; line-height: 1.5em; font-weight: 600;">The Problem with Google Calendar</div>
                        <div class="kg-bookmark-description" style="display: -webkit-box; overflow-y: hidden; margin-top: 12px; max-height: 40px; color: #15212a; color: rgba(0, 0, 0, 0.6); font-size: 13px; line-height: 1.5em; font-weight: 400; -webkit-line-clamp: 2; -webkit-box-orient: vertical;">As we hit the limits of the Roman Empire&#x2019;s 2,000-year-old Calendar, now is the time for t<span class="desktop-only">ransition from linear and huma</span>&#x2026;</div>
                        <div class="kg-bookmark-metadata" style="display: flex; flex-wrap: wrap; align-items: center; margin-top: 14px; color: #15212A; font-size: 13px; font-weight: 400;">
                            <img class="kg-bookmark-icon" src="https://earthen.io/content/images/icon/page-logo-10.png" alt style="border: none; -ms-interpolation-mode: bicubic; max-width: 100%; margin-right: 8px; width: 22px; height: 22px;" width="22" height="22">
                            <span class="kg-bookmark-author" src="Earthen" style="line-height: 1.5em;">Earthen</span>
                            <span class="kg-bookmark-publisher" src="Russell Maier" style="overflow: hidden; max-width: 240px; line-height: 1.5em; text-overflow: ellipsis; white-space: nowrap;"><span style="margin: 0 6px;">&#x2022;</span>Russell Maier</span>
                        </div>
                    </div>
                    <div class="kg-bookmark-thumbnail" style="min-width: 140px; max-width: 180px; background-repeat: no-repeat; background-size: cover; background-position: center; border-radius: 0 2px 2px 0; background-image: url(&#39;https://earthen.io/content/images/thumbnail/colleuseum-engraving2-1.webp&#39;);">
                        <img src="https://earthen.io/content/images/thumbnail/colleuseum-engraving2-1.webp" alt onerror="this.style.display=&#39;none&#39;" style="border: none; -ms-interpolation-mode: bicubic; max-width: 100%; display: none;"></div>
                </a>
                
            </div>
        <!--[endif]-->
        <!--[if vml]>
            <table class="kg-card kg-bookmark-card--outlook" style="margin: 0; padding: 0; width: 100%; border: 1px solid #e5eff5; background: #ffffff; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif; border-collapse: collapse; border-spacing: 0;" width="100%">
                <tr>
                    <td width="100%" style="padding: 20px;">
                        <table style="margin: 0; padding: 0; border-collapse: collapse; border-spacing: 0;">
                            <tr>
                                <td class="kg-bookmark-title--outlook">
                                    <a href="https://earthen.io/the-problem-with-google-calendar/" style="text-decoration: none; color: #15212A; font-size: 15px; line-height: 1.5em; font-weight: 600;">
                                        The Problem with Google Calendar
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="kg-bookmark-description--outlook">
                                        <a href="https://earthen.io/the-problem-with-google-calendar/" style="text-decoration: none; margin-top: 12px; color: #738a94; font-size: 13px; line-height: 1.5em; font-weight: 400;">
                                            As we hit the limits of the Roman Empire’s 2,000-year-old Calendar, now is the time for t<span class="desktop-only">ransition from linear and huma</span>…
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td class="kg-bookmark-metadata--outlook" style="padding-top: 14px; color: #15212A; font-size: 13px; font-weight: 400; line-height: 1.5em;">
                                    <table style="margin: 0; padding: 0; border-collapse: collapse; border-spacing: 0;">
                                        <tr>
                                            
                                                <td valign="middle" class="kg-bookmark-icon--outlook" style="padding-right: 8px; font-size: 0; line-height: 1.5em;">
                                                    <a href="https://earthen.io/the-problem-with-google-calendar/" style="text-decoration: none; color: #15212A;">
                                                        <img src="https://earthen.io/content/images/icon/page-logo-10.png" width="22" height="22" alt=" ">
                                                    </a>
                                                </td>
                                            
                                            <td valign="middle" class="kg-bookmark-byline--outlook">
                                                <a href="https://earthen.io/the-problem-with-google-calendar/" style="text-decoration: none; color: #15212A;">
                                                    Earthen
                                                    &nbsp;&#x2022;&nbsp;
                                                    Russell Maier
                                                </a>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
            <div class="kg-bookmark-spacer--outlook" style="height: 1.5em;">&nbsp;</div>
        <![endif]--></div>
                                                <!-- POST CONTENT END -->

                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>

                            <!-- END MAIN CONTENT AREA -->

                                <tr>
                                    <td class="feedback-buttons-container" dir="ltr" width="100%" align="center" style="font-family: -apple-system, BlinkMacSystemFont, Roboto, Helvetica, Arial, sans-serif; font-size: 18px; vertical-align: top; color: #15212A; padding: 32px 0 24px; border-bottom: 1px solid #e0e7eb; text-align: center; background-color: #ffffff;" valign="top" bgcolor="#ffffff">
                                        <table class="feedback-buttons" role="presentation" border="0" cellpadding="0" cellspacing="0" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; margin: auto; width: 100%;" width="100%">
                                            <tr>
                                                    <td dir="ltr" valign="top" align="center" style="font-size: 18px; color: #15212A; display: inline-block; vertical-align: top; font-family: inherit; text-align: center; padding: 0 4px 4px; cursor: pointer; width: 30%;" width="30%">
                                                        <a href="https://earthen.io/writing-earth-right/#/feedback/690dd1f51b74ba04b3f29f3e/1/?uuid=4dbbb711-73e9-4fd0-9056-a7cc1af6a905&amp;key=6c3ffe5e66725cd21a19a3f06a3f9c57d439ef226283a59999acecb11fb087dc" target="_blank" style="color: #4B8501; text-decoration: none; overflow-wrap: anywhere;">
                                                            <img src="https://static.ghost.org/v5.0.0/images/more-like-this-mobile.png" border="0" width="42" height="42" alt="More like this" style="border: none; -ms-interpolation-mode: bicubic; max-width: 100%; display: block; margin: 0 auto; vertical-align: middle;">
                                                            <p class="feedback-button-text" style="display: inline-block; font-family: -apple-system, BlinkMacSystemFont, Roboto, Helvetica, Arial, sans-serif; color: #15212A; font-weight: 500; margin: 1em 0 0 0; line-height: 1.4em; word-break: break-word; font-size: 13px;">More like this</p>
                                                        </a>
                                                    </td>                                                    <td dir="ltr" valign="top" align="center" style="font-size: 18px; color: #15212A; display: inline-block; vertical-align: top; font-family: inherit; text-align: center; padding: 0 4px 4px; cursor: pointer; width: 30%;" width="30%">
                                                        <a href="https://earthen.io/writing-earth-right/#/feedback/690dd1f51b74ba04b3f29f3e/0/?uuid=4dbbb711-73e9-4fd0-9056-a7cc1af6a905&amp;key=6c3ffe5e66725cd21a19a3f06a3f9c57d439ef226283a59999acecb11fb087dc" target="_blank" style="color: #4B8501; text-decoration: none; overflow-wrap: anywhere;">
                                                            <img src="https://static.ghost.org/v5.0.0/images/less-like-this-mobile.png" border="0" width="42" height="42" alt="Less like this" style="border: none; -ms-interpolation-mode: bicubic; max-width: 100%; display: block; margin: 0 auto; vertical-align: middle;">
                                                            <p class="feedback-button-text" style="display: inline-block; font-family: -apple-system, BlinkMacSystemFont, Roboto, Helvetica, Arial, sans-serif; color: #15212A; font-weight: 500; margin: 1em 0 0 0; line-height: 1.4em; word-break: break-word; font-size: 13px;">Less like this</p>
                                                        </a>
                                                    </td>                                                    <td dir="ltr" valign="top" align="center" style="font-size: 18px; color: #15212A; display: inline-block; vertical-align: top; font-family: inherit; text-align: center; padding: 0 4px 4px; cursor: pointer; width: 30%;" width="30%">
                                                        <a href="https://earthen.io/r/bdf1f78a?m=4dbbb711-73e9-4fd0-9056-a7cc1af6a905" target="_blank" style="color: #4B8501; text-decoration: none; overflow-wrap: anywhere;">
                                                            <img src="https://static.ghost.org/v5.0.0/images/comment-mobile.png" border="0" width="42" height="42" alt="Comment" style="border: none; -ms-interpolation-mode: bicubic; max-width: 100%; display: block; margin: 0 auto; vertical-align: middle;">
                                                            <p class="feedback-button-text" style="display: inline-block; font-family: -apple-system, BlinkMacSystemFont, Roboto, Helvetica, Arial, sans-serif; color: #15212A; font-weight: 500; margin: 1em 0 0 0; line-height: 1.4em; word-break: break-word; font-size: 13px;">Comment</p>
                                                        </a>
                                                    </td>                                            </tr>
                                        </table>
                                    </td>
                                </tr>



                            <tr>
                                <td class="wrapper" align="center" style="font-family: -apple-system, BlinkMacSystemFont, Roboto, Helvetica, Arial, sans-serif; font-size: 18px; vertical-align: top; color: #15212A; box-sizing: border-box;" valign="top">
                                    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%; padding-top: 40px; padding-bottom: 30px;">
                                        <tr>
                                            <td class="footer" style="font-family: -apple-system, BlinkMacSystemFont, Roboto, Helvetica, Arial, sans-serif; vertical-align: top; color: #15212a; color: rgba(0, 0, 0, 0.6); margin-top: 20px; text-align: center; padding-bottom: 10px; padding-top: 10px; padding-left: 30px; padding-right: 30px; line-height: 1.5em; font-size: 13px;" valign="top" align="center">Earthen &#xA9; 2025 &#x2013; <a href="{{unsubscribe_link}}" style="overflow-wrap: anywhere; color: #15212a; color: rgba(0, 0, 0, 0.6); text-decoration: underline; font-size: 13px;" target="_blank">Unsubscribe</a></td>
                                        </tr>

                                            <tr>
                                                <td class="footer-powered" style="font-family: -apple-system, BlinkMacSystemFont, Roboto, Helvetica, Arial, sans-serif; font-size: 18px; vertical-align: top; color: #15212A; text-align: center; padding-top: 70px; padding-bottom: 40px;" valign="top" align="center"><a href="https://ghost.org/?via=pbg-newsletter&amp;ref=earthen.io" style="color: #4B8501; text-decoration: none; overflow-wrap: anywhere;" target="_blank"><img src="https://static.ghost.org/v4.0.0/images/powered.png" border="0" width="142" height="30" class="gh-powered" alt="Powered by Ghost" style="border: none; -ms-interpolation-mode: bicubic; max-width: 100%; width: 142px; height: 30px;"></a></td>
                                            </tr>
                                    </table>
                                </td>
                            </tr>

                        </table>
                        <!-- END asdfsadf CENTERED WHITE CONTAINER -->
                    </div>
                </td>
                <td style="font-family: -apple-system, BlinkMacSystemFont, Roboto, Helvetica, Arial, sans-serif; font-size: 18px; vertical-align: top; color: #15212A;" valign="top">&#xA0;</td>
            </tr>

            <!--[if mso]>
                            </table>
                        </center>
                    </td>
                </tr>
            <![endif]-->
        </table>
    <img width="1" height="1" alt="" src="http://email.earthen.ecobricks.org/o/eJxMzjFuwzAMQNHTVFsMiaTIaOBhTJGIhaQ1ICs9f4GiQ8ePvzxXAGZLoUWYAIikpPjcx-s2XLmBhDtgttoa52hYw7unQ7lWqV1wB8sYZlSRIKKbiDV0TEMhQy0FJDOUkje7k-O9Ze4kgtQ-KMc-1xFfW_TT5ujPazvnI02d7-v6t8eZltr79bz9ytLSx3Fe66--FX4CAAD__zE4Op8"></body>
</html>
HTML;

$unsubscribe_href = $unsubscribe_link ?: 'https://earthen.io/unsubscribe/?uuid=4dbbb711-73e9-4fd0-9056-a7cc1af6a905&key=6c3ffe5e66725cd21a19a3f06a3f9c57d439ef226283a59999acecb11fb087dc&newsletter=1db69ae6-6504-48ba-9fd9-d78b3928071f';
$email_template = str_replace('{{unsubscribe_link}}', htmlspecialchars($unsubscribe_href, ENT_QUOTES, 'UTF-8'), $email_template);

?>
