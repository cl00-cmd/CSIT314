<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\MonthlyReport;

// BCE route:
// Boundary/MonthlyReportUI.php calls this Controller.
// This Controller validates the monthly period before calling Entity/MonthlyReport.php.
final class MonthlyReportC
{
    private MonthlyReport $monthlyReport;

    public function __construct()
    {
        // Controller -> Entity.
        $this->monthlyReport = new MonthlyReport();
    }

    public function validatePeriod(string $monthPeriod): array
    {
        $monthPeriod = trim($monthPeriod);
        if ($monthPeriod === '' || strtotime($monthPeriod . '-01') === false) {
            return ['success' => false, 'message' => 'Please select a valid month period.'];
        }

        return ['success' => true, 'monthPeriod' => date('Y-m', strtotime($monthPeriod . '-01'))];
    }

    public function generateReport(string $monthPeriod): array
    {
        $validated = $this->validatePeriod($monthPeriod);
        if (!$validated['success']) {
            return ['success' => false, 'message' => $validated['message'], 'report' => null];
        }

        return [
            'success' => true,
            'message' => 'Monthly report generated.',
            'report' => $this->monthlyReport->generateReport($validated['monthPeriod']),
        ];
    }
}
