<?php
require_once __DIR__ . './../auth/session_start.php';

session_start();

// Determine language dynamically
$lang = $lang ?? 'en'; // fallback if not already set

// Check login state properly
$is_logged_in = isset($_SESSION['buwana_user']) && !empty($_SESSION['buwana_user']['jwt']);

if ($is_logged_in) {
    header("Location: dashboard.php");
    exit;
}

// Rebuild query string to pass to Buwana
$query_string = http_build_query($_GET);
$buwana_url = "https://buwana.ecobricks.org/$lang/login.php?app=gbrk_f2c61a85a4cd4b8b89a7";

if (!empty($query_string)) {
    $buwana_url .= '&' . $query_string;
}

// Redirect to Buwana login
header("Location: $buwana_url");
exit;
