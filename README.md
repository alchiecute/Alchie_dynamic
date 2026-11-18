# Secure Dynamic Personal Portfolio (PHP + MySQL)

Built from your original static portfolio (same design, same content).
Pages are now rendered from MySQL and managed from an admin dashboard.

## Requirements
PHP 8.0+ (PDO MySQL, fileinfo, mbstring), MySQL/MariaDB, Apache (XAMPP / Laragon / WAMP work).

## Install (XAMPP)
1. Copy the `portfolio` folder to `C:\xampp\htdocs\portfolio`.
2. Start Apache and MySQL, open phpMyAdmin → Import → choose `database.sql`.
3. Edit `config/config.php` (DB_USER / DB_PASS if yours differ).
4. Open http://localhost/portfolio/admin/setup.php, create your admin account, then DELETE `admin/setup.php`.
5. Public site: http://localhost/portfolio/ — Admin: http://localhost/portfolio/admin/login.php

## Visitor tracking
Public pages record page views (admin visits, bots and prefetches are ignored). Only a daily-rotating
hash of IP + browser is stored, never the raw IP. Unique visitors = unique per day.
Already imported the database before? Import `migration_visits.sql` once. Set `VISIT_SALT` in `config/config.php`.

## Security features
- PDO prepared statements (SQL injection) • htmlspecialchars on all output (XSS)
- CSRF tokens on every form • password_hash/password_verify (bcrypt)
- Session hardening: HttpOnly, SameSite=Strict, ID regeneration on login, idle timeout
- Login rate limiting (5 failures / 15 min / IP) • contact form honeypot + 5 messages/hour/IP
- Validated uploads (MIME via finfo, size limit, random filenames, PHP execution disabled in /uploads)
- Security headers incl. Content-Security-Policy • config/includes blocked by .htaccess
- Server-side validation (JS validation is only a convenience)

## Fixes to your original code
- Regex in the form validator was double-escaped (`\\s`) so every email failed — fixed.
- "View My Projects" pointed to `projects1.html` — now `projects.php`.
- Contact number link was `https://09750453688/` — now `tel:`.
- JavaScript skill showed 70% but bar was 80% — both now come from one DB value.

## Before going live
Set DEBUG=false (default), use HTTPS (uncomment the rule in .htaccess), use a dedicated MySQL user
(not root) with a strong password, delete admin/setup.php, and back up the database.
