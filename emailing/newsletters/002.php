<?php

$email_from = 'Earthen <earthen@ecobricks.org>';
$email_reply_to = 'Earthen <earthen@ecobricks.org>';
$email_subject = 'Plastic, the Biosphere & Ecobricks + latest Earthen posts';

// Build the unsubscribe link using the recipient's email when available.
$recipient_uuid = !empty($recipient_uuid) ? $recipient_uuid : '{{RECIPIENT_UUID}}';
$default_unsubscribe = 'https://earthen.io/unsubscribe/?uuid=4dbbb711-73e9-4fd0-9056-a7cc1af6a905&key=6c3ffe5e66725cd21a19a3f06a3f9c57d439ef226283a59999acecb11fb087dc&newsletter=18bce2af-ca5b-4a10-bff3-f79d32479f09';
$unsubscribe_link = isset($recipient_email)
    ? "https://gobrik.com/emailing/unsubscribe.php?email=" . urlencode($recipient_email)
    : $default_unsubscribe;

$email_template = <<<HTML


<!doctype html>
<html>
    <head>
        <meta name="viewport" content="width=device-width">
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <!--[if mso]><xml><o:OfficeDocumentSettings><o:PixelsPerInch>96</o:PixelsPerInch><o:AllowPNG/></o:OfficeDocumentSettings></xml><![endif]-->
        <title>Plastic, the Biosphere &amp; Ecobricks + latest Earthen posts</title>
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
        <span class="preheader" style="color: transparent; display: none; height: 0; max-height: 0; max-width: 0; opacity: 0; overflow: hidden; mso-hide: all; visibility: hidden; width: 0;">This coming Sunday, we&#39;re excited to run our last intro to ecobricks course for 2025!

This will also be the last free course that we run. Our courses have been free this past year while we&#39;ve set up the Global Ecobrick Alliance&#39;s official Indonesian organization (and its bank account)&#x2013; and our own payment processing system. Don&#39;t worry, the cost will be reasonable! But this will be the last one...

Paula and Russell will be leading the 90 minute overview of the principles, theory and best pract</span>
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
                                                                                                            <td class="site-icon" style="font-family: -apple-system, BlinkMacSystemFont, Roboto, Helvetica, Arial, sans-serif; font-size: 18px; vertical-align: top; color: #15212A; padding-bottom: 8px; padding-top: 8px; text-align: center; border-radius: 3px;" valign="top" align="center"><a href="https://earthen.io/r/8521f669?m={$recipient_uuid}" style="color: #4B8501; text-decoration: none; overflow-wrap: anywhere;" target="_blank"><img src="https://earthen.io/content/images/2022/07/page-logo.png" alt="Earthen" border="0" width="44" height="44" style="border: none; -ms-interpolation-mode: bicubic; max-width: 100%; width: 44px; height: 44px; border-radius: 3px;"></a></td>
                                                                                                        </tr>
                                                                                                        <tr>
                                                                                                            <td class="site-url " style="font-family: -apple-system, BlinkMacSystemFont, Roboto, Helvetica, Arial, sans-serif; vertical-align: top; color: #15212A; font-size: 16px; letter-spacing: -0.1px; font-weight: 700; text-transform: uppercase; text-align: center;" valign="top" align="center"><div style="width: 100% !important;"><a href="https://earthen.io/r/fb84f209?m={$recipient_uuid}" class="site-title" style="text-decoration: none; color: #15212A; overflow-wrap: anywhere;" target="_blank">Earthen</a></div></td>
                                                                                                        </tr>
                                                                                                        <tr>
                                                                                                            <td class="site-url site-url-bottom-padding" style="font-family: -apple-system, BlinkMacSystemFont, Roboto, Helvetica, Arial, sans-serif; vertical-align: top; color: #15212A; font-size: 16px; letter-spacing: -0.1px; font-weight: 700; text-transform: uppercase; text-align: center; padding-bottom: 12px;" valign="top" align="center"><div style="width: 100% !important;"><a href="https://earthen.io/r/0d112d8d?m={$recipient_uuid}" class="site-subtitle" style="text-decoration: none; color: #15212a; color: rgba(0, 0, 0, 0.6); font-size: 13px; font-weight: 400; text-transform: none; overflow-wrap: anywhere;" target="_blank">Ayyew 452</a></div></td>
                                                                                                        </tr>

                                                                                                </table>
                                                                                    </td>
                                                                                        </tr>

                                            <tr>
                                                <td class="post-title post-title-no-excerpt" style="font-family: -apple-system, BlinkMacSystemFont, Roboto, Helvetica, Arial, sans-serif; font-size: 18px; vertical-align: top; text-align: center; color: #000000; padding-bottom: 16px;" valign="top" align="center">
                                                    <table role="presentation" border="0" cellpadding="0" cellspacing="0" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%;" width="100%">
                                                        <tr>
                                                            <td style="font-family: -apple-system, BlinkMacSystemFont, Roboto, Helvetica, Arial, sans-serif; vertical-align: top; font-size: 36px; line-height: 1.1; font-weight: 700; text-align: center; color: #000000;" valign="top" align="center">
                                                                <a href="https://earthen.io/r/a740e14c?m=4dbbb711-73e9-4fd0-9056-a7cc1af6a905" class="post-title-link" style="text-decoration: none; display: block; margin-top: 32px; text-align: center; line-height: 1.1em; overflow-wrap: anywhere; color: #000000;" target="_blank">Plastic, the Biosphere &amp; Ecobricks + latest Earthen posts</a>
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
                                                                By GEA Center Circle &#x2022; <span class="post-meta-date" style="white-space: nowrap;">2 Dec 2025 </span>
                                                            </td>
                                                            <td class="post-meta post-meta-center view-online desktop" style="font-family: -apple-system, BlinkMacSystemFont, Roboto, Helvetica, Arial, sans-serif; vertical-align: top; color: rgba(0, 0, 0, 0.6); font-size: 13px; font-weight: 400; text-align: center; display: none;" valign="top" align="center">
                                                                <a href="https://earthen.io/r/b482a7e6?m=4dbbb711-73e9-4fd0-9056-a7cc1af6a905" class="view-online-link" style="word-wrap: none; white-space: nowrap; color: #15212a; color: rgba(0, 0, 0, 0.6); overflow-wrap: anywhere; text-decoration: underline;" target="_blank">View in browser</a>
                                                            </td>
                                                        </tr>
                                                        <tr class="post-meta post-meta-center view-online-mobile" style="color: #15212a; color: rgba(0, 0, 0, 0.6); font-size: 13px; font-weight: 400; text-align: center;" align="center">
                                                            <td height="20" class="view-online" style="font-family: -apple-system, BlinkMacSystemFont, Roboto, Helvetica, Arial, sans-serif; vertical-align: top; color: #15212a; color: rgba(0, 0, 0, 0.6); font-size: 13px; font-weight: 400; text-align: center; text-decoration: underline;" valign="top" align="center">
                                                                <a href="https://earthen.io/r/2c9cb531?m=4dbbb711-73e9-4fd0-9056-a7cc1af6a905" class="view-online-link" style="word-wrap: none; white-space: nowrap; color: #15212a; color: rgba(0, 0, 0, 0.6); overflow-wrap: anywhere; text-decoration: underline;" target="_blank">View in browser</a>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>

                                            <tr class="feature-image-row">
                                                <td class="feature-image
                                                " align="center" style="font-family: -apple-system, BlinkMacSystemFont, Roboto, Helvetica, Arial, sans-serif; font-size: 18px; vertical-align: top; color: #15212A; padding-bottom: 30px; width: 100%; text-align: center;" width="100%" valign="top"><img src="https://earthen.io/content/images/size/w1200/2025/12/ecobrick-course.webp" width="600" style="border: none; -ms-interpolation-mode: bicubic; max-width: 100%;"></td>
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
                                                <p style="margin: 0 0 1.5em 0; line-height: 1.6em; color: #15212A;">This coming Sunday, we&#39;re excited to run our last intro to ecobricks course for 2025!  </p><p style="margin: 0 0 1.5em 0; line-height: 1.6em; color: #15212A;">This will also be the last free course that we run.  Our courses have been free this past year while we&#39;ve set up the Global Ecobrick Alliance&#39;s official Indonesian organization (and its bank account)&#x2013; and our own payment processing system.  Don&#39;t worry, the cost will be reasonable!  But this will be the last one...</p><p style="margin: 0 0 1.5em 0; line-height: 1.6em; color: #15212A;">Paula and Russell will be leading the 90 minute overview of the principles, theory and best practices behind ecobricking and plastic sequestration.  Use your Buwana GoBrik account to register &#x1F447;</p><div>
        <!--[if !mso !vml]-->
            <div class="kg-card kg-bookmark-card " style="margin: 0 0 1.5em; padding: 0; width: 100%;">
                <a class="kg-bookmark-container" href="https://earthen.io/r/e643f8de?m=4dbbb711-73e9-4fd0-9056-a7cc1af6a905" style="display: flex; min-height: 148px; font-family: -apple-system, BlinkMacSystemFont, Roboto, Helvetica, Arial, sans-serif; border-radius: 3px; background-color: #ffffff; background-color: rgba(255, 255, 255, 0.25); border: 1px solid #e0e7eb; border: 1px solid rgba(0, 0, 0, 0.12); overflow-wrap: anywhere; color: #4B8501; text-decoration: none;" target="_blank">
                    <div class="kg-bookmark-content" style="display: inline-block; width: 100%; padding: 20px;">
                        <div class="kg-bookmark-title" style="color: #15212A; font-size: 15px; line-height: 1.5em; font-weight: 600;">Plastic, the Biosphere and Ecobricks</div>
                        <div class="kg-bookmark-description" style="display: -webkit-box; overflow-y: hidden; margin-top: 12px; max-height: 40px; color: #15212a; color: rgba(0, 0, 0, 0.6); font-size: 13px; line-height: 1.5em; font-weight: 400; -webkit-line-clamp: 2; -webkit-box-orient: vertical;">Register for our Online Starter Workshop led by Paula Apollonia and Russell Maier on Dece<span class="desktop-only">mber 7, 2025</span><span class="hide-desktop" style="display: none;">&#x2026;</span></div>
                        <div class="kg-bookmark-metadata" style="display: flex; flex-wrap: wrap; align-items: center; margin-top: 14px; color: #15212A; font-size: 13px; font-weight: 400;">
                            <img class="kg-bookmark-icon" src="https://earthen.io/content/images/icon/apple-icon-180x180-22.png" alt style="border: none; -ms-interpolation-mode: bicubic; max-width: 100%; margin-right: 8px; width: 22px; height: 22px;" width="22" height="22">
                            <span class="kg-bookmark-author" src="GoBrik.com" style="line-height: 1.5em;">GoBrik.com</span>
                            
                        </div>
                    </div>
                    <div class="kg-bookmark-thumbnail" style="min-width: 140px; max-width: 180px; background-repeat: no-repeat; background-size: cover; background-position: center; border-radius: 0 2px 2px 0; background-image: url(&#39;https://earthen.io/content/images/thumbnail/starter-workshop-feature-1-en-4.webp&#39;);">
                        <img src="https://earthen.io/content/images/thumbnail/starter-workshop-feature-1-en-4.webp" alt onerror="this.style.display=&#39;none&#39;" style="border: none; -ms-interpolation-mode: bicubic; max-width: 100%; display: none;"></div>
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
                                                        <img src="https://earthen.io/content/images/icon/apple-icon-180x180-22.png" width="22" height="22" alt=" ">
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
        <![endif]--></div><p style="margin: 0 0 1.5em 0; line-height: 1.6em; color: #15212A;">Meanwhile, be sure to check out the latest posts on Earthen that you might have missed any. The last two essays Russell has been slow and steady working on for the last two years!</p><div>
        <!--[if !mso !vml]-->
            <div class="kg-card kg-bookmark-card " style="margin: 0 0 1.5em; padding: 0; width: 100%;">
                <a class="kg-bookmark-container" href="https://earthen.io/r/9f8ce9c4?m=4dbbb711-73e9-4fd0-9056-a7cc1af6a905" style="display: flex; min-height: 148px; font-family: -apple-system, BlinkMacSystemFont, Roboto, Helvetica, Arial, sans-serif; border-radius: 3px; background-color: #ffffff; background-color: rgba(255, 255, 255, 0.25); border: 1px solid #e0e7eb; border: 1px solid rgba(0, 0, 0, 0.12); overflow-wrap: anywhere; color: #4B8501; text-decoration: none;" target="_blank">
                    <div class="kg-bookmark-content" style="display: inline-block; width: 100%; padding: 20px;">
                        <div class="kg-bookmark-title" style="color: #15212A; font-size: 15px; line-height: 1.5em; font-weight: 600;">Writing Earth Right</div>
                        <div class="kg-bookmark-description" style="display: -webkit-box; overflow-y: hidden; margin-top: 12px; max-height: 40px; color: #15212a; color: rgba(0, 0, 0, 0.6); font-size: 13px; line-height: 1.5em; font-weight: 400; -webkit-line-clamp: 2; -webkit-box-orient: vertical;">How we write the name of our home planet unconsciously encodes the relationship we have w<span class="desktop-only">ith it.</span><span class="hide-desktop" style="display: none;">&#x2026;</span></div>
                        <div class="kg-bookmark-metadata" style="display: flex; flex-wrap: wrap; align-items: center; margin-top: 14px; color: #15212A; font-size: 13px; font-weight: 400;">
                            <img class="kg-bookmark-icon" src="https://earthen.io/content/images/icon/page-logo-11.png" alt style="border: none; -ms-interpolation-mode: bicubic; max-width: 100%; margin-right: 8px; width: 22px; height: 22px;" width="22" height="22">
                            <span class="kg-bookmark-author" src="Earthen" style="line-height: 1.5em;">Earthen</span>
                            <span class="kg-bookmark-publisher" src="Russell Maier" style="overflow: hidden; max-width: 240px; line-height: 1.5em; text-overflow: ellipsis; white-space: nowrap;"><span style="margin: 0 6px;">&#x2022;</span>Russell Maier</span>
                        </div>
                    </div>
                    <div class="kg-bookmark-thumbnail" style="min-width: 140px; max-width: 180px; background-repeat: no-repeat; background-size: cover; background-position: center; border-radius: 0 2px 2px 0; background-image: url(&#39;https://earthen.io/content/images/thumbnail/earthrise-cropped-1.webp&#39;);">
                        <img src="https://earthen.io/content/images/thumbnail/earthrise-cropped-1.webp" alt onerror="this.style.display=&#39;none&#39;" style="border: none; -ms-interpolation-mode: bicubic; max-width: 100%; display: none;"></div>
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
                                    <a href="https://earthen.io/writing-earth-right/" style="text-decoration: none; color: #15212A; font-size: 15px; line-height: 1.5em; font-weight: 600;">
                                        Writing Earth Right
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="kg-bookmark-description--outlook">
                                        <a href="https://earthen.io/writing-earth-right/" style="text-decoration: none; margin-top: 12px; color: #738a94; font-size: 13px; line-height: 1.5em; font-weight: 400;">
                                            How we write the name of our home planet unconsciously encodes the relationship we have w<span class="desktop-only">ith it.</span><span class="hide-desktop">…</span>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td class="kg-bookmark-metadata--outlook" style="padding-top: 14px; color: #15212A; font-size: 13px; font-weight: 400; line-height: 1.5em;">
                                    <table style="margin: 0; padding: 0; border-collapse: collapse; border-spacing: 0;">
                                        <tr>
                                            
                                                <td valign="middle" class="kg-bookmark-icon--outlook" style="padding-right: 8px; font-size: 0; line-height: 1.5em;">
                                                    <a href="https://earthen.io/writing-earth-right/" style="text-decoration: none; color: #15212A;">
                                                        <img src="https://earthen.io/content/images/icon/page-logo-11.png" width="22" height="22" alt=" ">
                                                    </a>
                                                </td>
                                            
                                            <td valign="middle" class="kg-bookmark-byline--outlook">
                                                <a href="https://earthen.io/writing-earth-right/" style="text-decoration: none; color: #15212A;">
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
        <![endif]--></div><div>
        <!--[if !mso !vml]-->
            <div class="kg-card kg-bookmark-card " style="margin: 0 0 1.5em; padding: 0; width: 100%;">
                <a class="kg-bookmark-container" href="https://earthen.io/r/30e80ec0?m=4dbbb711-73e9-4fd0-9056-a7cc1af6a905" style="display: flex; min-height: 148px; font-family: -apple-system, BlinkMacSystemFont, Roboto, Helvetica, Arial, sans-serif; border-radius: 3px; background-color: #ffffff; background-color: rgba(255, 255, 255, 0.25); border: 1px solid #e0e7eb; border: 1px solid rgba(0, 0, 0, 0.12); overflow-wrap: anywhere; color: #4B8501; text-decoration: none;" target="_blank">
                    <div class="kg-bookmark-content" style="display: inline-block; width: 100%; padding: 20px;">
                        <div class="kg-bookmark-title" style="color: #15212A; font-size: 15px; line-height: 1.5em; font-weight: 600;">The Problem with Google Calendar</div>
                        <div class="kg-bookmark-description" style="display: -webkit-box; overflow-y: hidden; margin-top: 12px; max-height: 40px; color: #15212a; color: rgba(0, 0, 0, 0.6); font-size: 13px; line-height: 1.5em; font-weight: 400; -webkit-line-clamp: 2; -webkit-box-orient: vertical;">As we hit the limits of the Roman Empire&#x2019;s 2,000-year-old Calendar, now is the time for t<span class="desktop-only">ransition from linear and huma</span>&#x2026;</div>
                        <div class="kg-bookmark-metadata" style="display: flex; flex-wrap: wrap; align-items: center; margin-top: 14px; color: #15212A; font-size: 13px; font-weight: 400;">
                            <img class="kg-bookmark-icon" src="https://earthen.io/content/images/icon/page-logo-12.png" alt style="border: none; -ms-interpolation-mode: bicubic; max-width: 100%; margin-right: 8px; width: 22px; height: 22px;" width="22" height="22">
                            <span class="kg-bookmark-author" src="Earthen" style="line-height: 1.5em;">Earthen</span>
                            <span class="kg-bookmark-publisher" src="Russell Maier" style="overflow: hidden; max-width: 240px; line-height: 1.5em; text-overflow: ellipsis; white-space: nowrap;"><span style="margin: 0 6px;">&#x2022;</span>Russell Maier</span>
                        </div>
                    </div>
                    <div class="kg-bookmark-thumbnail" style="min-width: 140px; max-width: 180px; background-repeat: no-repeat; background-size: cover; background-position: center; border-radius: 0 2px 2px 0; background-image: url(&#39;https://earthen.io/content/images/thumbnail/colleuseum-engraving2-1-1.webp&#39;);">
                        <img src="https://earthen.io/content/images/thumbnail/colleuseum-engraving2-1-1.webp" alt onerror="this.style.display=&#39;none&#39;" style="border: none; -ms-interpolation-mode: bicubic; max-width: 100%; display: none;"></div>
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
                                                        <img src="https://earthen.io/content/images/icon/page-logo-12.png" width="22" height="22" alt=" ">
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
                                                        <a href="https://earthen.io/plastic-the-biosphere-ecobricks-latest-earthen-posts/#/feedback/69146a7b1b74ba04b3f2a339/1/?uuid=4dbbb711-73e9-4fd0-9056-a7cc1af6a905&amp;key=6c3ffe5e66725cd21a19a3f06a3f9c57d439ef226283a59999acecb11fb087dc" target="_blank" style="color: #4B8501; text-decoration: none; overflow-wrap: anywhere;">
                                                            <img src="https://static.ghost.org/v5.0.0/images/more-like-this-mobile.png" border="0" width="42" height="42" alt="More like this" style="border: none; -ms-interpolation-mode: bicubic; max-width: 100%; display: block; margin: 0 auto; vertical-align: middle;">
                                                            <p class="feedback-button-text" style="display: inline-block; font-family: -apple-system, BlinkMacSystemFont, Roboto, Helvetica, Arial, sans-serif; color: #15212A; font-weight: 500; margin: 1em 0 0 0; line-height: 1.4em; word-break: break-word; font-size: 13px;">More like this</p>
                                                        </a>
                                                    </td>                                                    <td dir="ltr" valign="top" align="center" style="font-size: 18px; color: #15212A; display: inline-block; vertical-align: top; font-family: inherit; text-align: center; padding: 0 4px 4px; cursor: pointer; width: 30%;" width="30%">
                                                        <a href="https://earthen.io/plastic-the-biosphere-ecobricks-latest-earthen-posts/#/feedback/69146a7b1b74ba04b3f2a339/0/?uuid=4dbbb711-73e9-4fd0-9056-a7cc1af6a905&amp;key=6c3ffe5e66725cd21a19a3f06a3f9c57d439ef226283a59999acecb11fb087dc" target="_blank" style="color: #4B8501; text-decoration: none; overflow-wrap: anywhere;">
                                                            <img src="https://static.ghost.org/v5.0.0/images/less-like-this-mobile.png" border="0" width="42" height="42" alt="Less like this" style="border: none; -ms-interpolation-mode: bicubic; max-width: 100%; display: block; margin: 0 auto; vertical-align: middle;">
                                                            <p class="feedback-button-text" style="display: inline-block; font-family: -apple-system, BlinkMacSystemFont, Roboto, Helvetica, Arial, sans-serif; color: #15212A; font-weight: 500; margin: 1em 0 0 0; line-height: 1.4em; word-break: break-word; font-size: 13px;">Less like this</p>
                                                        </a>
                                                    </td>                                                    <td dir="ltr" valign="top" align="center" style="font-size: 18px; color: #15212A; display: inline-block; vertical-align: top; font-family: inherit; text-align: center; padding: 0 4px 4px; cursor: pointer; width: 30%;" width="30%">
                                                        <a href="https://earthen.io/r/766788b6?m=4dbbb711-73e9-4fd0-9056-a7cc1af6a905" target="_blank" style="color: #4B8501; text-decoration: none; overflow-wrap: anywhere;">
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
                                            <td class="footer" style="font-family: -apple-system, BlinkMacSystemFont, Roboto, Helvetica, Arial, sans-serif; vertical-align: top; color: #15212a; color: rgba(0, 0, 0, 0.6); margin-top: 20px; text-align: center; padding-bottom: 10px; padding-top: 10px; padding-left: 30px; padding-right: 30px; line-height: 1.5em; font-size: 13px;" valign="top" align="center">Earthen &#xA9; 2025 &#x2013; <a href="https://earthen.io/unsubscribe/?uuid=4dbbb711-73e9-4fd0-9056-a7cc1af6a905&key=6c3ffe5e66725cd21a19a3f06a3f9c57d439ef226283a59999acecb11fb087dc&newsletter=18bce2af-ca5b-4a10-bff3-f79d32479f09" style="overflow-wrap: anywhere; color: #15212a; color: rgba(0, 0, 0, 0.6); text-decoration: underline; font-size: 13px;" target="_blank">Unsubscribe</a></td>
                                        </tr>

                                    </table>
                                </td>
                            </tr>

                        </table>
                        <!-- END CENTERED WHITE CONTAINER -->
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
    <img width="1" height="1" alt="" src="http://email.earthen.ecobricks.org/o/eJxMzkFuxCAMQNHTlN1ExjhOvPBhTHAmaKZFAtLzV6q66PLrb15RROYcXOPGxCwkKfin1fejFmVBz3uCBHkVYXBJjOYSLj0LAW1rPmkDs4jFczFCwyPCmZKHqgi4RgQEWfeIC9BpMR1omxXgPX4QuPV5-dfiR8u9Hq-xtP4MXfs9xr9dW5j6vNqYj19amJrv9-svvhV_AgAA__9M4TsM"></body>
</html>
HTML;

$email_template = str_replace($default_unsubscribe, '{unsubscribe_link}', $email_template);

$unsubscribe_href = $unsubscribe_link ?: $default_unsubscribe;
$email_template = str_replace('{unsubscribe_link}', htmlspecialchars($unsubscribe_href, ENT_QUOTES, 'UTF-8'), $email_template);

?>
