<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

// Legacy Boundary kept so older links still open the BCE-named create account flow.
// Active BCE route: Boundary/UACreateAccount.php -> Controller/UACreateAccountC.php -> Entity/Account.php.
app_redirect('UACreateAccount.php');
