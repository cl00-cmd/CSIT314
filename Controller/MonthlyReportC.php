<?php
declare(strict_types=1);

namespace App\Controller;

// Load the MonthlyReport Entity class.
use App\Entity\MonthlyReport;

// BCE route:
// Boundary/MonthlyReportUI.php calls this Controller.
// This Controller validates the monthly period before calling Entity/MonthlyReport.php.
final class MonthlyReportC
{
    private MonthlyReport $monthlyReport;

    // Creates the MonthlyReport Entity object.
    public function __construct()
    {
        // Controller -> Entity.
        $this->monthlyReport = new MonthlyReport();
    }

    // Validates the selected monthly report period.
    public function validatePeriod(string $monthPeriod): array
    {
        $monthPeriod = trim($monthPeriod);

        // Checks whether the selected month period is valid.
        if ($monthPeriod === '' || strtotime($monthPeriod . '-01') === false) {
            return [
                'success' => false,
                'message' => 'Please select a valid month period.'
            ];
        }

        return [
            'success' => true,
            'monthPeriod' => date('Y-m', strtotime($monthPeriod . '-01'))
        ];
    }

    // Generates the monthly report after validation.
    public function generateReport(string $monthPeriod): array
    {
        $validated = $this->validatePeriod($monthPeriod);

        // Stops the report generation when validation fails.
        if (!$validated['success']) {
            return [
                'success' => false,
                'message' => $validated['message'],
                'report' => null
            ];
        }

        return [
            'success' => true,
            'message' => 'Monthly report generated.',

            // Controller -> Entity to retrieve report data.
            'report' => $this->monthlyReport->generateReport(
                $validated['monthPeriod']
            ),
        ];
    }
}