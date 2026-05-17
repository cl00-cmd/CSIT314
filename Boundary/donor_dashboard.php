<?php
declare(strict_types=1);

// Load shared donor layout, helper functions, and system setup
require_once __DIR__ . '/donor_shared.php';

use App\Controller\DActivityC;
use App\Controller\DSearchFavouriteC;
use App\Controller\DonationHistoryC;

// BCE route:
// Boundary/donor_dashboard.php -> Controller/DActivityC.php -> Entity/FundraisingActivity.php.
// Boundary/donor_dashboard.php -> Controller/DSearchFavouriteC.php -> Entity/FavouriteList.php.
// Boundary/donor_dashboard.php -> Controller/DonationHistoryC.php -> Entity/Donation.php.
// This Boundary shows the Donor overall summary and links each action to its own BCE page.
require_login(['donor']);

//  Retrieves the logged-in donor details from the session.
$user = current_user();
$userId = (int) $user['id'];

// Boundary -> Controller to retrieve dashboard data.
$activityController = new DActivityC();
$favouriteController = new DSearchFavouriteC();
$historyController = new DonationHistoryC();

// Gets activities, favourites, donation history, and donation summary.
$activities = $activityController->searchActivity($userId);
$favourites = $favouriteController->searchFavourite($userId);
$history = $historyController->displayResults($userId);
$summary = $historyController->getSummary($userId);

// Shows only recent records on the dashboard.
$recentActivities = array_slice($activities, 0, 4);
$recentFavourites = array_slice($favourites, 0, 4);
$recentHistory = array_slice($history, 0, 5);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donor Dashboard</title>
    <link rel="stylesheet" href="../assets/css/app.css">
</head>
<body>

    <!-- Donor top navigation bar -->
    <?php render_donor_topbar('Donor Dashboard', 'dashboard'); ?>

    <main class="page-shell donor-shell">

        <!-- Display success or error message if available -->
        <?php render_donor_flash_if_any(); ?>

        <!-- Donor summary cards -->
        <section class="stats-grid donor-stats">
            <article class="stat-card">
                <span>Available Activities</span>
                <strong><?= e((string) count($activities)) ?></strong>
            </article>

            <article class="stat-card">
                <span>Favourite List</span>
                <strong><?= e((string) count($favourites)) ?></strong>
            </article>

            <article class="stat-card">
                <span>Total Donations</span>
                <strong><?= e((string) ($summary['donation_count'] ?? 0)) ?></strong>
            </article>

            <article class="stat-card">
                <span>Donation Value</span>
                <strong><?= e(format_currency($summary['total_amount'] ?? 0)) ?></strong>
            </article>
        </section>

        <!-- Recent activities and favourites section -->
        <section class="layout-grid donor-two-column">

            <!-- Recent fundraising activities -->
            <section class="panel donor-panel">
                <div class="panel__header">
                    <div>
                        <h2>Recent fundraising activities</h2>
                    </div>

                    <a class="button button--ghost button--small" href="DSearchUI.php">
                        View All
                    </a>
                </div>

                <div class="table-shell">
                    <table>
                        <thead>
                            <tr>
                                <th>Activity</th>
                                <th>Category</th>
                                <th>Progress</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php foreach ($recentActivities as $activity): ?>
                                <tr>
                                    <td>
                                        <a href="DActivityUI.php?activity_id=<?= e((string) $activity['id']) ?>">
                                            <?= e($activity['title']) ?>
                                        </a>
                                    </td>
                                    <td><?= e($activity['category_name']) ?></td>
                                    <td><?= e((string) progress_percent($activity)) ?>%</td>
                                </tr>
                            <?php endforeach; ?>

                            <!-- Show message when no activities are found -->
                            <?php if ($recentActivities === []): ?>
                                <tr>
                                    <td colspan="3">No fundraising activity found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- Recent favourite list -->
            <section class="panel donor-panel">
                <div class="panel__header">
                    <div>
                        <h2>Recent favourite list</h2>
                    </div>

                    <a class="button button--ghost button--small" href="DFavouriteUI.php">
                        View All
                    </a>
                </div>

                <div class="table-shell">
                    <table>
                        <thead>
                            <tr>
                                <th>Activity</th>
                                <th>Category</th>
                                <th>Status</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php foreach ($recentFavourites as $favourite): ?>
                                <tr>
                                    <td>
                                        <a href="DViewFavouriteUI.php?activity_id=<?= e((string) $favourite['id']) ?>">
                                            <?= e($favourite['title']) ?>
                                        </a>
                                    </td>
                                    <td><?= e($favourite['category_name']) ?></td>
                                    <td><?= e($favourite['status']) ?></td>
                                </tr>
                            <?php endforeach; ?>

                            <!-- Show message when no favourites are found -->
                            <?php if ($recentFavourites === []): ?>
                                <tr>
                                    <td colspan="3">No saved fundraising activity found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </section>

        <!-- Recent donation history -->
        <section class="panel donor-panel">
            <div class="panel__header">
                <div>
                    <h2>Recent donation history</h2>
                </div>

                <a class="button button--ghost button--small" href="DonationHistoryUI.php">
                    View All
                </a>
            </div>

            <div class="table-shell">
                <table>
                    <thead>
                        <tr>
                            <th>Activity</th>
                            <th>Category</th>
                            <th>Amount</th>
                            <th>Date</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($recentHistory as $donation): ?>
                            <tr>
                                <td><?= e($donation['campaign_title']) ?></td>
                                <td><?= e($donation['category_name']) ?></td>
                                <td><?= e(format_currency($donation['amount'])) ?></td>
                                <td><?= e(format_date($donation['donated_at'])) ?></td>
                            </tr>
                        <?php endforeach; ?>

                        <!-- Show message when no donation history is found -->
                        <?php if ($recentHistory === []): ?>
                            <tr>
                                <td colspan="4">No donation history found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</body>
</html>
