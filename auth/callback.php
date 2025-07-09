<?php
session_start();

// 1. Verify `state` matches
if (!isset($_GET['state']) || $_GET['state'] !== $_SESSION['oidc_state']) {
    exit('Invalid state. Possible CSRF attempt.');
}

// 2. Get the authorization code
$code = $_GET['code'] ?? null;
if (!$code) {
    exit('No authorization code received.');
}

// 3. Prepare token request
$token_url = 'https://buwana.ecobricks.org/token';
$client_id = 'gbrk_f2c61a85a4cd4b8b89a7';
$client_secret = 'your_gobrik_client_secret_here'; // 🔐 store securely

$params = [
    'grant_type' => 'authorization_code',
    'code' => $code,
    'redirect_uri' => 'https://gobrik.com/auth/callback.php',
    'client_id' => $client_id,
    'client_secret' => $client_secret
];

// 4. Exchange code for tokens
$options = ['http' => [
    'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
    'method'  => 'POST',
    'content' => http_build_query($params)
]];
$response = file_get_contents($token_url, false, stream_context_create($options));
$tokens = json_decode($response, true);

if (!isset($tokens['id_token'])) {
    exit('Failed to receive ID token.');
}

// 5. Validate the ID token (JWT)
require_once 'verify_jwt.php';
$claims = verify_id_token($tokens['id_token'], $client_id);

if (!$claims) {
    exit('Invalid ID token.');
}

// 6. Save user info to session
$_SESSION['buwana_user'] = [
    'sub' => $claims['sub'],
    'email' => $claims['email'] ?? null,
    'name' => $claims['name'] ?? null,
    'jwt' => $tokens['id_token']
];

// 7. Redirect to GoBrik dashboard or landing page
header('Location: /dashboard.php');
exit;
