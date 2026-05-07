<?php
$me = current_user();
$pageTitle = $pageTitle ?? 'Pixora';
$flash = flash();
$active = basename($_SERVER['PHP_SELF']);
$cssVersion = filemtime(__DIR__ . '/../assets/css/style.css');
$logoVersion = filemtime(__DIR__ . '/../assets/logo.svg');
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?> - Pixora</title>
    <link rel="stylesheet" href="assets/css/style.css?v=<?= $cssVersion ?>">
</head>
<body>
<header class="topbar">
    <a class="brand" href="index.php"><img src="assets/logo.svg?v=<?= $logoVersion ?>" alt="Pixora"></a>
    <form class="search" action="search.php" method="get">
        <span></span>
        <input type="search" name="q" placeholder="Search creators" value="<?= e($_GET['q'] ?? '') ?>">
    </form>
    <nav class="nav">
        <?php if ($me): ?>
            <a class="<?= $active === 'index.php' ? 'active' : '' ?>" href="index.php"><?= p_icon('home') ?><span>Feed</span></a>
            <a class="<?= $active === 'create_post.php' ? 'active' : '' ?>" href="create_post.php"><?= p_icon('plus') ?><span>Create</span></a>
            <a class="<?= $active === 'messages.php' ? 'active' : '' ?>" href="messages.php"><?= p_icon('message') ?><span>Messages</span></a>
            <a class="<?= $active === 'profile.php' ? 'active' : '' ?>" href="profile.php?u=<?= e($me['username']) ?>"><?= p_icon('user') ?><span>Profile</span></a>
            <a class="<?= $active === 'account.php' ? 'active' : '' ?>" href="account.php"><?= p_icon('settings') ?><span>Account</span></a>
            <a href="logout.php"><?= p_icon('logout') ?><span>Logout</span></a>
        <?php else: ?>
            <a href="login.php">Login</a>
            <a class="button small" href="register.php">Sign up</a>
        <?php endif; ?>
    </nav>
</header>

<?php if ($flash): ?>
    <div class="flash <?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
<?php endif; ?>

<main class="shell">
