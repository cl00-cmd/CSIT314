<?php
declare(strict_types=1);

// Load shared donor layout, helper functions, and system setup
require_once __DIR__ . '/donor_shared.php';

use App\Controller\DActivityC;
use App\Controller\DSaveFavouriteC;

// BCE route:
// Boundary/DActivityUI.php -> Controller/DActivityC.php -> Entity/FundraisingActivity.php.
// Boundary/DActivityUI.php -> Controller/DSaveFavouriteC.php -> Entity/FavouriteList.php.
// This Boundary lets Donors view fundraising activity details before donating or saving it.
require_login(['donor']);

// Retrieves the logged-in donor and selected activity.
$user = current_user();
$userId = (int) $user['id'];
$activityId = (int) ($_GET['activity_id'] ?? $_POST['activity_id'] ?? 0);

// Boundary -> Controller to handle activity and favourite actions.
$activityController = new DActivityC();
$saveFavouriteController = new DSaveFavouriteC();

// Processes donate, save favourite, and remove favourite actions.
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $result = match ((string) ($_POST['action'] ?? '')) {
        'save_favourite' => $saveFavouriteController->addFavourite($userId, $activityId),
        'remove_favourite' => $saveFavouriteController->removeFavourite($userId, $activityId),
        'donate' => $activityController->submitDonation(
            $userId,
            $activityId,
            (float) ($_POST['amount'] ?? 0),
            (string) ($_POST['message'] ?? '')
        ),
        default => ['success' => false, 'message' => 'Unknown action.'],
    };

    set_flash($result['success'] ? 'success' : 'error', $result['message']);
    app_redirect('DActivityUI.php', ['activity_id' => $activityId]);
}

// Retrieves selected activity details for display.
$activity = $activityId > 0 ? $activityController->viewActivityDetails($userId, $activityId) : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Fundraising Activity</title>
    <link rel="stylesheet" href="../assets/css/app.css">
</head>
<body>

    <!-- Donor top navigation bar -->
    <?php render_donor_topbar('View Activity', 'search'); ?>

    <main class="page-shell donor-shell">

        <!-- Display success or error message if available -->
        <?php render_donor_flash_if_any(); ?>

        <!-- Activity details section -->
        <section class="panel donor-panel">
            <div class="panel__header">
                <div>
                    <p class="section-label">DActivityUI</p>
                    <h2><?= e($activity['title'] ?? 'View fundraising activity') ?></h2>
                </div>

                <a class="button button--ghost button--small" href="DSearchUI.php">
                    Back to Search
                </a>
            </div>

            <!-- Show message when no activity is selected -->
            <?php if ($activity === null): ?>
                <p class="muted">Please choose a fundraising activity from the search page.</p>

            <?php else: ?>

                <!-- Activity information and donation form layout -->
                <div class="detail-grid donor-detail-grid">

                    <!-- Fundraising activity information -->
                    <article class="card card--soft">
                        <p class="muted">
                            <?= e($activity['fundraiser_name']) ?> &middot; <?= e($activity['category_name']) ?>
                        </p>

                        <p><?= e($activity['story']) ?></p>

                        <!-- Activity summary details -->
                        <div class="campaign-meta">
                            <span>Status: <?= e($activity['status']) ?></span>
                            <span>Goal: <?= e(format_currency($activity['funding_goal'])) ?></span>
                            <span>Current: <?= e(format_currency($activity['current_amount'])) ?></span>
                            <span>Views: <?= e((string) $activity['view_count']) ?></span>
                        </div>

                        <!-- Activity progress bar -->
                        <div class="progress">
                            <div class="progress__bar"
                                 style="width: <?= e((string) progress_percent($activity)) ?>%">
                            </div>
                        </div>

                        <!-- View progress and favourite actions -->
                        <div class="action-row">
                            <a class="button button--ghost button--small"
                               href="DonorProgressUI.php?activity_id=<?= e((string) $activity['id']) ?>">
                                View Progress
                            </a>

                            <form method="post">
                                <input type="hidden" name="activity_id" value="<?= e((string) $activity['id']) ?>">

                                <?php if ((int) $activity['is_favourite'] === 1): ?>
                                    <input type="hidden" name="action" value="remove_favourite">
                                    <button type="submit" class="button button--ghost button--small">
                                        Remove Favourite
                                    </button>
                                <?php else: ?>
                                    <input type="hidden" name="action" value="save_favourite">
                                    <button type="submit" class="button button--primary button--small">
                                        Save Favourite
                                    </button>
                                <?php endif; ?>
                            </form>
                        </div>
                    </article>

                    <!-- Donation form -->
                    <form method="post" class="card form-stack">
                        <input type="hidden" name="action" value="donate">
                        <input type="hidden" name="activity_id" value="<?= e((string) $activity['id']) ?>">

                        <div>
                            <p class="section-label">Donation</p>
                            <h3>Support this activity</h3>
                        </div>

                        <label class="field">
                            <span>Amount</span>
                            <input type="number" name="amount" min="1" step="0.01" required>
                        </label>

                        <label class="field">
                            <span>Message</span>
                            <textarea name="message" rows="4"></textarea>
                        </label>

                        <button type="submit" class="button button--primary">
                            Submit Donation
                        </button>
                    </form>
                </div>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>