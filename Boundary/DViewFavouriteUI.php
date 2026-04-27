<?php
declare(strict_types=1);

require_once __DIR__ . '/donor_shared.php';

use App\Controller\DSaveFavouriteC;
use App\Controller\DViewFavouriteC;

// BCE route:
// Boundary/DViewFavouriteUI.php -> Controller/DViewFavouriteC.php -> Entity/FundraisingActivity.php.
// Boundary/DViewFavouriteUI.php -> Controller/DSaveFavouriteC.php -> Entity/FavouriteList.php.
// This Boundary lets Donors view fundraising activity details from their favourite list.
require_login(['donor']);

$user = current_user();
$userId = (int) $user['id'];
$activityId = (int) ($_GET['activity_id'] ?? $_POST['activity_id'] ?? 0);

// Boundary -> Controller.
$viewFavouriteController = new DViewFavouriteC();
$saveFavouriteController = new DSaveFavouriteC();

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $result = match ((string) ($_POST['action'] ?? '')) {
        'remove_favourite' => $saveFavouriteController->removeFavourite($userId, $activityId),
        default => ['success' => false, 'message' => 'Unknown action.'],
    };

    set_flash($result['success'] ? 'success' : 'error', $result['message']);
    app_redirect('DFavouriteUI.php');
}

$activity = $activityId > 0 ? $viewFavouriteController->displayFavourites($userId, $activityId) : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Favourite Activity</title>
    <link rel="stylesheet" href="../assets/css/app.css">
</head>
<body>
    <?php render_donor_topbar('View Favourite', 'favourites'); ?>

    <main class="page-shell donor-shell">
        <?php render_donor_flash_if_any(); ?>

        <section class="panel donor-panel">
            <div class="panel__header">
                <div>
                    <p class="section-label">DViewFavouriteUI</p>
                    <h2><?= e($activity['title'] ?? 'View favourite activity') ?></h2>
                </div>
                <a class="button button--ghost button--small" href="DFavouriteUI.php">Back to Favourite List</a>
            </div>

            <?php if ($activity === null): ?>
                <p class="muted">Please choose a saved fundraising activity from the favourite list.</p>
            <?php else: ?>
                <article class="card card--soft">
                    <p class="muted"><?= e($activity['fundraiser_name']) ?> &middot; <?= e($activity['category_name']) ?></p>
                    <p><?= e($activity['story']) ?></p>
                    <div class="campaign-meta">
                        <span>Status: <?= e($activity['status']) ?></span>
                        <span>Goal: <?= e(format_currency($activity['funding_goal'])) ?></span>
                        <span>Current: <?= e(format_currency($activity['current_amount'])) ?></span>
                        <span>Views: <?= e((string) $activity['view_count']) ?></span>
                    </div>
                    <div class="progress">
                        <div class="progress__bar" style="width: <?= e((string) progress_percent($activity)) ?>%"></div>
                    </div>
                    <div class="action-row">
                        <a class="button button--ghost button--small" href="DonorProgressUI.php?activity_id=<?= e((string) $activity['id']) ?>">View Progress</a>
                        <form method="post">
                            <input type="hidden" name="action" value="remove_favourite">
                            <input type="hidden" name="activity_id" value="<?= e((string) $activity['id']) ?>">
                            <button type="submit" class="button button--ghost button--small">Remove Favourite</button>
                        </form>
                    </div>
                </article>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>
