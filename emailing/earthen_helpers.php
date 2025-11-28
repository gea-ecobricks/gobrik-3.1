<?php
/**
 * Cron-safe Earthen / Ghost helpers.
 * - No HTML/JS output
 * - No reliance on browser DOM
 * - Throws Exceptions on error so caller can log nicely
 */

/**
 * Load the Ghost stats database connection from the external credential file.
 *
 * @return mysqli|null
 */
function loadGhostStatsConnection(): ?mysqli
{
    $credentialPaths = [
        dirname(__DIR__) . '/ghostconn_env.php',
    ];

    if (!empty($_SERVER['DOCUMENT_ROOT'])) {
        $credentialPaths[] = rtrim($_SERVER['DOCUMENT_ROOT'], '/') . '/ghostconn_env.php';
    }

    $ghoststats_conn = null;

    foreach ($credentialPaths as $path) {
        if (is_readable($path)) {
            require $path;
            break;
        }
    }

    if (!$ghoststats_conn instanceof mysqli) {
        error_log('[GHOST STATS] Credential file not found or did not create a mysqli connection.');
        return null;
    }

    if ($ghoststats_conn->connect_error) {
        error_log('[GHOST STATS] DB connection failed: ' . $ghoststats_conn->connect_error);
        return null;
    }

    return $ghoststats_conn;
}

// URL-safe base64 encoder
function base64UrlEncode($data) {
    return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($data));
}

/**
 * Fetch and validate the Ghost Admin API key from the environment.
 *
 * EARTHEN_KEY must be in the format: "{id}:{secret}"
 * - id: any non-empty string (Ghost's key id)
 * - secret: 64 hex chars (256-bit), e.g. /^[0-9a-fA-F]{64}$/
 *
 * @return array [id, secret]
 * @throws Exception on any format/validation problem
 */
function getEarthenAdminKey() {
    $raw = getenv('EARTHEN_KEY');

    if (!$raw) {
        throw new Exception('EARTHEN_KEY (Ghost Admin API key) not set in environment.');
    }

    $raw = trim($raw);

    // How many colons?
    $colonCount = substr_count($raw, ':');
    if ($colonCount !== 1) {
        throw new Exception(
            "EARTHEN_KEY must be in the format \"id:secret\" with exactly 1 colon; found {$colonCount}."
        );
    }

    // Split into id and secret
    list($id, $secret) = explode(':', $raw, 2);
    $id     = trim($id);
    $secret = trim($secret);

    if ($id === '' || $secret === '') {
        throw new Exception('EARTHEN_KEY id or secret is empty after trimming.');
    }

    // Secret must be hex only
    if (!preg_match('/^[0-9a-fA-F]+$/', $secret)) {
        $len = strlen($secret);
        throw new Exception(
            "EARTHEN_KEY secret contains non-hex characters (length {$len}). " .
            "Make sure you copied the **Admin API key** exactly from Ghost."
        );
    }

    // Length must be even (Ghost secrets are typically 64 hex chars)
    $len = strlen($secret);
    if ($len % 2 !== 0) {
        throw new Exception(
            "EARTHEN_KEY secret length ({$len}) is not even. " .
            "It should typically be 64 hex characters. Check for a missing or extra character."
        );
    }

    // For debugging without leaking the actual key

    return [$id, $secret];
}

/**
 * Build a Ghost Admin JWT using EARTHEN_KEY.
 *
 * @return string JWT
 * @throws Exception if key is missing/invalid
 */
function createGhostJWT() {
    list($id, $secret) = getEarthenAdminKey();

    $header = json_encode([
        'typ' => 'JWT',
        'alg' => 'HS256',
        'kid' => $id,
    ]);

    $now = time();
    $payload = json_encode([
        'iat' => $now,
        'exp' => $now + 300, // 5 minutes
        'aud' => '/v4/admin/',
    ]);

    $base64UrlHeader  = base64UrlEncode($header);
    $base64UrlPayload = base64UrlEncode($payload);

    $signature = hash_hmac(
        'sha256',
        $base64UrlHeader . '.' . $base64UrlPayload,
        hex2bin($secret),
        true
    );

    $base64UrlSignature = base64UrlEncode($signature);

    return $base64UrlHeader . '.' . $base64UrlPayload . '.' . $base64UrlSignature;
}

/**
 * Fetch Ghost members directly from the stats database instead of the Admin API.
 *
 * Supported params (minimal, to satisfy existing callers):
 * - limit: maximum rows to return (defaults to 1000)
 * - filter: only supports "id:<value>" or "email:<value>" filters
 *
 * @param array $params
 * @return array Member rows with labels/newsletters expanded
 * @throws Exception when the database connection or query fails
 */
function fetchGhostMembers(array $params = []): array
{
    $conn = loadGhostStatsConnection();

    if (!$conn) {
        throw new Exception('Ghost stats database connection unavailable.');
    }

    $limit = isset($params['limit']) ? max(1, (int) $params['limit']) : 1000;

    $filters   = [];
    $bindTypes = '';
    $bindVars  = [];

    if (!empty($params['filter'])) {
        $filter = $params['filter'];

        if (preg_match('/^id:(.+)$/', $filter, $matches)) {
            $filters[]  = 'm.id = ?';
            $bindTypes .= 's';
            $bindVars[] = $matches[1];
        } elseif (preg_match('/^email:(.+)$/', $filter, $matches)) {
            $filters[]  = 'm.email = ?';
            $bindTypes .= 's';
            $bindVars[] = $matches[1];
        } else {
            throw new Exception('Unsupported filter format for fetchGhostMembers.');
        }
    }

    $sql = "SELECT m.id, m.uuid, m.email, m.name, m.created_at, m.updated_at, m.email_count, m.email_opened_count, " .
           "GROUP_CONCAT(DISTINCT l.name) AS label_names, " .
           "GROUP_CONCAT(DISTINCT n.name) AS newsletter_names " .
           "FROM members m " .
           "LEFT JOIN members_labels ml ON m.id = ml.member_id " .
           "LEFT JOIN labels l ON ml.label_id = l.id " .
           "LEFT JOIN members_newsletters mn ON m.id = mn.member_id " .
           "LEFT JOIN newsletters n ON mn.newsletter_id = n.id";

    if (!empty($filters)) {
        $sql .= ' WHERE ' . implode(' AND ', $filters);
    }

    $sql .= ' GROUP BY m.id ORDER BY m.created_at ASC LIMIT ?';

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        throw new Exception('Failed to prepare Ghost member query: ' . $conn->error);
    }

    $bindTypes .= 'i';
    $bindVars[] = $limit;

    $stmt->bind_param($bindTypes, ...$bindVars);

    if (!$stmt->execute()) {
        $error = $stmt->error ?: 'Unknown execution error';
        $stmt->close();
        throw new Exception('Failed to execute Ghost member query: ' . $error);
    }

    $result = $stmt->get_result();
    $members = [];

    while ($row = $result->fetch_assoc()) {
        $labelNames = array_filter(array_map('trim', explode(',', $row['label_names'] ?? '')));
        $newsletterNames = array_filter(array_map('trim', explode(',', $row['newsletter_names'] ?? '')));

        $members[] = [
            'id'                 => $row['id'] ?? null,
            'uuid'               => $row['uuid'] ?? null,
            'email'              => $row['email'] ?? null,
            'name'               => $row['name'] ?? null,
            'created_at'         => $row['created_at'] ?? null,
            'updated_at'         => $row['updated_at'] ?? null,
            'email_count'        => isset($row['email_count']) ? (int) $row['email_count'] : 0,
            'email_opened_count' => isset($row['email_opened_count']) ? (int) $row['email_opened_count'] : 0,
            'labels'             => array_map(function ($name) {
                return ['name' => $name];
            }, $labelNames),
            'newsletters'        => array_map(function ($name) {
                return ['name' => $name];
            }, $newsletterNames),
        ];
    }

    $stmt->close();

    return $members;
}

function memberHasLabel(array $member, string $labelName): bool
{
    if (empty($member['labels'])) {
        return false;
    }

    foreach ($member['labels'] as $label) {
        if (isset($label['name']) && strcasecmp($label['name'], $labelName) === 0) {
            return true;
        }
    }

    return false;
}

function calculateOpenRate(array $member): string
{
    $emailCount  = $member['email_count'] ?? 0;
    $openedCount = $member['email_opened_count'] ?? 0;

    if ($emailCount <= 0) {
        return '0%';
    }

    return round(($openedCount / $emailCount) * 100, 2) . '%';
}

function summarizeGhostMembers(array $members, string $sentLabel = 'sent-001'): array
{
    $subscribed = array_values(array_filter($members, function ($member) {
        return !empty($member['newsletters']);
    }));

    $sentMembers = array_values(array_filter($subscribed, function ($member) use ($sentLabel) {
        return memberHasLabel($member, $sentLabel);
    }));

    $pendingMembers = array_values(array_filter($subscribed, function ($member) use ($sentLabel) {
        return !memberHasLabel($member, $sentLabel);
    }));

    $total     = count($subscribed);
    $sentCount = count($sentMembers);

    return [
        'subscribed'      => $subscribed,
        'sent'            => $sentMembers,
        'pending'         => $pendingMembers,
        'total'           => $total,
        'sent_count'      => $sentCount,
        'sent_percentage' => $total > 0 ? round(($sentCount / $total) * 100, 2) : 0,
    ];
}

function ensureMemberHasLabel(string $memberId, string $labelName): bool
{
    $members = fetchGhostMembers([
        'limit'  => 1,
        'filter' => "id:$memberId",
    ]);

    if (empty($members)) {
        return false;
    }

    $member = $members[0];
    $labels = $member['labels'] ?? [];

    if (!memberHasLabel($member, $labelName)) {
        $labels[] = ['name' => $labelName];
    }

    $payload = json_encode([
        'members' => [
            [
                'id'     => $memberId,
                'labels' => $labels,
            ],
        ],
    ]);

    $jwt       = createGhostJWT();
    $updateUrl = 'https://earthen.io/ghost/api/v4/admin/members/' . rawurlencode($memberId) . '/';

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $updateUrl);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Ghost ' . $jwt,
        'Content-Type: application/json',
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

    $response  = curl_exec($ch);
    $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);

    curl_close($ch);

    if ($curlError) {
        throw new Exception('Curl error while updating member label: ' . $curlError);
    }

    if ($httpCode >= 200 && $httpCode < 300) {
        return true;
    }

    error_log('Failed to update member label: ' . $response);
    return false;
}

/**
 * Get Ghost member ID for an email, or null if not found.
 */
function getMemberIdByEmail($email) {
    $email_encoded = urlencode($email);
    $ghost_api_url = "https://earthen.io/ghost/api/v4/admin/members/?filter=email:$email_encoded";
    $jwt           = createGhostJWT();

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $ghost_api_url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Ghost ' . $jwt,
        'Content-Type: application/json',
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response  = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_err  = curl_error($ch);
    curl_close($ch);

    if ($curl_err) {
        error_log("Earthen getMemberIdByEmail cURL error: $curl_err");
        throw new Exception('Error contacting Earthen (curl).');
    }

    if ($http_code < 200 || $http_code >= 300) {
        error_log("Earthen getMemberIdByEmail HTTP $http_code: $response");
        throw new Exception("Earthen API returned HTTP $http_code while looking up member.");
    }

    $data = json_decode($response, true);
    if (
        !isset($data['members']) ||
        !is_array($data['members']) ||
        count($data['members']) === 0
    ) {
        // Not an error: just "user not found"
        return null;
    }

    return $data['members'][0]['id'] ?? null;
}

/**
 * Unsubscribe a user from Earthen by deleting the Ghost member.
 *
 * Throws Exception on error. Returns true on success, false if not found.
 */
function earthenUnsubscribe($email) {
    error_log("Earthen unsubscribe: process initiated for email: $email");

    $member_id = getMemberIdByEmail($email);
    error_log("Earthen unsubscribe: member id for $email is " . ($member_id ?: 'null'));

    if (!$member_id) {
        // Treat "not found" as non-fatal: there's nothing to delete.
        return false;
    }

    $ghost_api_url = "https://earthen.io/ghost/api/v4/admin/members/$member_id/";
    $jwt           = createGhostJWT();

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $ghost_api_url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Ghost ' . $jwt,
        'Content-Type: application/json',
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');

    $response  = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_err  = curl_error($ch);
    curl_close($ch);

    if ($curl_err) {
        error_log("Earthen unsubscribe cURL error: $curl_err");
        throw new Exception('Error contacting Earthen (curl) during unsubscribe.');
    }

    if ($http_code < 200 || $http_code >= 300) {
        error_log("Earthen unsubscribe HTTP $http_code: $response");
        throw new Exception("Earthen API returned HTTP $http_code during unsubscribe.");
    }

    error_log("Earthen unsubscribe: completed successfully for $email (HTTP $http_code)");
    return true;
}
