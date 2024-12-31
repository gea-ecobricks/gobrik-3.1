<?php
include '../gobrikconn_env.php';
$conn->set_charset("utf8mb4");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
                'field' => 'field_141',
                'operator' => 'is not',
                'value' => '0'
            ]
        ]
    ];

    $url = "https://api.knack.com/v1/objects/$object_id/records?filters=" . urlencode(json_encode($filters)) . "&sort_field=field_261&sort_order=desc";

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "X-Knack-Application-ID: $app_id",
        "X-Knack-REST-API-Key: $api_key",
        "Content-Type: application/json"
    ]);

    $response = curl_exec($ch);

    if ($response === false) {
        echo json_encode(['error' => 'Error retrieving data: ' . curl_error($ch)]);
        curl_close($ch);
        exit;
    }

    curl_close($ch);

    $json_response = json_decode($response, true);
    if (!empty($json_response['records'])) {
        $record = $json_response['records'][0];
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

        $buwana_activated = 0;
        $gobrik_migrated = 1;
        $account_notes = 'migrated from knack gobrik on July 29th, 2024';
        $gobrik_migrated_dt = date('Y-m-d H:i:s');

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
                    echo json_encode(['error' => 'Error updating Knack database: ' . curl_error($update_ch)]);
                } else {
                    echo json_encode(['success' => htmlspecialchars($full_name, ENT_QUOTES) . "'s account has been updated on the knack GoBrik 2.0 database as migrated!"]);
                }
                curl_close($update_ch);
            } else {
                echo json_encode(['error' => 'Error inserting data: ' . $stmt_insert->error]);
            }
            $stmt_insert->close();
        } else {
            echo json_encode(['error' => 'Error preparing statement: ' . $conn->error]);
        }
    } else {
        echo json_encode(['error' => 'No ecobrickers found that match the criteria.']);
    }
    $conn->close();
}
?>
