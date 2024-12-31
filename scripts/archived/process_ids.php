<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>ᐉ Maker ID Update</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #ddd;
        }


    </style>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            function processNextEcobrick() {
                $.ajax({
                    url: 'process_ids.php',
                    method: 'POST',
                    success: function(response) {
                        $('#process-status').html(response);
                        setTimeout(processNextEcobrick, 1000);  // Adjust the timeout as needed
                    },
                    error: function() {
                        $('#process-status').html('<p>Error processing the next ecobrick. Please try again.</p>');
                    }
                });
            }

            processNextEcobrick();
        });
    </script>
</head>
<body>

<h1>Ecobricker Migration</h1>
<p>We're moving ecobricker accounts to the new GoBrik3 database.  First step is to connect all the ecobricks in the brikchain with these accounts.  To do so we're updating all ecobricks with their maker's record ID.</p>


<div id="process-status">
    <?php


        // PART 2: Go to knack database and get ecobrick to extract maker ID from.
        include '../gobrikconn_env.php';

        // Knack API settings
        $api_key = "360aa2b0-af19-11e8-bd38-41d9fc3da0cf";
        $app_id = "5b8c28c2a1152679c209ce0c";

        // Create connection to the database
        $conn = new mysqli($servername, $username, $password, $dbname);

        // Check connection
        if ($conn->connect_error) {
            die("<script>confirm('Connection failed: " . $conn->connect_error . ". Do you want to proceed to the next ecobrick?'); window.location.href = 'process_ecobricks.php';</script>");
        }

        // Prepare filters to get records with the transfer status field field_2526 set to "No" and field_534 containing "Authenticated"
        $filters = [
            'match' => 'and',
            'rules' => [
                [
                    'field' => 'field_2526',
                    'operator' => 'is not',
                    'value' => 'Yes'
                ],
                [
                    'field' => 'field_534',
                    'operator' => 'contains',
                    'value' => 'Authenticated'
                ]
            ]
        ];

        // Prepare the API request to retrieve ecobrick record...
        $url = "https://api.knack.com/v1/objects/object_2/records?filters=" . urlencode(json_encode($filters)) . "&sort_field=field_73&sort_order=desc&rows_per_page=1";

        // Initialize cURL session
        $ch = curl_init($url);

        // Set cURL options
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "X-Knack-Application-Id: $app_id",
            "X-Knack-REST-API-Key: $api_key"
        ]);

        // Execute cURL request
        $response = curl_exec($ch);

        // Check for cURL errors
        if ($response === false) {
            $error = curl_error($ch);
            echo "<script>confirm('Error fetching data from Knack API: " . addslashes($error) . ". Do you want to proceed to the next ecobrick?'); window.location.href = 'process_ecobricks.php';</script>";
            curl_close($ch);
            exit;
        }
        echo "<h3>Starting to retrieve ecobrick data</h3>";

        // Close cURL session
        curl_close($ch);

        // Add console logging to confirm API access and response
        echo "<script>console.log('Knack API Request URL: " . addslashes($url) . "');</script>";
        echo "<script>console.log('Knack API Response: " . addslashes($response) . "');</script>";

        $data = json_decode($response, true);

        $record_found = false;
        $record_details = "";

        // Retrieval section:
        if (isset($data['records']) && count($data['records']) > 0) {
            $record = $data['records'][0];
            $ecobrick_unique_id = $record['field_73'];
            $knack_record_id = $record['id'];  // Assuming the record ID is stored in 'id'
            $record_found = true;
            $record_details = $record;

            // Extract the maker_record_id from field_335
            $maker_record_id = "";
            if (isset($record['field_335_raw'][0]['id'])) {
                $maker_record_id = $record['field_335_raw'][0]['id'];
            }

            // Displaying the serial number and message
            echo "<h3>Now processing ecobrick $ecobrick_unique_id ...</h3>";
            echo "<p>Maker record ID was retrieved! $maker_record_id</p>";

            // Update field_2526 to 'Yes'
            $update_data = [
                'field_2526' => 'Yes'
            ];

            $update_url = "https://api.knack.com/v1/objects/object_2/records/$knack_record_id";

            $ch_update = curl_init($update_url);
            curl_setopt($ch_update, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch_update, CURLOPT_CUSTOMREQUEST, 'PUT');
            curl_setopt($ch_update, CURLOPT_HTTPHEADER, [
                "X-Knack-Application-ID: $app_id",
                "X-Knack-REST-API-Key: $api_key",
                "Content-Type: application/json"
            ]);
            curl_setopt($ch_update, CURLOPT_POSTFIELDS, json_encode($update_data));

            $update_response = curl_exec($ch_update);

            // Check for cURL errors during the update
            if ($update_response === false) {
                $error = curl_error($ch_update);
                echo "<p>Error updating Knack record: " . addslashes($error) . "</p>";
            } else {
                echo "<p>Successfully updated Knack record with field_2526 set to 'Yes'.</p>";
            }

            curl_close($ch_update);

        } else {
            echo "<p>No ecobrick records found with the provided criteria.</p>";
        }

        // PART 3 of the code
        // Connect to the MySQL database and update the ecobrick record
        echo "<p>Contacting Brikchain server...</p>";

        $servername = "localhost";
        $username = "ecobricks_brikchain_viewer";
        $password = "desperate-like-the-Dawn";
        $dbname = "ecobricks_gobrik_msql_db";

        // Create connection to the database
        $conn2 = new mysqli($servername, $username, $password, $dbname);

        // Check connection
        if ($conn2->connect_error) {
            die("<script>confirm('Connection failed: " . $conn2->connect_error . ". Do you want to proceed to the next ecobrick?'); window.location.href = 'process_ecobricks.php';</script>");
        }
        echo "<p>Connected to Brikchain database...</p>";

        // Update the ecobrick record
        $sql_update_ecobrick = "UPDATE tb_ecobricks SET maker_id = ? WHERE serial_no = ?";

        $stmt_update_ecobrick = $conn2->prepare($sql_update_ecobrick);
        if ($stmt_update_ecobrick) {
            $stmt_update_ecobrick->bind_param("ss", $maker_record_id, $ecobrick_unique_id);

            if ($stmt_update_ecobrick->execute()) {
                echo "<p>Successfully updated ecobrick with serial number $ecobrick_unique_id. Maker ID set to $maker_record_id.</p>";
            } else {
                echo "<p>Error updating ecobrick: " . $stmt_update_ecobrick->error . "</p>";
            }
            $stmt_update_ecobrick->close();
        } else {
            echo "<p>Error preparing statement: " . $conn2->error . "</p>";
        }

        $conn2->close();

    ?>
</div>

</body>
</html>
