<?php
declare(strict_types=1);

// Load system setup and helper functions
require_once __DIR__ . '/../bootstrap.php';

// Boundary-only exit flow:
// Boundary/logout.php clears the session and redirects to Boundary/login.php.
// All roles use this shared logout Boundary.
// No Controller or Entity is needed because no database action is performed.

// Clears all current session data.
$_SESSION = [];

// Destroys the active session.
if (session_status() === PHP_SESSION_ACTIVE) {
    session_destroy();
}

// Starts a new session to store the logout flash message.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Sets logout success message.
set_flash('success', 'You have been logged out.');

// Redirects user back to the login page.
app_redirect('login.php');