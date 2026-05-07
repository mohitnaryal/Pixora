<?php
require_once __DIR__ . '/includes/auth.php';
redirect_if_logged_in();

$pageTitle = 'Login';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = strtolower(trim($_POST['login'] ?? ''));
    $password = $_POST['password'] ?? '';
    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ? OR username = ? LIMIT 1');
    $stmt->execute([$login, $login]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = (int) $user['id'];
        header('Location: index.php');
        exit;
    }
    $error = 'Invalid username/email or password.';
}

include __DIR__ . '/includes/header.php';
?>
<section class="auth-wrap">
    <div class="auth-intro">
        <span class="eyebrow">Welcome back</span>
        <h1>Your feed is waiting.</h1>
        <p>Jump back into fresh posts, comments, profiles, and creator updates.</p>
    </div>
    <div class="auth-card">
        <h2>Login</h2>
        <?php if ($error): ?><p class="error"><?= e($error) ?></p><?php endif; ?>
        <form method="post" class="stack">
            <input name="login" placeholder="Username or email" required>
            <input type="password" name="password" placeholder="Password" required>
            <button class="button" type="submit">Open feed</button>
        </form>
        <p class="muted">New here? <a href="register.php">Create account</a></p>
    </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>

