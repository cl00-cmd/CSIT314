<?php
declare(strict_types=1);

namespace App\Entity;

// Load database connection and PDO.
use App\Config\Database;
use PDO;

// Entity layer for platform management reports.
// Called by Controller/PlatformController.php.
final class ReportEntity
{
    private PDO $db;

    // Creates the database connection.
    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    // Retrieves platform summary based on the selected report period.
    public function getSummary(string $period): array
    {
        $startDate = $this->periodStartDate($period);

        $statement = $this->db->prepare(
            "SELECT
                (SELECT COUNT(*) FROM campaigns WHERE created_at >= :start_date) AS new_campaigns,
                (SELECT COUNT(*) FROM campaigns WHERE status = 'completed' AND COALESCE(end_date, created_at) >= :start_date) AS completed_campaigns,
                (SELECT COUNT(*) FROM donations WHERE donated_at >= :start_date) AS donations_count,
                (SELECT COALESCE(SUM(amount), 0) FROM donations WHERE donated_at >= :start_date) AS donations_value,
                (SELECT COUNT(*) FROM users WHERE created_at >= :start_date) AS new_users"
        );

        $statement->execute([
            'start_date' => $startDate,
        ]);

        return $statement->fetch() ?: [
            'new_campaigns' => 0,
            'completed_campaigns' => 0,
            'donations_count' => 0,
            'donations_value' => 0,
            'new_users' => 0,
        ];
    }

    // Retrieves category-level report breakdown.
    public function getCategoryBreakdown(string $period): array
    {
        $startDate = $this->periodStartDate($period);

        $statement = $this->db->prepare(
            "SELECT cat.name,
                    COUNT(DISTINCT c.id) AS campaign_count,
                    COALESCE(SUM(d.amount), 0) AS amount_raised
             FROM categories cat
             LEFT JOIN campaigns c ON c.category_id = cat.id
             LEFT JOIN donations d ON d.campaign_id = c.id AND d.donated_at >= :start_date
             WHERE cat.status = 'active'
             GROUP BY cat.id, cat.name
             ORDER BY amount_raised DESC, campaign_count DESC, cat.name ASC
             LIMIT 12"
        );

        $statement->execute([
            'start_date' => $startDate,
        ]);

        return $statement->fetchAll();
    }

    // Converts the selected period into a start date.
    private function periodStartDate(string $period): string
    {
        return match ($period) {
            'daily' => date('Y-m-d H:i:s', strtotime('-1 day')),
            'weekly' => date('Y-m-d H:i:s', strtotime('-7 days')),
            default => date('Y-m-d H:i:s', strtotime('-30 days')),
        };
    }
}