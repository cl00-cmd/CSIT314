<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

// BCE route:
// Boundary/FRHistoryDateSearchUI.php -> Controller/FRHistoryDateSearchController.php -> Entity/FundraisingActivity.php.
require_login(['fund_raiser']);

$query = [];
foreach (['service_type', 'from', 'to'] as $key) {
    if (isset($_GET[$key]) && trim((string) $_GET[$key]) !== '') {
        $query[$key] = trim((string) $_GET[$key]);
    }
}

app_redirect('FRHistorySearchUI.php', $query);
