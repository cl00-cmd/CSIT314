<?php
declare(strict_types=1);

// Load system setup, shared fundraiser layout, and helper functions
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/fundraiser_shared.php';

use App\Controller\FRHistorySearchController;
use App\Controller\FRViewsController;
use App\Controller\FRshortlistController;
use App\Controller\FundraisingSearchC;

// BCE route:
// Boundary/fundraiser_dashboard.php -> Controller/FundraisingSearchC.php -> Entity/FundraisingActivity.php.
// Boundary/fundraiser_dashboard.php -> Controller/FRViewsController.php -> Entity/FundraisingActivity.php.
// Boundary/fundraiser_dashboard.php -> Controller/FRshortlistController.php -> Entity/FundraisingActivity.php.
// Boundary/fundraiser_dashboard.php -> Controller/FRHistorySearchController.php -> Entity/FundraisingActivity.php.
// This Boundary loads the Fund Raiser dashboard summary cards and recent activity table.
require_login(['fund_raiser']);

// Retrieves the logged-in fundraiser.
$user = current_user();
$userId = (int) $user['id'];

// Boundary -> Controller to retrieve dashboard data.
$searchController = new FundraisingSearchC();
$viewsController = new FRViewsController();
$shortlistController = new FRshortlistController();
$historyController = new FRHistorySearchController();

// Gets fundraising activities, views, shortlists, and completed history.
$activities = $searchController->searchActivity($userId);
$views = $viewsController->getViewCount($userId);
$shortlists = $shortlistController->retrieveShortlistedCount($userId);
$completedHistory = $historyController->searchHistory($userId);

// Calculates dashboard summary totals.
$totalViews = array_sum(array_column($views, 'viewsCount'));
$totalShortlists = array_sum(array_column($shortlists, 'shortlistedCount'));
$completedCount = count($completedHistory);
$activeCount = count(array_filter($activities, static fn (array $activity): bool => ($activity['status'] ?? '') === 'active'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fund Raiser Dashboard</title>
    <link rel="stylesheet" href="../assets/css/app.css">
</head>
<body>

    <!-- Fundraiser top navigation bar -->
    <?php render_fundraiser_topbar('Fund Raiser Dashboard', 'dashboard'); ?>

    <main class="page-shell">

        <!-- Display success or error message if available -->
        <?php render_fundraiser_flash_if_any(); ?>

        <!-- Fundraiser dashboard summary cards -->
        <section class="stats-grid stats-grid--five">
            <article class="stat-card">
                <span>Total Activities</span>
                <strong><?= e((string) count($activities)) ?></strong>
            </article>

            <article class="stat-card">
                <span>Active Activities</span>
                <strong><?= e((string) $activeCount) ?></strong>
            </article>

            <article class="stat-card">
                <span>Total Views</span>
                <strong><?= e((string) $totalViews) ?></strong>
            </article>

            <article class="stat-card">
                <span>Total Shortlists</span>
                <strong><?= e((string) $totalShortlists) ?></strong>
            </article>

            <article class="stat-card">
                <span>Completed History</span>
                <strong><?= e((string) $completedCount) ?></strong>
            </article>
        </section>

        <!-- Recent fundraising activity table -->
        <section class="panel">
            <div class="panel__header">
                <div>
                    <h2>Overall / Recent Fundraising Activity</h2>
                    <p class="muted">
                        A quick overview of the latest fundraising activities, views, and shortlists.
                    </p>
                </div>
            </div>

            <div class="table-shell">
                <table>
                    <thead>
                        <tr>
                            <th>Activity</th>
                            <th>Service</th>
                            <th>Status</th>
                            <th>Views</th>
                            <th>Shortlists</th>
                            <th>Commands</th>
                        </tr>
                    </thead>

                    <tbody>

                        <!-- Display latest fundraising activities -->
                        <?php foreach (array_slice($activities, 0, 10) as $activity): ?>
                            <tr>
                                <td><?= e($activity['title']) ?></td>
                                <td><?= e($activity['service_type']) ?></td>
                                <td><?= e($activity['status']) ?></td>
                                <td><?= e((string) ($activity['view_count'] ?? 0)) ?></td>
                                <td><?= e((string) ($activity['shortlist_count'] ?? 0)) ?></td>

                                <!-- Activity management buttons -->
                                <td class="action-row">
                                    <a class="button button--ghost button--small"
                                       href="FundraisingUI.php?command=view&activity_id=<?= e((string) $activity['id']) ?>">
                                        View
                                    </a>

                                    <a class="button button--ghost button--small"
                                       href="FundraisingUI.php?command=update&activity_id=<?= e((string) $activity['id']) ?>">
                                        Update
                                    </a>

                                    <a class="button button--ghost button--small"
                                       href="FundraisingUI.php?command=delete&activity_id=<?= e((string) $activity['id']) ?>">
                                        Delete
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>

                        <!-- Show message when no fundraising activities are found -->
                        <?php if ($activities === []): ?>
                            <tr>
                                <td colspan="6">
                                    No fundraising activities found.
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