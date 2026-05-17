<?php
declare(strict_types=1);

// Load system setup, shared platform layout, and helper functions
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/platform_shared.php';

use App\Controller\MonthlyReportC;

// BCE route:
// Boundary/MonthlyReportUI.php -> Controller/MonthlyReportC.php -> Entity/MonthlyReport.php.
// This Boundary collects the selected month period and displays the generated report.
require_login(['platform_manager']);

// Gets the selected month period or defaults to the current month.
$monthPeriod = (string) ($_GET['monthPeriod'] ?? date('Y-m'));

// Boundary -> Controller to generate the monthly report.
$result = (new MonthlyReportC())->generateReport($monthPeriod);

// Retrieves the generated report data.
$report = $result['report'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MonthlyReportUI</title>
    <link rel="stylesheet" href="../assets/css/app.css">
</head>
<body>

    <!-- Platform manager top navigation bar -->
    <?php render_platform_topbar('MonthlyReportUI', 'monthly'); ?>

    <main class="page-shell">

        <!-- Display success or error message if available -->
        <?php render_platform_flash_if_any(); ?>

        <!-- Display report generation error message -->
        <?php if (!$result['success']): ?>
            <div class="flash flash--error">
                <?= e($result['message']) ?>
            </div>
        <?php endif; ?>

        <!-- Monthly report generation section -->
        <section class="panel">
            <div class="panel__header panel__header--stack">
                <div>
                    <h2>Generate Monthly Report</h2>
                </div>

                <!-- Monthly report filter form -->
                <form method="get" class="inline-filters">
                    <input type="month" name="monthPeriod" value="<?= e($monthPeriod) ?>">

                    <button type="submit" class="button button--ghost">
                        Generate
                    </button>
                </form>
            </div>

            <!-- Display generated monthly report summary -->
            <?php if ($report !== null): ?>
                <div class="stats-grid">

                    <article class="stat-card">
                        <span>Report ID</span>
                        <strong><?= e($report['reportID']) ?></strong>
                    </article>

                    <article class="stat-card">
                        <span>Month Period</span>
                        <strong><?= e($report['monthPeriod']) ?></strong>
                    </article>

                    <article class="stat-card">
                        <span>New Users</span>
                        <strong><?= e((string) $report['reportDetails']['newUsers']) ?></strong>
                    </article>

                    <article class="stat-card">
                        <span>Donation Value</span>
                        <strong><?= e(format_currency($report['reportDetails']['donationValue'])) ?></strong>
                    </article>

                </div>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>