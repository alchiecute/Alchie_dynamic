<?php
require_once __DIR__ . '/admin_boot.php';

$cur = basename($_SERVER['SCRIPT_NAME']);
$pageTitle = $pageTitle ?? 'Admin';

$st = db()->prepare('SELECT username FROM admins WHERE id = ?');
$st->execute([$_SESSION['admin_id']]);
$adminUser = (string)($st->fetchColumn() ?: 'Admin');
$initials = strtoupper(mb_substr($adminUser, 0, 2));
$unreadCount = (int)db()->query('SELECT COUNT(*) FROM messages WHERE is_read = 0')->fetchColumn();

// stroke-based icons (24x24)
$icons = [
    'dash'  => '<rect x="3" y="3" width="7" height="9" rx="1"/><rect x="14" y="3" width="7" height="5" rx="1"/><rect x="14" y="12" width="7" height="9" rx="1"/><rect x="3" y="16" width="7" height="5" rx="1"/>',
    'user'  => '<circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 4-6 8-6s8 2 8 6"/>',
    'folder'=> '<path d="M3 7a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>',
    'chart' => '<path d="M3 3v18h18"/><path d="M7 15l4-4 3 3 5-6"/>',
    'doc'   => '<path d="M6 3h8l4 4v14H6z"/><path d="M14 3v4h4M9 12h6M9 16h6"/>',
    'award' => '<circle cx="12" cy="9" r="6"/><path d="M8.5 14L7 22l5-3 5 3-1.5-8"/>',
    'mail'  => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 7l9 6 9-6"/>',
    'lock'  => '<rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/>',
    'out'   => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/>',
];
$menu = [
    'index.php'        => ['Dashboard', 'dash'],
    'settings.php'     => ['Profile & Contact', 'user'],
    'projects.php'     => ['Projects', 'folder'],
    'skills.php'       => ['Skills', 'chart'],
    'timeline.php'     => ['Resume', 'doc'],
    'certificates.php' => ['Certificates', 'award'],
    'messages.php'     => ['Messages', 'mail'],
    'password.php'     => ['Password', 'lock'],
];
function adm_icon(string $svg): string
{
    return '<svg viewBox="0 0 24 24" aria-hidden="true">' . $svg . '</svg>';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title><?= e($pageTitle) ?> | Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <script src="../assets/js/theme-init.js"></script>
</head>
<body class="admin">
<div class="adm-shell">
    <aside class="adm-side">
        <div class="adm-logo"><?= e(setting('logo_text', 'AC')) ?><span>.</span> Admin</div>
        <div class="adm-menu">
            <?php foreach ($menu as $file => [$label, $icon]): ?>
                <a href="<?= e($file) ?>"<?= $cur === $file ? ' class="active"' : '' ?>>
                    <?= adm_icon($icons[$icon]) ?><?= e($label) ?>
                    <?php if ($file === 'messages.php' && $unreadCount > 0): ?><span class="adm-badge"><?= $unreadCount ?></span><?php endif; ?>
                </a>
            <?php endforeach; ?>
        </div>
        <a class="adm-logout" href="logout.php"><?= adm_icon($icons['out']) ?>Log out</a>
    </aside>

    <div class="adm-main">
        <div class="adm-top">
            <form class="adm-search" action="messages.php" method="get" role="search">
                <input type="text" name="q" placeholder="Search messages..." maxlength="100" value="<?= e($_GET['q'] ?? '') ?>">
                <button type="submit" aria-label="Search">🔍</button>
            </form>
            <div class="adm-pill">
                <a class="site" href="../index.php" target="_blank" rel="noopener">View site</a>
                <button id="themeToggle" type="button">🌙</button>
                <span class="adm-avatar" title="<?= e($adminUser) ?>"><?= e($initials) ?></span>
            </div>
        </div>

        <div class="adm-content admin-wrap">
            <h1><?= e($pageTitle) ?></h1>
            <?= flash_render() ?>
