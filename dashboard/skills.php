<?php
$pageTitle = 'Skills';
require __DIR__ . '/includes/header.php';

$skills = db()->query('SELECT * FROM skills ORDER BY sort_order, id')->fetchAll();
?>

<section class="page-header">
    <p class="eyebrow">WHAT I CAN DO</p>
    <h1>My Skills</h1>
    <p>Technical and creative skills that I continue to develop.</p>
</section>

<section class="section">
    <div class="skills-grid">
        <?php foreach ($skills as $s): $pct = max(0, min(100, (int)$s['percent'])); ?>
            <div class="skill">
                <div class="skill-title">
                    <strong><?= e($s['name']) ?></strong>
                    <span><?= $pct ?>%</span>
                </div>
                <div class="progress"><div style="width:<?= $pct ?>%"></div></div>
            </div>
        <?php endforeach; ?>
        <?php if (!$skills): ?><p>No skills added yet.</p><?php endif; ?>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
