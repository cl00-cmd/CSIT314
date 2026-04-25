<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/fundraiser_shared.php';

use App\Controller\FRViewsController;

// BCE route:
// Boundary/FRViewsUI.php -> Controller/FRViewsController.php -> Entity/FundraisingActivity.php.
// This Boundary loads the view counts for each Fund Raiser activity.
require_login(['fund_raiser']);

$user = current_user();
$controller = new FRViewsController();
$views = $controller->retrieveViews((int) $user['id']);
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
    <?php render_fundraiser_topbar('FRViewsUI', 'views'); ?>
    <main class="page-shell">
        <?php render_fundraiser_flash_if_any(); ?>

        <section class="stats-grid">
            <article class="stat-card">
                <span>Total Views</span>
                <strong><?= e((string) $totalViews) ?></strong>
            </article>
        </section>

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
                        <?php foreach ($views as $view): ?>
                            <tr>
                                <td><?= e($view['title']) ?></td>
                                <td><?= e($view['status']) ?></td>
                                <td><?= e((string) $view['viewsCount']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</body>
</html>
