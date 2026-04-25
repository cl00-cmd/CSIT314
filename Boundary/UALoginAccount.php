<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

// Legacy Boundary kept only so old UALoginAccount.php links do not break.
// The actual BCE login flow is now Boundary/login.php -> Controller/LoginController.php -> Entity/UserEntity.php.
app_redirect('login.php');
