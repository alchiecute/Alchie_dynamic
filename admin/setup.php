<?php
/**
 * One-time setup: creates the first admin account.
 * It refuses to run once an admin exists. DELETE this file afterwards.
 */
require_once __DIR__ . '/../includes/functions.php';
send_security_headers();
start_secure_session();

$exists = (int)db()->query('SELECT COUNT(*) FROM admins')->fetchColumn() > 0;
$error = '';
$done = false;

if (!$exists && $_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $user = trim((string)($_POST['username'] ?? ''));
    $pass = (string)($_POST['password'] ?? '');
    $conf = (string)($_POST['confirm'] ?? '');

    if (!preg_match('/^[A-Za-z0-9_.-]{3,40}$/', $user)) {
        $error = 'Username must be 3–40 characters: letters, numbers, . _ -';
    } elseif (strlen($pass) < 10) {
        $error = 'Password must be at least 10 characters.';
    } elseif ($pass !== $conf) {
        $error = 'Passwords do not match.';
    } else {
        db()->prepare('INSERT INTO admins (username, password_hash) VALUES (?, ?)')
            ->execute([$user, password_hash($pass, PASSWORD_DEFAULT)]);
        $done = true;
        $exists = true;
    }
}
?>
<!DOCTYPE html>
<html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex"><title>Setup | Admin</title>
<link rel="stylesheet" href="../assets/css/style.css">
<link rel="stylesheet" href="../assets/css/admin.css">
</head><body class="admin-auth">
<div class="login-box panel">
    <h2>Admin setup</h2>
    <?php if ($done): ?>
        <div class="alert alert-success">Admin account created.</div>
        <p><strong>Important:</strong> delete <code>admin/setup.php</code> from your server now.</p>
        <a class="btn primary" href="login.php">Go to login</a>
    <?php elseif ($exists): ?>
        <div class="alert alert-error">Setup is already complete. Delete this file.</div>
        <a class="btn primary" href="login.php">Go to login</a>
    <?php else: ?>
        <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
        <form method="post" autocomplete="off">
            <?= csrf_field() ?>
            <label for="username">Username</label>
            <input type="text" id="username" name="username" required maxlength="40">
            <label for="password">Password (min. 10 characters)</label>
            <input type="password" id="password" name="password" required minlength="10">
            <label for="confirm">Confirm password</label>
            <input type="password" id="confirm" name="confirm" required minlength="10">
            <button class="btn primary" type="submit">Create admin</button>
        </form>
    <?php endif; ?>
</div>
</body></html>
