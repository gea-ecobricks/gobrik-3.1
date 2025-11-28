<?php
// ghoststats_conn_env.php
// Read-only connection to Ghost stats DB (earthen_prod)

// Pull from environment
$ghoststats_host = getenv('GHOST_STATS_DB_HOST') ?: 'localhost';
$ghoststats_db   = getenv('GHOST_STATS_DB_NAME') ?: 'earthen_prod';
$ghoststats_user = getenv('GHOST_STATS_DB_USER') ?: 'ghost_stats';
$ghoststats_pass = getenv('GHOST_STATS_DB_PASS') ?: '';

// Make the connection
$ghoststats_conn = @new mysqli(
    $ghoststats_host,
    $ghoststats_user,
    $ghoststats_pass,
    $ghoststats_db
);

if ($ghoststats_conn->connect_error) {
    error_log('[GHOST STATS] DB connection failed: ' . $ghoststats_conn->connect_error);
    // You can throw/exit here if you want this to be fatal:
    // throw new Exception('Ghost stats DB connection failed');
}

$ghoststats_conn->set_charset('utf8mb4');
