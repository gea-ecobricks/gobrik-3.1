<?php
/**
 * Provides a MySQLi connection to the Ghost database using the EARTHEN_KEY environment variable.
 *
 * Expected EARTHEN_KEY format: mysql://user:password@host:port/database
 */

$earth_key = getenv('EARTHEN_KEY');

if (!$earth_key) {
    throw new RuntimeException('EARTHEN_KEY is not defined in the environment.');
}

$parsed = parse_url($earth_key);

if ($parsed === false || !isset($parsed['host'], $parsed['user'], $parsed['pass'])) {
    throw new RuntimeException('EARTHEN_KEY is not a valid database URL.');
}

$ghost_db_host = $parsed['host'];
$ghost_db_user = $parsed['user'];
$ghost_db_pass = $parsed['pass'];
$ghost_db_name = ltrim($parsed['path'] ?? '', '/');
$ghost_db_port = $parsed['port'] ?? 3306;

$ghost_conn = new mysqli($ghost_db_host, $ghost_db_user, $ghost_db_pass, $ghost_db_name, $ghost_db_port);

if ($ghost_conn->connect_error) {
    throw new RuntimeException('Connection to Ghost database failed: ' . $ghost_conn->connect_error);
}

$ghost_conn->set_charset('utf8mb4');
