<?php
require_once __DIR__ . '/includes/auth.php';
$me = require_login();
$postId = (int) ($_GET['id'] ?? 0);

$stmt = $pdo->prepare(
    'SELECT p.*, u.name, u.username, u.avatar,
        COUNT(DISTINCT l.user_id) AS like_count,
        MAX(CASE WHEN ml.user_id IS NULL THEN 0 ELSE 1 END) AS liked_by_me
     FROM posts p
     JOIN users u ON u.id = p.user_id
     LEFT JOIN likes l ON l.post_id = p.id
     LEFT JOIN likes ml ON ml.post_id = p.id AND ml.user_id = ?
     WHERE p.id = ?
     GROUP BY p.id'
);
$stmt->execute([$me['id'], $postId]);
$post = $stmt->fetch();
if (!$post) {
    http_response_code(404);
    exit('Post not found.');
}

$comments = $pdo->prepare(
    'SELECT c.*, u.username, u.avatar FROM comments c JOIN users u ON u.id = c.user_id WHERE c.post_id = ? ORDER BY c.created_at ASC'
);
$comments->execute([$post['id']]);

$pageTitle = 'Post';
include __DIR__ . '/includes/header.php';
?>
<article class="post detail">
    <div class="post-head">
        <a class="user-chip" href="profile.php?u=<?= e($post['username']) ?>">
            <img src="<?= e(avatar_url($post['avatar'])) ?>" alt="">
            <span><strong><?= e($post['name']) ?></strong><small>@<?= e($post['username']) ?></small></span>
        </a>
        <span class="muted"><?= e(time_ago($post['created_at'])) ?></span>
    </div>
    <img class="post-img" src="uploads/<?= e($post['image']) ?>" alt="">
    <div class="post-body">
        <p class="caption"><?= e($post['caption']) ?></p>
        <div class="actions">
            <div class="post-tools">
                <form action="like.php" method="post">
                    <input type="hidden" name="post_id" value="<?= (int) $post['id'] ?>">
                    <button class="icon-action <?= $post['liked_by_me'] ? 'liked' : '' ?>" type="submit" title="<?= $post['liked_by_me'] ? 'Unlike' : 'Like' ?>"><?= p_icon('heart') ?></button>
                </form>
                <a class="icon-action" href="share.php?post_id=<?= (int) $post['id'] ?>" title="Share"><?= p_icon('share') ?></a>
                <span class="metric"><strong><?= (int) $post['like_count'] ?></strong> likes</span>
            </div>
        </div>
        <div class="comments all-comments">
            <?php foreach ($comments as $comment): ?><p><strong><?= e($comment['username']) ?></strong> <?= e($comment['body']) ?></p><?php endforeach; ?>
        </div>
    </div>
    <form class="comment-form" action="comment.php" method="post">
        <input type="hidden" name="post_id" value="<?= (int) $post['id'] ?>">
        <input name="body" placeholder="Add a comment..." required>
        <button type="submit">Post</button>
    </form>
</article>
<?php include __DIR__ . '/includes/footer.php'; ?>
