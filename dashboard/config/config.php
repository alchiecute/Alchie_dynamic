<?php
/**
 * Application configuration.
 * Change the database credentials to match your MySQL server.
 */

// ---- Database ----
define('DB_HOST', 'localhost');
define('DB_NAME', 'portfolio_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// ---- Site ----
define('SITE_NAME', 'Alchie Chavez');
define('BASE_PATH', dirname(__DIR__));
define('UPLOAD_DIR', BASE_PATH . '/uploads/');
define('MAX_UPLOAD_BYTES', 2 * 1024 * 1024); // 2 MB

// ---- Visitor tracking ----
// Random secret used to hash visitors (raw IP addresses are never stored).
// Change this to your own random string.
define('VISIT_SALT', 'change-this-to-a-long-random-string');

// ---- Security limits ----
define('LOGIN_MAX_ATTEMPTS', 5);      // failed logins allowed...
define('LOGIN_WINDOW_MINUTES', 15);   // ...within this many minutes
define('CONTACT_MAX_PER_HOUR', 5);    // messages per IP per hour

// Set to true only while developing
define('DEBUG', false);

if (DEBUG) {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    error_reporting(E_ALL);
    ini_set('log_errors', '1');
}
