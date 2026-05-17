<?php
declare(strict_types=1);

// Load system setup, shared fundraiser layout, and helper functions
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/fundraiser_shared.php';

use App\Controller\FRViewsController;

// BCE route:
// Boundary/FRViewsUI.php -> Controller/FRViewsController.php -> Entity/FundraisingActivity.php.
// This Boundary loads the view counts for each Fund Raiser activity.
require_login(['fund_raiser']);

// Retrieves the logged-in fundraiser.
$user = current_user();

// Boundary -> Controller to retrieve activity view statistics.
$controller = new FRViewsController();

// Gets view counts for all fundraising activities created by the fundraiser.
$views = $controller->getViewCount((int) $user['id']);

// Calculates the total number of views across all activities.
$totalViews = array_sum(array_column($views, 'viewsCount'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FRViewsUI</title>
    <link rel="stylesheet" href="../assets/css/app.css">
</head>
<body>

    <!-- Fundraiser top navigation bar -->
    <?php render_fundraiser_topbar('FRViewsUI', 'views'); ?>

    <main class="page-shell">

        <!-- Display success or error message if available -->
        <?php render_fundraiser_flash_if_any(); ?>

        <!-- Views statistics summary -->
        <section class="stats-grid">
            <article class="stat-card">
                <span>Total Views</span>
                <strong><?= e((string) $totalViews) ?></strong>
            </article>
        </section>

        <!-- Fundraising activity views table -->
        <section class="panel">
            <div class="panel__header">
                <div>
                    <h2>View number of views for each fundraising activity</h2>
                </div>
            </div>

            <div class="table-shell">
                <table>
                    <thead>
                        <tr>
                            <th>Activity</th>
                            <th>Status</th>
                            <th>Views Count</th>
                        </tr>
                    </thead>

                    <tbody>

                        <!-- Display view count for each fundraising activity -->
                        <?php foreach ($views as $view): ?>
                            <tr>
                                <td><?= e($view['title']) ?></td>
                                <td><?= e($view['status']) ?></td>
                                <td><?= e((string) $view['viewsCount']) ?></td>
                            </tr>
                        <?php endforeach; ?>

                        <!-- Show message when no view data is available -->
                        <?php if ($views === []): ?>
                            <tr>
                                <td colspan="3">
                                    No fundraising activity views found.
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