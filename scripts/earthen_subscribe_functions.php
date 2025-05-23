<?php
/**
 * Encodes data to a URL-safe Base64 format.
 * 
 * @param string $data Data to be encoded.
 * @return string URL-safe base64 encoded string.
 */
function base64UrlEncode($data) {
    return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($data));
}

/**
 * Creates a JWT token for Ghost Admin API authentication.
 * @return string JWT token.
 * @throws Exception if the API key is not found or invalid.
 */

function createGhostJWT() {
    // Retrieve the API key from the environment variable
    $apiKey = getenv('EARTHEN_KEY');

    if (!$apiKey) {
        displayError('API key not set.');
        exit();
    }

    // Split the API Key into ID and Secret for JWT generation
    list($id, $secret) = explode(':', $apiKey);

    // Prepare the header and payload for the JWT
    $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256', 'kid' => $id]);
    $now = time();
    $payload = json_encode([
        'iat' => $now,
        'exp' => $now + 300, // Token valid for 5 minutes
        'aud' => '/v4/admin/' // Correct Audience value
    ]);

    // Encode Header and Payload
    $base64UrlHeader = base64UrlEncode($header);
    $base64UrlPayload = base64UrlEncode($payload);

    // Create the Signature
    $signature = hash_hmac('sha256', $base64UrlHeader . '.' . $base64UrlPayload, hex2bin($secret), true);
    $base64UrlSignature = base64UrlEncode($signature);

    // Return the complete JWT token
    return $base64UrlHeader . '.' . $base64UrlPayload . '.' . $base64UrlSignature;
}

/**
 * Displays an error message within the designated error div on the page.
 *
 * @param string $error_type The type of error to display.
 */
function displayError($error_type) {
    echo "<script>
        document.getElementById('earthen-server-error').style.display = 'block';
        document.getElementById('earthen-server-error').innerText = 'An error has occurred connecting to the Earthen Newsletter server: $error_type';
    </script>";
}


/**
 * Fetches active newsletters from the Ghost API and displays them as subscription options.
 * Checks if the user is subscribed to any of the newsletters and marks the corresponding checkboxes.
 * If the user is not subscribed to any, the "Earthen" newsletter will be preselected.
 */

function grabActiveEarthenSubs() {
    global $subscribed_newsletters; // Access the global variable to compare with user subscriptions
    $is_user_subscribed = !empty($subscribed_newsletters); // Determine if the user is subscribed to any newsletters

    try {
        // Define the API URL for fetching newsletters
        $ghost_api_url = "https://earthen.io/ghost/api/admin/newsletters/";
        $jwt = createGhostJWT();

        // Set up the cURL request to the Ghost Admin API
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $ghost_api_url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Authorization: Ghost ' . $jwt,
            'Content-Type: application/json'
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPGET, true); // Use GET to fetch data

        // Execute the cURL session
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            displayError('Curl error: ' . curl_error($ch));
            exit();
        }

        if ($http_code >= 200 && $http_code < 300) {
            // Successful response, parse the JSON data
            $response_data = json_decode($response, true);

            if ($response_data && isset($response_data['newsletters']) && is_array($response_data['newsletters'])) {
                // Generate HTML for each active newsletter
                foreach ($response_data['newsletters'] as $newsletter) {
                    if ($newsletter['status'] === 'active') {
                        // Extract data
                        $sub_id = htmlspecialchars($newsletter['id']);
                        $sub_slug = htmlspecialchars($newsletter['slug']);
                        $sub_name = htmlspecialchars($newsletter['name']);
                        $sub_description = htmlspecialchars($newsletter['description']);
                        $sub_sender_name = htmlspecialchars($newsletter['sender_name']);
                        $sub_language = "English"; // Adjust if data in the JSON specifies a different language
                        $sub_frequency = "1-3 posts a month"; // Hard-coded frequency for demonstration

                      // Determine if this newsletter should be preselected
$is_checked = in_array($sub_name, $subscribed_newsletters) ||
              (!$is_user_subscribed && in_array($sub_name, ['Earthen', 'GoBrik News'])) ?
              'checked' : '';

                        // Apply the full selection styles if the box is checked
                        $selected_styles = $is_checked ? 'style="border: 2px solid green; background-color: var(--darker);"' : '';

                        // Output the subscription box HTML
                        echo "
                            <div id=\"{$sub_slug}\" class=\"sub-box\" data-color=\"green\" {$selected_styles}>
                                <input type=\"checkbox\" class=\"sub-checkbox\" id=\"checkbox-{$sub_slug}\" name=\"subscriptions[]\" value=\"{$sub_id}\" {$is_checked}>
                                <label for=\"checkbox-{$sub_slug}\" class=\"checkbox-label\"></label>
                                <div class=\"sub-image\"></div>
                                <div class=\"sub-content\">
                                    <div class=\"sub-header\">
                                        <div class=\"sub-icon\"></div>
                                        <div class=\"sub-header-text\">
                                            <div class=\"sub-name\">{$sub_name}</div>
                                            <div class=\"sub-sender-name\">by {$sub_sender_name}</div>
                                        </div>
                                    </div>
                                    <div class=\"sub-description\">{$sub_description}</div>
                                    <div class=\"sub-lang\">{$sub_language} | {$sub_frequency}</div>
                                </div>
                            </div>
                        ";
                    }
                }
            } else {
                echo "<script>console.log('No active newsletters found.');</script>";
            }
        } else {
            displayError('HTTP status ' . $http_code);
        }

        // Close the cURL session
        curl_close($ch);
    } catch (Exception $e) {
        displayError('Exception: ' . $e->getMessage());
    }
}







/**
 * Checks the subscription status of an email with the Ghost API and logs the response to the console.
 *
 * @param string $email The email address to check.
 * @return string JSON response from the Ghost API.
 */
function checkEarthenEmailStatus($email) {
    global $subscribed_newsletters, $ghost_member_id; // Access global variables to store newsletter names and member ID

    try {
        // Prepare and encode the email address for use in the API URL
        $email_encoded = urlencode($email);
        $ghost_api_url = "https://earthen.io/ghost/api/admin/members/?filter=email:$email_encoded";
        $jwt = createGhostJWT();

        // Set up the cURL request to the Ghost Admin API
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $ghost_api_url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Authorization: Ghost ' . $jwt,
            'Content-Type: application/json'
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPGET, true); // Use GET to fetch data

        // Execute the cURL session
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        // Handle cURL errors
        if (curl_errno($ch)) {
            $error_message = 'Curl error: ' . curl_error($ch);
            curl_close($ch);
            logToConsole(['status' => 'error', 'message' => $error_message]); // Log to console
            return json_encode(['status' => 'error', 'message' => $error_message]);
        }

        // Close the cURL session
        curl_close($ch);

        // Check the HTTP status code
        if ($http_code >= 200 && $http_code < 300) {
            // Successful response, parse the JSON data
            $response_data = json_decode($response, true);

            // Check if members are found
            $registered = 0;
            $newsletters = [];

            if ($response_data && isset($response_data['members']) && is_array($response_data['members']) && count($response_data['members']) > 0) {
                $registered = 1;

                // Save the member ID to the global variable
                $ghost_member_id = $response_data['members'][0]['id'] ?? null;

                // Extract newsletter names and store them in the global variable
                if (isset($response_data['members'][0]['newsletters'])) {
                    foreach ($response_data['members'][0]['newsletters'] as $newsletter) {
                        $newsletters[] = $newsletter['name'];
                    }
                    $subscribed_newsletters = $newsletters; // Store the names in the global variable
                }

                $jsonResponse = json_encode(['status' => 'success', 'registered' => $registered, 'message' => 'User is subscribed.', 'newsletters' => $newsletters]);
                logToConsole($jsonResponse); // Log the JSON response to the console
                return $jsonResponse;
            } else {
                $jsonResponse = json_encode(['status' => 'success', 'registered' => $registered, 'message' => 'User is not subscribed.']);
                logToConsole($jsonResponse); // Log the JSON response to the console
                return $jsonResponse;
            }
        } else {
            // Handle non-2xx HTTP codes
            $errorResponse = json_encode(['status' => 'error', 'message' => 'HTTP status ' . $http_code]);
            logToConsole($errorResponse); // Log the JSON response to the console
            return $errorResponse;
        }
    } catch (Exception $e) {
        // Handle exceptions
        $exceptionResponse = json_encode(['status' => 'error', 'message' => 'Exception: ' . $e->getMessage()]);
        logToConsole($exceptionResponse); // Log the JSON response to the console
        return $exceptionResponse;
    }
}


/**
 * Logs JSON data to the browser's console.
 *
 * @param mixed $data The data to log, typically an associative array or JSON string.
 */
function logToConsole($data) {
    $jsonData = is_array($data) ? json_encode($data) : $data;
    echo "<script>console.log('JSON Response:', " . $jsonData . ");</script>";
}

/**
 * Update subscription for an existing user using PUT with all selected newsletters at once.
 */
function updateSubscribeUser($member_id, $newsletter_ids) {
    try {
        // Correct URL format using the member ID
        $ghost_api_url = "https://earthen.io/ghost/api/admin/members/" . $member_id . '/';
        $jwt = createGhostJWT();

        // Prepare updated subscription data with all selected newsletters
        $newsletters = array_map(function($id) {
            return ['id' => $id, 'subscribed' => true];
        }, $newsletter_ids);

        $data = [
            'members' => [
                [
                    'newsletters' => $newsletters
                ]
            ]
        ];

        $jsonData = json_encode($data);
        error_log("Attempting to update subscription for user with data: " . $jsonData);
        error_log("Request URL: " . $ghost_api_url);

        // Setup cURL for the PUT request to update subscriptions
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $ghost_api_url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Authorization: Ghost ' . $jwt,
            'Content-Type: application/json'
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT'); // Use PUT to update
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);

        // Execute the cURL request
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        // Log the response and status code for debugging
        error_log('Update subscription API response: ' . $response);
        error_log('HTTP status code: ' . $http_code);
        error_log('Full URL used: ' . $ghost_api_url);

        // Handle potential errors
        if (curl_errno($ch) || $http_code >= 400) {
            error_log('Error updating subscription: ' . curl_error($ch) . ' - Response: ' . $response);
        }

        // Close cURL session
        curl_close($ch);
    } catch (Exception $e) {
        error_log('Exception occurred while updating subscription: ' . $e->getMessage());
    }
}






/**
 * Update to unsubscribe a user from a specific newsletter using PATCH.
 */
function updateUnsubscribeUser($member_id, $newsletter_id) {
    try {
        // Construct the API URL with the member ID
        $ghost_api_url = "https://earthen.io/ghost/api/v4/admin/members/" . $member_id . '/';
        $jwt = createGhostJWT();

        // Prepare data to unsubscribe from the newsletter
        $data = [
            'newsletters' => [['id' => $newsletter_id, 'subscribed' => false]]
        ]; // Verify this structure matches the API's expected format for unsubscribing

        $jsonData = json_encode($data);
        error_log("Attempting to update unsubscribe for user: " . $jsonData); // Log the data for debugging

        // Set up the cURL request
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $ghost_api_url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Authorization: Ghost ' . $jwt,
            'Content-Type: application/json'
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH'); // Use PATCH to update
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData); // Send the JSON payload

        // Execute the request
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        // Log the response and HTTP status code
        error_log('Unsubscribe API response: ' . $response);
        error_log('HTTP status code: ' . $http_code);

        // Check for cURL errors or non-success HTTP status codes
        if (curl_errno($ch) || $http_code >= 400) {
            error_log('Error unsubscribing from newsletter: ' . curl_error($ch) . ' - Response: ' . $response);
        }

        // Close the cURL session
        curl_close($ch);
    } catch (Exception $e) {
        error_log('Exception occurred while unsubscribing: ' . $e->getMessage());
    }
}


/**
 * Subscribe the user to specific newsletters by creating a new member with selected newsletters.
 */
function subscribeUserToNewsletter($email, $newsletter_ids) {
    try {
        $ghost_api_url = "https://earthen.io/ghost/api/v4/admin/members/";
        $jwt = createGhostJWTsubscribe();

        // Prepare subscription data with all selected newsletters
        $newsletters = array_map(function($id) {
            return ['id' => $id];
        }, $newsletter_ids);

        $data = [
            'members' => [
                [
                    'email' => $email,
                    'newsletters' => $newsletters
                ]
            ]
        ];

        // Convert data to JSON and log it for debugging
        $jsonData = json_encode($data);
        error_log("Attempting to subscribe user with data: " . $jsonData);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $ghost_api_url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Authorization: Ghost ' . $jwt,
            'Content-Type: application/json'
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        // Log the response and status code for debugging
        error_log('Subscription API response: ' . $response);
        error_log('HTTP status code: ' . $http_code);

        if (curl_errno($ch) || $http_code >= 400) {
            error_log('Error subscribing to newsletter: ' . curl_error($ch) . ' - Response: ' . $response);
        }

        curl_close($ch);
    } catch (Exception $e) {
        error_log('Exception occurred while subscribing to newsletter: ' . $e->getMessage());
    }
}


function createGhostJWTsubscribe() {
    // Retrieve the API key from the environment variable
    $apiKey = getenv('EARTHEN_KEY');

    if (!$apiKey) {
        displayError('API key not set.');
        exit();
    }

    // Split the API Key into ID and Secret for JWT generation
    list($id, $secret) = explode(':', $apiKey);

    // Prepare the header and payload for the JWT
    $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256', 'kid' => $id]);
    $now = time();
    $payload = json_encode([
        'iat' => $now,
        'exp' => $now + 300, // Token valid for 5 minutes
        'aud' => '/v4/admin/' // Correct Audience value
    ]);

    // Encode Header and Payload
    $base64UrlHeader = base64UrlEncode($header);
    $base64UrlPayload = base64UrlEncode($payload);

    // Create the Signature
    $signature = hash_hmac('sha256', $base64UrlHeader . '.' . $base64UrlPayload, hex2bin($secret), true);
    $base64UrlSignature = base64UrlEncode($signature);

    // Return the complete JWT token
    return $base64UrlHeader . '.' . $base64UrlPayload . '.' . $base64UrlSignature;
}




// Prepare the function to check the subscription status
function checkEarthenEmailStatus2($email) {
    // Prepare and encode the email address for use in the API URL
    $email_encoded = urlencode($email);
    $ghost_api_url = "https://earthen.io/ghost/api/v3/admin/members/?filter=email:$email_encoded";

    // Split API Key into ID and Secret for JWT generation
    $apiKey = '66db68b5cff59f045598dbc3:5c82d570631831f277b1a9b4e5840703e73a68e948812b2277a0bc11c12c973f';
    list($id, $secret) = explode(':', $apiKey);

    // Prepare the header and payload for the JWT
    $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256', 'kid' => $id]);
    $now = time();
    $payload = json_encode([
        'iat' => $now,
        'exp' => $now + 300, // Token valid for 5 minutes
        'aud' => '/v3/admin/' // Corrected audience value to match the expected pattern
    ]);

    // Encode Header and Payload
    $base64UrlHeader = base64UrlEncode($header);
    $base64UrlPayload = base64UrlEncode($payload);

    // Create the Signature
    $signature = hash_hmac('sha256', $base64UrlHeader . '.' . $base64UrlPayload, hex2bin($secret), true);
    $base64UrlSignature = base64UrlEncode($signature);

    // Create the JWT token
    $jwt = $base64UrlHeader . '.' . $base64UrlPayload . '.' . $base64UrlSignature;

    // Set up the cURL request to the Ghost Admin API
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $ghost_api_url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Authorization: Ghost ' . $jwt,
        'Content-Type: application/json'
    ));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPGET, true); // Use GET to fetch data

    // Execute the cURL session
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if (curl_errno($ch)) {
        error_log('Curl error: ' . curl_error($ch));
        echo json_encode(['status' => 'error', 'message' => 'Curl error: ' . curl_error($ch)]);
        exit();
    }

    if ($http_code >= 200 && $http_code < 300) {
        // Successful response, parse the JSON data
        $response_data = json_decode($response, true);

        // Check if members are found
        $registered = 0; // Default to not registered
        $newsletters = []; // Array to hold newsletter names

        if ($response_data && isset($response_data['members']) && is_array($response_data['members']) && count($response_data['members']) > 0) {
            $registered = 1; // Member with the given email exists

            // Extract newsletter names
            if (isset($response_data['members'][0]['newsletters'])) {
                foreach ($response_data['members'][0]['newsletters'] as $newsletter) {
                    $newsletters[] = $newsletter['name'];
                }
            }

            echo json_encode(['status' => 'success', 'registered' => $registered, 'message' => 'User is subscribed.', 'newsletters' => $newsletters]);
        } else {
            echo json_encode(['status' => 'success', 'registered' => $registered, 'message' => 'User is not subscribed.']);
        }
    } else {
        // Handle error
        error_log('HTTP status ' . $http_code . ': ' . $response);
        echo json_encode(['status' => 'error', 'message' => 'API call to Earthen.io failed with HTTP code: ' . $http_code]);
    }

    // Close the cURL session
    curl_close($ch);
}






/**
 * Retrieves the member ID based on the provided email address.
 *
 * @param string $email The email address to search for.
 * @return string|null Returns the member ID if found, otherwise null.
 */
function getMemberIdByEmail($email) {
    $email_encoded = urlencode($email);
    $ghost_api_url = "https://earthen.io/ghost/api/v4/admin/members/?filter=email:$email_encoded";
    $jwt = createGhostJWT();

    // Set up the cURL request
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $ghost_api_url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Authorization: Ghost ' . $jwt,
        'Content-Type: application/json'
    ));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code >= 200 && $http_code < 300) {
        $response_data = json_decode($response, true);

        // Check if member is found and extract the member ID
        if (isset($response_data['members'][0]['id'])) {
            return $response_data['members'][0]['id'];
        }
    }

    // Log an error if member not found or if there was an error
    error_log("Failed to retrieve member ID for email $email with HTTP code $http_code and response: $response");
    return null;
}


function earthenUnsubscribe($email) {
    error_log("Unsubscribe process initiated for email: $email");
    $member_id = getMemberIdByEmail($email);
    error_log("Member ID retrieved: $member_id");

    if (!$member_id) {
        error_log("No member found for email: $email");
        return false; // Return false to indicate failure
    }

    // Proceed with unsubscribe using the member ID
    $ghost_api_url = "https://earthen.io/ghost/api/v4/admin/members/$member_id/";
    $jwt = createGhostJWT();

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $ghost_api_url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Authorization: Ghost ' . $jwt,
        'Content-Type: application/json'
    ));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code >= 200 && $http_code < 300) {
        error_log("Successfully unsubscribed $email.");
        return true; // Return true for success
    } else {
        error_log("Failed to unsubscribe $email. HTTP status: $http_code, Response: $response");
        return false; // Return false for failure
    }
}




// Handle incoming requests
if (isset($_POST['email'])) {
    $email = $_POST['email'];

    if (isset($_POST['unsubscribe']) && $_POST['unsubscribe'] === 'true') {
        earthenUnsubscribe($email);
    } else {
        checkEarthenEmailStatus2($email);
    }
} else {

}
?>



