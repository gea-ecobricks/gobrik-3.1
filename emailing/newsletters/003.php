<?php

$email_from = 'Earthen <earthen@ecobricks.org>';
$email_reply_to = 'Earthen <earthen@ecobricks.org>';
$email_subject = 'The Problem of the Year';

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
<title>The Problem of the Year</title>
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
        <span class="preheader" style="color: transparent; display: none; height: 0; max-height: 0; max-width: 0; opacity: 0; overflow: hidden; mso-hide: all; visibility: hidden; width: 0;">Like water to a fish, we&#39;re swimming in modern notions of time—especially the idea of a precisely measured year. What happens when we rethink that cycle through a kincentric lens?</span>
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
                                                                                                            <td class="site-icon" style="font-family: -apple-system, BlinkMacSystemFont, Roboto, Helvetica, Arial, sans-serif; font-size: 18px; vertical-align: top; color: #15212A; padding-bottom: 8px; padding-top: 8px; text-align: center; border-radius: 3px;" valign="top" align="center"><a href="https://earthen.io/?m={$recipient_uuid}" style="color: #4B8501; text-decoration: none; overflow-wrap: anywhere;" target="_blank"><img src="https://earthen.io/content/images/2022/07/page-logo.png" alt="Earthen" border="0" width="44" height="44" style="border: none; -ms-interpolation-mode: bicubic; max-width: 100%; width: 44px; height: 44px; border-radius: 3px;"></a></td>
                                                                                                        </tr>
                                                                                                        <tr>
                                                                                                            <td class="site-url " style="font-family: -apple-system, BlinkMacSystemFont, Roboto, Helvetica, Arial, sans-serif; vertical-align: top; color: #15212A; font-size: 16px; letter-spacing: -0.1px; font-weight: 700; text-transform: uppercase; text-align: center;" valign="top" align="center"><div style="width: 100% !important;"><a href="https://earthen.io/?m={$recipient_uuid}" class="site-title" style="text-decoration: none; color: #15212A; overflow-wrap: anywhere;" target="_blank">Earthen</a></div></td>
                                                                                                        </tr>
                                                                                                        <tr>
                                                                                                            <td class="site-url site-url-bottom-padding" style="font-family: -apple-system, BlinkMacSystemFont, Roboto, Helvetica, Arial, sans-serif; vertical-align: top; color: #15212A; font-size: 16px; letter-spacing: -0.1px; font-weight: 700; text-transform: uppercase; text-align: center; padding-bottom: 12px;" valign="top" align="center"><div style="width: 100% !important;"><a href="https://earthen.io/cycles/?m={$recipient_uuid}" class="site-subtitle" style="text-decoration: none; color: #15212a; color: rgba(0, 0, 0, 0.6); font-size: 13px; font-weight: 400; text-transform: none; overflow-wrap: anywhere;" target="_blank">Into the Meaning of Greening</a></div></td>
                                                                                                        </tr>

                                                                                                </table>
                                                                                    </td>
                                                                                        </tr>

                                            <tr>
                                                <td class="post-title post-title-no-excerpt" style="font-family: -apple-system, BlinkMacSystemFont, Roboto, Helvetica, Arial, sans-serif; font-size: 18px; vertical-align: top; text-align: center; color: #000000; padding-bottom: 16px;" valign="top" align="center">
                                                    <table role="presentation" border="0" cellpadding="0" cellspacing="0" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%;" width="100%">
                                                        <tr>
                                                            <td style="font-family: -apple-system, BlinkMacSystemFont, Roboto, Helvetica, Arial, sans-serif; vertical-align: top; font-size: 36px; line-height: 1.1; font-weight: 700; text-align: center; color: #000000;" valign="top" align="center">
                                                                <a href="https://earthen.io/the-problem-of-the-year/?m={$recipient_uuid}" class="post-title-link" style="text-decoration: none; display: block; margin-top: 32px; text-align: center; line-height: 1.1em; overflow-wrap: anywhere; color: #000000;" target="_blank">The Problem of the Year</a>
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
                                                                By Russell Maier &#x2022; <span class="post-meta-date" style="white-space: nowrap;">27 Dec 2025 </span>
                                                            </td>
                                                            <td class="post-meta post-meta-center view-online desktop" style="font-family: -apple-system, BlinkMacSystemFont, Roboto, Helvetica, Arial, sans-serif; vertical-align: top; color: rgba(0, 0, 0, 0.6); font-size: 13px; font-weight: 400; text-align: center; display: none;" valign="top" align="center">
                                                                <a href="https://earthen.io/the-problem-of-the-year/?m={$recipient_uuid}" class="view-online-link" style="word-wrap: none; white-space: nowrap; color: #15212a; color: rgba(0, 0, 0, 0.6); overflow-wrap: anywhere; text-decoration: underline;" target="_blank">View in browser</a>
                                                            </td>
                                                        </tr>
                                                        <tr class="post-meta post-meta-center view-online-mobile" style="color: #15212a; color: rgba(0, 0, 0, 0.6); font-size: 13px; font-weight: 400; text-align: center;" align="center">
                                                            <td height="20" class="view-online" style="font-family: -apple-system, BlinkMacSystemFont, Roboto, Helvetica, Arial, sans-serif; vertical-align: top; color: #15212a; color: rgba(0, 0, 0, 0.6); font-size: 13px; font-weight: 400; text-align: center; text-decoration: underline;" valign="top" align="center">
                                                                <a href="https://earthen.io/the-problem-of-the-year/?m={$recipient_uuid}" class="view-online-link" style="word-wrap: none; white-space: nowrap; color: #15212a; color: rgba(0, 0, 0, 0.6); overflow-wrap: anywhere; text-decoration: underline;" target="_blank">View in browser</a>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>

                                            <tr class="feature-image-row">
                                                <td class="feature-image
                                                " align="center" style="font-family: -apple-system, BlinkMacSystemFont, Roboto, Helvetica, Arial, sans-serif; font-size: 18px; vertical-align: top; color: #15212A; padding-bottom: 30px; width: 100%; text-align: center;" width="100%" valign="top"><img src="https://earthen.io/content/images/size/w1200/2025/12/stonehenge-lead.png" width="700" style="border: none; -ms-interpolation-mode: bicubic; max-width: 100%;"></td>
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
                                                <h3 id="a-kincentric-critique-of-the-modern-year">A Kincentric Critique of the Modern Year</h3>
                                                <p style="margin: 0 0 1.5em 0; line-height: 1.6em; color: #15212A;"><br>Like water to a fish, we&#39;re swimming in our modern notions of time.  As minutes, days, weeks and months go by, we take these notions of time for granted, barely considering the impact that these concepts have.  Perhaps most invisible and assumed of all is our modern concept of a year.</p>
                                                <p style="margin: 0 0 1.5em 0; line-height: 1.6em; color: #15212A;">So deeply is this notion embedded in modern consciousness that to question it can feel naïve, even absurd. As one year gives way to the next, we collectively anticipate another precisely measured circuit of Earth around the sun: 365 days, 5 hours, 48 minutes, and change. This number has become so authoritative that few pause to ask what, exactly, it represents—or, omits.  This year, we have come to believe, is a fixed quantity of time. A unit. A measure. A container into which life is neatly poured.</p>
                                                <p style="margin: 0 0 1.5em 0; line-height: 1.6em; color: #15212A;">Yet this has not always been so.</p>
                                                <p style="margin: 0 0 1.5em 0; line-height: 1.6em; color: #15212A;">And more importantly, despite our insistence– it is <em>not so</em>.</p>
                                                <p style="margin: 0 0 1.5em 0; line-height: 1.6em; color: #15212A;">Our modern definition of the year rests upon a profound ontological error: not of arithmetic, but of emphasis. It mistakes measurement for meaning. It privileges precision over participation. And in doing so, it severs our lives from the beating, rhythmic cycles that truly make and mould the universe</p>
                                                <p style="margin: 0 0 1.5em 0; line-height: 1.6em; color: #15212A;">A year is meant to represent our planet&#39;s spin around the sun.  However, to understand the problem with the modern notion, we must first recognize that a simplified<sup>3</sup> solar spin involves two distinct cycles:</p>
                                                <ol style="margin: 0 0 1.5em 1.3em; line-height: 1.6em; color: #15212A;">
                                                    <li style="margin: 0.4em 0; line-height: 1.6em; color: #15212A;">The rotation of Earth on its axis</li>
                                                    <li style="margin: 0.4em 0; line-height: 1.6em; color: #15212A;">The orbit of Earth around the sun</li>
                                                </ol>
                                                <p style="margin: 0 0 1.5em 0; line-height: 1.6em; color: #15212A;">The first cycle gives rise to the most immediate and visceral rhythm known to life on this planet: day and night. Almost all Earthly beings, human and otherwise, entrain themselves to this oscillation.</p>
                                                <p style="margin: 0 0 1.5em 0; line-height: 1.6em; color: #15212A;">The second cycle—the solar orbit—is the one we call a year. When we attempt to measure it in days, its duration is not a clean and obedient number. Earth completes this journey in approximately <strong>365.241299 days</strong><sup>3</sup>.</p>
                                                <p style="margin: 0 0 1.5em 0; line-height: 1.6em; color: #15212A;">Close enough to 365¼ to tempt simplification.<br><br>But not close enough to obey it.</p>
                                                <p style="margin: 0 0 1.5em 0; line-height: 1.6em; color: #15212A;">It is this remainder—this fractional refusal—that has haunted calendar-makers for millennia.</p>
                                                <p style="margin: 0 0 1.5em 0; line-height: 1.6em; color: #15212A;">Ancient civilizations recognized the discrepancy and responded in different ways.  <a href="https://earthen.io/the-problem-with-google-calendar/" rel="noreferrer">As we saw in my previous essay,</a> the ancient Egyptians added an extra day to their calendar every 4 years. Then, two thousand years ago, Julius Ceasar, borrowed this concept and refined it further so that the Roman calendar could be even more accurate. Then, in 1582, Pope Gregory XIII “improved” the Roman system, by adopting the rule that there would be no extra day on centuries—00 years—except on those that are multiples of four.</p>
                                                <p style="margin: 0 0 1.5em 0; line-height: 1.6em; color: #15212A;">While we call our calendar the Gregorian calendar, in effect the Roman one– the minor additions by Pope Gregory, and in the last century by governments, are simply refinements.</p>
                                                <p style="margin: 0 0 1.5em 0; line-height: 1.6em; color: #15212A;">Each reform promised greater accuracy. Each moved closer to a perfectly regulated year.</p>
                                                <p style="margin: 0 0 1.5em 0; line-height: 1.6em; color: #15212A;">Yet with each refinement, something essential was lost.</p>
                                                <p style="margin: 0 0 1.5em 0; line-height: 1.6em; color: #15212A;">For all its technical ingenuity, the Roman calendar—and its Gregorian descendant—did not resolve the discrepancy between Earth&#39;s spin and orbital cycles. It concealed it. By burying two different cycles beneath layers of correction, the calendar transformed time into a linear mechanism. The year became a number that always fit, regardless of what Earth was actually doing.</p>
                                                <p style="margin: 0 0 1.5em 0; line-height: 1.6em; color: #15212A;">In doing so, modern civilization gained an extraordinarily efficient tool for empire, administration, and commerce.</p>
                                                <p style="margin: 0 0 1.5em 0; line-height: 1.6em; color: #15212A;">But it lost time itself.</p>
                                                <p style="margin: 0 0 1.5em 0; line-height: 1.6em; color: #15212A;">For time is not linear.  And cycles are not reducible without remainder.  Above all else time is cyclical, and in so far as these cycles interweave: synchronistic.</p>
                                                <blockquote class="kg-blockquote-alt">“If you want to find the secrets of the universe, think in terms of energy, frequency, and vibration.”  – Nikola Tesla</blockquote>
                                                <p style="margin: 0 0 1.5em 0; line-height: 1.6em; color: #15212A;"><a href="https://book.earthen.io/en/kincentric.html?ref=earthen.io" rel="noreferrer">Kincentric</a> cultures—ancient and ongoing—understood this intuitively. For them, what we call a “year” was not an abstract duration, but a lived recurrence. Their temporal units were Earthen rhythms, not numerical intervals.</p>
                                                <p style="margin: 0 0 1.5em 0; line-height: 1.6em; color: #15212A;">Each year, <a href="https://book.earthen.io/en/spirals.html?ref=earthen.io" rel="noreferrer">the return of the <em>kilin</em> bird</a> marked the time of harvest for the Igorots of Northern Luzon<sup>2</sup>. In the land of the Wet&#39;su&#39;weten in North Pacific coast of America, the rivers would boil each Autumn with salmon, summoning the community into shared labor, feast, and story. It was a yearly, cyclical moment that happened to coincide with a particular point of Earth&#39;s orbit of the sun–  but for whom the cycle was far more important than the precisely calculated date and time.</p>
                                                <p style="margin: 0 0 1.5em 0; line-height: 1.6em; color: #15212A;">Their yearly celebrations were not <em>on time</em>, but <em>in rhythm</em>.</p>
                                                <figure class="kg-card kg-image-card kg-card-hascaption"><img src="https://earthen.io/content/images/2025/12/lucie-at-stonehenge.jpeg" class="kg-image" alt="" loading="lazy" width="700" srcset="https://earthen.io/content/images/size/w600/2025/12/lucie-at-stonehenge.jpeg 600w, https://earthen.io/content/images/size/w1000/2025/12/lucie-at-stonehenge.jpeg 1000w, https://earthen.io/content/images/2025/12/lucie-at-stonehenge.jpeg 1600w" sizes="(min-width: 720px) 720px"><figcaption><span style="white-space: pre-wrap;">On Dec 21st, 20205 my friends in the UK are celebrated the Winter Solstice at Stone Henge. Erected four thousand years ago, this construction enables both the precise notation of a precise real world Earthen moment (the longest day of the year) and visceral experiential connection to a cyclical moment as the last rays of the longest day of the year pass through a gap in the stones to illuminate the altar stone.</span></figcaption></figure>
                                                <p style="margin: 0 0 1.5em 0; line-height: 1.6em; color: #15212A;">Crucially, these cultures were not imprecise in their measurement of time. Many achieved astonishing astronomical accuracy. Stone alignments such as those at <a href="https://www.bbc.com/culture/article/20251219-the-ancient-monuments-saluting-the-winter-solstice?ref=earthen.io" rel="noreferrer">solstitial sites</a> attest to a refined understanding of Earth’s movements. Yet precision was always subordinate to to the cycle itself. Measurement served the cycle; the cycle did not serve the measure.</p>
                                                <p style="margin: 0 0 1.5em 0; line-height: 1.6em; color: #15212A;">This is the beating heart of the matter.</p>
                                                <p style="margin: 0 0 1.5em 0; line-height: 1.6em; color: #15212A;">Beginning with Roman reform, metrical quantification came to dominate the cycle itself. Intercalary time—those living margins where cycles failed to align—was abolished in favor of self-correcting mechanisms. Fractions replaced festivals. Corrections replaced consciousness.</p>
                                                <p style="margin: 0 0 1.5em 0; line-height: 1.6em; color: #15212A;">Over centuries, this logic compounded.</p>
                                                <p style="margin: 0 0 1.5em 0; line-height: 1.6em; color: #15212A;">The result is the calendar years we now inhabit: a grid of arbitrary and misnamed months (O<em>ctober </em>is from the Latin root for eight, yet it is the 10th month!).  It is a temporal architecture optimized for economic administration over ecological integration (The word <em>calendar</em> comes from the Latin for account book). And its beginning and end are fundamental ungrounded: December 31st marks no harvest, no solstice, no Earthen or planetary occurrence—only the exhaustion of a number.</p>
                                                <p style="margin: 0 0 1.5em 0; line-height: 1.6em; color: #15212A;">Despite its digital polish and global synchronization, this system is profoundly disconnected from Earth’s raw rhythms. It tracks time flawlessly while missing the cycles entirely.</p>
                                                <p style="margin: 0 0 1.5em 0; line-height: 1.6em; color: #15212A;">In contrast, other great kincentric civilizations did not “solve” the discrepancy between solar orbit, spin and lunar cycles (not to mention other planetary and galactic cycles).</p>
                                                <p style="margin: 0 0 1.5em 0; line-height: 1.6em; color: #15212A;">They honoured and centred their societies upon them.</p>
                                                <p style="margin: 0 0 1.5em 0; line-height: 1.6em; color: #15212A;">These <a href="https://book.earthen.io/en/kincentric.html?ref=earthen.io" rel="noreferrer"><em>cyclocentric</em></a> societies allowed time to overflow its containers. They recognized intercalary periods as thresholds rather than errors. And upon this living foundation, they built cultures that accumulated ecological insight cycle by cycle—each return deepening awareness, strengthening reciprocity, and reinforcing harmony.</p>
                                                <p style="margin: 0 0 1.5em 0; line-height: 1.6em; color: #15212A;">For much of history, the ecological consequences of abandoning this orientation were muted by scale. The Roman world, for all its temporal rigidity, remained small compared to planetary systems.</p>
                                                <p style="margin: 0 0 1.5em 0; line-height: 1.6em; color: #15212A;">Today, it does not.</p>
                                                <p style="margin: 0 0 1.5em 0; line-height: 1.6em; color: #15212A;">After two millennia of compounding linearity, humanity now finds itself pressing against biospheric limits. The mechanization of time has marched in lockstep with the mechanization of life. Disconnection from cycles has become disconnection from consequence.</p>
                                                <p style="margin: 0 0 1.5em 0; line-height: 1.6em; color: #15212A;">What we are experiencing is not merely ecological crisis.<br><br>It is first and foremost temporal dissonance.</p>
                                                <p style="margin: 0 0 1.5em 0; line-height: 1.6em; color: #15212A;">It is time to rethink time.  There&#39;s no other way to change our times.</p>
                                                <h3 id="footnotes"><br><br><br>Footnotes</h3>
                                                <p style="margin: 0 0 1.5em 0; line-height: 1.6em; color: #15212A;">_________________________________________________<br>1. There are some notable historical initiatives:  In the mid-1950s, proponents of a new <a href="https://en.wikipedia.org/wiki/World_Calendar?ref=earthen.io" rel="noreferrer"><em>World Calendar</em></a><em>,</em> brought their proposal of a perennial year to the UN.  Various governments were in favor until the US to rejected the motion to take it further.<em>  </em>In the early 2000&#39;s Jose Argueles tried to implement the <a href="https://lawoftime.org/thirteenmoon.html?content=249&ref=earthen.io" rel="noreferrer">13 Moon Calendar</a> arguing that nothing was more important: &quot;A crooked measure produces crooked judgments. A crooked calendar produces crooked lives.&quot;</p>
                                                <ol start="2">
                                                    <li style="margin: 0.4em 0; line-height: 1.6em; color: #15212A;">For an an account of the Igorots remarkable ecological synchrony with the cycles of the living world around them see: William Henry Scott, (1959), American Anthropologist 60(3):563 - 570, <em>Some Calendars of Northern Luzon</em></li>
                                                    <li style="margin: 0.4em 0; line-height: 1.6em; color: #15212A;">There is, in fact, another cycle at work here. Earth’s axis undergoes a slow wobble on its axis.  This wobble, which is separate from Earth&#39;s orbit, makes a full circular wobble approximately once every 25,772 years. This &quot;axial precession&quot; causes the seasonal markers—such as solstices—to arrive slightly <em>before</em> Earth completes a full orbital return relative to the stars. As a result, the time between successive solstices (the<em> tropical year</em>) differs from the time it takes Earth to return to the same orbital position in space (the <em>sidereal year</em>). The mean tropical year is approximately 365.24219 days, while the sidereal year is approximately 365.25636 days, or 20 minutes and 24 seconds and corresponds to an axial shift of roughly 50.3 arcseconds 0.014° per year. As yet another Earthen cycle, we will return to the significance of this, in the next essay. 🙂</li>
                                                </ol>

                                               Start 2026 with a whole moment management system.  <a href="https://earthcal.app">Earthcal.app</a>
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
                                                        <a href="https://earthen.io/the-problem-of-the-year/#/feedback/694823af30b59960e936a441/1/?uuid=4dbbb711-73e9-4fd0-9056-a7cc1af6a905&amp;key=6c3ffe5e66725cd21a19a3f06a3f9c57d439ef226283a59999acecb11fb087dc" target="_blank" style="color: #4B8501; text-decoration: none; overflow-wrap: anywhere;">
                                                            <img src="https://static.ghost.org/v5.0.0/images/more-like-this-mobile.png" border="0" width="42" height="42" alt="More like this" style="border: none; -ms-interpolation-mode: bicubic; max-width: 100%; display: block; margin: 0 auto; vertical-align: middle;">
                                                            <p class="feedback-button-text" style="display: inline-block; font-family: -apple-system, BlinkMacSystemFont, Roboto, Helvetica, Arial, sans-serif; color: #15212A; font-weight: 500; margin: 1em 0 0 0; line-height: 1.4em; word-break: break-word; font-size: 13px;">More like this</p>
                                                        </a>
                                                    </td>                                                    <td dir="ltr" valign="top" align="center" style="font-size: 18px; color: #15212A; display: inline-block; vertical-align: top; font-family: inherit; text-align: center; padding: 0 4px 4px; cursor: pointer; width: 30%;" width="30%">
                                                        <a href="https://earthen.io/the-problem-of-the-year/#/feedback/694823af30b59960e936a441/0/?uuid=4dbbb711-73e9-4fd0-9056-a7cc1af6a905&amp;key=6c3ffe5e66725cd21a19a3f06a3f9c57d439ef226283a59999acecb11fb087dc" target="_blank" style="color: #4B8501; text-decoration: none; overflow-wrap: anywhere;">
                                                            <img src="https://static.ghost.org/v5.0.0/images/less-like-this-mobile.png" border="0" width="42" height="42" alt="Less like this" style="border: none; -ms-interpolation-mode: bicubic; max-width: 100%; display: block; margin: 0 auto; vertical-align: middle;">
                                                            <p class="feedback-button-text" style="display: inline-block; font-family: -apple-system, BlinkMacSystemFont, Roboto, Helvetica, Arial, sans-serif; color: #15212A; font-weight: 500; margin: 1em 0 0 0; line-height: 1.4em; word-break: break-word; font-size: 13px;">Less like this</p>
                                                        </a>
                                                    </td>                                                    <td dir="ltr" valign="top" align="center" style="font-size: 18px; color: #15212A; display: inline-block; vertical-align: top; font-family: inherit; text-align: center; padding: 0 4px 4px; cursor: pointer; width: 30%;" width="30%">
                                                        <a href="https://earthen.io/the-problem-of-the-year/?m={$recipient_uuid}#comments" target="_blank" style="color: #4B8501; text-decoration: none; overflow-wrap: anywhere;">
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
