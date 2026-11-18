<?php
// Auth + session only (no output) so pages can redirect after handling POST.
require_once __DIR__ . '/functions.php';
send_security_headers();
start_secure_session();
require_admin();
