<?php
session_start();

// Optional: log logout activity
file_put_contents('debug.log', "Logging out user with session ID: " . session_id() . "\n", FILE_APPEND);

// Blow away all session data including the JWT
$_SESSION = [];
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}
session_destroy();

// Optional: clear additional auth-related cookies
setcookie('buwana_id', '', time() - 3600, '/');

// Redirect target
$redirect = isset($_GET['redirect']) ? filter_var($_GET['redirect'], FILTER_SANITIZE_SPECIAL_CHARS) : '';
$redirect_url = 'login.php?status=logout';
if (!empty($redirect)) {
    $redirect_url .= '&redirect=' . urlencode($redirect);
}

// Redirect
header('Location: ' . $redirect_url);
exit();
?>
