<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/fundraiser_shared.php';

use App\Controller\FRHistorySearchController;
use App\Controller\FRViewsController;
use App\Controller\FRshortlistController;
use App\Controller\FundraisingSearchC;

// Dashboard command page for Fund Raiser BCE boundaries.
// It keeps the commands together in the same style as the User Admin dashboard.
require_login(['fund_raiser']);

$user = current_user();
$userId = (int) $user['id'];

$searchController = new FundraisingSearchC();
$viewsController = new FRViewsController();
$shortlistController = new FRshortlistController();
$historyController = new FRHistorySearchController();

$activities = $searchController->searchActivity($userId);
$views = $viewsController->retrieveViews($userId);
$shortlists = $shortlistController->retrieveShortlistedCount($userId);
$completedHistory = $historyController->searchHistory($userId);

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
    <?php render_fundraiser_topbar('Fund Raiser Dashboard', 'dashboard'); ?>
    <main class="page-shell">
        <?php render_fundraiser_flash_if_any(); ?>

        <section class="stats-grid">
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

        <section class="panel">
            <div class="panel__header">
                <div>
                    <h2>Overall / Recent Fundraising Activity</h2>
                    <p class="muted">A quick overview of the latest fundraising activities, views, and shortlists.</p>
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
                        <?php foreach (array_slice($activities, 0, 10) as $activity): ?>
                            <tr>
                                <td><?= e($activity['title']) ?></td>
                                <td><?= e($activity['service_type']) ?></td>
                                <td><?= e($activity['status']) ?></td>
                                <td><?= e((string) ($activity['view_count'] ?? 0)) ?></td>
                                <td><?= e((string) ($activity['shortlist_count'] ?? 0)) ?></td>
                                <td class="action-row">
                                    <a class="button button--ghost button--small" href="FundraisingUI.php?command=view&activity_id=<?= e((string) $activity['id']) ?>">View</a>
                                    <a class="button button--ghost button--small" href="FundraisingUI.php?command=update&activity_id=<?= e((string) $activity['id']) ?>">Update</a>
                                    <a class="button button--ghost button--small" href="FundraisingUI.php?command=delete&activity_id=<?= e((string) $activity['id']) ?>">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</body>
</html>
