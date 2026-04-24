<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/fundraiser_shared.php';

use App\Controller\FRHistorySearchController;

// BCE route:
// Boundary/FRHistorySearchUI.php -> Controller/FRHistorySearchController.php -> Entity/FundraisingActivity.php.
require_login(['fund_raiser']);

$user = current_user();
$controller = new FRHistorySearchController();
$serviceType = trim((string) ($_GET['service_type'] ?? ''));
$validation = $controller->validateCriteria($serviceType);
$history = $validation['success']
    ? $controller->searchHistory((int) $user['id'], $serviceType)
    : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FRHistorySearchUI</title>
    <link rel="stylesheet" href="../assets/css/app.css">
</head>
<body>
    <?php render_fundraiser_topbar('FRHistorySearchUI', 'history_service'); ?>
    <main class="page-shell">
        <?php render_fundraiser_flash_if_any(); ?>

        <?php if (!$validation['success']): ?>
            <div class="flash flash--error"><?= e($validation['message']) ?></div>
        <?php endif; ?>

        <section class="panel">
            <div class="panel__header panel__header--stack">
                <div>
                    <h2>Search completed fundraising activity history by services</h2>
                </div>
                <form method="get" class="inline-filters">
                    <select name="service_type">
                        <option value="">All services</option>
                        <?php foreach ($controller->getServiceTypes() as $service): ?>
                            <option value="<?= e($service) ?>" <?= selected_if($serviceType, $service) ?>><?= e($service) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="button button--ghost">Search</button>
                </form>
            </div>

            <div class="table-shell">
                <table>
                    <thead>
                        <tr>
                            <th>Activity</th>
                            <th>Service</th>
                            <th>Status</th>
                            <th>Completed Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($history as $activity): ?>
                            <tr>
                                <td><?= e($activity['title']) ?></td>
                                <td><?= e($activity['service_type']) ?></td>
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
