<?php
require_once __DIR__ . '/../config/database.php';

/* ---------- Security headers ---------- */
function send_security_headers(): void
{
    header('X-Frame-Options: DENY');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
    header("Content-Security-Policy: default-src 'self'; "
        . "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; "
        . "font-src https://fonts.gstatic.com; "
        . "img-src 'self' data:; script-src 'self'; "
        . "form-action 'self'; frame-ancestors 'none'; base-uri 'self'");
}

/* ---------- Secure session ---------- */
function start_secure_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    session_name('PORTFOLIOSESSID');
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => $https,
        'httponly' => true,
        'samesite' => 'Strict',
    ]);
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    session_start();

    // Idle timeout: 30 minutes
    if (isset($_SESSION['last_activity']) && time() - $_SESSION['last_activity'] > 1800) {
        session_unset();
        session_destroy();
        session_start();
    }
    $_SESSION['last_activity'] = time();
}

/* ---------- Output escaping (XSS) ---------- */
function e($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/* ---------- CSRF ---------- */
function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function csrf_check(): void
{
    $sent = $_POST['csrf'] ?? '';
    if (!is_string($sent) || !hash_equals($_SESSION['csrf'] ?? '', $sent)) {
        http_response_code(403);
        exit('Invalid or expired form token. Go back, refresh the page and try again.');
    }
}

/* ---------- Helpers ---------- */
function client_ip(): string
{
    return substr($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0', 0, 45);
}

function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

function flash(string $type, string $msg): void
{
    $_SESSION['flash'] = ['type' => $type, 'msg' => $msg];
}

function flash_render(): string
{
    if (empty($_SESSION['flash'])) {
        return '';
    }
    $f = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return '<div class="alert alert-' . e($f['type']) . '">' . e($f['msg']) . '</div>';
}

/* ---------- Site settings (key/value) ---------- */
function settings(): array
{
    static $cache = null;
    if ($cache === null) {
        $cache = [];
        foreach (db()->query('SELECT `key`, `value` FROM settings') as $row) {
            $cache[$row['key']] = $row['value'];
        }
    }
    return $cache;
}

function setting(string $key, string $default = ''): string
{
    $s = settings();
    return $s[$key] ?? $default;
}

/* ---------- Auth ---------- */
function is_admin(): bool
{
    return !empty($_SESSION['admin_id']);
}

function require_admin(): void
{
    if (!is_admin()) {
        redirect('login.php');
    }
}

/* ---------- Image upload (validated) ---------- */
/**
 * Saves an uploaded image safely. Returns relative path (e.g. uploads/projects/abc.jpg)
 * or null when no file was sent. Throws RuntimeException on invalid file.
 */
function handle_image_upload(string $field, string $folder): ?string
{
    if (empty($_FILES[$field]) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    $file = $_FILES[$field];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Upload failed. Please try again.');
    }
    if ($file['size'] > MAX_UPLOAD_BYTES) {
        throw new RuntimeException('Image is too large (max 2 MB).');
    }
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime  = $finfo->file($file['tmp_name']);
    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    if (!isset($allowed[$mime]) || @getimagesize($file['tmp_name']) === false) {
        throw new RuntimeException('Only JPG, PNG or WEBP images are allowed.');
    }
    $name = bin2hex(random_bytes(16)) . '.' . $allowed[$mime];
    $dir  = UPLOAD_DIR . $folder . '/';
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    if (!move_uploaded_file($file['tmp_name'], $dir . $name)) {
        throw new RuntimeException('Could not save the uploaded image.');
    }
    return 'uploads/' . $folder . '/' . $name;
}

function delete_upload(?string $path): void
{
    // Only delete files inside /uploads (never the bundled assets)
    if ($path && strpos($path, 'uploads/') === 0 && strpos($path, '..') === false) {
        $full = BASE_PATH . '/' . $path;
        if (is_file($full)) {
            @unlink($full);
        }
    }
}

/* ---------- Rate limiting ---------- */
function too_many_logins(string $ip): bool
{
    $st = db()->prepare('SELECT COUNT(*) FROM login_attempts
        WHERE ip = ? AND attempted_at > (NOW() - INTERVAL ' . (int)LOGIN_WINDOW_MINUTES . ' MINUTE)');
    $st->execute([$ip]);
    return (int)$st->fetchColumn() >= LOGIN_MAX_ATTEMPTS;
}

function record_failed_login(string $ip, string $username): void
{
    db()->prepare('INSERT INTO login_attempts (ip, username) VALUES (?, ?)')
        ->execute([$ip, substr($username, 0, 60)]);
}

function clear_login_attempts(string $ip): void
{
    db()->prepare('DELETE FROM login_attempts WHERE ip = ?')->execute([$ip]);
}

function too_many_messages(string $ip): bool
{
    $st = db()->prepare('SELECT COUNT(*) FROM messages
        WHERE ip = ? AND created_at > (NOW() - INTERVAL 1 HOUR)');
    $st->execute([$ip]);
    return (int)$st->fetchColumn() >= CONTACT_MAX_PER_HOUR;
}


/* ---------- Visitor tracking (privacy-friendly) ---------- */
/**
 * Records one page view for public pages. Stores only a daily-rotating hash
 * (IP + browser + date + secret), never the raw IP. Skips bots, admins and prefetches.
 * Fails silently so the site keeps working even if the table is missing.
 */
function track_visit(): void
{
    try {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET' || is_admin()) {
            return;
        }
        $ua = (string)($_SERVER['HTTP_USER_AGENT'] ?? '');
        if ($ua === '' || preg_match('/bot|crawl|spider|slurp|curl|wget|preview|monitor|headless/i', $ua)) {
            return;
        }
        $purpose = (string)($_SERVER['HTTP_SEC_PURPOSE'] ?? $_SERVER['HTTP_PURPOSE'] ?? '');
        if (stripos($purpose, 'prefetch') !== false) {
            return;
        }
        $page = substr(basename($_SERVER['SCRIPT_NAME'] ?? 'index.php', '.php'), 0, 40);
        $hash = hash_hmac('sha256', client_ip() . '|' . $ua . '|' . date('Y-m-d'), VISIT_SALT);
        db()->prepare('INSERT INTO visits (visitor_hash, page) VALUES (?, ?)')->execute([$hash, $page]);
    } catch (Throwable $e) {
        error_log('track_visit: ' . $e->getMessage());
    }
}
