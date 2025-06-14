<?php

$unsubscribe_link = isset($recipient_email)
    ? "https://gobrik.com/emailing/unsubscribe.php?email=" . urlencode($recipient_email)
    : '';


 // Default email HTML with dynamic unsubscribe link
$email_template = <<<HTML
<!DOCTYPE html>
<!--June 14th version-->
<html>
 <head>
  <meta content="width=device-width" name="viewport"/>
  <meta content="text/html; charset=utf-8" http-equiv="Content-Type"/>
  <title>
   Earthen Ethics + Ecobrick Intro
  </title>
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
            color: #73818c;
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
        .kg-cta-link-accent .kg-cta-text a {
            color: #4B8501 !important;
        }
        .kg-audio-link {
            color: #73818c !important;
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

            table.body .post-excerpt {
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

            table.body .main {
                border-spacing: 10px 0 !important;
                border-left-width: 0 !important;
                border-radius: 0 !important;
                border-right-width: 0 !important;
            }

            table.body .btn table {
                width: 100% !important;
            }

            table.body .btn a {
                width: 100% !important;
            }

            table.body .img-responsive {
                height: auto !important;
                max-width: 100% !important;
                width: auto !important;
            }

            table.body .site-icon {
                padding-top: 0 !important;
            }

            table.body .site-info {
                padding-top: 24px !important;
            }

            table.body .post-title-link {
                margin-top: 24px !important;
            }

            table.body .post-meta-wrapper {
                padding-bottom: 24px !important;
            }

            table.body .site-icon img {
                width: 36px !important;
                height: 36px !important;
            }

            table.body .site-url a {
                font-size: 13px !important;
                padding-bottom: 16px !important;
            }

            table.body .post-meta,
            table.body .post-meta-date {
                white-space: normal !important;
                font-size: 13px !important;
                line-height: 1.2em;
            }

            table.body .post-meta,
            table.body .view-online {
                width: 100% !important;
            }

            table.body .post-meta-left,
            table.body .post-meta-left.view-online {
                width: 100% !important;
                text-align: left !important;
            }

            table.body .post-meta.view-online-mobile {
                display: table-row !important;
            }

            table.body .post-meta-left.view-online-mobile,
            table.body .post-meta-left.view-online-mobile .view-online {
                text-align: left !important;
            }

            table.body .post-meta.view-online.desktop {
                display: none !important;
            }

            table.body .view-online {
                text-decoration: underline;
            }

            table.body .footer p,
            table.body .footer p span {
                font-size: 13px !important;
            }

            table.body .view-online-link,
            table.body .footer,
            table.body .footer a {
                font-size: 13px !important;
            }

            table.body .post-title a {
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

            table.body .kg-header-card.kg-v2 span {
                font-size: inherit !important;
            }

            table.body .kg-header-card.kg-v2 .kg-header-card-content {
                padding-top: 64px !important;
                padding-bottom: 64px !important;
            }

            table.body .kg-header-card.kg-v2 .kg-header-card-image + .kg-header-card-        content {
            padding-top: 52px !important;
            padding-bottom: 52px !important;
        }

            table.body .kg-header-card.kg-v2 .kg-header-card-heading {
                font-size: 2.2em !important;
                line-height: 1.1 !important;
            }

            table.body .kg-header-card.kg-v2 .kg-header-card-subheading {
                line-height: 1.3em !important;
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

            table.body table.kg-cta-content-wrapper {
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

            table.body .kg-cta-minimal a.kg-cta-button {
                display: inline-block !important;
            }

            table.body .kg-cta-minimal table.kg-cta-button-wrapper td {
                padding: 4px 16px 5px;
            }

            table.body .kg-cta-immersive .kg-cta-button-wrapper {
                padding: 6px 18px 7px;
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

            .btn-primary table td:hover {
                background-color: #34495e !important;
            }

            .btn-primary a:hover {
                background-color: #34495e !important;
                border-color: #34495e !important;
            }
        }
  </style>
 </head>
 <body style="background-color: #fff; font-family: -apple-system, Blin        kMacSystemFont, Roboto, Helvetica, Arial, sans-serif; -webkit-font-smoothin              g: antialiased; font-size: 18px; line-height: 1.4; margin: 0; padding: 0; -              ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; color: #15212A;">
  <span class="preheader" style="color: transparent; display: non        e; height: 0; max-height: 0; max-width: 0; opacity: 0; overflow: hidden; ms              o-hide: all; visibility: hidden; width: 0;">
   Keen about green? We're thrilled to announce three offerings for you this June. We've got the launch of the second edition of the Earthen Ethics and a free intro to ecobricking course. Finally, we've got a community webinar on the pressing relevance of indigenous wisdom in the revamp of our archaic modern environmental paradigm.


Plastic, the Biosphere and Ecobrick

First, GEA co-founder Russell Maier and GEA Center Circle Trainer Paula Apollonia will be leading a free online intro to ecobricks
  </span>
  <table bgcolor="#fff" border="0" cellpadding="0" cellspa="" cing="0" class="body" role="presentation" style="border-collapse: separate               ; mso-table-lspace: 0pt; mso-table-rspace: 0pt; background-color: #fff; wid               th: 100%;" width="100%">
   <tr>
    <td style="font-family: -apple-system, BlinkMacSystemFont            , Roboto, Helvetica, Arial, sans-serif; font-size: 18px; vertical-align: to                p; color: #15212A;" valign="top">
    </td>
    <td class="container" style="font-family: -apple-system            , BlinkMacSystemFont, Roboto, Helvetica, Arial, sans-serif; font-size: 18px                ; vertical-align: top; color: #15212A; display: block; max-width: 600px; ma                rgin: 0 auto;" valign="top">
     <div class="content" style="box-sizing: border-box;            display: block; margin: 0 auto; max-width: 600px;">
      <table border="0" cellpaddi="" cellspacing="0" class="main" ng="0" role="presentation" style="border-co           llapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; background:                   #ffffff; border-radius: 3px; border-spacing: 20px 0; width: 100%;" width="100%">
       <tr>
        <td class="wrapper" style="font-family:        -apple-system, BlinkMacSystemFont, Roboto, Helvetica, Arial, sans-serif; f                ont-size: 18px; vertical-align: top; color: #15212A; box-sizing: border-box                ;" valign="top">
         <table border="               0" cellpadding="0" cellspacing="0" role="presentation" style="border-colla        pse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%;" width="100%">
          <tr class="header-image-row">
           <td align="center" class="header-image" lign="top" style="font-family: -apple-system, BlinkM    acSystemFont, Roboto, Helvetica, Arial, sans-serif; font-size: 18px; vertic    al-align: top; color: #15212A; padding-top: 24px; padding-bottom: 16px;" va="" width="100%">
            <a href="https://eart       hen.io/r/bca45a49?m=611f7d90-e87c-4c43-ab51-0772a7883703" style="color:    #4B8501; text-decoration: none; overflow-wrap: anywhere;" target="_blank    ">
             <img src="https:/    /earthen.io/content/images/size/w1200/2022/08/email-cover-1200px.jpg" style="border: none; -ms-interpolation-mode: bicubic; max-width:    100%;" width="600"/>
            </a>
           </td>
          </tr>
          <tr class="site-info-row">
           <td align="center" class="site-info" style="font-family: -apple-system, BlinkMacS                ystemFont, Roboto, Helvetica, Arial, sans-serif; font-size: 18px; vertical-                align: top; color: #15212A; padding-top: 32px;" th="100%" valign="top" wid="">
            <table border="0" cellpadding="0" cellspacing="0" role="presenta           tion" style="border-coll    apse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%;" width="100%">
             <tr>
              <td align="center" class="site-icon" style="font-family: -apple-system, BlinkMacSystemFont, Rob                oto, Helvetica, Arial, sans-serif; font-size: 18px; vertical-align: top; co                lor: #15212A; padding-bottom: 8px; padding-top: 8px; text-align: center; bo                rder-radius: 3px;" valign="top">
               <a href="https://earth                                                                      en.io/r/5177cc01?m=611f7d90-e87c-4c43-ab51-0772a7883703" style="color:     #4B8501; text-decoration: none; overflow-wrap: anywhere;" target="_blank">
                <img alt="Earthen" border="0" height="44" src="https://earthen.io/content/images/2022/07/page-logo.png" style="border: none;                  -ms-interpolation-mode: bicubic; max-width: 100%; width: 44px; height: 44p                  x; border-radius: 3px;" width="44"/>
               </a>
              </td>
             </tr>
             <tr>
              <td align="center" class="site-url site-url-bottom-padding" style="font-family: -apple-system,     BlinkMacSystemFont, Roboto, Helvetica, Arial, sans-serif; vertical-align: t    op; color: #15212A; font-size: 16px; letter-spacing: -0.1px; font-weight: 7    00; text-transform: uppercase; text-align: center; padding-bottom: 12px;" v="">
               <div style="width: 100% !important;">
                <a class="site-title" ef="https://earthen.io/r/f0821404?m=611f7d90-e87c-4c43-ab51-0772a788370                                                                             3" hr="" style="text-decoration: none; color: #15212A; ove    rflow-wrap: anywhere;" target="_blank">
                 Earthen
                </a>
               </div>
              </td>
             </tr>
            </table>
           </td>
          </tr>
          <tr>
           <td align="center" class="post-title pos t-title-no-excerpt" style="font-family: -apple-system, BlinkMacSystemFont                , Roboto, Helvetica, Arial, sans-serif; vertical-align: top; color: #15212A                ; font-size: 36px; line-height: 1.1em; font-weight: 700; text-align: center                ; padding-bottom: 16px;" valign="top">
            <a class="post-t itle-link" href="https://eart       hen.io/r/9f663353?m=611f7d90-e87c-4c43-ab51-0772a7883703" style="text-decoration: none; display: block; margin-top: 32px    ; color: #15212A; text-align: center; line-height: 1.1em; overflow-wrap: an    ywhere;" target="_blank">
             Earthen Ethics + Ecobrick Intro
            </a>
           </td>
          </tr>
          <tr>
           <td style="font-family: -            apple-system, BlinkMacSystemFont, Roboto, Helvetica, Arial, sans-serif; fon                t-size: 18px; vertical-align: top; color: #15212A; width: 100%;" valign="top" width="1    00%">
            <table border="0" cellpadding="0" cellspacin="" class="post-me ta-wrapper" g="0" role="presentation" style="border-collapse: separate; mso-table-lspace    : 0pt; mso-table-rspace: 0pt; width: 100%; padding-bottom: 32px;" width="100%">
             <tr>
              <td align="center" class="post-meta post-meta-center" height="2        0" style="font-family: -apple-system    , BlinkMacSystemFont, Roboto, Helvetica, Arial, sans-serif; vertical-align:    top; color: #73818c; font-size: 13px; font-weight: 400; text-align: center    ; padding: 0;" valign="top">
               By GEA Cent    er Circle •
               <span class="post-meta-date" style="white-space: now        rap;">
                13 Jun 2025
               </span>
              </td>
              <td align="center" class="po st-meta post-meta-center view-online desktop" style="font-family: -apple-    system, BlinkMacSystemFont, Roboto, Helvetica, Arial, sans-serif; vertical-    align: top; color: #73818c; font-size: 13px; font-weight: 400; text-align:     center; display: none;" valign="top">
               <a cla="" href="       https://earthen.io/r/b34a54d2?m=611f7d90-e87c-4c43-ab51-0772a7883703" ss="view-online-link" style="word-wrap: none; white-space: nowrap; colo    r: #73818c; overflow-wrap: anywhere; text-decoration: underline;" target="_blank">
                View in browser
               </a>
              </td>
             </tr>
             <tr align="center" class="post-m eta post-meta-center view-online-mobile" style="color: #73818c; font-size: 13px; font-weight: 400; text-align: center;">
              <td align="center" class="view-online" height="2    0" style="font-family: -apple-system, BlinkMacSystemFont, Roboto, Helvetica, Arial, sans-serif; vertical-align: top; color: #73818c; font-size: 13px; font-weight: 400; text-align: center; text-decoration: underline;" valign="top">
               <a class="view-online-link" href="   https://earthen.io/r/aaef4264?m=611f7d90-e87c-4c43-ab51-0772a7883703" style="word-wrap: none; white-space: nowrap; color: #73818c; overflow-wrap: anywhere; text-decoration: underline;" target="_blank">
                View in browser
               </a>
              </td>
             </tr>
            </table>
           </td>
          </tr>
          <tr class="feature-image-row">
           <td align="top" class="feature-image feature-image-with- caption" style="font-family: -apple-system, BlinkMacSystemFont, Roboto, Helvetica, Arial,     sans-serif; vertical-align: top; color: #15212A; padding-bottom: 30px; text    -align: center; width: 100%; padding: 0; font-size: 13px;" v="" width="100%">
            <img src="https://earthen.io/content/images/size/w1200/2025    /06/photo1-desktop-1.webp" style="border: none; -ms-interpo    lation-mode: bicubic; max-width: 100%;" width="600"/>
           </td>
          </tr>
          <tr>
           <td align="center" lign="top" st="" va="" yle="font-family: -apple-system, BlinkMacSystemFont, Roboto, Helvetica, A                rial, sans-serif; font-size: 18px; vertical-align: top; color: #15212A;">
            <div class="featu re-image-caption" style="width: 100%; padding-top: 5px; padding-bottom: 3    2px; text-align: center; color: #73818c; line-height: 1.5em; font-size: 13p    x;">
             <span style="          white-space: pre-wrap;">
              Our upcoming online starter workshop on June 18th.&lt;    /span&gt;
             </span>
            </div>
           </td>
          </tr>
          <tr class="post-content-row">
           <td class="post-content" e="vertical-align: top; font-family: Georgia, serif; font-size: 18px; lin                e-height: 1.5em; color: #15212A; padding-bottom: 20px; border-bottom: 1px s                olid #e0e7eb; max-width: 600px;" styl="" valign="top">
            <p style="margin: 0 0 1.5            em 0; line-height: 1.6em;">
             Keen about green?  We're thrilled to announc    e three offerings for you this June.  We've got the launch of the secon    d edition of the
             <em>
              Earthen Ethics
             </em>
             and a free intro to ecobricking co    urse.  Finally, we've got a community webinar on the pressing relevance    of indigenous wisdom in the revamp of our archaic modern environmental par    adigm.
            </p>
            <h3 id="plastic-the-biosphere-and-ecobrick" style="margin-top                  : 0; font-family: -apple-system, BlinkMacSystemFont, Roboto, Helvetica, Ari                          al, sans-serif; line-height: 1.11em; font-weight: 700; text-rendering: opti                          mizeLegibility; margin: 1.5em 0 0.5em 0; font-size: 26px;">
             Plastic, the Bio    sphere and Ecobrick
            </h3>
            <p style="margin: 0 0 1.5em 0; line-height: 1.6em        ;">
             First, GEA co-founder Russell Maier and GEA Center Circle Trainer Paula     Apollonia will be leading a free online intro to ecobricks workshop on June    18th at 7:30 PM Jakarta time and 1:30 PM UK time.
            </p>
            <p style="margin:                                                              0 0 1.5em 0; line-height: 1.6em;">
             In a 90 minute live zoom event, their &lt;    em&gt;Plastic, the Biosphere and Ecobricks
             presentation will cover the an    cient Earth history of plastic, its modern context, and the case for seques    tration.  They will go over best-practice ecobricking techniques and the va    rious applications for ecobricks.
            </p>
            <p style="margin: 0 0 1.5em 0; line-        height: 1.6em;">
             We've been hard at work upgrading GoBrik to offer train    ings and webinars– so this is a first test of our new course registr    ation system.  Alas, we haven't got the payment system working yet so,     lucky you, the course is completely free!
            </p>
            <div>
             <div class="kg-card kg-bookmark-card" style="margin: 0 0 1    .5em; padding: 0; width: 100%; background: #ffffff;">
              <a class="kg-bookmark-container" get="_blank" href="https://earthen.       io/r/e07843d0?m=611f7d90-e87c-4c43-ab51-0772a7883703" style="display: f    lex; min-height: 148px; font-family: -apple-system, BlinkMacSystemFont, Rob    oto, Helvetica, Arial, sans-serif; border-radius: 3px; border: 1px solid #e    0e7eb; overflow-wrap: anywhere; color: #4B8501; text-decoration: none;" tar="">
               <div class="kg-bookmark-content" style="display: in            line-block; width: 100%; padding: 20px;">
                <div class="kg-bookmark-title" style="color: #1            5212A; font-size: 15px; line-height: 1.5em; font-weight: 600;">
                 Plastic, the    Biosphere and Ecobricks
                </div>
                <div class="kg-bookmark-description" style="dis         play: -webkit-box; overflow-y: hidden; margin-top: 12px; max-height: 40px;                  color: #73818c; font-size: 13px; line-height: 1.5em; font-weight: 400; -web                 kit-line-clamp: 2; -webkit-box-orient: vertical;">
                 Register for our Online S    tarter Workshop led by Paula Apollonia and Russell Maier on June
                 <span class="desktop-only">
                  18, 2025
                 </span>
                 <span class="hide-desktop" style="dis                                                                                                                          play: none;">
                  …
                 </span>
                </div>
                <div class="kg-bookmark-metadata" style="displa         y: flex; flex-wrap: wrap; align-items: center; margin-top: 14px; color: #15                 212A; font-size: 13px; font-weight: 400;">
                 <img alt="" class="kg-bookmark-icon" height="22" src="https://         earthen.io/content/images/icon/apple-icon-180x180-11.png" style="bord    er: none; -ms-interpolation-mode: bicubic; max-width: 100%; margin-right: 8    px; width: 22px; height: 22px;" width="22"/>
                 <span class="kg-bookmark-author" src="GoBri          k.com" style="line-height: 1.5em;">
                  GoBrik.com
                 </span>
                 =20
                </div>
               </div>
               <div class="kg-bookmark-thumbnail" style="min-width         : 140px; max-width: 180px; background-repeat: no-repeat; background-size: c                 over; background-position: center; border-radius: 0 2px 2px 0; background-i                 mage: url('https://earthen.io/content/images/thumbnail/starter-workshop    -feature-1-en.webp');">
                <img alt="" onerror="this.style.display    ='none'" src="https://earthen.io/content/images/thumb         nail/starter-workshop-feature-1-en.webp" style="border: none; -ms-interpolation-mode: bicubic;     max-width: 100%; display: none;"/>
               </div>
              </a>
              =20
             </div>
            </div>
            <h3 id="earthen-ethics-2nd-edition" style="ma                          rgin-top: 0; font-family: -apple-system, BlinkMacSystemFont, Roboto, Helvet                                      ica, Arial, sans-serif; line-height: 1.11em; font-weight: 700; text-renderi                                      ng: optimizeLegibility; margin: 1.5em 0 0.5em 0; font-size: 26px;">
             Earthen     Ethics 2nd Edition
            </h3>
            <div class="kg-card kg-image-card" style="margin    : 0 0 1.5em; padding: 0;">
             <img alt="" class="kg-image" height="600" loading="lazy" src="https://earthen.io/content/images/202                                   5/06/print-preview-01-1000px.webp" style="border: none; -ms-interpolation-mode:    bicubic; max-width: 100%; display: block; margin: 0 auto; height: auto; wi    dth: auto;" width="600"/>
            </div>
            <p style="margin: 0 0 1.5em 0; line-height: 1.6em;">
             Rus    sell and Banayan Angway, (also a GEA co-founder!) have just released the se    cond edition of their
             <em>
              Tractatus Ayyew - Earthen Ethics
             </em>
             .  Their col    laboration merges Irene's indigenous and academic background in her peo    ple's Igorot culture and Russell's academic background in Western p    hilosophy to lay out a theory of what green should really mean.
            </p>
            <p style="margin: 0 0 1.5em 0; line-height: 1.6em;">
             If you're tired at looki    ng at screens, this is your chance to get their paper book (10$) as it is n    ow back in stock.
            </p>
            <div class="kg-card kg-image-card kg-card-hascapt ion" style="margin: 0 0 1.5em; padding: 0;">
             <img alt="" class="kg-image" height="318" loadi="" ng="lazy" src="https://earthen.i                                                           o/content/images/size/w1600/2025/06/image.png" style="border: none; -ms-interpo    lation-mode: bicubic; max-width: 100%; display: block; margin: 0 auto; heig    ht: auto; width: auto;" width="600"/>
             <div class="kg-card-figcaption" style="text-ali                                 gn: center; font-family: -apple-system, BlinkMacSystemFont, Roboto, Helveti                                         ca, Arial, sans-serif; padding-top: 10px; padding-bottom: 10px; line-height                                         : 1.5em; color: #73818c; font-size: 13px;">
              <span style="text-align: cente        r; white-space: pre-wrap;">
               PDF, Ebook (epub) and print formats on sale. As     always the content is also freely available in Earthbook format.
              </span>
             </div>
             <div>
              <div class="kg-card kg-bookmark-card kg-card-hascaption" e="margin: 0 0 1.5em; padding: 0; width: 100%; background: #ffffff;" styl="">
               <a class="kg-bookmark-container" get="_blank" href="https://earthen.       io/r/b222ddcc?m=611f7d90-e87c-4c43-ab51-0772a7883703" style="display: f    lex; min-height: 148px; font-family: -apple-system, BlinkMacSystemFont, Rob    oto, Helvetica, Arial, sans-serif; border-radius: 3px; border: 1px solid #e    0e7eb; overflow-wrap: anywhere; color: #4B8501; text-decoration: none;" tar="">
                <div class="kg-bookmark-content" style="display: in            line-block; width: 100%; padding: 20px;">
                 <div class="kg-bookmark-title" style="color: #1            5212A; font-size: 15px; line-height: 1.5em; font-weight: 600;">
                  Order Print,    Epub &amp; PDF Editions
                 </div>
                 <div class="kg-bookmark-description" style="dis         play: -webkit-box; overflow-y: hidden; margin-top: 12px; max-height: 40px;                  color: #73818c; font-size: 13px; line-height: 1.5em; font-weight: 400; -web                 kit-line-clamp: 2; -webkit-box-orient: vertical;">
                  Tractatus Ayyew - Earthen    Ethics | A theory of what green should really mean by Banayan A
                  <span class="desktop-only">
                   ngway &amp;amp; Russell Maier
                  </span>
                  <span class="hide-d esktop" style="display: none;">
                   …
                  </span>
                 </div>
                 <div class="kg-bookmark-metadata" style="displa         y: flex; flex-wrap: wrap; align-items: center; margin-top: 14px; color: #15                 212A; font-size: 13px; font-weight: 400;">
                  <img alt="" class="kg-bookmark-icon" height="22" src="https://         earthen.io/content/images/icon/apple-touch-icon-5.png" style="border:    none; -ms-interpolation-mode: bicubic; max-width: 100%; margin-right: 8px;    width: 22px; height: 22px;" width="22"/>
                  <span class="kg-bookmark-author" src="Earth          en Ethics - Tracatus Ayyew" style="line-height: 1.5em;">
                   Earthen Ethics -     Tracatus Ayyew
                  </span>
                  <span class="kg-bookmark-publisher" src="Ru          ssell Maier &amp; Banayan Angway" style="overflow: hidden; max-width: 240    px; line-height: 1.5em; text-overflow: ellipsis; white-space: nowrap;">
                   <spa n="" style="margin: 0 6px;">
                    •
                   </spa>
                  </span>
                  Russell Maier &amp; Banayan Angwa    y
                 </div>
                </div>
                <div class="kg-bookmark-thumbnail" style="min-width         : 140px; max-width: 180px; background-repeat: no-repeat; background-size: c                 over; background-position: center; border-radius: 0 2px 2px 0; background-i                 mage: url('https://earthen.io/content/images/thumbnail/printed-books-ba    nner-2.webp');">
                 <img alt="" onerror="this.style.display='    none'" src="https://earthen.io/content/images/thumb         nail/printed-books-banner-2.webp" style="border: none; -ms-interpolation-mode: bicubic; max-widt    h: 100%; display: none;"/>
                </div>
               </a>
               <div class="kg-card-figcaption" style="text-align: cent            er; font-family: -apple-system, BlinkMacSystemFont, Roboto, Helvetica, Aria                 l, sans-serif; padding-top: 10px; padding-bottom: 10px; line-height: 1.5em;                 color: #73818c; font-size: 13px;">
                <p style="margin: 0 0 1.5em 0; line-he        ight: 1.6em;">
                 <span style="white-space: pre-wrap;">
                  Order the print versio    n
                 </span>
                </p>
               </div>
              </div>
             </div>
             <p style="margin: 0 0 1.5em 0; line-height: 1.6        em;">
              For UK orders, please order from
              <a href="https://earthen.io/r/66753                                                 d9c?m=611f7d90-e87c-4c43-ab51-0772a7883703" rel="noreferrer" style="o                                                 verflow-wrap: anywhere; color: #4B8501; text-decoration: underline;" target="_blank">
               New Forest Aquaponics
              </a>
              or pick up a copy at
              <a href="https                                                                  ://earthen.io/r/ec7b5af3?m=611f7d90-e87c-4c43-ab51-0772a7883703" rel="n oreferrer" style="overflow-wrap: anywhere; color: #4B8501; text-decoratio    n: underline;" target="_blank">
               Lucie's Place in Hythe, Newforest
              </a>
              .
             </p>
             <div class="kg-card kg-image-card kg-card-hascaption" style="marg    in: 0 0 1.5em; padding: 0;">
              <img alt="" class="kg-image" height="328" loading="lazy    " src="https://earthen.io/content/images/2                                     025/06/earthen-ethics-launch-2.webp" style="border: none; -ms-interpolation-mod    e: bicubic; max-width: 100%; display: block; margin: 0 auto; height: auto;     width: auto;" width="600"/>
              <div class="kg-card-figcaption" style="text-align: center        ; font-family: -apple-system, BlinkMacSystemFont, Roboto, Helvetica, Arial,                               sans-serif; padding-top: 10px; padding-bottom: 10px; line-height: 1.5em; c                               olor: #73818c; font-size: 13px;">
               <span style="text-align: center; white-s        pace: pre-wrap;">
                Maier &amp; Angway with the first and second editions of t    he
               </span>
               <i>
                <em class="italic" style="white-space: pre-wrap;">
                 Tractatu    s
                </em>
               </i>
              </div>
             </div>
             <h3 id="upcoming-gea-public-community-event" style="margin-top: 0; font-family: -apple-system, BlinkMacSystemFont, Roboto,                                       Helvetica, Arial, sans-serif; line-height: 1.11em; font-weight: 700; text-r                                      endering: optimizeLegibility; margin: 1.5em 0 0.5em 0; font-size: 26px;">
              Up    coming GEA Public Community Event
             </h3>
             <p style="margin: 0 0 1.5em 0; lin        e-height: 1.6em;">
              Lucie and Ani are preparing a GEA public community event     to interview Russell and Banayan about Ayyew and Earthen Ethics on June 27t    h.  We'll share the registration link shortly.  Stay tuned!
             </p>
             <hr le="position: relative; display: block; width: 100%; margin: 3em 0; paddi                                                                                    ng: 0; height: 1px; background-color: transparent; border: 0; border-top: 1                                                                                    px solid #e0e7eb;" sty=""/>
             <p style="margin: 0 0 1.5em 0; line-height: 1.6em;">
              Ag    ain, be sure to check out our new GoBrik course registration and reserve yo    ur spot for this month's Plastic, the Biosphere &amp; Ecobricks present    ation.
             </p>
             <div>
              <div class="kg-card kg-bookmark-card" style="margin: 0 0 1    .5em; padding: 0; width: 100%; background: #ffffff;">
               <a class="kg-bookmark-container" get="_blank" href="https://earthen.       io/r/c4f20f86?m=611f7d90-e87c-4c43-ab51-0772a7883703" style="display: f    lex; min-height: 148px; font-family: -apple-system, BlinkMacSystemFont, Rob    oto, Helvetica, Arial, sans-serif; border-radius: 3px; border: 1px solid #e    0e7eb; overflow-wrap: anywhere; color: #4B8501; text-decoration: none;" tar="">
                <div class="kg-bookmark-content" style="display: in            line-block; width: 100%; padding: 20px;">
                 <div class="kg-bookmark-title" style="color: #1            5212A; font-size: 15px; line-height: 1.5em; font-weight: 600;">
                  Plastic, the    Biosphere and Ecobricks
                 </div>
                 <div class="kg-bookmark-description" style="dis         play: -webkit-box; overflow-y: hidden; margin-top: 12px; max-height: 40px;                  color: #73818c; font-size: 13px; line-height: 1.5em; font-weight: 400; -web                 kit-line-clamp: 2; -webkit-box-orient: vertical;">
                  Register for our Online S    tarter Workshop led by Paula Apollonia and Russell Maier on June
                  <span class="desktop-only">
                   18, 2025
                  </span>
                  <span class="hide-desktop" style="dis                                                                                                                          play: none;">
                   …
                  </span>
                 </div>
                 <div class="kg-bookmark-metadata" style="displa         y: flex; flex-wrap: wrap; align-items: center; margin-top: 14px; color: #15                 212A; font-size: 13px; font-weight: 400;">
                  <img alt="" class="kg-bookmark-icon" height="22" src="https://         earthen.io/content/images/icon/apple-icon-180x180-12.png" style="bord    er: none; -ms-interpolation-mode: bicubic; max-width: 100%; margin-right: 8    px; width: 22px; height: 22px;" width="22"/>
                  <span class="kg-bookmark-author" src="GoBri          k.com" style="line-height: 1.5em;">
                   GoBrik.com
                  </span>
                  =20
                 </div>
                </div>
                <div class="kg-bookmark-thumbnail" style="min-width         : 140px; max-width: 180px; background-repeat: no-repeat; background-size: c                 over; background-position: center; border-radius: 0 2px 2px 0; background-i                 mage: url('https://earthen.io/content/images/thumbnail/starter-workshop    -feature-1-en-1.webp');">
                 <img alt="" onerror="this.style.displa    y='none'" src="https://earthen.io/content/images/thumb         nail/starter-workshop-feature-1-en-1.webp" style="border: none; -ms-interpolation-mode: bicubic;    max-width: 100%; display: none;"/>
                </div>
               </a>
               =20
              </div>
             </div>
            </div>
           </td>
          </tr>
         </table>
        </td>
       </tr>
       <tr>
        <td align="center" bgcolor="#ffffff" dir="ltr" style="font-family: -apple-system, BlinkMacSystemFont, Roboto, Helvetica, Arial,     sans-serif; font-size: 18px; vertical-align: top; color: #15212A; backgroun    d-color: #ffffff; text-align: center; padding: 32px 0 24px; border-bottom:     1px solid #e0e7eb;" valign="top" width="100%">
         <table border="0" cellpadding="0" cellspacing="0" class="feedback-buttons" ole="presentation" r="" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt                   ; margin: auto; width: 100%;" width="100%">
          <tr>
           <td align="center" dir="ltr" style="font-size: 18px; color: #15212A; display                : inline-block; vertical-align: top; font-family: inherit; text-align: cent                er; padding: 0 4px 4px; cursor: pointer; width: 30%;" valign="top" width="30%">
            <a href="https://       earthen.io/earthen-ethics-ecobrick-intro/#/feedback/68494df141ce4d043b070fe    3/1/?uuid=611f7d90-e87c-4c43-ab51-0772a7883703&amp;key=c8c3faf87323b6ad    7a8b96bcc9f9d742316e82dc604c69de46e524bcb11e3104" style="color: #4B8501; text-decoration: none; overflow-wrap: anywhere;" target="_blank">
             <img alt="More like this" border="0" height="42" src="htt         ps://static.ghost.org/v5.0.0/images/more-like-this-mobile.png" style="border: none; -    ms-interpolation-mode: bicubic; max-width: 100%; display: block; margin: 0     auto; vertical-align: middle;" width="42"/>
             <p class="fee dback-button-text" style="display: inline-block; font-family: -apple-syst    em, BlinkMacSystemFont, Roboto, Helvetica, Arial, sans-serif; color: #15212    A; font-weight: 500; margin: 1em 0 0 0; line-height: 1.4em; word-break: bre    ak-word; font-size: 13px;">
              More like this
             </p>
            </a>
           </td>
           <td align="c        enter" dir="ltr" style="font-size: 18px; color: #15212A; display: inline-block; ver    tical-align: top; font-family: inherit; text-align: center; padding: 0 4px     4px; cursor: pointer; width: 30%;" valign="top" width="30%">
            <a href="https://       earthen.io/earthen-ethics-ecobrick-intro/#/feedback/68494df141ce4d043b070fe    3/0/?uuid=611f7d90-e87c-4c43-ab51-0772a7883703&amp;key=c8c3faf87323b6ad    7a8b96bcc9f9d742316e82dc604c69de46e524bcb11e3104" style="color: #4B8501; text-decoration: none; overflow-wrap: anywhere;" target="_blank">
             <img alt="Less like this" border="0" height="42" src="htt         ps://static.ghost.org/v5.0.0/images/less-like-this-mobile.png" style="border: none; -    ms-interpolation-mode: bicubic; max-width: 100%; display: block; margin: 0     auto; vertical-align: middle;" width="42"/>
             <p class="fee dback-button-text" style="display: inline-block; font-family: -apple-syst    em, BlinkMacSystemFont, Roboto, Helvetica, Arial, sans-serif; color: #15212    A; font-weight: 500; margin: 1em 0 0 0; line-height: 1.4em; word-break: bre    ak-word; font-size: 13px;">
              Less like this
             </p>
            </a>
           </td>
          </tr>
         </table>
        </td>
       </tr>
       <tr>
        <td class="subscription-box" style="font-family: -apple-system, BlinkMacSystemFont, Roboto, Helvetica, Arial,     sans-serif; font-size: 18px; vertical-align: top; padding: 32px 0; border-b    ottom: 1px solid #e0e7eb; color: #15212A;" valign="top">
         <h3 style="margin-top: 0; font-fa            mily: -apple-system, BlinkMacSystemFont, Roboto, Helvetica, Arial, sans-ser                if; line-height: 1.11em; text-rendering: optimizeLegibility; font-size: 14p                x; font-weight: 700; text-transform: uppercase; margin: 0 0 18px;">
          Subscrip    tion details
         </h3>
         <p style="margin: 0 0 1.5em 0; fo            nt-size: 15px; font-weight: 400; line-height: 1.45em; text-decoration: none               ; margin-bottom: 16px; color: #15212A;">
          <span>
           You are receiving this because you are a
           <strong style="font-weight: 700;">
            free subscriber
           </strong>
           to Earthen.
          </span>
          =20
         </p>
         <table border="0" cellpadding="0" cellspacing="0" role="presentation" style="border-c           ollapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100                   %;" width="100%">
          <tr>
           <td class="subscription-d etails" style="font-family: -apple-system, BlinkMacSystemFont, Roboto, He            lvetica, Arial, sans-serif; font-size: 18px; vertical-align: top; color: #1            5212A;" valign="top">
            <p class="" style="               margin: 0 0 1.5em 0; margin-bottom: 0; font-size: 15px; font-weight: 400; l                       ine-height: 1.45em; text-decoration: none; color: #15212A;">
             Name: Russell M            aier
            </p>
            <p style="margin: 0 0                    1.5em 0; margin-bottom: 0; font-size: 15px; font-weight: 400; line-height:                       1.45em; text-decoration: none; color: #15212A;">
             Email:
             <a href="#" style="overflow-wrap: anywhere; text-decoration: none; color: #15212A;" target="_blank">
              russmaier@gmail.com
             </a>
            </p>
            <p style="margin: 0 0                    1.5em 0; margin-bottom: 0; font-size: 15px; font-weight: 400; line-height:                       1.45em; text-decoration: none; color: #15212A;">
             Member since: 15 January 2            022
            </p>
           </td>
           <td align="right" class="manage-subscription" style="font-family: -apple-syst                        em, BlinkMacSystemFont, Roboto, Helvetica, Arial, sans-serif; white-space:                         nowrap; font-size: 15px; font-weight: 600; text-align: right; line-height:                         1.45em; vertical-align: bottom; color: #4B8501;" valign="bottom">
            <a href="https://eart               hen.io/#/portal/account" style="color: #4B8501; text-decoration: none; ov            erflow-wrap: anywhere;" target="_blank">
             Manage subscription →
            </a>
           </td>
          </tr>
         </table>
        </td>
       </tr>
       <tr>
        <td align="center" class="wrapper" e="font-family: -apple-system, BlinkMacSystemFont, Roboto, Helvetica, Ari                al, sans-serif; font-size: 18px; vertical-align: top; color: #15212A; box-s                izing: border-box;" styl="" valign="top">
         <table border="           0" cellpadding="0" cellspacing="0" role="presentation" style="border-colla    pse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%; p    adding-top: 40px; padding-bottom: 30px;" width="100%">
          <tr>
           <td align="center" class="footer" style="font-family: -apple-system, BlinkMacSystemFont, Roboto, Helvetica, Aria                l, sans-serif; vertical-align: top; color: #73818c; margin-top: 20px; text-                align: center; padding-bottom: 10px; padding-top: 10px; padding-left: 30px;                padding-right: 30px; line-height: 1.5em; font-size: 13px;" valign="top">
            <p dir="ltr" style="margin: 0 0 1.5em 0; line-height:             1.6em;">
             <i>
              <em class="italic">
               Together we can be the transition to ever i        ncreasing harmony with the cycles of life.
              </em>
             </i>
            </p>
           </td>
          </tr>
          <tr>

        <div class="footer">
            <p><em>Together we can be the transition to ever increasing harmony with the cycles of life.</em></p>
            <p>Earthen © 2025 – <a href="https://earthen.io/unsubscribe/?uuid=611f7d90-e87c-4c43-ab51-0772a7883703&key=c8c3faf87323b6ad7a8b96bcc9f9d742316e82dc604c69de46e524bcb11e3104&newsletter=7bbd5ff6-f69e-4ff0-a9d3-67963d85410b">Unsubscribe</a></p>
            <p style="margin-top: 20px;"><a href="https://ghost.org/?via=pbg-newsletter"><img src="https://static.ghost.org/v4.0.0/images/powered.png" width="142" height="30" alt="Powered by Ghost"></a></p>
        </div>
    </div>
</body>
</html>

HTML;

?>