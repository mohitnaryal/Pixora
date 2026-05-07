<?php
require_once __DIR__ . '/includes/auth.php';
$user = require_login();
$pageTitle = 'Account';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $name = trim($_POST['name'] ?? '');
        $bio = trim($_POST['bio'] ?? '');
        if ($name === '') {
            throw new RuntimeException('Name is required.');
        }
        $avatar = upload_image($_FILES['avatar'] ?? [], 'avatar') ?: $user['avatar'];
        $stmt = $pdo->prepare('UPDATE users SET name = ?, bio = ?, avatar = ? WHERE id = ?');
        $stmt->execute([$name, $bio, $avatar, $user['id']]);
        flash('Account updated.');
        header('Location: account.php');
        exit;
    } catch (RuntimeException $e) {
        $error = $e->getMessage();
    }
}

include __DIR__ . '/includes/header.php';
?>
<section class="panel narrow">
    <span class="eyebrow">Settings</span>
    <h1>Account</h1>
    <?php if ($error): ?><p class="error"><?= e($error) ?></p><?php endif; ?>
    <form method="post" enctype="multipart/form-data" class="stack">
        <img class="avatar-preview" src="<?= e(avatar_url($user['avatar'])) ?>" alt="">
        <input name="name" value="<?= e($user['name']) ?>" required>
        <textarea name="bio" rows="4" maxlength="240" placeholder="Bio"><?= e($user['bio']) ?></textarea>
        <label class="file-drop compact">
            <input type="file" name="avatar" accept="image/*">
            <span>Update avatar</span>
        </label>
        <button class="button" type="submit">Save changes</button>
    </form>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>

