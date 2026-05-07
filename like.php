<?php
require_once __DIR__ . '/includes/auth.php';
$user = require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postId = (int) ($_POST['post_id'] ?? 0);
    $stmt = $pdo->prepare('SELECT 1 FROM likes WHERE user_id = ? AND post_id = ?');
    $stmt->execute([$user['id'], $postId]);
    if ($stmt->fetch()) {
        $delete = $pdo->prepare('DELETE FROM likes WHERE user_id = ? AND post_id = ?');
        $delete->execute([$user['id'], $postId]);
    } else {
        $insert = $pdo->prepare('INSERT IGNORE INTO likes (user_id, post_id) VALUES (?, ?)');
        $insert->execute([$user['id'], $postId]);
    }
}

header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? 'index.php'));
exit;

