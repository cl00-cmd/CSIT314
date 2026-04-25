<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

// BCE route:
// Boundary/FRLoginAccount.php -> Boundary/login.php -> Controller/LoginController.php -> Entity/UserEntity.php.
// This old Fund Raiser login Boundary now redirects into the shared login flow used by all roles.
app_redirect('login.php');
