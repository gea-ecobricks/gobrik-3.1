<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Ecobricker Migration Form</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #ddd;
            font-size: small;
        }
        .gallery {
            display: flex;
            flex-wrap: wrap;
            gap: 2px;
        }
        .gallery div {
            background: white;
            padding: 2px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
    </style>
</head>
<body>

<!-- Part 2: Display Latest Ecobrickers -->
<div id="ecobrick-being-processed">
    <div id="ecobricks-processed-gallery">
        <?php
        ini_set('display_errors', 1);
        error_reporting(E_ALL);

        include '../gobrikconn_env.php';
        $conn->set_charset("utf8mb4");

        // SQL query to fetch the latest 10 ecobrickers
        $query = "SELECT first_name, buwana_id, location_full_txt, date_registered, email_addr FROM tb_ecobrickers
                  ORDER BY gobrik_migrated_dt DESC
                  LIMIT 10";

        $result = $conn->query($query);
        ?>

        <h1>Latest Ecobrickers Transferred v.1</h1>
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
    </div>
</div>

<!-- Part 4: Process and Upload Data to GoBrik Database -->
<div id="knack-response">
    <?php
    // Start migration automatically when the page loads
    $api_key = "360aa2b0-af19-11e8-bd38-41d9fc3da0cf";
    $app_id = "5b8c28c2a1152679c209ce0c";
    $object_id = "object_14";

    $filters = [
        'match' => 'and',
        'rules' => [
            [
                'field' => 'field_2525',
                'operator' => 'is not',
                'value' => 'yes'
            ],

                  [
                'field' => 'field_141_raw',
                'operator' => 'is not',
                'value' => '0'
            ]
        ]
    ];

    $url = "https://api.knack.com/v1/objects/$object_id/records?filters=" . urlencode(json_encode($filters)) . "&sort_field=field_261&sort_order=asc";

    // Initialize cURL session
    $ch = curl_init($url);

    // Set cURL options
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "X-Knack-Application-ID: $app_id",
        "X-Knack-REST-API-Key: $api_key",
        "Content-Type: application/json"
    ]);

    // Execute cURL request
    $response = curl_exec($ch);

    // Check for cURL errors
    if ($response === false) {
        echo '<p>Error retrieving data: ' . curl_error($ch) . '</p>';
        ob_flush();
        flush();
    } else {
        // Log the entire JSON response to the console
        echo "<script>console.log('Knack API Response: " . addslashes($response) . "');</script>";
        ob_flush();
        flush();

        $json_response = json_decode($response, true);
        if (!empty($json_response['records'])) {
            $processed_count = 0;

            foreach ($json_response['records'] as $record) {
                $processed_count++;
                if ($processed_count > 20) {
                    echo '<script>
                        setTimeout(function() {
                            window.location.href = "process_ecobrickers.php";
                        }, 5000);
                    </script>';
                    break;
                }

                $record_id = $record['id'] ?? null;
                $legacy_gobrik_user_id = $record['field_261'] ?? null;
                $first_name = $record['field_198'] ?? '';
                $last_name = $record['field_102_raw']['last'] ?? '';
                $full_name = $record['field_102_raw']['full'] ?? '';
                $user_roles = $record['profile_keys'] ?? '';
                $gea_status = $record['field_273'] ?? '';
                $community = strip_tags($record['field_125'] ?? '');
                $email_addr = $record['field_103_raw']['email'] ?? '';
                $date_registered = $record['field_294'] ?? '';
                $phone_no = $record['field_421_raw']['full'] ?? '';
                $ecobricks_made = $record['field_141_raw'] ?? 0;
                $brk_balance = $record['field_400_raw'] ?? 0;
                $aes_balance = $record['field_1747_raw'] ?? '';
                $aes_purchased = $record['field_2000_raw'] ?? '';
                $country_txt = strip_tags($record['field_326'] ?? '');
                $region_txt = strip_tags($record['field_359'] ?? '');
                $city_txt = strip_tags($record['field_342'] ?? '');
                $location_full_txt = $record['field_429'] ?? '';
                $household_txt = strip_tags($record['field_2028'] ?? '');
                $gender = $record['field_283'] ?? '';
                $personal_catalyst = strip_tags($record['field_1676'] ?? '');
                $trainer_availability = $record['field_430'] ?? '';
                $pronoun = $record['field_552'] ?? '';
                $household_generation = $record['field_2231_raw'] ?? 0;
                $country_per_capita_consumption = $record['field_2106_raw'] ?? 0;
                $my_consumption_estimate = $record['field_2221'] ?? 0;
                $household_members = $record['field_1851'] ?? 0;
                $household = $record['field_2038'] ?? 0;

                // Manually set fields
                $buwana_activated = 0;
                $gobrik_migrated = 1;
                $account_notes = 'migrated from knack gobrik on July 29th, 2024';
                $gobrik_migrated_dt = date('Y-m-d H:i:s');

                // Insert the data into tb_ecobrickers
                $sql_insert = "INSERT INTO tb_ecobrickers (maker_id, legacy_gobrik_user_id, first_name, last_name, full_name, user_roles, gea_status, community, email_addr, date_registered, phone_no, ecobricks_made, brk_balance, aes_balance, aes_purchased, country_txt, region_txt, city_txt, location_full_txt, household_txt, gender, personal_catalyst, trainer_availability, pronoun, household_generation, country_per_capita_consumption, my_consumption_estimate, household_members, household, buwana_activated, gobrik_migrated, account_notes, gobrik_migrated_dt) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

                $stmt_insert = $conn->prepare($sql_insert);
                if ($stmt_insert) {
                    $stmt_insert->bind_param(
                        'sisssssssssiddsssssssssssdddiiiss',
                        $record_id,
                        $legacy_gobrik_user_id,
                        $first_name,
                        $last_name,
                        $full_name,
                        $user_roles,
                        $gea_status,
                        $community,
                        $email_addr,
                        $date_registered,
                        $phone_no,
                        $ecobricks_made,
                        $brk_balance,
                        $aes_balance,
                        $aes_purchased,
                        $country_txt,
                        $region_txt,
                        $city_txt,
                        $location_full_txt,
                        $household_txt,
                        $gender,
                        $personal_catalyst,
                        $trainer_availability,
                        $pronoun,
                        $household_generation,
                        $country_per_capita_consumption,
                        $my_consumption_estimate,
                        $household_members,
                        $household,
                        $buwana_activated,
                        $gobrik_migrated,
                        $account_notes,
                        $gobrik_migrated_dt
                    );

                    if ($stmt_insert->execute()) {
                        echo '<p>' . htmlspecialchars($full_name, ENT_QUOTES) . ' has been added to the GoBrik 3.0 database!</p>';
                        ob_flush();
                        flush();

                        // Part 5: Update Knack database
                        $update_data = ['field_2525' => '1'];
                        $update_url = "https://api.knack.com/v1/objects/$object_id/records/$record_id";
                        $update_ch = curl_init($update_url);
                        curl_setopt($update_ch, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($update_ch, CURLOPT_HTTPHEADER, [
                            "X-Knack-Application-ID: $app_id",
                            "X-Knack-REST-API-Key: $api_key",
                            "Content-Type: application/json"
                        ]);
                        curl_setopt($update_ch, CURLOPT_CUSTOMREQUEST, "PUT");
                        curl_setopt($update_ch, CURLOPT_POSTFIELDS, json_encode($update_data));

                        $update_response = curl_exec($update_ch);
                        if ($update_response === false) {
                            echo '<p>Error updating Knack database: ' . curl_error($update_ch) . '</p>';
                        } else {
                            echo '<p>' . htmlspecialchars($full_name, ENT_QUOTES) . "'s account has been updated on the knack GoBrik 2.0 database as migrated!</p>";
                        }
                        ob_flush();
                        flush();
                        curl_close($update_ch);
                    } else {
                        echo '<p>Error inserting data: ' . $stmt_insert->error . '</p>';
                        ob_flush();
                        flush();
                    }
                    $stmt_insert->close();
                } else {
                    echo '<p>Error preparing statement: ' . $conn->error . '</p>';
                    ob_flush();
                    flush();
                }
            }

            echo '<script>
                setTimeout(function() {
                    window.location.href = "process_ecobrickers.php";
                }, 5000); // Redirect after 5 seconds
            </script>';
        } else {
            echo '<p>No ecobrickers found that match the criteria.</p>';
            ob_flush();
            flush();
        }
    }
    curl_close($ch);
    ?>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('knack-migration-form').submit();
    });
</script>


<script>
    (function() {
        let lastActivity = Date.now();

        // Function to update last activity time
        function updateLastActivity() {
            lastActivity = Date.now();
        }

        // Check for inactivity every second
        setInterval(function() {
            if (Date.now() - lastActivity > 8000) {
                window.location.href = "process_ecobrickers.php";
            }
        }, 1000);

        // Listen for various user activity events
        ['mousemove', 'keydown', 'scroll', 'click', 'touchstart'].forEach(event => {
            window.addEventListener(event, updateLastActivity, false);
        });

        // Catch script errors
        window.addEventListener('error', function() {
            setTimeout(function() {
                window.location.href = "process_ecobrickers.php";
            }, 10000);
        });
    })();
</script>

</body>
</html>
