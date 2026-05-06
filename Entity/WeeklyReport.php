<?php
declare(strict_types=1);

namespace App\Entity;

use App\Config\Database;
use PDO;

// Entity layer for Platform Manager weekly report data.
// Called by Controller/WeeklyReportC.php in the BCE flow.
final class WeeklyReport
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function generateReport(string $weekPeriod): array
    {
        $weekStart = date('Y-m-d', strtotime('monday this week', strtotime($weekPeriod)));
        $weekEnd = date('Y-m-d', strtotime($weekStart . ' +6 days'));

        return [
            'reportID' => 'WEEKLY-' . str_replace('-', '', $weekStart),
            'weekPeriod' => $weekStart . ' to ' . $weekEnd,
            'reportDetails' => $this->getReportDetails($weekStart . ' 00:00:00', $weekEnd . ' 23:59:59'),
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
