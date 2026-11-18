<?php
require __DIR__ . '/includes/boot.php';
$pageTitle = 'Contact';

$errors = [];
$old = ['name' => '', 'email' => '', 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    $old['name']    = trim((string)($_POST['name'] ?? ''));
    $old['email']   = trim((string)($_POST['email'] ?? ''));
    $old['message'] = trim((string)($_POST['message'] ?? ''));

    // Honeypot: real users never fill this hidden field
    if (!empty($_POST['website'])) {
        redirect('contact.php');
    }

    if (mb_strlen($old['name']) < 2 || mb_strlen($old['name']) > 100) {
        $errors['name'] = 'Please enter your name (2–100 characters).';
    }
    if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL) || mb_strlen($old['email']) > 150) {
        $errors['email'] = 'Please enter a valid email address.';
    }
    if (mb_strlen($old['message']) < 10 || mb_strlen($old['message']) > 2000) {
        $errors['message'] = 'Message must be 10–2000 characters.';
    }
    if (!$errors && too_many_messages(client_ip())) {
        $errors['form'] = 'You have sent too many messages. Please try again later.';
    }

    if (!$errors) {
        db()->prepare('INSERT INTO messages (name, email, message, ip) VALUES (?, ?, ?, ?)')
            ->execute([$old['name'], $old['email'], $old['message'], client_ip()]);
        flash('success', 'Thank you! Your message has been sent.');
        redirect('contact.php');   // Post/Redirect/Get
    }
}

$phone = setting('phone');
$phoneHref = preg_replace('/[^0-9+]/', '', $phone);
$github = setting('github_url');
$linkedin = setting('linkedin_url');
require __DIR__ . '/includes/header.php';
?>

<section class="page-header">
    <p class="eyebrow">LET'S CONNECT</p>
    <h1>Contact Me</h1>
    <p>Have a question or project idea? Send me a message.</p>
</section>

<section class="section">
    <div class="contact-grid">
        <div>
            <h2>Contact Information</h2>
            <p><strong>Email:</strong> <a href="mailto:<?= e(setting('email')) ?>"><?= e(setting('email')) ?></a></p>
            <?php if (preg_match('#^https?://#i', $github)): ?>
                <p><strong>GitHub:</strong> <a href="<?= e($github) ?>" target="_blank" rel="noopener noreferrer"><?= e(preg_replace('#^https?://(www\.)?#i', '', $github)) ?></a></p>
            <?php endif; ?>
            <?php if (preg_match('#^https?://#i', $linkedin)): ?>
                <p><strong>LinkedIn:</strong> <a href="<?= e($linkedin) ?>" target="_blank" rel="noopener noreferrer"><?= e(preg_replace('#^https?://(www\.)?#i', '', $linkedin)) ?></a></p>
            <?php endif; ?>
            <p><strong>Contact#:</strong> <a href="tel:<?= e($phoneHref) ?>"><?= e($phone) ?></a></p>

            <div class="info-card">
                <h3>Available For</h3>
                <p><?= e(setting('available_for')) ?></p>
            </div>
        </div>

        <form id="contactForm" class="contact-form" method="post" action="contact.php" novalidate>
            <?= flash_render() ?>
            <?php if (!empty($errors['form'])): ?><div class="alert alert-error"><?= e($errors['form']) ?></div><?php endif; ?>
            <?= csrf_field() ?>

            <!-- Honeypot (hidden from humans) -->
            <div class="hp" aria-hidden="true">
                <label for="website">Leave this empty</label>
                <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
            </div>

            <label for="name">Name</label>
            <input type="text" id="name" name="name" maxlength="100" placeholder="Your name" value="<?= e($old['name']) ?>" required>
            <span id="nameError" class="error"><?= e($errors['name'] ?? '') ?></span>

            <label for="email">Email</label>
            <input type="email" id="email" name="email" maxlength="150" placeholder="you@example.com" value="<?= e($old['email']) ?>" required>
            <span id="emailError" class="error"><?= e($errors['email'] ?? '') ?></span>

            <label for="message">Message</label>
            <textarea id="message" name="message" rows="6" maxlength="2000" placeholder="Write your message..." required><?= e($old['message']) ?></textarea>
            <span id="messageError" class="error"><?= e($errors['message'] ?? '') ?></span>

            <button type="submit" class="btn primary">Send Message</button>
        </form>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
