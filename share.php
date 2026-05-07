<?php
require_once __DIR__ . '/includes/auth.php';
$me = require_login();
$pageTitle = 'Share post';
$postId = (int) ($_GET['post_id'] ?? $_POST['post_id'] ?? 0);

$postStmt = $pdo->prepare(
    'SELECT p.id, p.image, p.caption, u.username
     FROM posts p
     JOIN users u ON u.id = p.user_id
     WHERE p.id = ?'
);
$postStmt->execute([$postId]);
$post = $postStmt->fetch();

if (!$post) {
    http_response_code(404);
    exit('Post not found.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $receiverId = (int) ($_POST['receiver_id'] ?? 0);
    $body = trim($_POST['body'] ?? 'Shared a post');

    if ($receiverId && $receiverId !== (int) $me['id']) {
        $stmt = $pdo->prepare('INSERT INTO messages (sender_id, receiver_id, post_id, body) VALUES (?, ?, ?, ?)');
        $stmt->execute([$me['id'], $receiverId, $post['id'], $body ?: 'Shared a post']);
        flash('Post shared in messages.');
        header('Location: messages.php');
        exit;
    }

    flash('Choose a user to share with.', 'error');
}

$people = $pdo->prepare(
    'SELECT id, name, username, avatar
     FROM users
     WHERE id <> ?
     ORDER BY username
     LIMIT 50'
);
$people->execute([$me['id']]);

include __DIR__ . '/includes/header.php';
?>
<section class="panel narrow">
    <h1>Share post</h1>
    <div class="share-preview">
        <img src="uploads/<?= e($post['image']) ?>" alt="">
        <p><strong>@<?= e($post['username']) ?></strong> <?= e($post['caption']) ?></p>
    </div>
    <form method="post" class="stack">
        <input type="hidden" name="post_id" value="<?= (int) $post['id'] ?>">
        <select name="receiver_id" required>
            <option value="">Send to...</option>
            <?php foreach ($people as $person): ?>
                <option value="<?= (int) $person['id'] ?>"><?= e($person['name']) ?> (@<?= e($person['username']) ?>)</option>
            <?php endforeach; ?>
        </select>
        <input name="body" value="Shared a post" placeholder="Message">
        <button class="button" type="submit">Send</button>
    </form>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>

