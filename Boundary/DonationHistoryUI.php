<?php
declare(strict_types=1);

require_once __DIR__ . '/donor_shared.php';

use App\Controller\DonationHistoryC;
use App\Entity\CategoryEntity;

// BCE route:
// Boundary/DonationHistoryUI.php -> Controller/DonationHistoryC.php -> Entity/Donation.php.
// This Boundary lets Donors search donation history by FSA category and date period.
require_login(['donor']);

$user = current_user();
$userId = (int) $user['id'];

// Boundary -> Controller.
$historyController = new DonationHistoryC();
$categoryEntity = new CategoryEntity();

$filters = [
    'history_category_id' => (string) ($_GET['history_category_id'] ?? ''),
    'history_from' => (string) ($_GET['history_from'] ?? ''),
    'history_to' => (string) ($_GET['history_to'] ?? ''),
];
$categories = $categoryEntity->getAll('', true);
$history = $historyController->displayResults($userId, $filters);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donation History</title>
    <link rel="stylesheet" href="../assets/css/app.css">
</head>
<body>
    <?php render_donor_topbar('Donation History', 'history'); ?>

    <main class="page-shell donor-shell">
        <?php render_donor_flash_if_any(); ?>

        <section class="panel donor-panel">
            <div class="panel__header panel__header--stack">
                <div>
                    <h2>Search donation history</h2>
                </div>
                <form method="get" class="inline-filters donor-filters donor-filters--history">
                    <select name="history_category_id">
                        <option value="">All FSA categories</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= e((string) $category['id']) ?>" <?= selected_if($filters['history_category_id'], $category['id']) ?>>
                                <?= e($category['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <input type="date" name="history_from" value="<?= e($filters['history_from']) ?>" aria-label="Donation from date">
                    <input type="date" name="history_to" value="<?= e($filters['history_to']) ?>" aria-label="Donation to date">
                    <button type="submit" class="button button--primary">Search</button>
                </form>
            </div>

            <div class="table-shell">
                <table>
                    <thead>
                        <tr>
                            <th>Activity</th>
                            <th>Category</th>
                            <th>Amount</th>
                            <th>Progress</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($history as $donation): ?>
                            <tr>
                                <td><?= e($donation['campaign_title']) ?></td>
                                <td><?= e($donation['category_name']) ?></td>
                                <td><?= e(format_currency($donation['amount'])) ?></td>
                                <td><?= e((string) progress_percent($donation)) ?>%</td>
                                <td><?= e(format_date($donation['donated_at'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if ($history === []): ?>
                            <tr><td colspan="5">No donation history found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</body>
</html>
