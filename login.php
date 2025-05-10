<?php
// Enable error reporting for debugging (optional in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Get full query parameters
$query = $_GET;

// Extract language
$lang = isset($query['lang']) ? $query['lang'] : 'en';

// Remove 'lang' from the query string to avoid duplication
unset($query['lang']);

// Build new query string
$remaining_query = http_build_query($query);

// Construct redirect URL
$redirect_url = '/' . urlencode($lang) . '/login.php';
if (!empty($remaining_query)) {
    $redirect_url .= '?' . $remaining_query;
}

// Redirect user
header("Location: $redirect_url");
exit();
?>
