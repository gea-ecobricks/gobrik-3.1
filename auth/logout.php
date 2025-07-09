<?php
session_start();

// Destroy all session data
$_SESSION = [];
session_destroy();

// Optional: redirect back to GoBrik after Buwana logout
$return_url = urlencode('https://gobrik.com/goodbye.php'); // customize as needed

// Buwana logout endpoint
$buwana_logout = 'https://buwana.ecobricks.org/logout?post_logout_redirect_uri=' . $return_url;

// Redirect the user
header('Location: ' . $buwana_logout);
exit;
