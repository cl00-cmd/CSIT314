<?php
declare(strict_types=1);

// Load system setup, shared fundraiser layout, and helper functions
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/fundraiser_shared.php';

use App\Controller\FRshortlistController;

// BCE route:
// Boundary/FRshortlistUI.php -> Controller/FRshortlistController.php -> Entity/FundraisingActivity.php.
// This Boundary loads the shortlist counts for each Fund Raiser activity.
require_login(['fund_raiser']);

// Retrieves the logged-in fundraiser.
$user = current_user();

// Boundary -> Controller to retrieve shortlist statistics.
$controller = new FRshortlistController();

// Gets shortlist counts for all fundraising activities created by the fundraiser.
$shortlists = $controller->retrieveShortlistedCount((int) $user['id']);

// Calculates the total number of shortlists across all activities.
$totalShortlists = array_sum(array_column($shortlists, 'shortlistedCount'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FRshortlistUI</title>
    <link rel="stylesheet" href="../assets/css/app.css">
</head>
<body>

    <!-- Fundraiser top navigation bar -->
    <?php render_fundraiser_topbar('FRshortlistUI', 'shortlists'); ?>

    <main class="page-shell">

        <!-- Display success or error message if available -->
        <?php render_fundraiser_flash_if_any(); ?>

        <!-- Shortlist statistics summary -->
        <section class="stats-grid">
            <article class="stat-card">
                <span>Total Shortlists</span>
                <strong><?= e((string) $totalShortlists) ?></strong>
            </article>
        </section>

        <!-- Fundraising activity shortlist table -->
        <section class="panel">
            <div class="panel__header">
                <div>
                    <h2>View number of times each fundraising activity was shortlisted</h2>
                </div>
            </div>

            <div class="table-shell">
                <table>
                    <thead>
                        <tr>
                            <th>Activity</th>
                            <th>Status</th>
                            <th>Shortlisted Count</th>
                        </tr>
                    </thead>

                    <tbody>

                        <!-- Display shortlist count for each fundraising activity -->
                        <?php foreach ($shortlists as $shortlist): ?>
                            <tr>
                                <td><?= e($shortlist['title']) ?></td>
                                <td><?= e($shortlist['status']) ?></td>
                                <td><?= e((string) $shortlist['shortlistedCount']) ?></td>
                            </tr>
                        <?php endforeach; ?>

                        <!-- Show message when no shortlist data is available -->
                        <?php if ($shortlists === []): ?>
                            <tr>
                                <td colspan="3">
                                    No shortlisted fundraising activity found.
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