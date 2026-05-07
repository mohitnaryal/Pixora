<?php
require_once __DIR__ . '/includes/auth.php';
$user = require_login();
$pageTitle = 'Create post';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $image = upload_image($_FILES['image'] ?? [], 'post');
        if (!$image) {
            throw new RuntimeException('Please choose an image.');
        }
        $caption = trim($_POST['caption'] ?? '');
        $stmt = $pdo->prepare('INSERT INTO posts (user_id, image, caption) VALUES (?, ?, ?)');
        $stmt->execute([$user['id'], $image, $caption]);
        flash('Post uploaded.');
        header('Location: index.php');
        exit;
    } catch (RuntimeException $e) {
        $error = $e->getMessage();
    }
}

include __DIR__ . '/includes/header.php';
?>
<section class="panel narrow upload-panel">
    <span class="eyebrow">Create</span>
    <h1>Share a new photo</h1>
    <?php if ($error): ?><p class="error"><?= e($error) ?></p><?php endif; ?>
    <form method="post" enctype="multipart/form-data" class="stack">
        <label class="file-drop">
            <input type="file" name="image" accept="image/*" required>
            <span>Choose image</span>
            <small>JPG, PNG, WEBP, or GIF up to 5 MB</small>
        </label>
        <textarea name="caption" rows="4" placeholder="Write a caption..."></textarea>
        <button class="button" type="submit">Share post</button>
    </form>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>

