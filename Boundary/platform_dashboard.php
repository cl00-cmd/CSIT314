<?php
declare(strict_types=1);

// Load system setup, shared platform layout, and helper functions
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/platform_shared.php';

use App\Controller\DailyReportC;
use App\Controller\FSASearchCategoryC;
use App\Controller\MonthlyReportC;
use App\Controller\WeeklyReportC;

// BCE routes for the Platform Manager dashboard:
// Boundary/platform_dashboard.php -> Controller/FSASearchCategoryC.php -> Entity/FSACategory.php.
// Boundary/platform_dashboard.php -> Controller/DailyReportC.php -> Entity/DailyReport.php.
// Boundary/platform_dashboard.php -> Controller/WeeklyReportC.php -> Entity/WeeklyReport.php.
// Boundary/platform_dashboard.php -> Controller/MonthlyReportC.php -> Entity/MonthlyReport.php.
// This dashboard only shows summary data and links to the specific Platform Manager sequence pages.
require_login(['platform_manager']);

// Boundary -> Controller calls for dashboard counts.
$categories = (new FSASearchCategoryC())->searchCategory('');
$dailyReport = (new DailyReportC())->generateReport(date('Y-m-d'))['report'];
$weeklyReport = (new WeeklyReportC())->generateReport(date('Y-m-d'))['report'];
$monthlyReport = (new MonthlyReportC())->generateReport(date('Y-m'))['report'];

// Counts the number of suspended FSA categories.
$suspendedCategories = count(array_filter($categories, static fn (array $row): bool => ($row['status'] ?? '') === 'suspended'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Platform Manager Dashboard</title>
    <link rel="stylesheet" href="../assets/css/app.css">
</head>
<body>

    <!-- Platform manager top navigation bar -->
    <?php render_platform_topbar('Platform Manager Dashboard', 'dashboard'); ?>

    <main class="page-shell">

        <!-- Display success or error message if available -->
        <?php render_platform_flash_if_any(); ?>

        <!-- Platform manager dashboard summary cards -->
        <section class="stats-grid">
            <article class="stat-card">
                <span>Categories</span>
                <strong><?= e((string) count($categories)) ?></strong>
            </article>

            <article class="stat-card">
                <span>Suspended Categories</span>
                <strong><?= e((string) $suspendedCategories) ?></strong>
            </article>

            <article class="stat-card">
                <span>Weekly Donations</span>
                <strong><?= e((string) $weeklyReport['reportDetails']['donationCount']) ?></strong>
            </article>

            <article class="stat-card">
                <span>Monthly Donation Value</span>
                <strong><?= e(format_currency($monthlyReport['reportDetails']['donationValue'])) ?></strong>
            </article>
        </section>

        <!-- Overall platform report summary table -->
        <section class="panel">
            <div class="panel__header">
                <div>
                    <h2>Overall Platform Summary</h2>
                </div>
            </div>

            <div class="table-shell">
                <table>
                    <thead>
                        <tr>
                            <th>Period</th>
                            <th>New Campaigns</th>
                            <th>Completed Campaigns</th>
                            <th>Donations</th>
                            <th>Donation Value</th>
                            <th>New Users</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>

                        <!-- Display daily report summary -->
                        <tr>
                            <td>Daily</td>
                            <td><?= e((string) $dailyReport['reportDetails']['newCampaigns']) ?></td>
                            <td><?= e((string) $dailyReport['reportDetails']['completedCampaigns']) ?></td>
                            <td><?= e((string) $dailyReport['reportDetails']['donationCount']) ?></td>
                            <td><?= e(format_currency($dailyReport['reportDetails']['donationValue'])) ?></td>
                            <td><?= e((string) $dailyReport['reportDetails']['newUsers']) ?></td>
                            <td>
                                <a class="button button--ghost button--small" href="DailyReportUI.php">
                                    View
                                </a>
                            </td>
                        </tr>

                        <!-- Display weekly report summary -->
                        <tr>
                            <td>Weekly</td>
                            <td><?= e((string) $weeklyReport['reportDetails']['newCampaigns']) ?></td>
                            <td><?= e((string) $weeklyReport['reportDetails']['completedCampaigns']) ?></td>
                            <td><?= e((string) $weeklyReport['reportDetails']['donationCount']) ?></td>
                            <td><?= e(format_currency($weeklyReport['reportDetails']['donationValue'])) ?></td>
                            <td><?= e((string) $weeklyReport['reportDetails']['newUsers']) ?></td>
                            <td>
                                <a class="button button--ghost button--small" href="WeeklyReportUI.php">
                                    View
                                </a>
                            </td>
                        </tr>

                        <!-- Display monthly report summary -->
                        <tr>
                            <td>Monthly</td>
                            <td><?= e((string) $monthlyReport['reportDetails']['newCampaigns']) ?></td>
                            <td><?= e((string) $monthlyReport['reportDetails']['completedCampaigns']) ?></td>
                            <td><?= e((string) $monthlyReport['reportDetails']['donationCount']) ?></td>
                            <td><?= e(format_currency($monthlyReport['reportDetails']['donationValue'])) ?></td>
                            <td><?= e((string) $monthlyReport['reportDetails']['newUsers']) ?></td>
                            <td>
                                <a class="button button--ghost button--small" href="MonthlyReportUI.php">
                                    View
                                </a>
                            </td>
                        </tr>

                    </tbody>
                </table>
            </div>
        </section>
    </main>
</body>
</html>