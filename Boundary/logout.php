<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

// Boundary-only exit flow:
// Boundary/logout.php clears the session and redirects to Boundary/login.php.
// No Controller or Entity is needed because no database action is performed.
$_SESSION = [];
if (session_status() === PHP_SESSION_ACTIVE) {
    session_destroy();
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
set_flash('success', 'You have been logged out.');
app_redirect('login.php');
