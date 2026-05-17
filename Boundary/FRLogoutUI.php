<?php
declare(strict_types=1);

// Load system setup and helper functions
require_once __DIR__ . '/../bootstrap.php';

// BCE route:
// Boundary/FRLogoutUI.php -> Boundary/logout.php.
// Logout is boundary-only here: Boundary/logout.php clears the session and returns to Boundary/login.php.
app_redirect('logout.php');
