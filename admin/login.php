<?php
require_once __DIR__ . '/../includes/functions.php';
send_security_headers();
start_secure_session();

if (is_admin()) {
    redirect('index.php');
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $ip   = client_ip();
    $user = trim((string)($_POST['username'] ?? ''));
    $pass = (string)($_POST['password'] ?? '');

    if (too_many_logins($ip)) {
        $error = 'Too many failed attempts. Try again in ' . LOGIN_WINDOW_MINUTES . ' minutes.';
    } else {
        $st = db()->prepare('SELECT id, password_hash FROM admins WHERE username = ? LIMIT 1');
        $st->execute([$user]);
        $row = $st->fetch();

        // Always run password_verify so response time doesn't reveal valid usernames
        $hash = $row['password_hash'] ?? '$2y$10$usesomesillystringforsaltdummyhashvalueXXXXXXXXXXXXXXXXXXXX';
        $ok = password_verify($pass, $hash) && $row;

        if ($ok) {
            session_regenerate_id(true);          // prevent session fixation
            $_SESSION['admin_id'] = (int)$row['id'];
            $_SESSION['csrf'] = bin2hex(random_bytes(32));
            clear_login_attempts($ip);
            if (password_needs_rehash($row['password_hash'], PASSWORD_DEFAULT)) {
                db()->prepare('UPDATE admins SET password_hash = ? WHERE id = ?')
                    ->execute([password_hash($pass, PASSWORD_DEFAULT), $row['id']]);
            }
            redirect('index.php');
        }
        record_failed_login($ip, $user);
        $error = 'Invalid username or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex"><title>Admin Login</title>
<link rel="stylesheet" href="../assets/css/style.css">
<link rel="stylesheet" href="../assets/css/admin.css">
<script src="../assets/js/theme-init.js"></script>
</head><body class="admin-auth">
<div class="login-box panel">
    <h2>Admin login</h2>
    <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
    <form method="post" autocomplete="off">
        <?= csrf_field() ?>
        <label for="username">Username</label>
        <input type="text" id="username" name="username" required maxlength="60" autofocus>
        <label for="password">Password</label>
        <input type="password" id="password" name="password" required>
        <button class="btn primary" type="submit">Log in</button>
    </form>
</div>
</body></html>
