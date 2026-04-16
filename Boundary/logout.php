<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

$_SESSION = [];
if (session_status() === PHP_SESSION_ACTIVE) {
    session_destroy();
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
set_flash('success', 'You have been logged out.');
app_redirect('login.php');
