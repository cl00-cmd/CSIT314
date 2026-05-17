<?php
declare(strict_types=1);

namespace App\Controller;

// Load the Entity class used by this Controller.
use App\Entity\WeeklyReport;

// BCE route:
// Boundary/WeeklyReportUI.php calls this Controller.
// This Controller validates the weekly period before calling Entity/WeeklyReport.php.
final class WeeklyReportC
{
    private WeeklyReport $weeklyReport;

    // Creates the WeeklyReport Entity object.
    public function __construct()
    {
        // Controller -> Entity.
        $this->weeklyReport = new WeeklyReport();
    }

    // Validates the selected weekly period.
    public function validatePeriod(string $weekPeriod): array
    {
        // Removes extra spaces from the input.
        $weekPeriod = trim($weekPeriod);

        // Checks whether the selected date is valid.
        if ($weekPeriod === '' || strtotime($weekPeriod) === false) {
            return [
                'success' => false,
                'message' => 'Please select a valid week period.'
            ];
        }

        return [
            'success' => true,
            'weekPeriod' => date('Y-m-d', strtotime($weekPeriod))
        ];
    }

    // Generates the weekly report.
    public function generateReport(string $weekPeriod): array
    {
        // Validates the selected period first.
        $validated = $this->validatePeriod($weekPeriod);

        // Stops report generation if validation fails.
        if (!$validated['success']) {
            return [
                'success' => false,
                'message' => $validated['message'],
                'report' => null
            ];
        }

        return [
            'success' => true,
            'message' => 'Weekly report generated.',

            // Controller -> Entity to generate report data.
            'report' => $this->weeklyReport->generateReport(
                $validated['weekPeriod']
            ),
        ];
    }
}