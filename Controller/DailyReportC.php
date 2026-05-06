<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\DailyReport;

// BCE route:
// Boundary/DailyReportUI.php calls this Controller.
// This Controller validates the date before calling Entity/DailyReport.php.
final class DailyReportC
{
    private DailyReport $dailyReport;

    public function __construct()
    {
        // Controller -> Entity.
        $this->dailyReport = new DailyReport();
    }

    public function validateDate(string $reportDate): array
    {
        $reportDate = trim($reportDate);
        if ($reportDate === '' || strtotime($reportDate) === false) {
            return ['success' => false, 'message' => 'Please select a valid report date.'];
        }

        return ['success' => true, 'reportDate' => date('Y-m-d', strtotime($reportDate))];
    }

    public function generateReport(string $reportDate): array
    {
        $validated = $this->validateDate($reportDate);
        if (!$validated['success']) {
            return ['success' => false, 'message' => $validated['message'], 'report' => null];
        }

        return [
            'success' => true,
            'message' => 'Daily report generated.',
            'report' => $this->dailyReport->generateReport($validated['reportDate']),
        ];
    }
}
