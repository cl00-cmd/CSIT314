<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/platform_shared.php';

use App\Controller\DailyReportC;

// BCE route:
// Boundary/DailyReportUI.php -> Controller/DailyReportC.php -> Entity/DailyReport.php.
// This Boundary collects the selected report date and displays the generated report.
require_login(['platform_manager']);

$reportDate = (string) ($_GET['reportDate'] ?? date('Y-m-d'));
$result = (new DailyReportC())->generateReport($reportDate);
$report = $result['report'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DailyReportUI</title>
    <link rel="stylesheet" href="../assets/css/app.css">
</head>
<body>
    <?php render_platform_topbar('DailyReportUI', 'daily'); ?>

    <main class="page-shell">
        <?php render_platform_flash_if_any(); ?>
        <?php if (!$result['success']): ?>
            <div class="flash flash--error"><?= e($result['message']) ?></div>
        <?php endif; ?>

        <section class="panel">
            <div class="panel__header panel__header--stack">
                <div>
                    <h2>Generate Daily Report</h2>
                </div>
                <form method="get" class="inline-filters">
                    <input type="date" name="reportDate" value="<?= e($reportDate) ?>">
                    <button type="submit" class="button button--ghost">Generate</button>
                </form>
            </div>

            <?php if ($report !== null): ?>
                <div class="stats-grid">
                    <article class="stat-card"><span>Report ID</span><strong><?= e($report['reportID']) ?></strong></article>
                    <article class="stat-card"><span>New Campaigns</span><strong><?= e((string) $report['reportDetails']['newCampaigns']) ?></strong></article>
                    <article class="stat-card"><span>Completed Campaigns</span><strong><?= e((string) $report['reportDetails']['completedCampaigns']) ?></strong></article>
                    <article class="stat-card"><span>Donation Value</span><strong><?= e(format_currency($report['reportDetails']['donationValue'])) ?></strong></article>
                </div>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>
