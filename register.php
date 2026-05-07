<?php
require_once __DIR__ . '/includes/auth.php';
redirect_if_logged_in();

$pageTitle = 'Sign up';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $username = strtolower(trim($_POST['username'] ?? ''));
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';

    if ($name === '' || $username === '' || $email === '' || $password === '') {
        $errors[] = 'All fields are required.';
    }
    if (!preg_match('/^[a-z0-9_]{3,40}$/', $username)) {
        $errors[] = 'Username must be 3-40 chars and use letters, numbers, or underscore.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Enter a valid email.';
    }
    if (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters.';
    }

    if (!$errors) {
        try {
            $stmt = $pdo->prepare('INSERT INTO users (name, username, email, password_hash) VALUES (?, ?, ?, ?)');
            $stmt->execute([$name, $username, $email, password_hash($password, PASSWORD_DEFAULT)]);
            $_SESSION['user_id'] = (int) $pdo->lastInsertId();
            flash('Welcome to Pixora.');
            header('Location: index.php');
            exit;
        } catch (PDOException $e) {
            $errors[] = 'Username or email is already taken.';
        }
    }
}

include __DIR__ . '/includes/header.php';
?>
<section class="auth-wrap">
    <div class="auth-intro">
        <span class="eyebrow">Photo social app</span>
        <h1>Share sharp moments with your circle.</h1>
        <p>Post photos, follow creators, react fast, and keep your profile looking clean.</p>
    </div>
    <div class="auth-card">
        <h2>Create account</h2>
        <?php foreach ($errors as $error): ?><p class="error"><?= e($error) ?></p><?php endforeach; ?>
        <form method="post" class="stack">
            <input name="name" placeholder="Full name" value="<?= e($_POST['name'] ?? '') ?>" required>
            <input name="username" placeholder="Username" value="<?= e($_POST['username'] ?? '') ?>" required>
            <input type="email" name="email" placeholder="Email" value="<?= e($_POST['email'] ?? '') ?>" required>
            <input type="password" name="password" placeholder="Password" required>
            <button class="button" type="submit">Create profile</button>
        </form>
        <p class="muted">Already have an account? <a href="login.php">Login</a></p>
    </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>

