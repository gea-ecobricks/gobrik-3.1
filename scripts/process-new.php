<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>ᐉ Help the Great GoBrik Migration</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #ddd;
        }
        .gallery {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        .gallery div {
            padding: 10px;
            background: white;
            border: 1px solid #ccc;
            border-radius: 5px;
            margin-bottom: 10px;
        }
        .button {
            padding: 10px 20px;
            margin: 10px;
            font-size: 16px;
            cursor: pointer;
        }
        .log {
            margin: 20px 0;
            padding: 10px;
            background: #fff;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
    </style>
</head>
<body>
<div class="control-buttons">
    <button class="button" onclick="stopProcessing()">Stop Processing</button>
    <button class="button" onclick="startProcessing()">ᐉ Start Processing</button>
</div>
<p>We're migrating ecobrickers from our old server to our new. Help us out by running this page on your computer or phone. Just keep it up. If it crashes or stops, reload the page. Thank you!</p>

<div id="ecobrick-being-processed">
    <div id="ecobrickers-processed-gallery">
        <?php
        ini_set('display_errors', 1);
        error_reporting(E_ALL);

        include '../gobrikconn_env.php';
        $conn->set_charset("utf8mb4");

        $query = "SELECT first_name, buwana_id, location_full_txt,
        date_registered, email_addr FROM tb_ecobrickers
                  ORDER BY gobrik_migrated_dt DESC
                  LIMIT 18";

        $result = $conn->query($query);
        ?>

        <h1>Latest Ecobricker Imports</h1>
        <div class="gallery">
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $first_name = htmlspecialchars($row['first_name']);
                    $buwana_id = htmlspecialchars($row['buwana_id']);
                    $location = htmlspecialchars($row['location_full_txt']);
                    $date_registered = htmlspecialchars($row['date_registered']);
                    $email = htmlspecialchars($row['email_addr']);

                    echo "<div>
                        <p><strong>Name:</strong> $first_name</p>
                        <p><strong>Buwana ID:</strong> $buwana_id</p>
                        <p><strong>Location:</strong> $location</p>
                        <p><strong>Date Registered:</strong> $date_registered</p>
                        <p><strong>Email:</strong> $email</p>
                      </div>";
                }
            } else {
                echo "<p>No ecobrickers found.</p>";
            }
            ?>
        </div>
        <?php $conn->close(); ?>
    </div>
</div>

<div id="knack-response"></div>

<script>
    function stopProcessing() {
        if (confirm('Are you sure you want to stop the processing?')) {
            window.location.href = 'process_ecobricker_new.php?action=stop';
        }
    }

    function startProcessing() {
        if (confirm('Are you sure you want to start the processing?')) {
            window.location.href = 'process_ecobricker_new.php?action=start';
        }
    }

    function processEcobricker() {
        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'process_ecobricker_new.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4 && xhr.status === 200) {
                const response = JSON.parse(xhr.responseText);
                const logDiv = document.createElement('div');
                logDiv.className = 'log';

                if (response.success) {
                    logDiv.innerHTML = response.success;
                } else {
                    logDiv.innerHTML = response.error;
                }

                document.getElementById('knack-response').appendChild(logDiv);
                processEcobricker();
            }
        };

        xhr.send();
    }

    document.addEventListener('DOMContentLoaded', function() {
        processEcobricker();
    });
</script>
</body>
</html>
