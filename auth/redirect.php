<?php
session_start();

// Optional security tokens
$state = bin2hex(random_bytes(8)); // Protect against CSRF
$nonce = bin2hex(random_bytes(8)); // Prevent replay attacks

// Save tokens to session
$_SESSION['oidc_state'] = $state;
$_SESSION['oidc_nonce'] = $nonce;

// Grab optional passthrough params
$extras = [];
if (isset($_GET['firstname'])) $extras['firstname'] = $_GET['firstname'];
if (isset($_GET['status'])) $extras['status'] = $_GET['status'];
if (isset($_GET['id'])) $extras['id'] = $_GET['id']; // buwana_id

// Base OpenID Connect params
$params = array_merge([
    'response_type' => 'code',
    'client_id' => 'gbrk_f2c61a85a4cd4b8b89a7',
    'redirect_uri' => 'https://gobrik.com/auth/callback.php',
    'scope' => 'openid email profile',
    'state' => $state,
    'nonce' => $nonce
], $extras);

// Assemble full redirect URL
$auth_url = 'https://buwana.ecobricks.org/authorize?' . http_build_query($params);
header('Location: ' . $auth_url);
exit;
