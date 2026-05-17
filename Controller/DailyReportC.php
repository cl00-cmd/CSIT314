<?php
declare(strict_types=1);

namespace App\Controller;

// Load the DailyReport Entity class.
use App\Entity\DailyReport;

// BCE route:
// Boundary/DailyReportUI.php calls this Controller.
// This Controller validates the date before calling Entity/DailyReport.php.
final class DailyReportC
{
    private DailyReport $dailyReport;

    // Creates the DailyReport Entity object.
    public function __construct()
    {
        // Controller -> Entity.
        $this->dailyReport = new DailyReport();
    }

    // Validates the selected report date.
    public function validateDate(string $reportDate): array
    {
        $reportDate = trim($reportDate);

        if ($reportDate === '' || strtotime($reportDate) === false) {
            return ['success' => false, 'message' => 'Please select a valid report date.'];
        }

        return [
            'success' => true,
            'reportDate' => date('Y-m-d', strtotime($reportDate)),
        ];
    }

    // Generates the daily report after validation.
    public function generateReport(string $reportDate): array
    {
        $validated = $this->validateDate($reportDate);

        // Stops report generation when validation fails.
        if (!$validated['success']) {
            return [
                'success' => false,
                'message' => $validated['message'],
                'report' => null
            ];
        }

        // Controller -> Entity to generate the report.
        return [
            'success' => true,
            'message' => 'Daily report generated.',
            'report' => $this->dailyReport->generateReport($validated['reportDate']),
        ];
    }
}