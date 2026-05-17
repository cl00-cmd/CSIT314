<?php
declare(strict_types=1);

namespace App\Entity;

// Load database connection and PDO.
use App\Config\Database;
use PDO;

// Entity layer for Platform Manager weekly report data.
// Called by Controller/WeeklyReportC.php in the BCE flow.
final class WeeklyReport
{
    private PDO $db;

    // Creates the database connection.
    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    // Generates the weekly report based on the selected week period.
    public function generateReport(string $weekPeriod): array
    {
        // Calculates the start and end dates of the selected week.
        $weekStart = date('Y-m-d', strtotime('monday this week', strtotime($weekPeriod)));
        $weekEnd = date('Y-m-d', strtotime($weekStart . ' +6 days'));

        return [
            'reportID' => 'WEEKLY-' . str_replace('-', '', $weekStart),
            'weekPeriod' => $weekStart . ' to ' . $weekEnd,
            'reportDetails' => $this->getReportDetails(
                $weekStart . ' 00:00:00',
                $weekEnd . ' 23:59:59'
            ),
        ];
    }

    // Retrieves the weekly report statistics from the database.
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