<?php
$pageTitle = 'Resume';
require __DIR__ . '/includes/header.php';

$timeline = db()->query('SELECT * FROM timeline ORDER BY sort_order, id')->fetchAll();
$certs    = db()->query('SELECT * FROM certificates ORDER BY id DESC')->fetchAll();
?>

<section class="page-header">
    <p class="eyebrow">MY JOURNEY</p>
    <h1>Resume / Experience</h1>
    <p>My education, activities, training, and experience.</p>
</section>

<section class="section">
    <div class="timeline">
        <?php foreach ($timeline as $t): ?>
            <div class="timeline-item">
                <span class="timeline-dot"></span>
                <p class="eyebrow"><?= e(strtoupper($t['label'])) ?></p>
                <h2><?= e($t['title']) ?></h2>
                <p><?= e($t['description']) ?></p>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<section class="welcome">
    <h2>Certifications</h2>
    <div class="cert-grid">
        <?php foreach ($certs as $c): ?>
            <figure class="cert-item">
                <img src="<?= e($c['image']) ?>" alt="<?= e($c['title']) ?>" class="certificate-img">
                <figcaption><?= e($c['title']) ?></figcaption>
            </figure>
        <?php endforeach; ?>
        <?php if (!$certs): ?><p>No certificates added yet.</p><?php endif; ?>
    </div>
    <a href="contact.php" class="btn primary">Contact Me</a>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
