<?php
require_once __DIR__ . '/includes/auth.php';
$user = require_login();
$pageTitle = 'Feed';

$stmt = $pdo->prepare(
    'SELECT p.*, u.name, u.username, u.avatar,
        COUNT(DISTINCT l.user_id) AS like_count,
        COUNT(DISTINCT c.id) AS comment_count,
        MAX(CASE WHEN ml.user_id IS NULL THEN 0 ELSE 1 END) AS liked_by_me
     FROM posts p
     JOIN users u ON u.id = p.user_id
     LEFT JOIN follows f ON f.following_id = p.user_id AND f.follower_id = ?
     LEFT JOIN likes l ON l.post_id = p.id
     LEFT JOIN comments c ON c.post_id = p.id
     LEFT JOIN likes ml ON ml.post_id = p.id AND ml.user_id = ?
     WHERE p.user_id = ? OR f.follower_id IS NOT NULL
     GROUP BY p.id
     ORDER BY p.created_at DESC'
);
$stmt->execute([$user['id'], $user['id'], $user['id']]);
$posts = $stmt->fetchAll();

$suggestions = $pdo->prepare(
    'SELECT u.id, u.name, u.username, u.avatar
     FROM users u
     WHERE u.id <> ?
       AND NOT EXISTS (SELECT 1 FROM follows f WHERE f.follower_id = ? AND f.following_id = u.id)
     ORDER BY u.created_at DESC
     LIMIT 5'
);
$suggestions->execute([$user['id'], $user['id']]);
$storyPeople = array_merge([$user], $suggestions->fetchAll());

include __DIR__ . '/includes/header.php';
?>
<div class="layout">
    <section class="feed">
        <div class="feed-top">
            <div>
                <h1>Feed</h1>
                <p>Posts from people you follow and your own profile.</p>
            </div>
            <a href="create_post.php" class="button">New post</a>
        </div>

        <div class="stories">
            <?php foreach ($storyPeople as $person): ?>
                <a class="story" href="profile.php?u=<?= e($person['username']) ?>">
                    <span><img src="<?= e(avatar_url($person['avatar'])) ?>" alt=""></span>
                    <small><?= e($person['username']) ?></small>
                </a>
            <?php endforeach; ?>
        </div>

        <?php if (!$posts): ?>
            <div class="empty">
                <h2>Your feed is quiet</h2>
                <p>Upload your first photo or follow suggested creators to fill this space.</p>
                <a class="button" href="create_post.php">Share first post</a>
            </div>
        <?php endif; ?>

        <?php foreach ($posts as $post): ?>
            <?php
            $commentStmt = $pdo->prepare(
                'SELECT c.*, u.username, u.avatar
                 FROM comments c
                 JOIN users u ON u.id = c.user_id
                 WHERE c.post_id = ?
                 ORDER BY c.created_at DESC
                 LIMIT 3'
            );
            $commentStmt->execute([$post['id']]);
            $comments = $commentStmt->fetchAll();
            ?>
            <article class="post">
                <div class="post-head">
                    <a class="user-chip" href="profile.php?u=<?= e($post['username']) ?>">
                        <img src="<?= e(avatar_url($post['avatar'])) ?>" alt="">
                        <span><strong><?= e($post['name']) ?></strong><small>@<?= e($post['username']) ?></small></span>
                    </a>
                    <div class="post-meta">
                        <span class="muted"><?= e(time_ago($post['created_at'])) ?></span>
                        <span class="dots">...</span>
                    </div>
                </div>
                <a href="post.php?id=<?= (int) $post['id'] ?>">
                    <img class="post-img" src="uploads/<?= e($post['image']) ?>" alt="Post by <?= e($post['username']) ?>">
                </a>
                <div class="post-body">
                    <?php if ($post['caption']): ?><p class="caption"><strong><?= e($post['username']) ?></strong> <?= e($post['caption']) ?></p><?php endif; ?>
                    <div class="actions">
                        <div class="post-tools">
                            <form action="like.php" method="post">
                                <input type="hidden" name="post_id" value="<?= (int) $post['id'] ?>">
                                <button class="icon-action <?= $post['liked_by_me'] ? 'liked' : '' ?>" type="submit" title="<?= $post['liked_by_me'] ? 'Unlike' : 'Like' ?>">
                                    <?= p_icon('heart') ?>
                                </button>
                            </form>
                            <a class="icon-action" href="post.php?id=<?= (int) $post['id'] ?>" title="Comments"><?= p_icon('comment') ?></a>
                            <a class="icon-action" href="share.php?post_id=<?= (int) $post['id'] ?>" title="Share"><?= p_icon('share') ?></a>
                        </div>
                        <a class="icon-action bookmark" href="post.php?id=<?= (int) $post['id'] ?>" title="Save"><?= p_icon('bookmark') ?></a>
                    </div>
                    <div class="metrics">
                        <span><strong><?= (int) $post['like_count'] ?></strong> likes</span>
                        <span><strong><?= (int) $post['comment_count'] ?></strong> comments</span>
                    </div>
                    <div class="comments">
                        <?php foreach ($comments as $comment): ?><p><strong><?= e($comment['username']) ?></strong> <?= e($comment['body']) ?></p><?php endforeach; ?>
                    </div>
                    <?php if ((int) $post['comment_count'] > 3): ?>
                        <a class="view-comments" href="post.php?id=<?= (int) $post['id'] ?>">View all <?= (int) $post['comment_count'] ?> comments</a>
                    <?php endif; ?>
                </div>
                <form class="comment-form" action="comment.php" method="post">
                    <input type="hidden" name="post_id" value="<?= (int) $post['id'] ?>">
                    <input name="body" placeholder="Add a comment..." required>
                    <button type="submit">Post</button>
                </form>
            </article>
        <?php endforeach; ?>
    </section>

    <aside class="sidebar">
        <div class="profile-mini">
            <img src="<?= e(avatar_url($user['avatar'])) ?>" alt="">
            <div>
                <strong><?= e($user['name']) ?></strong>
                <a href="profile.php?u=<?= e($user['username']) ?>">@<?= e($user['username']) ?></a>
            </div>
        </div>
        <div class="quick-create">
            <strong>Create</strong>
            <p>Share a photo with a caption and keep your profile active.</p>
            <a class="button block" href="create_post.php">Upload photo</a>
        </div>
        <h2>Suggested creators</h2>
        <?php foreach (array_slice($storyPeople, 1) as $person): ?>
            <div class="suggestion">
                <a class="user-chip" href="profile.php?u=<?= e($person['username']) ?>">
                    <img src="<?= e(avatar_url($person['avatar'])) ?>" alt="">
                    <span><strong><?= e($person['name']) ?></strong><small>@<?= e($person['username']) ?></small></span>
                </a>
                <form action="follow.php" method="post">
                    <input type="hidden" name="user_id" value="<?= (int) $person['id'] ?>">
                    <button class="plain" type="submit">Follow</button>
                </form>
            </div>
        <?php endforeach; ?>
    </aside>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
