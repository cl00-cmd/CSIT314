<?php
declare(strict_types=1);

require_once __DIR__ . '/donor_shared.php';

use App\Controller\DActivityC;
use App\Controller\DonorProgressC;

// BCE route:
// Boundary/DonorProgressUI.php -> Controller/DonorProgressC.php -> Entity/FundraisingActivity.php.
// Boundary/DonorProgressUI.php -> Controller/DActivityC.php -> Entity/FundraisingActivity.php.
// This Boundary lets Donors view fundraising activity progress before donating.
require_login(['donor']);

$user = current_user();
$userId = (int) $user['id'];
$activityId = (int) ($_GET['activity_id'] ?? 0);
$searchTerm = trim((string) ($_GET['search'] ?? ''));

// Boundary -> Controller.
$progressController = new DonorProgressC();
$activityController = new DActivityC();

$activities = $activityController->searchActivity($userId, [
    'search' => $searchTerm,
]);
$selectedProgress = $activityId > 0 ? $progressController->getProgress($userId, $activityId) : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activity Progress</title>
    <link rel="stylesheet" href="../assets/css/app.css">
</head>
<body>
    <?php render_donor_topbar('Activity Progress', 'progress'); ?>

    <main class="page-shell donor-shell">
        <?php render_donor_flash_if_any(); ?>

        <?php if ($selectedProgress !== null): ?>
            <section class="panel donor-panel donor-progress-panel">
                <div class="panel__header">
                <div>
                    <h2><?= e($selectedProgress['title']) ?></h2>
                </div>
                    <a class="button button--ghost button--small" href="DActivityUI.php?activity_id=<?= e((string) $selectedProgress['id']) ?>">View Details</a>
                </div>
                <div class="donor-progress">
                    <p class="muted"><?= e($selectedProgress['category_name']) ?> &middot; <?= e($selectedProgress['status']) ?></p>
                    <div class="donor-progress__numbers">
                        <strong><?= e((string) $selectedProgress['progress_percent']) ?>%</strong>
                        <span><?= e(format_currency($selectedProgress['current_amount'])) ?> raised of <?= e(format_currency($selectedProgress['funding_goal'])) ?></span>
                    </div>
                    <div class="progress">
                        <div class="progress__bar" style="width: <?= e((string) $selectedProgress['progress_percent']) ?>%"></div>
                    </div>
                </div>
            </section>
        <?php endif; ?>

        <section class="panel donor-panel">
            <div class="panel__header panel__header--stack">
                <div>
                    <h2>View fundraising activity progress</h2>
                </div>
                <form method="get" class="inline-filters donor-filters">
                    <input type="text" name="search" value="<?= e($searchTerm) ?>" placeholder="Search activity, fundraiser, service, or category">
                    <button type="submit" class="button button--primary">Search</button>
                    <?php if ($searchTerm !== ''): ?>
                        <a class="button button--ghost" href="DonorProgressUI.php">Clear</a>
                    <?php endif; ?>
                </form>
            </div>
            <div class="table-shell">
                <table>
                    <thead>
                        <tr>
                            <th>Activity</th>
                            <th>Category</th>
                            <th>Goal</th>
                            <th>Current</th>
                            <th>Progress</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($activities as $activity): ?>
                            <tr>
                                <td><?= e($activity['title']) ?></td>
                                <td><?= e($activity['category_name']) ?></td>
                                <td><?= e(format_currency($activity['funding_goal'])) ?></td>
                                <td><?= e(format_currency($activity['current_amount'])) ?></td>
                                <td><?= e((string) progress_percent($activity)) ?>%</td>
                                <td>
                                    <a class="button button--ghost button--small" href="DonorProgressUI.php?activity_id=<?= e((string) $activity['id']) ?>">View</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if ($activities === []): ?>
                            <tr><td colspan="6">No fundraising activity found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</body>
</html>
