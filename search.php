<?php
require_once __DIR__ . '/includes/auth.php';
$me = require_login();
$pageTitle = 'Search';
$q = trim($_GET['q'] ?? '');
$people = [];

if ($q !== '') {
    $stmt = $pdo->prepare(
        'SELECT id, name, username, avatar, bio FROM users WHERE username LIKE ? OR name LIKE ? ORDER BY username LIMIT 30'
    );
    $term = '%' . $q . '%';
    $stmt->execute([$term, $term]);
    $people = $stmt->fetchAll();
}

include __DIR__ . '/includes/header.php';
?>
<section class="panel">
    <span class="eyebrow">Discover</span>
    <h1>Search creators</h1>
    <?php if ($q === ''): ?><p class="muted">Search by name or username.</p><?php elseif (!$people): ?><p class="muted">No users found for "<?= e($q) ?>".</p><?php endif; ?>
    <div class="people-list">
        <?php foreach ($people as $person): ?>
            <a class="person-row" href="profile.php?u=<?= e($person['username']) ?>">
                <img src="<?= e(avatar_url($person['avatar'])) ?>" alt="">
                <span><strong><?= e($person['name']) ?></strong><small>@<?= e($person['username']) ?></small></span>
                <em><?= e($person['bio']) ?></em>
            </a>
        <?php endforeach; ?>
    </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>

