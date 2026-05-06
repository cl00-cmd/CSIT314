<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/platform_shared.php';

use App\Controller\WeeklyReportC;

// BCE route:
// Boundary/WeeklyReportUI.php -> Controller/WeeklyReportC.php -> Entity/WeeklyReport.php.
// This Boundary collects the selected week period and displays the generated report.
require_login(['platform_manager']);

$weekPeriod = (string) ($_GET['weekPeriod'] ?? date('Y-m-d'));
$result = (new WeeklyReportC())->generateReport($weekPeriod);
$report = $result['report'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WeeklyReportUI</title>
    <link rel="stylesheet" href="../assets/css/app.css">
</head>
<body>
    <?php render_platform_topbar('WeeklyReportUI', 'weekly'); ?>

    <main class="page-shell">
        <?php render_platform_flash_if_any(); ?>
        <?php if (!$result['success']): ?>
            <div class="flash flash--error"><?= e($result['message']) ?></div>
        <?php endif; ?>

        <section class="panel">
            <div class="panel__header panel__header--stack">
                <div>
                    <h2>Generate Weekly Report</h2>
                </div>
                <form method="get" class="inline-filters">
                    <input type="date" name="weekPeriod" value="<?= e($weekPeriod) ?>">
                    <button type="submit" class="button button--ghost">Generate</button>
                </form>
            </div>

            <?php if ($report !== null): ?>
                <div class="stats-grid">
                    <article class="stat-card"><span>Report ID</span><strong><?= e($report['reportID']) ?></strong></article>
                    <article class="stat-card"><span>Week Period</span><strong><?= e($report['weekPeriod']) ?></strong></article>
                    <article class="stat-card"><span>Donations</span><strong><?= e((string) $report['reportDetails']['donationCount']) ?></strong></article>
                    <article class="stat-card"><span>Donation Value</span><strong><?= e(format_currency($report['reportDetails']['donationValue'])) ?></strong></article>
                </div>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>
