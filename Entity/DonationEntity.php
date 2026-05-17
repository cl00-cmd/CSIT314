<?php
declare(strict_types=1);

namespace App\Entity;

// Load database connection, PDO, and RuntimeException.
use App\Config\Database;
use PDO;
use RuntimeException;

// Entity layer for donation records and donor donation history.
// Called through Entity/Donation.php by Donor donation and history Controllers.
final class DonationEntity
{
    private PDO $db;

    // Creates the database connection.
    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    // Creates a donation record and updates the campaign total amount.
    public function createDonation(int $donorUserId, int $campaignId, float $amount, string $message): bool
    {
        // Starts the database transaction so both donation and campaign updates succeed together.
        $this->db->beginTransaction();

        // Locks and retrieves the selected campaign record.
        $campaignStatement = $this->db->prepare(
            'SELECT id, status
             FROM campaigns
             WHERE id = :campaign_id
             LIMIT 1
             FOR UPDATE'
        );
        $campaignStatement->execute(['campaign_id' => $campaignId]);

        $campaign = $campaignStatement->fetch();

        // Stops the donation if the campaign does not exist.
        if ($campaign === false) {
            $this->db->rollBack();
            throw new RuntimeException('Campaign not found.');
        }

        // Stops the donation if the campaign is not accepting donations.
        if ($campaign['status'] !== 'active' && $campaign['status'] !== 'completed') {
            $this->db->rollBack();
            throw new RuntimeException('This campaign is not accepting donations.');
        }

        // Saves the donation record.
        $donationStatement = $this->db->prepare(
            'INSERT INTO donations (campaign_id, donor_user_id, amount, message)
             VALUES (:campaign_id, :donor_user_id, :amount, :message)'
        );
        $donationStatement->execute([
            'campaign_id' => $campaignId,
            'donor_user_id' => $donorUserId,
            'amount' => $amount,
            'message' => $message,
        ]);

        // Updates the campaign current amount after the donation.
        $updateStatement = $this->db->prepare(
            'UPDATE campaigns
             SET current_amount = current_amount + :amount
             WHERE id = :campaign_id'
        );
        $updateStatement->execute([
            'campaign_id' => $campaignId,
            'amount' => $amount,
        ]);

        // Confirms all transaction changes.
        $this->db->commit();

        return true;
    }

    // Retrieves the donor donation summary.
    public function getDonorSummary(int $donorUserId): array
    {
        $statement = $this->db->prepare(
            'SELECT COUNT(*) AS donation_count,
                    COALESCE(SUM(amount), 0) AS total_amount
             FROM donations
             WHERE donor_user_id = :donor_user_id'
        );
        $statement->execute([
            'donor_user_id' => $donorUserId,
        ]);

        return $statement->fetch() ?: [
            'donation_count' => 0,
            'total_amount' => 0,
        ];
    }

    // Retrieves donor donation history records based on selected filters.
    public function getDonorHistory(int $donorUserId, array $filters = []): array
    {
        $sql = "SELECT d.id, d.amount, d.message, d.donated_at,
                       c.title AS campaign_title, c.status AS campaign_status,
                       c.funding_goal, c.current_amount,
                       cat.name AS category_name
                FROM donations d
                INNER JOIN campaigns c ON c.id = d.campaign_id
                INNER JOIN categories cat ON cat.id = c.category_id
                WHERE d.donor_user_id = :donor_user_id";

        $parameters = [
            'donor_user_id' => $donorUserId,
        ];

        // Filters donation history by category.
        if (!empty($filters['category_id'])) {
            $sql .= ' AND c.category_id = :category_id';
            $parameters['category_id'] = (int) $filters['category_id'];
        }

        // Filters donation history by start date.
        if (!empty($filters['from'])) {
            $sql .= ' AND DATE(d.donated_at) >= :from_date';
            $parameters['from_date'] = $filters['from'];
        }

        // Filters donation history by end date.
        if (!empty($filters['to'])) {
            $sql .= ' AND DATE(d.donated_at) <= :to_date';
            $parameters['to_date'] = $filters['to'];
        }

        // Shows latest donations first.
        $sql .= ' ORDER BY d.donated_at DESC, d.id DESC';

        $statement = $this->db->prepare($sql);
        $statement->execute($parameters);

        return $statement->fetchAll();
    }
}