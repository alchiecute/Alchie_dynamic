<?php
require_once __DIR__ . '/boot.php';
track_visit();

$current = basename($_SERVER['SCRIPT_NAME']);
$pageTitle = $pageTitle ?? 'Portfolio';
$nav = [
    'index.php'    => 'Home',
    'about.php'    => 'About',
    'skills.php'   => 'Skills',
    'projects.php' => 'Projects',
    'resume.php'   => 'Resume',
    'contact.php'  => 'Contact',
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?> | <?= e(setting('full_name', SITE_NAME)) ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="assets/js/theme-init.js"></script>
</head>
<body>

<header>
    <div class="logo"><?= e(setting('logo_text', 'AC')) ?><span>.</span></div>
    <button class="menu-toggle" id="menuToggle" type="button" aria-label="Toggle menu">☰</button>

    <nav id="navbar">
        <?php foreach ($nav as $file => $label): ?>
            <a href="<?= e($file) ?>"<?= $current === $file ? ' class="active"' : '' ?>><?= e($label) ?></a>
        <?php endforeach; ?>
        <button id="themeToggle" type="button">🌙</button>
    </nav>
</header>

<main>
