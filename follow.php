<?php
require_once __DIR__ . '/includes/auth.php';
$user = require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $targetId = (int) ($_POST['user_id'] ?? 0);
    if ($targetId && $targetId !== (int) $user['id']) {
        $stmt = $pdo->prepare('SELECT 1 FROM follows WHERE follower_id = ? AND following_id = ?');
        $stmt->execute([$user['id'], $targetId]);
        if ($stmt->fetch()) {
            $delete = $pdo->prepare('DELETE FROM follows WHERE follower_id = ? AND following_id = ?');
            $delete->execute([$user['id'], $targetId]);
        } else {
            $insert = $pdo->prepare('INSERT IGNORE INTO follows (follower_id, following_id) VALUES (?, ?)');
            $insert->execute([$user['id'], $targetId]);
        }
    }
}

header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? 'index.php'));
exit;

