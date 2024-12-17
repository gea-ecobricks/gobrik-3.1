<?php

$allowed_origins = [
    'https://cycles.earthen.io',
    'https://ecobricks.org',
    'http://localhost:8000' // Added for local testing
];

// CORS headers
if (isset($_SERVER['HTTP_ORIGIN']) && in_array($_SERVER['HTTP_ORIGIN'], $allowed_origins)) {
    header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
    header('Access-Control-Allow-Methods: POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
    header('Access-Control-Allow-Credentials: true');
} else {
    header('Access-Control-Allow-Origin: *'); // Debugging only
    error_log('CORS error: Missing or invalid HTTP_ORIGIN');
}

// Preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Origin: ' . ($_SERVER['HTTP_ORIGIN'] ?? '*'));
    header('Access-Control-Allow-Methods: POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
    header('Access-Control-Allow-Credentials: true');
    exit(0);
}

// Dependencies
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require '../vendor/autoload.php';
require_once("../gobrikconn_env.php");
require_once("../buwanaconn_env.php");

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

// Initialize variables
$response = [];
$credential_key = $_POST['credential_key'] ?? '';
$first_name = '';
$continent_code = '';
$location_full = '';

if (empty($credential_key)) {
    $response['status'] = 'empty_fields';
    echo json_encode($response);
    exit();
}

// Function to generate a random 2FA code
function generateCode() {
    return strtoupper(substr(bin2hex(random_bytes(3)), 0, 5));
}

// Send the verification code
function sendVerificationCode($email_addr, $login_code, $buwana_id, $first_name) {
    $client = new Client(['base_uri' => 'https://api.eu.mailgun.net/v3/']);
    $mailgunApiKey = getenv('MAILGUN_API_KEY');
    $mailgunDomain = 'mail.gobrik.com';

    $loginUrl = "https://cycles.earthen.io/index.html?id=" . urlencode($buwana_id) . "&code=" . urlencode($login_code);

    $subject = 'GoBrik Login Code';
    $html_body = "Hello " . htmlspecialchars($first_name) . ",<br><br>Your code to log in is: <b>$login_code</b><br><br>" .
                 "Click this link to log in directly: <a href=\"$loginUrl\">$loginUrl</a><br><br>GoBrik team";
    $text_body = "Hello $first_name,\n\nYour login code is: $login_code\n\nUse this link to log in: $loginUrl\n\nGoBrik team";

    try {
        $response = $client->post("{$mailgunDomain}/messages", [
            'auth' => ['api', $mailgunApiKey],
            'form_params' => [
                'from' => 'GoBrik Team <no-reply@mail.gobrik.com>',
                'to' => $email_addr,
                'subject' => $subject,
                'html' => $html_body,
                'text' => $text_body,
            ]
        ]);
        return $response->getStatusCode() === 200;
    } catch (RequestException $e) {
        error_log("Mailgun API Exception: " . $e->getMessage());
        return false;
    }
}

// PART 3: Verify credential and generate code
$sql_credential = "SELECT buwana_id, 2fa_issued_count FROM credentials_tb WHERE credential_key = ?";
$stmt_credential = $buwana_conn->prepare($sql_credential);
if ($stmt_credential) {
    $stmt_credential->bind_param('s', $credential_key);
    $stmt_credential->execute();
    $stmt_credential->store_result();

    if ($stmt_credential->num_rows === 1) {
        $stmt_credential->bind_result($buwana_id, $issued_count);
        $stmt_credential->fetch();
        $stmt_credential->close();

        // Fetch user details: first_name, continent_code, location_full
        $sql_user = "SELECT first_name, continent_code, location_full FROM users_tb WHERE buwana_id = ?";
        $stmt_user = $buwana_conn->prepare($sql_user);
        if ($stmt_user) {
            $stmt_user->bind_param('i', $buwana_id);
            $stmt_user->execute();
            $stmt_user->store_result();
            $stmt_user->bind_result($first_name, $continent_code, $location_full);
            $stmt_user->fetch();
            $stmt_user->close();
        }


        // Generate a new 2FA code
        $temp_code = generateCode();
        $issued_datetime = date('Y-m-d H:i:s');
        $new_issued_count = $issued_count + 1;

        $sql_update = "UPDATE credentials_tb SET 2fa_temp_code = ?, 2fa_code_issued = ?, 2fa_issued_count = ? WHERE buwana_id = ?";
        $stmt_update = $buwana_conn->prepare($sql_update);
        if ($stmt_update) {
            $stmt_update->bind_param('ssii', $temp_code, $issued_datetime, $new_issued_count, $buwana_id);
            $stmt_update->execute();
            $stmt_update->close();

            // Send the code
            if (sendVerificationCode($credential_key, $temp_code, $buwana_id, $first_name)) {
                $response['status'] = 'credfound';
                $response['buwana_id'] = $buwana_id;
                $response['first_name'] = $first_name;
                $response['continent_code'] = $continent_code;
                $response['location_full'] = $location_full;
                echo json_encode($response);
                exit();
            } else {
                $response['status'] = 'email_error';
                echo json_encode($response);
                exit();
            }
        }
    } else {
        $response['status'] = 'crednotfound';
        echo json_encode($response);
        exit();
    }
}

$response['status'] = 'error';
$response['message'] = 'Database error.';
echo json_encode($response);
exit();

?>
