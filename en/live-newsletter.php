<?php

 // Default email HTML with dynamic unsubscribe link
$email_template = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Equinox Recap: Spring update v1.0</title>
    <style>
        body {
            background-color: #ffffff;
            font-family: -apple-system, BlinkMacSystemFont, Roboto, Helvetica, Arial, sans-serif;
            font-size: 18px;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            color: #15212A;
        }

        a {
            color: #4B8501;
            text-decoration: none;
        }

        img {
            max-width: 100%;
            height: auto;
            border: 0;
            display: block;
            margin: 0 auto;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }

        .site-title {
            text-align: center;
            text-transform: uppercase;
            font-weight: bold;
            padding-bottom: 10px;
            font-size: 16px;
        }

        .post-title {
            font-size: 36px;
            font-weight: 700;
            text-align: center;
            margin: 30px 0 10px;
        }

        .post-meta {
            text-align: center;
            font-size: 13px;
            color: #73818c;
        }

        .post-content p {
            margin-bottom: 1.5em;
        }

        .feature-image-caption,
        .kg-card-figcaption {
            text-align: center;
            color: #73818c;
            font-size: 13px;
            margin-top: 5px;
        }

        .bookmark {
            border: 1px solid #e0e7eb;
            border-radius: 3px;
            margin: 20px 0;
            overflow: hidden;
        }

        .bookmark a {
            display: block;
            color: #15212A;
            text-decoration: none;
        }

        .bookmark .meta {
            font-size: 13px;
            color: #73818c;
        }

        .footer {
            text-align: center;
            font-size: 13px;
            color: #73818c;
            margin-top: 30px;
            border-top: 1px solid #e0e7eb;
            padding-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="https://earthen.io/">
            <img src="https://earthen.io/content/images/size/w1200/2022/08/email-cover-1200px.jpg" alt="Earthen Cover">
        </a>

        <div class="site-title">
            <img src="https://earthen.io/content/images/2022/07/page-logo.png" alt="Earthen Logo" width="44" height="44"><br>
            <a href="https://earthen.io/">Earthen</a>
        </div>

        <div class="post-title">
            <a href="https://earthen.io/p/99c9ba31-badb-4ba5-ba45-25c00a207c7f/">Earthen Spring Recap v.1.0</a>
        </div>

        <div class="post-meta">
            By the Global Ecobrick Alliance • 22 Apr 2025<br>
            <a href="https://earthen.io/p/99c9ba31-badb-4ba5-ba45-25c00a207c7f/">View in browser</a>
        </div>

        <div class="post-content">
            <img src="https://earthen.io/content/images/size/w2000/2025/04/easter-spring-update.png" alt="Earthcal">
            <p class="feature-image-caption">
                And the planets align! This week Earth, Venus, Mercury, Jupiter and Neptune align - Image: <a href="https://cycles.earthen.io/">Earthcal</a>
            </p>

            <p>Happy Earth Day!  Whether it's the onset of Fall or Spring for you, with the passing of the Equinox, Eid and the arrival of Easter... it's time for our GEA Earthen quarterly update!</p>

            <p>In celebration of our planet, its crucial to remember, that the way something is said has more meaning than what is said.  And that's the case with this very newsletter! The GEA has proudly completely ditched its reliance on corporate technology platforms for 2025.</p>

            <p>This Earthen update is being sent to using the opensource Ghost.org newsletter platform that we run on our own server. We've rebuilt our GoBrik app from the ground up to get off amazon servers. Our center circle has ditched whatsapp for Signal. We're using Nextcloud instead of dropbox/google for our docs. And our dev team has built our own open source Buwana account system so you don't have to sign into our services with an apple or facebook account!</p>

            <div class="bookmark">
                <a href="https://earthen.io/gobrik-2025-accounts-ditching-big-tech-2/">

                    <div style="padding: 10px;">
                        <strong>We’ve ditched big-tech for 2025</strong><br>
                        <span class="meta">Earthen • GEA Center Circle</span>
                    </div>
                </a>
            </div>

            <br><br>
             <img src="https://earthen.io/content/images/2025/04/image.png" alt="UN condemns systematic destruction">

            <p class="kg-card-figcaption">UN rights expert condemns ‘systematic’ war-time mass destruction of homes.  <a href="https://news.un.org/en/story/2024/03/1147272">UN.org</a></p>

            <p>Second, we’re now USD free. Over a year ago, our GEA team, observing the direct connection between the US support of the ecocide and genocide in Gaza, we decided to no longer accept, hold, or denominate our accounting in USD.</p>

            <p>As the occupation forces continue to wage destruction of homes and biomes in Gaza and the West Bank, we grieve and are deeply saddened.  This goes against all our  ecological and humanitarian principles.  The GEA stands by our decision to disengage from the petro-captical currency that is behind the social oppression and ecological depletion in Palestine (and other places around the world). We've thus moved our accounting to Indonesian Rupiahs instead.</p>

            <div class="bookmark">
                <a href="https://earthen.io/were-ditching-the-us-dollar/">
                    <div style="padding: 10px;">
                        <strong>We’ve moved on from the US Dollar</strong><br>
                        <span class="meta">Earthen • GEA Center Circle</span>
                    </div>
                </a>
            </div>

            <br><br>

<img src="https://earthen.io/content/images/size/w1600/2025/04/image-1.png" alt="Bamboo build">
            <p>Finally, we're excited to share a breakthrough by Russell Maier, one of our founders and a lead developer on the GEA team. Russell and his engineering team has been working on bamboo to connect ecobricks in all sorts of new geometric ways! Their initial experiments are very promising! We're really excited to see where this goes.</p>


            <div class="bookmark">
                <a href="https://earthen.io/a-new-way-to-connect-ecobricks-2/">
                    <div style="padding: 10px;">
                        <strong>New ways to connect ecobricks</strong><br>
                        <span class="meta">Earthen • Russell Maier</span>
                    </div>
                </a>
            </div>

            <p>Stay tuned. As we get rolling with our new GoBrik and Earthen newsletter this year, we'll share more great regenerative news, events and developments in the plastic transition and earthen movement.</p>
        </div>

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