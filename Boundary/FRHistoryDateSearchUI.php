<?php
declare(strict_types=1);

// Load system setup, helper functions, and required files
require_once __DIR__ . '/../bootstrap.php';

// BCE route:
// Boundary/FRHistoryDateSearchUI.php -> Controller/FRHistoryDateSearchController.php -> Entity/FundraisingActivity.php.
// This Boundary only collects the date filters, then redirects to Boundary/FRHistorySearchUI.php to show the results.
require_login(['fund_raiser']);

// Collect valid search filters from the request.
$query = [];
foreach (['service_type', 'from', 'to'] as $key) {
    if (isset($_GET[$key]) && trim((string) $_GET[$key]) !== '') {
        $query[$key] = trim((string) $_GET[$key]);
    }
}

// Redirect to the main history search page with the selected filters.
app_redirect('FRHistorySearchUI.php', $query);