<?php
// Enforce a 3-hour session lifetime
$sessionLifetime = 3 * 60 * 60;

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => $sessionLifetime,
        'path' => '/',
        'secure' => true,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    session_start();
}

// 1. Ensure user is logged in via Buwana
if (!isset($_SESSION['buwana_user']) || !isset($_SESSION['buwana_user']['jwt'])) {
    // Not logged in — redirect to start SSO login
    header('Location: /auth/redirect.php');
    exit;
}

// 2. Enforce session timeout regardless of JWT expiry
if (!isset($_SESSION['authenticated_at'])) {
    $_SESSION['authenticated_at'] = time();
}

if ((time() - $_SESSION['authenticated_at']) > $sessionLifetime) {
    session_destroy();
    header('Location: /auth/redirect.php');
    exit;
}

// 3. Optionally re-verify JWT using the authentication timestamp to bypass expiry
require_once 'verify_jwt.php';
$claims = verify_id_token(
    $_SESSION['buwana_user']['jwt'],
    'gbrk_f2c61a85a4cd4b8b89a7',
    $_SESSION['authenticated_at']
);

if (!$claims) {
    session_destroy();
    header('Location: /auth/redirect.php');
    exit;
}

// 4. User is authenticated and within the session lifetime — you're good to go
