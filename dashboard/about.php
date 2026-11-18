<?php
$pageTitle = 'About';
require __DIR__ . '/includes/header.php';

$goals = db()->query('SELECT * FROM goals ORDER BY sort_order, id')->fetchAll();
?>

<section class="page-header">
    <p class="eyebrow">ABOUT ME</p>
    <h1>My Background</h1>
    <p>Learn more about my education, goals, interests, and background.</p>
</section>

<section class="section">
    <div class="two-column">
        <div>
            <h2>Who I Am</h2>
            <?php foreach (preg_split('/\R{2,}/', trim(setting('about_text'))) as $para): ?>
                <p><?= nl2br(e($para)) ?></p>
            <?php endforeach; ?>
        </div>

        <div class="info-card">
            <h3>Quick Information</h3>
            <p><strong>Education:</strong> <?= e(setting('education')) ?></p>
            <p><strong>Focus:</strong> <?= e(setting('focus')) ?></p>
            <p><strong>Goal:</strong> <?= e(setting('goal')) ?></p>
            <p><strong>Interests:</strong> <?= e(setting('interests')) ?></p>
        </div>
    </div>
</section>

<section class="section light">
    <div class="container">
        <h2>My Goals</h2>
        <div class="cards">
            <?php foreach ($goals as $i => $g): ?>
                <div class="card">
                    <span><?= str_pad((string)($i + 1), 2, '0', STR_PAD_LEFT) ?></span>
                    <h3><?= e($g['title']) ?></h3>
                    <p><?= e($g['description']) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
