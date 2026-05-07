<?php
require_once __DIR__ . '/includes/auth.php';
$me = require_login();
$pageTitle = 'Messages';
$selectedUsername = $_GET['u'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $receiverId = (int) ($_POST['receiver_id'] ?? 0);
    $body = trim($_POST['body'] ?? '');

    if ($receiverId && $receiverId !== (int) $me['id'] && $body !== '') {
        $stmt = $pdo->prepare('INSERT INTO messages (sender_id, receiver_id, body) VALUES (?, ?, ?)');
        $stmt->execute([$me['id'], $receiverId, $body]);
    }

    header('Location: messages.php?u=' . urlencode($_POST['receiver_username'] ?? ''));
    exit;
}

$people = $pdo->prepare(
    'SELECT DISTINCT u.id, u.name, u.username, u.avatar
     FROM users u
     WHERE u.id <> ?
     ORDER BY u.username'
);
$people->execute([$me['id']]);
$peopleList = $people->fetchAll();

$selectedUser = null;
if ($selectedUsername !== '') {
    $stmt = $pdo->prepare('SELECT id, name, username, avatar FROM users WHERE username = ? AND id <> ?');
    $stmt->execute([$selectedUsername, $me['id']]);
    $selectedUser = $stmt->fetch() ?: null;
}
if (!$selectedUser && $peopleList) {
    $selectedUser = $peopleList[0];
}

$messages = [];
if ($selectedUser) {
    $stmt = $pdo->prepare(
        'SELECT m.*, p.image, p.caption, pu.username AS post_username
         FROM messages m
         LEFT JOIN posts p ON p.id = m.post_id
         LEFT JOIN users pu ON pu.id = p.user_id
         WHERE (m.sender_id = ? AND m.receiver_id = ?)
            OR (m.sender_id = ? AND m.receiver_id = ?)
         ORDER BY m.created_at ASC'
    );
    $stmt->execute([$me['id'], $selectedUser['id'], $selectedUser['id'], $me['id']]);
    $messages = $stmt->fetchAll();
}

include __DIR__ . '/includes/header.php';
?>
<section class="messages-layout">
    <aside class="message-list">
        <h2>Messages</h2>
        <?php foreach ($peopleList as $person): ?>
            <a class="person-row <?= $selectedUser && (int) $selectedUser['id'] === (int) $person['id'] ? 'active' : '' ?>" href="messages.php?u=<?= e($person['username']) ?>">
                <img src="<?= e(avatar_url($person['avatar'])) ?>" alt="">
                <span><strong><?= e($person['name']) ?></strong><small>@<?= e($person['username']) ?></small></span>
            </a>
        <?php endforeach; ?>
    </aside>

    <div class="chat-panel">
        <?php if ($selectedUser): ?>
            <div class="chat-head">
                <img src="<?= e(avatar_url($selectedUser['avatar'])) ?>" alt="">
                <div><strong><?= e($selectedUser['name']) ?></strong><small>@<?= e($selectedUser['username']) ?></small></div>
            </div>
            <div class="chat-box">
                <?php if (!$messages): ?><p class="muted">No messages yet.</p><?php endif; ?>
                <?php foreach ($messages as $message): ?>
                    <div class="bubble <?= (int) $message['sender_id'] === (int) $me['id'] ? 'mine' : '' ?>">
                        <p><?= e($message['body']) ?></p>
                        <?php if ($message['post_id'] && $message['image']): ?>
                            <a class="shared-post" href="post.php?id=<?= (int) $message['post_id'] ?>">
                                <img src="uploads/<?= e($message['image']) ?>" alt="">
                                <span>@<?= e($message['post_username']) ?>'s post</span>
                            </a>
                        <?php endif; ?>
                        <small><?= e(time_ago($message['created_at'])) ?></small>
                    </div>
                <?php endforeach; ?>
            </div>
            <form method="post" class="comment-form chat-form">
                <input type="hidden" name="receiver_id" value="<?= (int) $selectedUser['id'] ?>">
                <input type="hidden" name="receiver_username" value="<?= e($selectedUser['username']) ?>">
                <input name="body" placeholder="Write a message..." required>
                <button type="submit">Send</button>
            </form>
        <?php else: ?>
            <div class="empty"><h2>No users yet</h2><p>Register another account to start messaging.</p></div>
        <?php endif; ?>
    </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
