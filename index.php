<?php
$pageTitle = 'Portfolio';
require __DIR__ . '/includes/header.php';
?>

<section class="hero">
    <div class="hero-content">
        <p class="eyebrow">HELLO, I'M</p>
        <h1><?= e(setting('first_name', 'Alchie')) ?> <span><?= e(setting('last_name', 'Chavez')) ?></span></h1>
        <h2><span id="typing" data-words="<?= e(setting('typing_words', 'IT Student,Web Developer')) ?>"></span></h2>
        <p><?= e(setting('intro')) ?></p>

        <div class="buttons">
            <a href="projects.php" class="btn primary">View My Projects</a>
            <a href="contact.php" class="btn secondary">Contact Me</a>
        </div>
    </div>

    <div class="profile-container">
        <img src="<?= e(setting('profile_image', 'assets/images/profile1.png')) ?>" alt="Profile image">
    </div>
</section>

<section class="welcome">
    <p class="eyebrow">WELCOME TO MY PORTFOLIO</p>
    <h2>Turning Ideas Into Digital Experiences</h2>
    <p>Explore my background, technical skills, academic projects, experience, and learning journey.</p>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
