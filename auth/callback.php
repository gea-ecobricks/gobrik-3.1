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
$client_secret = getenv('GBRK_CLIENT_SECRET'); // 🔐 Loaded securely

if (!$client_secret) {
    exit('Client secret not configured.');
}

$params = [
    'grant_type' => 'authorization_code',
    'code' => $code,
    'redirect_uri' => 'https://gobrik.com/auth/callback.php',
    'client_id' => $client_id,
    'client_secret' => $client_secret
];

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
    exit("❌ Token Response Error\n\nRaw response:\n$response");
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

// Track when the session was authenticated so we can enforce a 3-hour lifetime
$_SESSION['authenticated_at'] = time();

// Save buwana_id from token claim explicitly
$_SESSION['buwana_id'] = $claims['buwana_id'] ?? null;

// 7. Redirect to the original page if provided; otherwise, go to dashboard
$postLoginRedirect = $_SESSION['post_login_redirect'] ?? null;

if ($postLoginRedirect && strpos($postLoginRedirect, '/') === 0) {
    unset($_SESSION['post_login_redirect']);
    header('Location: ' . $postLoginRedirect);
    exit;
}

header('Location: /en/dashboard.php');
exit;
