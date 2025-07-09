<?php
use Firebase\JWT\JWK;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

require_once __DIR__ . '/../vendor/autoload.php';

function verify_id_token($id_token, $expected_aud) {
    // Decode token header to get kid
    $header = json_decode(base64_decode(explode('.', $id_token)[0]), true);
    $kid = $header['kid'] ?? null;

    if (!$kid) return false;

    // Fetch JWKS
    $jwks_url = "https://buwana.ecobricks.org/.well-known/jwks.php?client_id=$expected_aud";
    $jwks_json = file_get_contents($jwks_url);
    $jwks = json_decode($jwks_json, true);

    if (!isset($jwks['keys'])) return false;

    // Convert to Key set
    $keySet = JWK::parseKeySet($jwks);

    try {
        $decoded = JWT::decode($id_token, $keySet);
        $claims = (array)$decoded;

        // Basic claim validation
        if ($claims['aud'] !== $expected_aud) return false;
        if ($claims['iss'] !== 'https://buwana.ecobricks.org') return false;
        if (isset($_SESSION['oidc_nonce']) && $claims['nonce'] !== $_SESSION['oidc_nonce']) return false;

        return $claims;
    } catch (Exception $e) {
        error_log("JWT validation failed: " . $e->getMessage());
        return false;
    }
}
