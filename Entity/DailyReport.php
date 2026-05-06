<?php
declare(strict_types=1);

namespace App\Entity;

use App\Config\Database;
use PDO;

// Entity layer for Platform Manager daily report data.
// Called by Controller/DailyReportC.php in the BCE flow.
final class DailyReport
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function generateReport(string $reportDate): array
    {
        $startDate = $reportDate . ' 00:00:00';
        $endDate = $reportDate . ' 23:59:59';

        return [
            'reportID' => 'DAILY-' . str_replace('-', '', $reportDate),
            'reportDate' => $reportDate,
            'reportDetails' => $this->getReportDetails($startDate, $endDate),
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
