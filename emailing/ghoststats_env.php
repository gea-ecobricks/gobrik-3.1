<?php
// ghoststats_conn_env.php
// Read-only connection to Ghost stats DB (earthen_prod)

// Helper to pull a value from any available environment source
$ghoststats_getenv = static function (string $key, string $default = '') {
    $value = getenv($key);

    if ($value === false || $value === '') {
        $value = function_exists('apache_getenv') ? apache_getenv($key, true) : false;
    }

    if ($value === false || $value === '') {
        $value = $_SERVER[$key] ?? $_ENV[$key] ?? '';
    }

    return $value !== '' ? $value : $default;
};

// Pull from environment
$ghoststats_host = $ghoststats_getenv('GHOST_STATS_DB_HOST', 'localhost');
$ghoststats_db   = $ghoststats_getenv('GHOST_STATS_DB_NAME', 'earthen_prod');
$ghoststats_user = $ghoststats_getenv('GHOST_STATS_DB_USER', 'ghost_stats');
$ghoststats_pass = $ghoststats_getenv('GHOST_STATS_DB_PASS');

if ($ghoststats_pass === '') {
    error_log('[GHOST STATS] Missing GHOST_STATS_DB_PASS environment variable.');
}

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
