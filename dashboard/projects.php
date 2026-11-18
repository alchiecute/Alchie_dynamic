<?php
$pageTitle = 'Projects';
require __DIR__ . '/includes/header.php';

$projects = db()->query('SELECT * FROM projects ORDER BY sort_order, id DESC')->fetchAll();
?>

<section class="page-header">
    <p class="eyebrow">MY WORK</p>
    <h1>Projects</h1>
    <p>Some of my academic and creative projects.</p>
</section>

<section class="section">
    <div class="filters">
        <button class="filter active" data-filter="all" type="button">All</button>
        <button class="filter" data-filter="web" type="button">Web</button>
        <button class="filter" data-filter="design" type="button">Design</button>
        <button class="filter" data-filter="animation" type="button">Animation</button>
    </div>

    <div class="project-grid">
        <?php foreach ($projects as $p): ?>
            <article class="project-card" data-category="<?= e($p['category']) ?>">
                <img src="<?= e($p['image']) ?>" alt="<?= e($p['title']) ?>">
                <div class="project-content">
                    <span class="tag"><?= e(ucfirst($p['category'])) ?></span>
                    <h3><?= e($p['title']) ?></h3>
                    <p><?= e($p['description']) ?></p>
                    <small><?= e($p['tech']) ?></small>
                    <button class="details-btn" type="button"
                            data-title="<?= e($p['title']) ?>"
                            data-description="<?= e($p['details'] ?: $p['description']) ?>">
                        View Details
                    </button>
                </div>
            </article>
        <?php endforeach; ?>
        <?php if (!$projects): ?><p>No projects added yet.</p><?php endif; ?>
    </div>
</section>

<div class="modal" id="projectModal">
    <div class="modal-box">
        <button id="closeModal" type="button" aria-label="Close">×</button>
        <h2 id="modalTitle"></h2>
        <p id="modalDescription"></p>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
