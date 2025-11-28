<?php

function base64UrlEncode($data)
{
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function createGhostJWT()
{
    $apiKey = getenv('EARTH_KEY');

    if (!$apiKey) {
        throw new Exception('EARTH_KEY environment variable is not set.');
    }

    [$id, $secret] = explode(':', $apiKey);

    $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256', 'kid' => $id]);
    $now = time();
    $payload = json_encode([
        'iat' => $now,
        'exp' => $now + 300,
        'aud' => 'admin/'
    ]);

    $base64UrlHeader = base64UrlEncode($header);
    $base64UrlPayload = base64UrlEncode($payload);

    $signature = hash_hmac('sha256', $base64UrlHeader . '.' . $base64UrlPayload, hex2bin($secret), true);
    $base64UrlSignature = base64UrlEncode($signature);

    return $base64UrlHeader . '.' . $base64UrlPayload . '.' . $base64UrlSignature;
}

function fetchGhostMembers(array $params = []): array
{
    $jwt = createGhostJWT();
    $baseUrl = 'https://earthen.io/ghost/api/admin/members/';

    $defaultParams = [
        'limit' => 'all',
        'include' => 'newsletters,labels'
    ];

    $query = http_build_query(array_merge($defaultParams, $params));
    $url = $baseUrl . '?' . $query;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Ghost ' . $jwt,
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if (curl_errno($ch)) {
        throw new Exception('Curl error: ' . curl_error($ch));
    }

    curl_close($ch);

    if ($httpCode >= 200 && $httpCode < 300) {
        $data = json_decode($response, true);
        return $data['members'] ?? [];
    }

    throw new Exception('Ghost API request failed with status ' . $httpCode . ': ' . $response);
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
    $emailCount = $member['email_count'] ?? 0;
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

    $total = count($subscribed);
    $sentCount = count($sentMembers);

    return [
        'subscribed' => $subscribed,
        'sent' => $sentMembers,
        'pending' => $pendingMembers,
        'total' => $total,
        'sent_count' => $sentCount,
        'sent_percentage' => $total > 0 ? round(($sentCount / $total) * 100, 2) : 0
    ];
}

function ensureMemberHasLabel(string $memberId, string $labelName): bool
{
    $members = fetchGhostMembers([
        'limit' => 1,
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
                'id' => $memberId,
                'labels' => $labels,
            ]
        ]
    ]);

    $jwt = createGhostJWT();
    $updateUrl = 'https://earthen.io/ghost/api/admin/members/' . rawurlencode($memberId) . '/';

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $updateUrl);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Ghost ' . $jwt,
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if (curl_errno($ch)) {
        curl_close($ch);
        throw new Exception('Curl error while updating member label: ' . curl_error($ch));
    }

    curl_close($ch);

    if ($httpCode >= 200 && $httpCode < 300) {
        return true;
    }

    error_log('Failed to update member label: ' . $response);
    return false;
}

