<?php
session_start();

// 1. Ensure user is logged in via Buwana
if (!isset($_SESSION['buwana_user']) || !isset($_SESSION['buwana_user']['jwt'])) {
    // Not logged in — redirect to start SSO login
    header('Location: /auth/redirect.php');
    exit;
}

// 2. Optionally: re-verify JWT on each request (adds extra security)
require_once 'verify_jwt.php';
$claims = verify_id_token($_SESSION['buwana_user']['jwt'], 'gbrk_f2c61a85a4cd4b8b89a7');

if (!$claims) {
    // Token failed validation — force re-login
    session_destroy();
    header('Location: /auth/redirect.php');
    exit;
}

// 3. User is authenticated and token is valid — you're good to go
