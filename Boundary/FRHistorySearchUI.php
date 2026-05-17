<?php
declare(strict_types=1);

// Load system setup, shared fundraiser layout, and helper functions
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/fundraiser_shared.php';

use App\Controller\FRHistorySearchController;

// BCE route:
// Boundary/FRHistorySearchUI.php -> Controller/FRHistorySearchController.php -> Entity/FundraisingActivity.php.
// This Boundary shows completed Fund Raiser history and passes service/date filters to the Controller.
require_login(['fund_raiser']);

// Retrieves the logged-in fundraiser.
$user = current_user();

// Boundary -> Controller to retrieve fundraising history.
$controller = new FRHistorySearchController();

// Gets search filters entered by the fundraiser.
$serviceType = trim((string) ($_GET['service_type'] ?? ''));
$fromDate = trim((string) ($_GET['from'] ?? ''));
$toDate = trim((string) ($_GET['to'] ?? ''));

// Validates the search criteria before searching.
$validation = $controller->validateCriteria($serviceType, $fromDate, $toDate);

// Gets completed fundraising activity history if validation succeeds.
$history = $validation['success']
    ? $controller->searchHistory((int) $user['id'], $serviceType, $fromDate, $toDate)
    : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fundraising History</title>
    <link rel="stylesheet" href="../assets/css/app.css">
</head>
<body>

    <!-- Fundraiser top navigation bar -->
    <?php render_fundraiser_topbar('Fundraising History', 'history'); ?>

    <main class="page-shell">

        <!-- Display success or error message if available -->
        <?php render_fundraiser_flash_if_any(); ?>

        <!-- Display validation error message -->
        <?php if (!$validation['success']): ?>
            <div class="flash flash--error">
                <?= e($validation['message']) ?>
            </div>
        <?php endif; ?>

        <!-- Fundraising history search section -->
        <section class="panel">
            <div class="panel__header panel__header--stack">
                <div>
                    <h2>View completed fundraising activity history</h2>
                </div>

                <!-- Search and filter form -->
                <form method="get" class="inline-filters">

                    <select name="service_type">
                        <option value="">All services</option>

                        <!-- Display available service types -->
                        <?php foreach ($controller->getServiceTypes() as $service): ?>
                            <option value="<?= e($service) ?>" <?= selected_if($serviceType, $service) ?>>
                                <?= e($service) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <input type="date"
                           name="from"
                           value="<?= e($fromDate) ?>"
                           aria-label="From date">

                    <input type="date"
                           name="to"
                           value="<?= e($toDate) ?>"
                           aria-label="To date">

                    <button type="submit" class="button button--ghost">
                        Search
                    </button>

                    <a class="button button--ghost"
                       href="FRHistorySearchUI.php">
                        Clear
                    </a>
                </form>
            </div>

            <!-- Fundraising history table -->
            <div class="table-shell">
                <table>
                    <thead>
                        <tr>
                            <th>Activity</th>
                            <th>Service</th>
                            <th>Status</th>
                            <th>Raised</th>
                            <th>Completed Date</th>
                        </tr>
                    </thead>

                    <tbody>

                        <!-- Display completed fundraising history -->
                        <?php foreach ($history as $activity): ?>
                            <tr>
                                <td><?= e($activity['title']) ?></td>
                                <td><?= e($activity['service_type']) ?></td>
                                <td><?= e($activity['status']) ?></td>
                                <td><?= e(format_currency($activity['current_amount'])) ?></td>
                                <td>
                                    <?= e(format_date($activity['end_date'] ?? $activity['created_at'])) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>

                        <!-- Show message when no fundraising history is found -->
                        <?php if ($history === []): ?>
                            <tr>
                                <td colspan="5">
                                    No completed fundraising history found.
                                </td>
                            </tr>
                        <?php endif; ?>

                    </tbody>
                </table>
            </div>
        </section>
    </main>
</body>
</html>