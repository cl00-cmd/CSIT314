<?php
declare(strict_types=1);

// Loads the application bootstrap file.
require_once __DIR__ . '/bootstrap.php';

// Redirects users to the shared login Boundary page.
app_redirect('Boundary/login.php');