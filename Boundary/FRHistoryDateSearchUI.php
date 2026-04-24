<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/fundraiser_shared.php';

use App\Controller\FRHistoryDateSearchController;

// BCE route:
// Boundary/FRHistoryDateSearchUI.php -> Controller/FRHistoryDateSearchController.php -> Entity/FundraisingActivity.php.
require_login(['fund_raiser']);

$user = current_user();
$controller = new FRHistoryDateSearchController();
$fromDate = trim((string) ($_GET['from'] ?? ''));
$toDate = trim((string) ($_GET['to'] ?? ''));
$validation = $controller->validateDateRange($fromDate, $toDate);
$history = $validation['success']
    ? $controller->searchByDate((int) $user['id'], $fromDate, $toDate)
    : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FRHistoryDateSearchUI</title>
    <link rel="stylesheet" href="../assets/css/app.css">
</head>
<body>
    <?php render_fundraiser_topbar('FRHistoryDateSearchUI', 'history_date'); ?>
    <main class="page-shell">
        <?php render_fundraiser_flash_if_any(); ?>

        <?php if (!$validation['success']): ?>
            <div class="flash flash--error"><?= e($validation['message']) ?></div>
        <?php endif; ?>

        <section class="panel">
            <div class="panel__header panel__header--stack">
                <div>
                    <h2>Search completed fundraising activity history by date period</h2>
                </div>
                <form method="get" class="inline-filters">
                    <input type="date" name="from" value="<?= e($fromDate) ?>">
                    <input type="date" name="to" value="<?= e($toDate) ?>">
                    <button type="submit" class="button button--ghost">Search</button>
                </form>
            </div>

            <div class="table-shell">
                <table>
                    <thead>
                        <tr>
                            <th>Activity</th>
                            <th>Status</th>
                            <th>Completed Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($history as $activity): ?>
                            <tr>
                                <td><?= e($activity['title']) ?></td>
                                <td><?= e($activity['status']) ?></td>
                                <td><?= e(format_date($activity['end_date'] ?? $activity['created_at'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</body>
</html>
