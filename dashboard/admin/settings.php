<?php
require __DIR__ . '/../includes/admin_boot.php';
$pageTitle = 'Profile & Contact';

// field => [label, type, max length]
$fields = [
    'logo_text'     => ['Logo text', 'text', 6],
    'full_name'     => ['Full name', 'text', 80],
    'first_name'    => ['First name (home)', 'text', 40],
    'last_name'     => ['Last name (home, highlighted)', 'text', 40],
    'typing_words'  => ['Typing words (comma separated)', 'text', 200],
    'intro'         => ['Home introduction', 'area', 500],
    'about_text'    => ['About text (blank line = new paragraph)', 'area', 2000],
    'education'     => ['Education', 'text', 120],
    'focus'         => ['Focus', 'text', 120],
    'goal'          => ['Goal', 'text', 120],
    'interests'     => ['Interests', 'text', 200],
    'email'         => ['Email', 'email', 150],
    'phone'         => ['Phone', 'text', 30],
    'github_url'    => ['GitHub URL', 'url', 200],
    'linkedin_url'  => ['LinkedIn URL', 'url', 200],
    'available_for' => ['Available for', 'area', 400],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $errors = [];
    $values = [];
    foreach ($fields as $key => [$label, $type, $max]) {
        $v = trim((string)($_POST[$key] ?? ''));
        if (mb_strlen($v) > $max) { $errors[] = "$label is too long (max $max)."; }
        if ($type === 'email' && !filter_var($v, FILTER_VALIDATE_EMAIL)) { $errors[] = 'Email is not valid.'; }
        if ($type === 'url' && $v !== '' && !preg_match('#^https?://#i', $v)) { $errors[] = "$label must start with http:// or https://"; }
        $values[$key] = $v;
    }
    try {
        $new = handle_image_upload('profile_image', 'profile');
    } catch (RuntimeException $ex) { $errors[] = $ex->getMessage(); $new = null; }

    if ($errors) {
        flash('error', implode(' ', $errors));
    } else {
        $st = db()->prepare('INSERT INTO settings (`key`, `value`) VALUES (?, ?)
                             ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)');
        foreach ($values as $k => $v) { $st->execute([$k, $v]); }
        if ($new) {
            delete_upload(setting('profile_image'));
            $st->execute(['profile_image', $new]);
        }
        flash('success', 'Settings saved.');
    }
    redirect('settings.php');
}
require __DIR__ . '/../includes/admin_header.php';
?>
<form method="post" enctype="multipart/form-data" class="panel">
    <?= csrf_field() ?>
    <div class="form-grid">
        <?php foreach ($fields as $key => [$label, $type, $max]): ?>
            <div class="<?= $type === 'area' ? 'full' : '' ?>">
                <label for="<?= e($key) ?>"><?= e($label) ?></label>
                <?php if ($type === 'area'): ?>
                    <textarea id="<?= e($key) ?>" name="<?= e($key) ?>" rows="4" maxlength="<?= $max ?>"><?= e(setting($key)) ?></textarea>
                <?php else: ?>
                    <input type="text" id="<?= e($key) ?>" name="<?= e($key) ?>" maxlength="<?= $max ?>" value="<?= e(setting($key)) ?>">
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
        <div class="full">
            <label for="profile_image">Profile image (JPG/PNG/WEBP, max 2 MB)</label>
            <input type="file" id="profile_image" name="profile_image" accept="image/png,image/jpeg,image/webp">
        </div>
    </div>
    <p><button class="btn primary" type="submit">Save changes</button></p>
</form>
<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
