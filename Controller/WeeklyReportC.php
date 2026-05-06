<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\WeeklyReport;

// BCE route:
// Boundary/WeeklyReportUI.php calls this Controller.
// This Controller validates the weekly period before calling Entity/WeeklyReport.php.
final class WeeklyReportC
{
    private WeeklyReport $weeklyReport;

    public function __construct()
    {
        // Controller -> Entity.
        $this->weeklyReport = new WeeklyReport();
    }

    public function validatePeriod(string $weekPeriod): array
    {
        $weekPeriod = trim($weekPeriod);
        if ($weekPeriod === '' || strtotime($weekPeriod) === false) {
            return ['success' => false, 'message' => 'Please select a valid week period.'];
        }

        return ['success' => true, 'weekPeriod' => date('Y-m-d', strtotime($weekPeriod))];
    }

    public function generateReport(string $weekPeriod): array
    {
        $validated = $this->validatePeriod($weekPeriod);
        if (!$validated['success']) {
            return ['success' => false, 'message' => $validated['message'], 'report' => null];
        }

        return [
            'success' => true,
            'message' => 'Weekly report generated.',
            'report' => $this->weeklyReport->generateReport($validated['weekPeriod']),
        ];
    }
}
