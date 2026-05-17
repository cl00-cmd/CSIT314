<?php
declare(strict_types=1);

// Load shared donor layout, helper functions, and system setup
require_once __DIR__ . '/donor_shared.php';

use App\Controller\DSaveFavouriteC;
use App\Controller\DViewFavouriteC;

// BCE route:
// Boundary/DViewFavouriteUI.php -> Controller/DViewFavouriteC.php -> Entity/FundraisingActivity.php.
// Boundary/DViewFavouriteUI.php -> Controller/DSaveFavouriteC.php -> Entity/FavouriteList.php.
// This Boundary lets Donors view fundraising activity details from their favourite list.
require_login(['donor']);

// Retrieves the logged-in donor and selected activity.
$user = current_user();
$userId = (int) $user['id'];
$activityId = (int) ($_GET['activity_id'] ?? $_POST['activity_id'] ?? 0);

// Boundary -> Controller to retrieve favourite activity details.
$viewFavouriteController = new DViewFavouriteC();
$saveFavouriteController = new DSaveFavouriteC();

// Handles remove favourite action submitted by the donor.
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $result = match ((string) ($_POST['action'] ?? '')) {
        'remove_favourite' => $saveFavouriteController->removeFavourite($userId, $activityId),
        default => ['success' => false, 'message' => 'Unknown action.'],
    };

    set_flash($result['success'] ? 'success' : 'error', $result['message']);
    app_redirect('DFavouriteUI.php');
}

// Gets the selected favourite fundraising activity details.
$activity = $activityId > 0
    ? $viewFavouriteController->displayFavourites($userId, $activityId)
    : null;
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

    <!-- Donor top navigation bar -->
    <?php render_donor_topbar('View Favourite', 'favourites'); ?>

    <main class="page-shell donor-shell">

        <!-- Display success or error message if available -->
        <?php render_donor_flash_if_any(); ?>

        <!-- Favourite fundraising activity details section -->
        <section class="panel donor-panel">
            <div class="panel__header">
                <div>
                    <p class="section-label">DViewFavouriteUI</p>
                    <h2><?= e($activity['title'] ?? 'View favourite activity') ?></h2>
                </div>

                <a class="button button--ghost button--small"
                   href="DFavouriteUI.php">
                    Back to Favourite List
                </a>
            </div>

            <!-- Show message when no favourite activity is selected -->
            <?php if ($activity === null): ?>
                <p class="muted">
                    Please choose a saved fundraising activity from the favourite list.
                </p>

            <!-- Display selected favourite activity details -->
            <?php else: ?>
                <article class="card card--soft">

                    <!-- Display fundraiser and category information -->
                    <p class="muted">
                        <?= e($activity['fundraiser_name']) ?>
                        &middot;
                        <?= e($activity['category_name']) ?>
                    </p>

                    <!-- Display fundraising activity story -->
                    <p><?= e($activity['story']) ?></p>

                    <!-- Display fundraising activity statistics -->
                    <div class="campaign-meta">
                        <span>Status: <?= e($activity['status']) ?></span>
                        <span>Goal: <?= e(format_currency($activity['funding_goal'])) ?></span>
                        <span>Current: <?= e(format_currency($activity['current_amount'])) ?></span>
                        <span>Views: <?= e((string) $activity['view_count']) ?></span>
                    </div>

                    <!-- Fundraising activity progress bar -->
                    <div class="progress">
                        <div class="progress__bar"
                             style="width: <?= e((string) progress_percent($activity)) ?>%">
                        </div>
                    </div>

                    <!-- Activity action buttons -->
                    <div class="action-row">

                        <a class="button button--ghost button--small"
                           href="DonorProgressUI.php?activity_id=<?= e((string) $activity['id']) ?>">
                            View Progress
                        </a>

                        <!-- Remove fundraising activity from favourite list -->
                        <form method="post">
                            <input type="hidden" name="action" value="remove_favourite">
                            <input type="hidden" name="activity_id" value="<?= e((string) $activity['id']) ?>">

                            <button type="submit" class="button button--ghost button--small">
                                Remove Favourite
                            </button>
                        </form>
                    </div>
                </article>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>