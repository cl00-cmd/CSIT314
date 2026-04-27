<?php
declare(strict_types=1);

require_once __DIR__ . '/donor_shared.php';

use App\Controller\DSearchFavouriteC;

// BCE route:
// Boundary/DFavouriteUI.php -> Controller/DSearchFavouriteC.php -> Entity/FavouriteList.php.
// This Boundary lets Donors search their favourite list and open saved fundraising activities.
require_login(['donor']);

$user = current_user();
$userId = (int) $user['id'];

// Boundary -> Controller.
$favouriteController = new DSearchFavouriteC();

$filters = [
    'favourite_search' => trim((string) ($_GET['favourite_search'] ?? '')),
];
$favourites = $favouriteController->searchFavourite($userId, $filters);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Favourite List</title>
    <link rel="stylesheet" href="../assets/css/app.css">
</head>
<body>
    <?php render_donor_topbar('Favourite List', 'favourites'); ?>

    <main class="page-shell donor-shell">
        <?php render_donor_flash_if_any(); ?>

        <section class="panel donor-panel">
            <div class="panel__header panel__header--stack">
                <div>
                    <p class="section-label">DFavouriteUI</p>
                    <h2>Search favourite list</h2>
                </div>
                <form method="get" class="inline-filters donor-filters donor-filters--single">
                    <input type="text" name="favourite_search" value="<?= e($filters['favourite_search']) ?>" placeholder="Search saved activity">
                    <button type="submit" class="button button--primary">Search</button>
                </form>
            </div>

            <div class="table-shell">
                <table>
                    <thead>
                        <tr>
                            <th>Activity</th>
                            <th>Category</th>
                            <th>Fund Raiser</th>
                            <th>Status</th>
                            <th>Progress</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($favourites as $favourite): ?>
                            <tr>
                                <td><?= e($favourite['title']) ?></td>
                                <td><?= e($favourite['category_name']) ?></td>
                                <td><?= e($favourite['fundraiser_name']) ?></td>
                                <td><?= e($favourite['status']) ?></td>
                                <td><?= e((string) progress_percent($favourite)) ?>%</td>
                                <td>
                                    <a class="button button--ghost button--small" href="DViewFavouriteUI.php?activity_id=<?= e((string) $favourite['id']) ?>">View</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if ($favourites === []): ?>
                            <tr><td colspan="6">No saved fundraising activity found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</body>
</html>
