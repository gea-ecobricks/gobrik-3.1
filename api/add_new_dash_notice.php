<?php
require_once '../earthenAuth_helper.php';
require_once '../gobrikconn_env.php';

if (!isLoggedIn()) {
    $gobrik_conn->close();
    header('Location: ../en/login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $gobrik_conn->close();
    header('Location: ../en/dashboard.php');
    exit();
}

$buwana_id = $_SESSION['buwana_id'] ?? null;
$user_roles = getUser_Role($buwana_id) ?? '';

if (!$buwana_id || strpos(strtolower($user_roles), 'admin') === false) {
    $gobrik_conn->close();
    header('Location: ../en/dashboard.php?notice_error=forbidden');
    exit();
}

$message_body = trim($_POST['message_body'] ?? '');
$featured_text = trim($_POST['featured_text'] ?? '');
$featured_url = trim($_POST['featured_url'] ?? '');
$message_emoji = trim($_POST['message_emoji'] ?? '');

if ($message_body === '') {
    $gobrik_conn->close();
    header('Location: ../en/dashboard.php?notice_error=missing');
    exit();
}

if ($featured_url !== '' && !filter_var($featured_url, FILTER_VALIDATE_URL)) {
    $gobrik_conn->close();
    header('Location: ../en/dashboard.php?notice_error=invalid_url');
    exit();
}

if ($message_emoji !== '') {
    if (function_exists('mb_substr')) {
        $message_emoji = mb_substr($message_emoji, 0, 10);
    } else {
        $message_emoji = substr($message_emoji, 0, 10);
    }
}

$message_body_param = $message_body;
$message_emoji_param = $message_emoji !== '' ? $message_emoji : null;
$featured_url_param = $featured_url !== '' ? $featured_url : null;
$featured_text_param = $featured_text !== '' ? $featured_text : null;

$stmt = $gobrik_conn->prepare(
    "INSERT INTO dash_notices_tb (message_body, message_emoji, featured_url, featured_text) VALUES (?, ?, ?, ?)"
);

if (!$stmt) {
    $gobrik_conn->close();
    header('Location: ../en/dashboard.php?notice_error=db');
    exit();
}

$stmt->bind_param('ssss', $message_body_param, $message_emoji_param, $featured_url_param, $featured_text_param);
$success = $stmt->execute();
$stmt->close();
$gobrik_conn->close();

if ($success) {
    header('Location: ../en/dashboard.php?notice_status=updated');
    exit();
}

header('Location: ../en/dashboard.php?notice_error=save');
exit();
