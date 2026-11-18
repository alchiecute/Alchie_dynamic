<?php
require __DIR__ . '/../includes/admin_boot.php';
$pageTitle = 'Change password';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $cur  = (string)($_POST['current'] ?? '');
    $new  = (string)($_POST['new'] ?? '');
    $conf = (string)($_POST['confirm'] ?? '');

    $st = db()->prepare('SELECT password_hash FROM admins WHERE id = ?');
    $st->execute([$_SESSION['admin_id']]);
    $hash = $st->fetchColumn();

    if (!$hash || !password_verify($cur, $hash)) {
        flash('error', 'Current password is incorrect.');
    } elseif (strlen($new) < 10) {
        flash('error', 'New password must be at least 10 characters.');
    } elseif ($new !== $conf) {
        flash('error', 'New passwords do not match.');
    } else {
        db()->prepare('UPDATE admins SET password_hash = ? WHERE id = ?')
            ->execute([password_hash($new, PASSWORD_DEFAULT), $_SESSION['admin_id']]);
        session_regenerate_id(true);
        flash('success', 'Password changed.');
    }
    redirect('password.php');
}
require __DIR__ . '/../includes/admin_header.php';
?>
<form method="post" class="panel" autocomplete="off" style="max-width:480px">
    <?= csrf_field() ?>
    <div class="form-grid">
        <div class="full"><label for="current">Current password</label><input type="password" id="current" name="current" required></div>
        <div class="full"><label for="new">New password (min. 10 characters)</label><input type="password" id="new" name="new" minlength="10" required></div>
        <div class="full"><label for="confirm">Confirm new password</label><input type="password" id="confirm" name="confirm" minlength="10" required></div>
    </div>
    <p><button class="btn primary" type="submit">Change password</button></p>
</form>
<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
