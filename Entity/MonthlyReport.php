<?php
declare(strict_types=1);

namespace App\Entity;

use App\Config\Database;
use PDO;

// Entity layer for Platform Manager monthly report data.
// Called by Controller/MonthlyReportC.php in the BCE flow.
final class MonthlyReport
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function generateReport(string $monthPeriod): array
    {
        $monthStart = date('Y-m-01', strtotime($monthPeriod . '-01'));
        $monthEnd = date('Y-m-t', strtotime($monthStart));

        return [
            'reportID' => 'MONTHLY-' . str_replace('-', '', $monthPeriod),
            'monthPeriod' => date('F Y', strtotime($monthStart)),
            'reportDetails' => $this->getReportDetails($monthStart . ' 00:00:00', $monthEnd . ' 23:59:59'),
        ];
    }

    private function getReportDetails(string $startDate, string $endDate): array
    {
        $statement = $this->db->prepare(
            "SELECT
                (SELECT COUNT(*) FROM campaigns WHERE created_at BETWEEN ? AND ?) AS newCampaigns,
                (SELECT COUNT(*) FROM campaigns WHERE status = 'completed' AND COALESCE(end_date, created_at) BETWEEN ? AND ?) AS completedCampaigns,
                (SELECT COUNT(*) FROM donations WHERE donated_at BETWEEN ? AND ?) AS donationCount,
                (SELECT COALESCE(SUM(amount), 0) FROM donations WHERE donated_at BETWEEN ? AND ?) AS donationValue,
                (SELECT COUNT(*) FROM users WHERE created_at BETWEEN ? AND ?) AS newUsers"
        );
        $statement->execute([
            $startDate,
            $endDate,
            $startDate,
            $endDate,
            $startDate,
            $endDate,
            $startDate,
            $endDate,
            $startDate,
            $endDate,
        ]);

        return $statement->fetch() ?: [];
    }
}
