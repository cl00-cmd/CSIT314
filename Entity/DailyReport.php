<?php
declare(strict_types=1);

namespace App\Entity;

// Load database connection and PDO.
use App\Config\Database;
use PDO;

// Entity layer for Platform Manager daily report data.
// Called by Controller/DailyReportC.php in the BCE flow.
final class DailyReport
{
    private PDO $db;

    // Creates the database connection.
    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    // Generates the daily report using the selected report date.
    public function generateReport(string $reportDate): array
    {
        // Defines the full start and end timestamps for the selected day.
        $startDate = $reportDate . ' 00:00:00';
        $endDate = $reportDate . ' 23:59:59';

        return [
            'reportID' => 'DAILY-' . str_replace('-', '', $reportDate),
            'reportDate' => $reportDate,

            // Retrieves the calculated report statistics.
            'reportDetails' => $this->getReportDetails($startDate, $endDate),
        ];
    }

    // Retrieves daily report statistics between the selected timestamps.
    private function getReportDetails(string $startDate, string $endDate): array
    {
        $statement = $this->db->prepare(
            "SELECT
                (SELECT COUNT(*) FROM campaigns WHERE created_at BETWEEN ? AND ?) AS newCampaigns,

                (SELECT COUNT(*)
                 FROM campaigns
                 WHERE status = 'completed'
                 AND COALESCE(end_date, created_at) BETWEEN ? AND ?) AS completedCampaigns,

                (SELECT COUNT(*)
                 FROM donations
                 WHERE donated_at BETWEEN ? AND ?) AS donationCount,

                (SELECT COALESCE(SUM(amount), 0)
                 FROM donations
                 WHERE donated_at BETWEEN ? AND ?) AS donationValue,

                (SELECT COUNT(*)
                 FROM users
                 WHERE created_at BETWEEN ? AND ?) AS newUsers"
        );

        // Executes all report summary calculations.
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