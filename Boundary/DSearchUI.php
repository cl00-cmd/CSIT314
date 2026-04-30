<?php
declare(strict_types=1);

require_once __DIR__ . '/donor_shared.php';

use App\Controller\DActivityC;
use App\Controller\DSaveFavouriteC;

// BCE route:
// Boundary/DSearchUI.php -> Controller/DActivityC.php -> Entity/FundraisingActivity.php.
// Boundary/DSearchUI.php -> Controller/DSaveFavouriteC.php -> Entity/FavouriteList.php.
// This Boundary lets Donors search fundraising activity and save activities into their favourite list.
require_login(['donor']);

$user = current_user();
$userId = (int) $user['id'];

// Boundary -> Controller.
$activityController = new DActivityC();
$saveFavouriteController = new DSaveFavouriteC();

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $activityId = (int) ($_POST['activity_id'] ?? 0);
    $result = match ((string) ($_POST['action'] ?? '')) {
        'save_favourite' => $saveFavouriteController->addFavourite($userId, $activityId),
        'remove_favourite' => $saveFavouriteController->removeFavourite($userId, $activityId),
        default => ['success' => false, 'message' => 'Unknown action.'],
    };

    set_flash($result['success'] ? 'success' : 'error', $result['message']);
    app_redirect('DSearchUI.php');
}

$filters = [
    'search' => trim((string) ($_GET['search'] ?? '')),
    'category_id' => (string) ($_GET['category_id'] ?? ''),
    'from' => (string) ($_GET['from'] ?? ''),
    'to' => (string) ($_GET['to'] ?? ''),
];
$categories = $activityController->listCategories();
$activities = $activityController->searchActivity($userId, $filters);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Fundraising Activity</title>
    <link rel="stylesheet" href="../assets/css/app.css">
</head>
<body>
    <?php render_donor_topbar('Search Activity', 'search'); ?>

    <main class="page-shell donor-shell">
        <?php render_donor_flash_if_any(); ?>

        <section class="panel donor-panel">
            <div class="panel__header panel__header--stack">
                <div>
                    <h2>Search fundraising activity</h2>
                </div>
                <form method="get" class="inline-filters donor-filters">
                    <input type="text" name="search" value="<?= e($filters['search']) ?>" placeholder="Search title, service, category">
                    <select name="category_id">
                        <option value="">All FSA categories</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= e((string) $category['id']) ?>" <?= selected_if($filters['category_id'], $category['id']) ?>>
                                <?= e($category['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <input type="date" name="from" value="<?= e($filters['from']) ?>" aria-label="Activity start date">
                    <input type="date" name="to" value="<?= e($filters['to']) ?>" aria-label="Activity end date">
                    <button type="submit" class="button button--primary">Search</button>
                </form>
            </div>

            <div class="campaign-grid donor-activity-grid">
                <?php foreach ($activities as $activity): ?>
                    <article class="campaign-card donor-activity-card">
                        <div class="campaign-card__top">
                            <div>
                                <h3><?= e($activity['title']) ?></h3>
                                <p class="muted"><?= e($activity['fundraiser_name']) ?> &middot; <?= e($activity['category_name']) ?></p>
                            </div>
                            <span class="pill"><?= e($activity['status']) ?></span>
                        </div>
                        <p><?= e(substr($activity['story'], 0, 150)) ?><?= strlen($activity['story']) > 150 ? '...' : '' ?></p>
                        <div class="campaign-meta">
                            <span>Views: <?= e((string) $activity['view_count']) ?></span>
                            <span>Saved: <?= e((string) $activity['shortlist_count']) ?></span>
                            <span>Progress: <?= e((string) progress_percent($activity)) ?>%</span>
                        </div>
                        <div class="progress">
                            <div class="progress__bar" style="width: <?= e((string) progress_percent($activity)) ?>%"></div>
                        </div>
                        <div class="action-row">
                            <a class="button button--ghost button--small" href="DActivityUI.php?activity_id=<?= e((string) $activity['id']) ?>">View Details</a>
                            <a class="button button--ghost button--small" href="DonorProgressUI.php?activity_id=<?= e((string) $activity['id']) ?>">Progress</a>
                            <form method="post">
                                <input type="hidden" name="activity_id" value="<?= e((string) $activity['id']) ?>">
                                <?php if ((int) $activity['is_favourite'] === 1): ?>
                                    <input type="hidden" name="action" value="remove_favourite">
                                    <button type="submit" class="button button--ghost button--small">Remove Favourite</button>
                                <?php else: ?>
                                    <input type="hidden" name="action" value="save_favourite">
                                    <button type="submit" class="button button--primary button--small">Save Favourite</button>
                                <?php endif; ?>
                            </form>
                        </div>
                    </article>
                <?php endforeach; ?>
                <?php if ($activities === []): ?>
                    <p class="muted">No fundraising activity found.</p>
                <?php endif; ?>
            </div>
        </section>
    </main>
</body>
</html>
