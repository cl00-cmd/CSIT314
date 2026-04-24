<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

// Boundary-only logout flow for the Fund Raiser BCE sequence.
// It uses the same session-clearing method as the User Admin logout boundary.
$_SESSION = [];
if (session_status() === PHP_SESSION_ACTIVE) {
    session_destroy();
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
set_flash('success', 'You have been logged out.');
app_redirect('login.php');
