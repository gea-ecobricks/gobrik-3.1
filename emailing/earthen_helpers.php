<?php
if (!defined('EARTHEN_TOTAL_MEMBERS')) {
    define('EARTHEN_TOTAL_MEMBERS', 70000);
}
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
    $offset = isset($params['offset']) ? max(0, (int) $params['offset']) : 0;

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

    $sql .= ' GROUP BY m.id ORDER BY m.created_at ASC LIMIT ? OFFSET ?';

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        throw new Exception('Failed to prepare Ghost member query: ' . $conn->error);
    }

    $bindTypes .= 'ii';
    $bindVars[] = $limit;
    $bindVars[] = $offset;

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

/**
 * Fetch Earthen member stats using the Earthen MySQL database connection.
 *
 * Caches the total member count in the current session while keeping the
 * sent count live so that progress updates remain accurate.
 */
function getEarthenMemberStats(mysqli $conn): array
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!isset($_SESSION['earthen_member_total'])) {
        $totalQuery = "SELECT COUNT(*) AS total_members FROM earthen_members_tb";
        $totalResult = $conn->query($totalQuery);
        $_SESSION['earthen_member_total'] = (int) ($totalResult && ($row = $totalResult->fetch_assoc()) ? ($row['total_members'] ?? 0) : 0);

        if ($totalResult instanceof mysqli_result) {
            $totalResult->free();
        }
    }

    $sentQuery = "SELECT COUNT(*) AS sent_count FROM earthen_members_tb WHERE test_sent = 1";
    $sentResult = $conn->query($sentQuery);
    $sentCount = (int) ($sentResult && ($row = $sentResult->fetch_assoc()) ? ($row['sent_count'] ?? 0) : 0);

    if ($sentResult instanceof mysqli_result) {
        $sentResult->free();
    }

    $totalMembers = (int) ($_SESSION['earthen_member_total'] ?? 0);

    return [
        'total' => $totalMembers,
        'sent' => $sentCount,
        'percentage' => $totalMembers > 0 ? round(($sentCount / $totalMembers) * 100, 3) : 0,
    ];
}

/**
 * Get member stats directly from the Ghost stats database.
 */
function getGhostMemberStats(?mysqli $conn, string $sentLabel = 'sent-001'): array
{
    if (!$conn) {
        return [
            'total' => 0,
            'sent' => 0,
            'percentage' => 0,
        ];
    }

    $totalSql = "SELECT COUNT(DISTINCT m.id) AS total_members
                 FROM members m
                 INNER JOIN members_newsletters mn ON m.id = mn.member_id
                 WHERE m.email IS NOT NULL AND m.email <> ''";

    $totalResult = $conn->query($totalSql);
    $totalMembers = (int) ($totalResult && ($row = $totalResult->fetch_assoc()) ? ($row['total_members'] ?? 0) : 0);

    if ($totalResult instanceof mysqli_result) {
        $totalResult->free();
    }

    $sentSql = "SELECT COUNT(DISTINCT m.id) AS sent_members
                FROM members m
                INNER JOIN members_newsletters mn ON m.id = mn.member_id
                INNER JOIN members_labels ml ON m.id = ml.member_id
                INNER JOIN labels l ON ml.label_id = l.id AND l.name = ?
                WHERE m.email IS NOT NULL AND m.email <> ''";

    $sentStmt = $conn->prepare($sentSql);

    if (!$sentStmt) {
        error_log('[EARTHEN] Failed to prepare Ghost sent stats: ' . $conn->error);

        return [
            'total' => $totalMembers,
            'sent' => 0,
            'percentage' => $totalMembers > 0 ? 0 : 0,
        ];
    }

    $sentStmt->bind_param('s', $sentLabel);
    $sentStmt->execute();
    $sentResult = $sentStmt->get_result();
    $sentCount = (int) ($sentResult && ($row = $sentResult->fetch_assoc()) ? ($row['sent_members'] ?? 0) : 0);

    $sentStmt->close();

    return [
        'total' => $totalMembers,
        'sent' => $sentCount,
        'percentage' => $totalMembers > 0 ? round(($sentCount / $totalMembers) * 100, 3) : 0,
    ];
}

/**
 * Retrieve a batch of pending Earthen members from the MySQL database.
 */
function fetchEarthenPendingBatch(mysqli $conn, int $limit = 100, int $offset = 0): array
{
    $limit = max(1, min($limit, 500));
    $offset = max(0, $offset);

    $sql = "SELECT id, email, name, test_sent, test_sent_date_time
            FROM earthen_members_tb
            WHERE test_sent = 0
              AND (processing IS NULL OR processing = 0)
              AND email IS NOT NULL
              AND email <> ''
            ORDER BY id ASC
            LIMIT ? OFFSET ?";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        error_log('[EARTHEN] Failed to prepare pending batch query: ' . $conn->error);
        return [];
    }

    $stmt->bind_param('ii', $limit, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
    $batch = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];

    $stmt->close();

    $mappedBatch = array_map(function ($member) {
        return [
            'id' => $member['id'] ?? null,
            'email' => $member['email'] ?? '',
            'name' => $member['name'] ?? '',
            'email_open_rate' => 0,
            'status' => 'pending',
            'test_sent' => (int) ($member['test_sent'] ?? 0),
            'test_sent_date_time' => $member['test_sent_date_time'] ?? 'N/A',
        ];
    }, $batch);

    return filterMembersWithoutGhostLabel($mappedBatch, 'sent-001');
}

/**
 * Retrieve a batch of pending Earthen members directly from the Ghost stats database.
 */
function fetchGhostPendingBatch(?mysqli $conn, int $limit = 100, int $offset = 0, string $sentLabel = 'sent-001'): array
{
    if (!$conn) {
        return [];
    }

    $limit = max(1, min($limit, 500));
    $offset = max(0, $offset);

    $sql = "SELECT m.id, m.uuid, m.email, m.name, m.email_count, m.email_opened_count
            FROM members m
            INNER JOIN members_newsletters mn ON m.id = mn.member_id
            WHERE m.email IS NOT NULL AND m.email <> ''
              AND NOT EXISTS (
                  SELECT 1
                  FROM members_labels ml
                  INNER JOIN labels l ON ml.label_id = l.id
                  WHERE ml.member_id = m.id
                    AND l.name LIKE CONCAT('%', ?, '%')
              )
            GROUP BY m.id
            ORDER BY m.created_at ASC
            LIMIT ? OFFSET ?";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        error_log('[EARTHEN] Failed to prepare Ghost pending batch query: ' . $conn->error);
        return [];
    }

    $stmt->bind_param('sii', $sentLabel, $limit, $offset);

    if (!$stmt->execute()) {
        error_log('[EARTHEN] Failed executing Ghost pending batch query: ' . $stmt->error);
        $stmt->close();
        return [];
    }

    $result = $stmt->get_result();
    $batch = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];

    $stmt->close();

    return array_map(function ($member) {
        $opened = (int) ($member['email_opened_count'] ?? 0);
        $sent = (int) ($member['email_count'] ?? 0);
        $openRate = $sent > 0 ? round(($opened / $sent) * 100, 2) . '%' : '0%';

        return [
            'id' => $member['id'] ?? null,
            'uuid' => $member['uuid'] ?? null,
            'email' => $member['email'] ?? '',
            'name' => $member['name'] ?? '',
            'email_open_rate' => $openRate,
            'status' => 'pending',
            'test_sent' => 0,
            'test_sent_date_time' => 'N/A',
        ];
    }, $batch);
}

/**
 * Remove members that already have a specific Ghost label.
 */
function filterMembersWithoutGhostLabel(array $members, string $labelName = 'sent-001'): array
{
    if (empty($members)) {
        return [];
    }

    $emails = array_values(array_unique(array_filter(array_column($members, 'email'))));

    if (empty($emails)) {
        return $members;
    }

    $ghost_conn = loadGhostStatsConnection();

    if (!$ghost_conn) {
        return $members;
    }

    $placeholders = implode(',', array_fill(0, count($emails), '?'));
    $sql = "SELECT DISTINCT m.email
            FROM members m
            INNER JOIN members_labels ml ON m.id = ml.member_id
            INNER JOIN labels l ON ml.label_id = l.id
            WHERE l.name = ? AND m.email IN ($placeholders)";

    $stmt = $ghost_conn->prepare($sql);

    if (!$stmt) {
        error_log('[EARTHEN] Failed to prepare Ghost label filter query: ' . $ghost_conn->error);
        return $members;
    }

    $params = array_merge([$labelName], $emails);
    $types = str_repeat('s', count($params));

    $bindValues = [$types];
    foreach ($params as $key => $value) {
        $bindValues[] = &$params[$key];
    }

    call_user_func_array([$stmt, 'bind_param'], $bindValues);

    if (!$stmt->execute()) {
        error_log('[EARTHEN] Failed executing Ghost label filter query: ' . $stmt->error);
        $stmt->close();
        return $members;
    }

    $result = $stmt->get_result();
    $labelledEmails = $result ? array_column($result->fetch_all(MYSQLI_ASSOC), 'email') : [];
    $stmt->close();

    if (empty($labelledEmails)) {
        return $members;
    }

    $labelledSet = array_flip(array_map('strtolower', $labelledEmails));

    return array_values(array_filter($members, function ($member) use ($labelledSet) {
        $email = strtolower($member['email'] ?? '');
        return $email === '' || !isset($labelledSet[$email]);
    }));
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

function personalizeEmailHtml(string $html, ?string $recipient_uuid, string $recipient_email): string
{
    $uuid_placeholder = '{{RECIPIENT_UUID}}';
    $fallback_uuid = '4dbbb711-73e9-4fd0-9056-a7cc1af6a905';
    $uuid = $recipient_uuid ?: $fallback_uuid;

    $html = str_replace($uuid_placeholder, $uuid, $html);

    $fallback_unsubscribe = 'https://earthen.io/unsubscribe/?uuid=4dbbb711-73e9-4fd0-9056-a7cc1af6a905&key=6c3ffe5e66725cd21a19a3f06a3f9c57d439ef226283a59999acecb11fb087dc&newsletter=1db69ae6-6504-48ba-9fd9-d78b3928071f';
    $unsubscribe_url = !empty($recipient_email)
        ? 'https://gobrik.com/emailing/unsubscribe.php?email=' . urlencode($recipient_email)
        : $fallback_unsubscribe;

    $html = preg_replace(
        '/https:\/\/gobrik\.com\/emailing\/unsubscribe\.php\?email=[^\s"\']+/i',
        $unsubscribe_url,
        $html
    );

    $html = preg_replace(
        '/https:\/\/earthen\.io\/unsubscribe\/\?uuid=[^&\"]+(&key=[^&\"]+)?(&newsletter=[^&\"]+)?/i',
        $unsubscribe_url,
        $html
    );

    return $html;
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
