<?php
session_start();

// 1. Verify `state` to prevent CSRF
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
$secrets = require __DIR__ . '/../gbrk_private_env.php';
$client_secret = $secrets['GBRK_CLIENT_SECRET'] ?? null;

if (!$client_secret) {
    exit('Client secret not yet configured it seems.');
}

$params = [
    'grant_type' => 'authorization_code',
    'code' => $code,
    'redirect_uri' => 'https://gobrik.com/auth/callback.php',
    'client_id' => $client_id,
    'client_secret' => $client_secret
];

echo "<pre>";
echo "Client Secret: " . htmlspecialchars($client_secret) . "\n";
echo "Client ID: " . $client_id . "\n";
echo "</pre>";


// 4. Exchange code for tokens using cURL
$ch = curl_init($token_url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/x-www-form-urlencoded'
]);

$response = curl_exec($ch);

if (curl_errno($ch)) {
    exit('cURL error: ' . curl_error($ch));
}
curl_close($ch);

// 5. Decode and validate ID token
$tokens = json_decode($response, true);

if (!$tokens || !isset($tokens['id_token'])) {
    echo "<h2>❌ Token Response Error</h2>";
    echo "<pre>Raw response:\n" . htmlspecialchars($response) . "</pre>";
    exit("Failed to receive ID token.");
}

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

// 7. Redirect to dashboard
header('Location: /en/dash.php');
exit;
