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
    <!--[if mso]>
    <xml><o:OfficeDocumentSettings><o:PixelsPerInch>96</o:PixelsPerInch><o:AllowPNG/></o:OfficeDocumentSettings></xml>
    <![endif]-->
    <title>Solstice, Ayyew &amp; Earthen</title>
    <style>
        /* Your entire original CSS block goes here exactly as in your provided raw HTML */
        .post-title-link {
            display: block;
            margin-top: 32px;
            color: #15212A;
            text-align: center;
            line-height: 1.1em;
        }
        /* etc... (full style block here exactly as you provided it) */
        /* I'm omitting the full style block here for brevity */
    </style>
</head>

<body style="background-color: #fff; font-family: -apple-system, BlinkMacSystemFont, Roboto, Helvetica, Arial, sans-serif; -webkit-font-smoothing: antialiased; font-size: 18px; line-height: 1.4; margin: 0; padding: 0; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; color: #15212A;">

<span class="preheader" style="color: transparent; display: none; height: 0; max-height: 0; max-width: 0; opacity: 0; overflow: hidden; mso-hide: all; visibility: hidden; width: 0;">
    With the coming of this year's Solstice (the other day!), it is most apropos to launch a course that celebrates cyclocentric culture and concepts.
</span>

<table role="presentation" border="0" cellpadding="0" cellspacing="0" class="body" width="100%" style="border-collapse: separate; background-color: #fff; width: 100%;" bgcolor="#fff">
    <tr>
        <td>&nbsp;</td>
        <td class="container" style="display: block; max-width: 600px; margin: 0 auto;">
            <div class="content" style="max-width: 600px; margin: 0 auto;">





<!doctype html>
<html>
<head>
    <meta name=3D"viewport" content=3D"width=3Ddevice-width">
    <meta http-equiv=3D"Content-Type" content=3D"text/html; charset=3DU=
          TF-8">
    <!--[if mso]><xml><o:OfficeDocumentSettings><o:PixelsPerInch>96</o:=
    PixelsPerInch><o:AllowPNG/></o:OfficeDocumentSettings></xml><![endif]-->
    <title>Solstice, Ayyew &amp; Earthen</title>
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
        .kg-cta-text a:not(.kg-cta-link-accent .kg-cta-text a) {
            color: #15212A;
            text-decoration: underline;
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

            table.body .kg-header-card.kg-v2 .kg-header-card-image + .kg-header-card-=
        content {
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

            table.body table.kg-cta-content-wrapper:not(.kg-cta-bg-none.kg-cta-no-div=
iders table.kg-cta-content-wrapper) {
                padding: 20px 0;
            }

            table.body .kg-cta-immersive.kg-cta-has-img:not(.kg-cta-bg-none):not(.kg-=
cta-no-label) table.kg-cta-content-wrapper {
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
<body style=3D"background-color: #fff; font-family: -apple-system, Blin=
        kMacSystemFont, Roboto, Helvetica, Arial, sans-serif; -webkit-font-smoothin=
              g: antialiased; font-size: 18px; line-height: 1.4; margin: 0; padding: 0; -=
              ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; color: #15212A;"=
>
<span class=3D"preheader" style=3D"color: transparent; display: non=
        e; height: 0; max-height: 0; max-width: 0; opacity: 0; overflow: hidden; ms=
              o-hide: all; visibility: hidden; width: 0;">With the coming of this year&#3=
9;s Solstice (the other day!), it is most apropos to launch a course that c=
elebrates cyclocentric culture and concepts.

What is a cyclocentric culture you ask? It is a culture that centers around=
its sync and celebration of Earthen cycles&#x2013; such the migration of a=
nimals, the position of Venus, the phases of the moon, or... the Solstice!

The term is coined by Russell and Irene who we will be interviewing about t=
heir theory of Earthen Ethics on June 29th. We&#39;ll talk abo</span>
<table role=3D"presentation" border=3D"0" cellpadding=3D"0" cellspa=
        cing=3D"0" class=3D"body" width=3D"100%" style=3D"border-collapse: separate=
               ; mso-table-lspace: 0pt; mso-table-rspace: 0pt; background-color: #fff; wid=
               th: 100%;" bgcolor=3D"#fff">
<!-- Outlook doesn't respect max-width so we need an extra cent=
ered table -->
<!--[if mso]>
<tr>
    <td>
        <center>
            <table border=3D"0" cellpadding=3D"0" cellspacing=
                    =3D"0" width=3D"600">
<![endif]-->
<tr>
    <td style=3D"font-family: -apple-system, BlinkMacSystemFont=
            , Roboto, Helvetica, Arial, sans-serif; font-size: 18px; vertical-align: to=
                p; color: #15212A;" valign=3D"top">&#xA0;</td>
    <td class=3D"container" style=3D"font-family: -apple-system=
            , BlinkMacSystemFont, Roboto, Helvetica, Arial, sans-serif; font-size: 18px=
                ; vertical-align: top; color: #15212A; display: block; max-width: 600px; ma=
                rgin: 0 auto;" valign=3D"top">
    <div class=3D"content" style=3D"box-sizing: border-box;=
            display: block; margin: 0 auto; max-width: 600px;">
    <!-- START CENTERED WHITE CONTAINER -->
    <table role=3D"presentation" border=3D"0" cellpaddi=
            ng=3D"0" cellspacing=3D"0" class=3D"main" width=3D"100%" style=3D"border-co=
           llapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; background:=
                   #ffffff; border-radius: 3px; border-spacing: 20px 0; width: 100%;">

    <!-- START MAIN CONTENT AREA -->
<tr>
    <td class=3D"wrapper" style=3D"font-family:=
        -apple-system, BlinkMacSystemFont, Roboto, Helvetica, Arial, sans-serif; f=
                ont-size: 18px; vertical-align: top; color: #15212A; box-sizing: border-box=
                ;" valign=3D"top">
        <table role=3D"presentation" border=3D"=
               0" cellpadding=3D"0" cellspacing=3D"0" width=3D"100%" style=3D"border-colla=
        pse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%;">
<tr class=3D"header-image-row">
    <td class=3D"header-image" =
    width=3D"100%" align=3D"center" style=3D"font-family: -apple-system, BlinkM=
    acSystemFont, Roboto, Helvetica, Arial, sans-serif; font-size: 18px; vertic=
    al-align: top; color: #15212A; padding-top: 24px; padding-bottom: 16px;" va=
    lign=3D"top">
    <a href=3D"https://eart=
       hen.io/r/ff19a55c?m=3D611f7d90-e87c-4c43-ab51-0772a7883703" style=3D"color:=
    #4B8501; text-decoration: none; overflow-wrap: anywhere;" target=3D"_blank=
    ">
    <img src=3D"https:/=
    /earthen.io/content/images/size/w1200/2022/08/email-cover-1200px.jpg" width=
    =3D"600" style=3D"border: none; -ms-interpolation-mode: bicubic; max-width:=
    100%;">
    </a>
    </td>
</tr>

<tr class=3D"site-info-row">
    <td class=3D"site-info" wid=
            th=3D"100%" align=3D"center" style=3D"font-family: -apple-system, BlinkMacS=
                ystemFont, Roboto, Helvetica, Arial, sans-serif; font-size: 18px; vertical-=
                align: top; color: #15212A; padding-top: 32px;" valign=3D"top">
    <table role=3D"presenta=
           tion" border=3D"0" cellpadding=3D"0" cellspacing=3D"0" style=3D"border-coll=
    apse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%;"=
    width=3D"100%">
<tr>
    <td class=
                =3D"site-icon" style=3D"font-family: -apple-system, BlinkMacSystemFont, Rob=
                oto, Helvetica, Arial, sans-serif; font-size: 18px; vertical-align: top; co=
                lor: #15212A; padding-bottom: 8px; padding-top: 8px; text-align: center; bo=
                rder-radius: 3px;" valign=3D"top" align=3D"center"><a href=3D"https://earth=
                                                                      en.io/r/5adad2dd?m=3D611f7d90-e87c-4c43-ab51-0772a7883703" style=3D"color: =
    #4B8501; text-decoration: none; overflow-wrap: anywhere;" target=3D"_blank"=
    ><img src=3D"https://earthen.io/content/images/2022/07/page-logo.png" alt=
        =3D"Earthen" border=3D"0" width=3D"44" height=3D"44" style=3D"border: none;=
                  -ms-interpolation-mode: bicubic; max-width: 100%; width: 44px; height: 44p=
                  x; border-radius: 3px;"></a></td>
</tr>
<tr>
    <td class=
                =3D"site-url site-url-bottom-padding" style=3D"font-family: -apple-system, =
    BlinkMacSystemFont, Roboto, Helvetica, Arial, sans-serif; vertical-align: t=
    op; color: #15212A; font-size: 16px; letter-spacing: -0.1px; font-weight: 7=
    00; text-transform: uppercase; text-align: center; padding-bottom: 12px;" v=
    align=3D"top" align=3D"center"><div style=3D"width: 100% !important;"><a hr=
                                                                                     ef=3D"https://earthen.io/r/0a0409ba?m=3D611f7d90-e87c-4c43-ab51-0772a788370=
                                                                             3" class=3D"site-title" style=3D"text-decoration: none; color: #15212A; ove=
    rflow-wrap: anywhere;" target=3D"_blank">Earthen</a></div></td>
</tr>

</table>
</td>
</tr>

<tr>
    <td class=3D"post-title pos=
            t-title-no-excerpt" style=3D"font-family: -apple-system, BlinkMacSystemFont=
                , Roboto, Helvetica, Arial, sans-serif; vertical-align: top; color: #15212A=
                ; font-size: 36px; line-height: 1.1em; font-weight: 700; text-align: center=
                ; padding-bottom: 16px;" valign=3D"top" align=3D"center">
    <a href=3D"https://eart=
       hen.io/r/08f20575?m=3D611f7d90-e87c-4c43-ab51-0772a7883703" class=3D"post-t=
    itle-link" style=3D"text-decoration: none; display: block; margin-top: 32px=
    ; color: #15212A; text-align: center; line-height: 1.1em; overflow-wrap: an=
    ywhere;" target=3D"_blank">Solstice, Ayyew &amp; Earthen</a>
    </td>
</tr>
<tr>
    <td style=3D"font-family: -=
            apple-system, BlinkMacSystemFont, Roboto, Helvetica, Arial, sans-serif; fon=
                t-size: 18px; vertical-align: top; color: #15212A; width: 100%;" width=3D"1=
    00%" valign=3D"top">
    <table class=3D"post-me=
           ta-wrapper" role=3D"presentation" border=3D"0" cellpadding=3D"0" cellspacin=
    g=3D"0" width=3D"100%" style=3D"border-collapse: separate; mso-table-lspace=
    : 0pt; mso-table-rspace: 0pt; width: 100%; padding-bottom: 32px;">
<tr>
    <td height=3D"2=
        0" class=3D"post-meta post-meta-center" style=3D"font-family: -apple-system=
    , BlinkMacSystemFont, Roboto, Helvetica, Arial, sans-serif; vertical-align:=
    top; color: #73818c; font-size: 13px; font-weight: 400; text-align: center=
    ; padding: 0;" valign=3D"top" align=3D"center">
    By GEA Cent=
    er Circle &#x2022; <span class=3D"post-meta-date" style=3D"white-space: now=
        rap;">25 Jun 2025 </span>
    </td>
    <td class=3D"po=
        st-meta post-meta-center view-online desktop" style=3D"font-family: -apple-=
    system, BlinkMacSystemFont, Roboto, Helvetica, Arial, sans-serif; vertical-=
    align: top; color: #73818c; font-size: 13px; font-weight: 400; text-align: =
    center; display: none;" valign=3D"top" align=3D"center">
    <a href=3D"=
       https://earthen.io/r/55ece3e6?m=3D611f7d90-e87c-4c43-ab51-0772a7883703" cla=
    ss=3D"view-online-link" style=3D"word-wrap: none; white-space: nowrap; colo=
    r: #73818c; overflow-wrap: anywhere; text-decoration: underline;" target=3D=
    "_blank">View in browser</a>
    </td>
</tr>
<tr class=3D"post-m=
    eta post-meta-center view-online-mobile" style=3D"color: #73818c; font-size=
: 13px; font-weight: 400; text-align: center;" align=3D"center">
<td height=3D"2=
    0" class=3D"view-online" style=3D"font-family: -apple-system, BlinkMacSyste=
mFont, Roboto, Helvetica, Arial, sans-serif; vertical-align: top; color: #7=
3818c; font-size: 13px; font-weight: 400; text-align: center; text-decorati=
on: underline;" valign=3D"top" align=3D"center">
<a href=3D"=
   https://earthen.io/r/6847d69e?m=3D611f7d90-e87c-4c43-ab51-0772a7883703" cla=
ss=3D"view-online-link" style=3D"word-wrap: none; white-space: nowrap; colo=
r: #73818c; overflow-wrap: anywhere; text-decoration: underline;" target=3D=
"_blank">View in browser</a>
</td>
</tr>
</table>
</td>
</tr>

<tr class=3D"feature-image-row"=
>
    <td class=3D"feature-image
        feature-image-with-=
                caption
    " align=3D"center" style=3D=
    "font-family: -apple-system, BlinkMacSystemFont, Roboto, Helvetica, Arial, =
    sans-serif; vertical-align: top; color: #15212A; padding-bottom: 30px; text=
    -align: center; width: 100%; padding: 0; font-size: 13px;" width=3D"100%" v=
    align=3D"top"><img src=3D"https://earthen.io/content/images/size/w1200/2025=
    /06/earthen-ethics-launch-1-1.png" width=3D"600" style=3D"border: none; -ms=
    -interpolation-mode: bicubic; max-width: 100%;"></td>
</tr>

<tr>
    <td align=3D"center" st=
            yle=3D"font-family: -apple-system, BlinkMacSystemFont, Roboto, Helvetica, A=
                rial, sans-serif; font-size: 18px; vertical-align: top; color: #15212A;" va=
    lign=3D"top">
    <div class=3D"featu=
         re-image-caption" style=3D"width: 100%; padding-top: 5px; padding-bottom: 3=
    2px; text-align: center; color: #73818c; line-height: 1.5em; font-size: 13p=
    x;">
    <span style=3D"=
          white-space: pre-wrap;">The land of the Igorot people in the Northern Phili=
    ppines where the Ayyew concept originates</span>
    </div>
    </td>
</tr>
<tr class=3D"post-content-row">
    <td class=3D"post-content" styl=
            e=3D"vertical-align: top; font-family: Georgia, serif; font-size: 18px; lin=
                e-height: 1.5em; color: #15212A; padding-bottom: 20px; border-bottom: 1px s=
                olid #e0e7eb; max-width: 600px;" valign=3D"top">
    <!-- POST CONTENT START -->
    <p style=3D"margin: 0 0 1.5=
            em 0; line-height: 1.6em;">With the coming of this year&#39;s Solstice (the=
    other day!), it is most apropos to launch a course that celebrates cycloce=
    ntric culture and concepts.</p><p style=3D"margin: 0 0 1.5em 0; line-height=
        : 1.6em;">What is a <em>cyclocentric</em> culture you ask?  It is a culture=
    that centers around its sync and celebration of Earthen cycles&#x2013; suc=
    h the migration of animals, the position of Venus, the phases of the moon, =
    or...  the Solstice!</p><p style=3D"margin: 0 0 1.5em 0; line-height: 1.6em=
        ;">The term is coined by Russell and Irene who we will be interviewing abou=
    t their theory of <em>Earthen Ethics</em> on June 29th.  We&#39;ll talk abo=
    ut Irene&#39;s cyclocentric Igorot culture and her people&#39;s concept of =
    Ayyew.  Russell and Irene argue that concept of Ayyew (and the cyclocentric=
    paradigm) are crucial for transitioning humanity to deep ecological integr=
    ation.</p><p style=3D"margin: 0 0 1.5em 0; line-height: 1.6em;">Course regi=
    stration on GoBrik is now open and free:</p><div>
    <!--[if !mso !vml]-->
    <div class=3D"kg-card kg-bookmark-card " style=3D"margin: 0 0 1=
    .5em; padding: 0; width: 100%; background: #ffffff;">
    <a class=3D"kg-bookmark-container" href=3D"https://earthen.=
       io/r/db2774f1?m=3D611f7d90-e87c-4c43-ab51-0772a7883703" style=3D"display: f=
    lex; min-height: 148px; font-family: -apple-system, BlinkMacSystemFont, Rob=
    oto, Helvetica, Arial, sans-serif; border-radius: 3px; border: 1px solid #e=
    0e7eb; overflow-wrap: anywhere; color: #4B8501; text-decoration: none;" tar=
    get=3D"_blank">
    <div class=3D"kg-bookmark-content" style=3D"display: in=
            line-block; width: 100%; padding: 20px;">
    <div class=3D"kg-bookmark-title" style=3D"color: #1=
            5212A; font-size: 15px; line-height: 1.5em; font-weight: 600;">An Intro to =
    Ayyew &amp; Earthen Ethics</div>
    <div class=3D"kg-bookmark-description" style=3D"dis=
         play: -webkit-box; overflow-y: hidden; margin-top: 12px; max-height: 40px; =
                 color: #73818c; font-size: 13px; line-height: 1.5em; font-weight: 400; -web=
                 kit-line-clamp: 2; -webkit-box-orient: vertical;">Register for our GEA Comm=
    unity Event led by Ani Himawati and Lucie Mann on June 29, 2025</div>
    <div class=3D"kg-bookmark-metadata" style=3D"displa=
         y: flex; flex-wrap: wrap; align-items: center; margin-top: 14px; color: #15=
                 212A; font-size: 13px; font-weight: 400;">
    <img class=3D"kg-bookmark-icon" src=3D"https://=
         earthen.io/content/images/icon/apple-icon-180x180-13.png" alt style=3D"bord=
    er: none; -ms-interpolation-mode: bicubic; max-width: 100%; margin-right: 8=
    px; width: 22px; height: 22px;" width=3D"22" height=3D"22">
    <span class=3D"kg-bookmark-author" src=3D"GoBri=
          k.com" style=3D"line-height: 1.5em;">GoBrik.com</span>
    =20
    </div>
    </div>
    <div class=3D"kg-bookmark-thumbnail" style=3D"min-width=
         : 140px; max-width: 180px; background-repeat: no-repeat; background-size: c=
                 over; background-position: center; border-radius: 0 2px 2px 0; background-i=
                 mage: url(&#39;https://earthen.io/content/images/thumbnail/earthen-ethics-l=
    aunch-1.web&#39;);">
    <img src=3D"https://earthen.io/content/images/thumb=
         nail/earthen-ethics-launch-1.web" alt onerror=3D"this.style.display=3D&#39;=
    none&#39;" style=3D"border: none; -ms-interpolation-mode: bicubic; max-widt=
    h: 100%; display: none;"></div>
    </a>
    =20
    </div>
    <!--[endif]-->
    <!--[if vml]>
        <table class=3D"kg-card kg-bookmark-card--outlook" style=3D"mar=
gin: 0; padding: 0; width: 100%; border: 1px solid #e5eff5; background: #ff=
ffff; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, O=
xygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif; border=
-collapse: collapse; border-spacing: 0;" width=3D"100%">
            <tr>
                <td width=3D"100%" style=3D"padding: 20px;">
                    <table style=3D"margin: 0; padding: 0; border-colla=
pse: collapse; border-spacing: 0;">
                        <tr>
                            <td class=3D"kg-bookmark-title--outlook">
                                <a href=3D"https://gobrik.com/en/regist=
er.php?id=3D913" style=3D"text-decoration: none; color: #15212A; font-size:=
15px; line-height: 1.5em; font-weight: 600;">
                                    An Intro to Ayyew &amp; Earthen Eth=
ics
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class=3D"kg-bookmark-description--=
outlook">
                                    <a href=3D"https://gobrik.com/en/re=
gister.php?id=3D913" style=3D"text-decoration: none; margin-top: 12px; colo=
r: #738a94; font-size: 13px; line-height: 1.5em; font-weight: 400;">
                                        Register for our GEA Community =
Event led by Ani Himawati and Lucie Mann on June 29, 2025
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td class=3D"kg-bookmark-metadata--outlook"=
style=3D"padding-top: 14px; color: #15212A; font-size: 13px; font-weight: =
400; line-height: 1.5em;">
                                <table style=3D"margin: 0; padding: 0; =
border-collapse: collapse; border-spacing: 0;">
                                    <tr>
                                       =20
                                            <td valign=3D"middle" class=
=3D"kg-bookmark-icon--outlook" style=3D"padding-right: 8px; font-size: 0; l=
ine-height: 1.5em;">
                                                <a href=3D"https://gobr=
ik.com/en/register.php?id=3D913" style=3D"text-decoration: none; color: #15=
212A;">
                                                    <img src=3D"https:/=
/earthen.io/content/images/icon/apple-icon-180x180-13.png" width=3D"22" hei=
ght=3D"22" alt=3D" ">
                                                </a>
                                            </td>
                                       =20
                                        <td valign=3D"middle" class=3D"=
kg-bookmark-byline--outlook">
                                            <a href=3D"https://gobrik.c=
om/en/register.php?id=3D913" style=3D"text-decoration: none; color: #15212A=
;">
                                                GoBrik.com
                                               =20
                                               =20
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
        <div class=3D"kg-bookmark-spacer--outlook" style=3D"height: 1.5=
em;">&nbsp;</div>
    <![endif]--></div><p style=3D"margin: 0 0 1.5em 0; line-height: 1.6=
        em;">We&#39;re also pleased to announce another introduction to Ecobrick co=
    urse which will run on July 19.  </p><div>
    <!--[if !mso !vml]-->
    <div class=3D"kg-card kg-bookmark-card " style=3D"margin: 0 0 1=
    .5em; padding: 0; width: 100%; background: #ffffff;">
    <a class=3D"kg-bookmark-container" href=3D"https://earthen.=
       io/r/33183cd9?m=3D611f7d90-e87c-4c43-ab51-0772a7883703" style=3D"display: f=
    lex; min-height: 148px; font-family: -apple-system, BlinkMacSystemFont, Rob=
    oto, Helvetica, Arial, sans-serif; border-radius: 3px; border: 1px solid #e=
    0e7eb; overflow-wrap: anywhere; color: #4B8501; text-decoration: none;" tar=
    get=3D"_blank">
    <div class=3D"kg-bookmark-content" style=3D"display: in=
            line-block; width: 100%; padding: 20px;">
    <div class=3D"kg-bookmark-title" style=3D"color: #1=
            5212A; font-size: 15px; line-height: 1.5em; font-weight: 600;">Plastic, the=
    Biosphere and Ecobricks</div>
    <div class=3D"kg-bookmark-description" style=3D"dis=
         play: -webkit-box; overflow-y: hidden; margin-top: 12px; max-height: 40px; =
                 color: #73818c; font-size: 13px; line-height: 1.5em; font-weight: 400; -web=
                 kit-line-clamp: 2; -webkit-box-orient: vertical;">Register for our Online S=
    tarter Workshop led by Paula Apollonia and Russell Maier on July<span class=
                                                                                  =3D"desktop-only"> 19, 2025</span><span class=3D"hide-desktop" style=3D"dis=
                                                                                                                          play: none;">&#x2026;</span></div>
    <div class=3D"kg-bookmark-metadata" style=3D"displa=
         y: flex; flex-wrap: wrap; align-items: center; margin-top: 14px; color: #15=
                 212A; font-size: 13px; font-weight: 400;">
    <img class=3D"kg-bookmark-icon" src=3D"https://=
         earthen.io/content/images/icon/apple-icon-180x180-14.png" alt style=3D"bord=
    er: none; -ms-interpolation-mode: bicubic; max-width: 100%; margin-right: 8=
    px; width: 22px; height: 22px;" width=3D"22" height=3D"22">
    <span class=3D"kg-bookmark-author" src=3D"GoBri=
          k.com" style=3D"line-height: 1.5em;">GoBrik.com</span>
    =20
    </div>
    </div>
    <div class=3D"kg-bookmark-thumbnail" style=3D"min-width=
         : 140px; max-width: 180px; background-repeat: no-repeat; background-size: c=
                 over; background-position: center; border-radius: 0 2px 2px 0; background-i=
                 mage: url(&#39;https://earthen.io/content/images/thumbnail/starter-workshop=
    -feature-1-en-2.webp&#39;);">
    <img src=3D"https://earthen.io/content/images/thumb=
         nail/starter-workshop-feature-1-en-2.webp" alt onerror=3D"this.style.displa=
    y=3D&#39;none&#39;" style=3D"border: none; -ms-interpolation-mode: bicubic;=
    max-width: 100%; display: none;"></div>
    </a>
    =20
    </div>
    <!--[endif]-->
    <!--[if vml]>
        <table class=3D"kg-card kg-bookmark-card--outlook" style=3D"mar=
gin: 0; padding: 0; width: 100%; border: 1px solid #e5eff5; background: #ff=
ffff; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, O=
xygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif; border=
-collapse: collapse; border-spacing: 0;" width=3D"100%">
            <tr>
                <td width=3D"100%" style=3D"padding: 20px;">
                    <table style=3D"margin: 0; padding: 0; border-colla=
pse: collapse; border-spacing: 0;">
                        <tr>
                            <td class=3D"kg-bookmark-title--outlook">
                                <a href=3D"https://gobrik.com/en/regist=
er.php?id=3D920" style=3D"text-decoration: none; color: #15212A; font-size:=
15px; line-height: 1.5em; font-weight: 600;">
                                    Plastic, the Biosphere and Ecobrick=
s
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class=3D"kg-bookmark-description--=
outlook">
                                    <a href=3D"https://gobrik.com/en/re=
gister.php?id=3D920" style=3D"text-decoration: none; margin-top: 12px; colo=
r: #738a94; font-size: 13px; line-height: 1.5em; font-weight: 400;">
                                        Register for our Online Starter=
Workshop led by Paula Apollonia and Russell Maier on July<span class=3D"de=
sktop-only"> 19, 2025</span><span class=3D"hide-desktop">=E2=80=A6</span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td class=3D"kg-bookmark-metadata--outlook"=
style=3D"padding-top: 14px; color: #15212A; font-size: 13px; font-weight: =
400; line-height: 1.5em;">
                                <table style=3D"margin: 0; padding: 0; =
border-collapse: collapse; border-spacing: 0;">
                                    <tr>
                                       =20
                                            <td valign=3D"middle" class=
=3D"kg-bookmark-icon--outlook" style=3D"padding-right: 8px; font-size: 0; l=
ine-height: 1.5em;">
                                                <a href=3D"https://gobr=
ik.com/en/register.php?id=3D920" style=3D"text-decoration: none; color: #15=
212A;">
                                                    <img src=3D"https:/=
/earthen.io/content/images/icon/apple-icon-180x180-14.png" width=3D"22" hei=
ght=3D"22" alt=3D" ">
                                                </a>
                                            </td>
                                       =20
                                        <td valign=3D"middle" class=3D"=
kg-bookmark-byline--outlook">
                                            <a href=3D"https://gobrik.c=
om/en/register.php?id=3D920" style=3D"text-decoration: none; color: #15212A=
;">
                                                GoBrik.com
                                               =20
                                               =20
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
        <div class=3D"kg-bookmark-spacer--outlook" style=3D"height: 1.5=
em;">&nbsp;</div>
    <![endif]--></div><p style=3D"margin: 0 0 1.5em 0; line-height: 1.6=
        em;">Hope to see you there.<br><br>The Earthen Team</p>
    <!-- POST CONTENT END -->

    </td>
</tr>
</table>
</td>
</tr>

<!-- END MAIN CONTENT AREA -->







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
