<?php
use Firebase\JWT\JWK;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

require_once __DIR__ . '/../vendor/autoload.php';

function verify_id_token($id_token, $expected_aud, $timestampOverride = null) {
    // Decode token header to get 'kid'
    $header = json_decode(base64_decode(explode('.', $id_token)[0]), true);
    $kid = $header['kid'] ?? null;

    if (!$kid) return false;

    // Fetch JWKS via cURL
    $jwks_url = "https://buwana.ecobricks.org/.well-known/jwks.php?client_id=$expected_aud";

    $ch = curl_init($jwks_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json']);
    $jwks_json = curl_exec($ch);

    if (curl_errno($ch)) {
        error_log("cURL error fetching JWKS: " . curl_error($ch));
        curl_close($ch);
        return false;
    }

    curl_close($ch);
    $jwks = json_decode($jwks_json, true);
    if (!isset($jwks['keys'])) return false;

    // Convert to key set
    $keySet = JWK::parseKeySet($jwks);

    // Optionally override the timestamp used for validating time-based claims
    $previousTimestamp = JWT::$timestamp;
    if ($timestampOverride !== null) {
        JWT::$timestamp = $timestampOverride;
    }

    try {
        $decoded = JWT::decode($id_token, $keySet);
        $claims = (array)$decoded;

        // Basic validation
        if ($claims['aud'] !== $expected_aud) return false;
        if ($claims['iss'] !== 'https://buwana.ecobricks.org') return false;
        if (isset($_SESSION['oidc_nonce']) && $claims['nonce'] !== $_SESSION['oidc_nonce']) return false;

        return $claims;
    } catch (Exception $e) {
        error_log("JWT validation failed: " . $e->getMessage());
        return false;
    } finally {
        // Restore the original timestamp to avoid side effects elsewhere
        JWT::$timestamp = $previousTimestamp;
    }
}
