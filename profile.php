<?php
require_once __DIR__ . '/includes/auth.php';
$me = require_login();
$username = $_GET['u'] ?? $me['username'];

$stmt = $pdo->prepare('SELECT id, name, username, bio, avatar, created_at FROM users WHERE username = ?');
$stmt->execute([$username]);
$profile = $stmt->fetch();
if (!$profile) {
    http_response_code(404);
    exit('Profile not found.');
}

$isMe = (int) $profile['id'] === (int) $me['id'];
$followingStmt = $pdo->prepare('SELECT 1 FROM follows WHERE follower_id = ? AND following_id = ?');
$followingStmt->execute([$me['id'], $profile['id']]);
$isFollowing = (bool) $followingStmt->fetch();
$counts = $pdo->prepare(
    'SELECT
        (SELECT COUNT(*) FROM posts WHERE user_id = ?) AS posts,
        (SELECT COUNT(*) FROM follows WHERE following_id = ?) AS followers,
        (SELECT COUNT(*) FROM follows WHERE follower_id = ?) AS following,
        (SELECT COUNT(*) FROM likes l JOIN posts p ON p.id = l.post_id WHERE p.user_id = ?) AS likes,
        (SELECT COUNT(*) FROM comments c JOIN posts p ON p.id = c.post_id WHERE p.user_id = ?) AS comments'
);
$counts->execute([$profile['id'], $profile['id'], $profile['id'], $profile['id'], $profile['id']]);
$stats = $counts->fetch();
$posts = $pdo->prepare('SELECT * FROM posts WHERE user_id = ? ORDER BY created_at DESC');
$posts->execute([$profile['id']]);

$pageTitle = $profile['username'];
include __DIR__ . '/includes/header.php';
?>
<section class="profile-head">
    <img src="<?= e(avatar_url($profile['avatar'])) ?>" alt="">
    <div>
        <div class="profile-title">
            <div>
                <span class="eyebrow">Creator profile</span>
                <h1>@<?= e($profile['username']) ?></h1>
            </div>
            <?php if ($isMe): ?>
                <a class="button small" href="account.php">Edit profile</a>
                <a class="button small secondary" href="create_post.php">New post</a>
            <?php else: ?>
                <form action="follow.php" method="post">
                    <input type="hidden" name="user_id" value="<?= (int) $profile['id'] ?>">
                    <button class="button small" type="submit"><?= $isFollowing ? 'Following' : 'Follow' ?></button>
                </form>
                <a class="button small secondary" href="messages.php?u=<?= e($profile['username']) ?>">Message</a>
            <?php endif; ?>
        </div>
        <strong><?= e($profile['name']) ?></strong>
        <p><?= e($profile['bio'] ?: 'No bio yet.') ?></p>
        <div class="stats">
            <span><strong><?= (int) $stats['posts'] ?></strong> posts</span>
            <span><strong><?= (int) $stats['followers'] ?></strong> followers</span>
            <span><strong><?= (int) $stats['following'] ?></strong> following</span>
            <span><strong><?= (int) $stats['likes'] ?></strong> likes</span>
        </div>
    </div>
</section>

<section class="profile-tools">
    <a href="messages.php"><?= p_icon('message') ?><span>Messages</span></a>
    <a href="search.php"><?= p_icon('user') ?><span>Find people</span></a>
    <a href="create_post.php"><?= p_icon('plus') ?><span>Create post</span></a>
</section>

<section class="grid">
    <?php foreach ($posts as $post): ?>
        <a class="tile" href="post.php?id=<?= (int) $post['id'] ?>">
            <img src="uploads/<?= e($post['image']) ?>" alt="">
        </a>
    <?php endforeach; ?>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
