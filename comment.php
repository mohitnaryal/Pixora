<?php
require_once __DIR__ . '/includes/auth.php';
$user = require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postId = (int) ($_POST['post_id'] ?? 0);
    $body = trim($_POST['body'] ?? '');
    if ($postId && $body !== '') {
        $stmt = $pdo->prepare('INSERT INTO comments (user_id, post_id, body) VALUES (?, ?, ?)');
        $stmt->execute([$user['id'], $postId, $body]);
    }
}

header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? 'index.php'));
exit;

