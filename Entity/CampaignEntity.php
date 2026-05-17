<?php
declare(strict_types=1);

namespace App\Entity;

// Load database connection and PDO.
use App\Config\Database;
use PDO;

// Entity layer for fundraising activity/campaign data.
// Called by Fund Raiser Controllers and Donor BCE Controllers.
final class CampaignEntity
{
    private PDO $db;

    // Creates the database connection.
    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    // Retrieves dashboard summary statistics for a Fund Raiser.
    public function getFundraiserStats(int $fundraiserUserId): array
    {
        $summaryStatement = $this->db->prepare(
            "SELECT
                COUNT(*) AS total_campaigns,
                SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) AS active_campaigns,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) AS completed_campaigns,
                COALESCE(SUM(current_amount), 0) AS total_raised
             FROM campaigns
             WHERE fundraiser_user_id = :fundraiser_user_id"
        );
        $summaryStatement->execute(['fundraiser_user_id' => $fundraiserUserId]);
        $summary = $summaryStatement->fetch() ?: [];

        // Retrieves total views and shortlists for the Fund Raiser's campaigns.
        $interestStatement = $this->db->prepare(
            "SELECT
                (SELECT COUNT(*)
                 FROM campaign_views v
                 INNER JOIN campaigns c ON c.id = v.campaign_id
                 WHERE c.fundraiser_user_id = :views_fundraiser_user_id) AS total_views,
                (SELECT COUNT(*)
                 FROM favourites f
                 INNER JOIN campaigns c2 ON c2.id = f.campaign_id
                 WHERE c2.fundraiser_user_id = :shortlists_fundraiser_user_id) AS total_shortlists"
        );
        $interestStatement->execute([
            'views_fundraiser_user_id' => $fundraiserUserId,
            'shortlists_fundraiser_user_id' => $fundraiserUserId,
        ]);
        $interest = $interestStatement->fetch() ?: [];

        // Returns default values merged with database results.
        return array_merge([
            'total_campaigns' => 0,
            'active_campaigns' => 0,
            'completed_campaigns' => 0,
            'total_raised' => 0,
            'total_views' => 0,
            'total_shortlists' => 0,
        ], $summary, $interest);
    }

    // Retrieves campaigns created by a Fund Raiser, with optional filters.
    public function getCampaignsByFundraiser(int $fundraiserUserId, array $filters = []): array
    {
        $sql = "SELECT c.id, c.title, c.media, c.service_type, c.story, c.funding_goal, c.current_amount,
                       c.status, c.start_date, c.end_date, c.created_at,
                       cat.name AS category_name, cat.id AS category_id,
                       COALESCE(v.view_count, 0) AS view_count,
                       COALESCE(f.shortlist_count, 0) AS shortlist_count
                FROM campaigns c
                INNER JOIN categories cat ON cat.id = c.category_id
                LEFT JOIN (
                    SELECT campaign_id, COUNT(*) AS view_count
                    FROM campaign_views
                    GROUP BY campaign_id
                ) v ON v.campaign_id = c.id
                LEFT JOIN (
                    SELECT campaign_id, COUNT(*) AS shortlist_count
                    FROM favourites
                    GROUP BY campaign_id
                ) f ON f.campaign_id = c.id
                WHERE c.fundraiser_user_id = :fundraiser_user_id";

        $parameters = ['fundraiser_user_id' => $fundraiserUserId];

        // Filter by campaign status.
        if (!empty($filters['status'])) {
            $sql .= ' AND c.status = :status';
            $parameters['status'] = $filters['status'];
        }

        // Filter by keyword across title, story, service type, and category.
        if (!empty($filters['keyword'])) {
            $sql .= ' AND (
                c.title LIKE :title_term
                OR c.story LIKE :story_term
                OR c.service_type LIKE :service_term
                OR cat.name LIKE :category_term
            )';
            $term = '%' . $filters['keyword'] . '%';
            $parameters['title_term'] = $term;
            $parameters['story_term'] = $term;
            $parameters['service_term'] = $term;
            $parameters['category_term'] = $term;
        }

        // Filter by service type.
        if (!empty($filters['service_type'])) {
            $sql .= ' AND c.service_type = :service_type';
            $parameters['service_type'] = $filters['service_type'];
        }

        // Filter by start date of history period.
        if (!empty($filters['from'])) {
            $sql .= ' AND DATE(COALESCE(c.end_date, c.created_at)) >= :from_date';
            $parameters['from_date'] = $filters['from'];
        }

        // Filter by end date of history period.
        if (!empty($filters['to'])) {
            $sql .= ' AND DATE(COALESCE(c.end_date, c.created_at)) <= :to_date';
            $parameters['to_date'] = $filters['to'];
        }

        $sql .= ' ORDER BY c.created_at DESC, c.id DESC';

        // Executes the campaign search query.
        $statement = $this->db->prepare($sql);
        $statement->execute($parameters);

        return $statement->fetchAll();
    }

    // Retrieves one campaign owned by the Fund Raiser for editing.
    public function getCampaignForFundraiser(int $fundraiserUserId, int $campaignId): ?array
    {
        $statement = $this->db->prepare(
            'SELECT id, category_id, title, media, service_type, story, funding_goal, current_amount,
                    status, start_date, end_date
             FROM campaigns
             WHERE fundraiser_user_id = :fundraiser_user_id
               AND id = :campaign_id
             LIMIT 1'
        );
        $statement->execute([
            'fundraiser_user_id' => $fundraiserUserId,
            'campaign_id' => $campaignId,
        ]);

        return $statement->fetch() ?: null;
    }

    // Creates a new fundraising campaign.
    public function createCampaign(int $fundraiserUserId, array $data): bool
    {
        $statement = $this->db->prepare(
            'INSERT INTO campaigns (
                fundraiser_user_id, category_id, title, media, service_type, story,
                funding_goal, current_amount, status, start_date, end_date
             ) VALUES (
                :fundraiser_user_id, :category_id, :title, :media, :service_type, :story,
                :funding_goal, 0, :status, :start_date, :end_date
             )'
        );

        return $statement->execute([
            'fundraiser_user_id' => $fundraiserUserId,
            'category_id' => $data['category_id'],
            'title' => $data['title'],
            'media' => $data['media'] ?? '',
            'service_type' => $data['service_type'],
            'story' => $data['story'],
            'funding_goal' => $data['funding_goal'],
            'status' => $data['status'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'] !== '' ? $data['end_date'] : null,
        ]);
    }

    // Updates an existing fundraising campaign.
    public function updateCampaign(int $fundraiserUserId, int $campaignId, array $data): bool
    {
        $statement = $this->db->prepare(
            'UPDATE campaigns
             SET category_id = :category_id,
                 title = :title,
                 media = :media,
                 service_type = :service_type,
                 story = :story,
                 funding_goal = :funding_goal,
                 status = :status,
                 start_date = :start_date,
                 end_date = :end_date
             WHERE id = :campaign_id
               AND fundraiser_user_id = :fundraiser_user_id'
        );

        return $statement->execute([
            'campaign_id' => $campaignId,
            'fundraiser_user_id' => $fundraiserUserId,
            'category_id' => $data['category_id'],
            'title' => $data['title'],
            'media' => $data['media'] ?? '',
            'service_type' => $data['service_type'],
            'story' => $data['story'],
            'funding_goal' => $data['funding_goal'],
            'status' => $data['status'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'] !== '' ? $data['end_date'] : null,
        ]);
    }

    // Deletes a campaign owned by the Fund Raiser.
    public function deleteCampaign(int $fundraiserUserId, int $campaignId): bool
    {
        $statement = $this->db->prepare(
            'DELETE FROM campaigns
             WHERE id = :campaign_id
               AND fundraiser_user_id = :fundraiser_user_id'
        );

        return $statement->execute([
            'campaign_id' => $campaignId,
            'fundraiser_user_id' => $fundraiserUserId,
        ]);
    }

    // Retrieves campaigns that donors can search and view.
    public function getDiscoverableCampaigns(int $donorUserId, array $filters = []): array
    {
        $sql = "SELECT c.id, c.title, c.service_type, c.story, c.funding_goal, c.current_amount,
                       c.status, c.start_date, c.end_date, c.created_at,
                       cat.name AS category_name,
                       u.full_name AS fundraiser_name,
                       COALESCE(v.view_count, 0) AS view_count,
                       COALESCE(f.shortlist_count, 0) AS shortlist_count,
                       CASE WHEN fav.id IS NULL THEN 0 ELSE 1 END AS is_favourite
                FROM campaigns c
                INNER JOIN categories cat ON cat.id = c.category_id
                INNER JOIN users u ON u.id = c.fundraiser_user_id
                LEFT JOIN favourites fav
                    ON fav.campaign_id = c.id AND fav.donor_user_id = :donor_user_id
                LEFT JOIN (
                    SELECT campaign_id, COUNT(*) AS view_count
                    FROM campaign_views
                    GROUP BY campaign_id
                ) v ON v.campaign_id = c.id
                LEFT JOIN (
                    SELECT campaign_id, COUNT(*) AS shortlist_count
                    FROM favourites
                    GROUP BY campaign_id
                ) f ON f.campaign_id = c.id
                WHERE c.status IN ('active', 'completed')";

        $parameters = ['donor_user_id' => $donorUserId];

        // Filter donor search by keyword.
        if (!empty($filters['search'])) {
            $sql .= ' AND (
                c.title LIKE :title_term
                OR c.story LIKE :story_term
                OR c.service_type LIKE :service_term
                OR cat.name LIKE :category_term
            )';
            $term = '%' . $filters['search'] . '%';
            $parameters['title_term'] = $term;
            $parameters['story_term'] = $term;
            $parameters['service_term'] = $term;
            $parameters['category_term'] = $term;
        }

        // Filter donor search by category.
        if (!empty($filters['category_id'])) {
            $sql .= ' AND c.category_id = :category_id';
            $parameters['category_id'] = (int) $filters['category_id'];
        }

        // Filter donor search by start date.
        if (!empty($filters['from'])) {
            $sql .= ' AND DATE(c.created_at) >= :from_date';
            $parameters['from_date'] = $filters['from'];
        }

        // Filter donor search by end date.
        if (!empty($filters['to'])) {
            $sql .= ' AND DATE(c.created_at) <= :to_date';
            $parameters['to_date'] = $filters['to'];
        }

        $sql .= ' ORDER BY c.status ASC, c.created_at DESC, c.id DESC';

        // Executes donor search query.
        $statement = $this->db->prepare($sql);
        $statement->execute($parameters);

        return $statement->fetchAll();
    }

    // Retrieves campaigns saved in a donor favourite list.
    public function getFavouriteCampaigns(int $donorUserId, array $filters = []): array
    {
        $sql = "SELECT c.id, c.title, c.service_type, c.funding_goal, c.current_amount, c.status,
                    cat.name AS category_name, u.full_name AS fundraiser_name, f.created_at,
                    COALESCE(v.view_count, 0) AS view_count,
                    COALESCE(s.shortlist_count, 0) AS shortlist_count
             FROM favourites f
             INNER JOIN campaigns c ON c.id = f.campaign_id
             INNER JOIN categories cat ON cat.id = c.category_id
             INNER JOIN users u ON u.id = c.fundraiser_user_id
             LEFT JOIN (
                SELECT campaign_id, COUNT(*) AS view_count
                FROM campaign_views
                GROUP BY campaign_id
             ) v ON v.campaign_id = c.id
             LEFT JOIN (
                SELECT campaign_id, COUNT(*) AS shortlist_count
                FROM favourites
                GROUP BY campaign_id
             ) s ON s.campaign_id = c.id
             WHERE f.donor_user_id = :donor_user_id";

        $parameters = ['donor_user_id' => $donorUserId];

        // Filter favourite list by search keyword.
        if (!empty($filters['favourite_search'])) {
            $sql .= ' AND (
                c.title LIKE :favourite_title_term
                OR c.service_type LIKE :favourite_service_term
                OR cat.name LIKE :favourite_category_term
                OR u.full_name LIKE :favourite_fundraiser_term
            )';
            $term = '%' . $filters['favourite_search'] . '%';
            $parameters['favourite_title_term'] = $term;
            $parameters['favourite_service_term'] = $term;
            $parameters['favourite_category_term'] = $term;
            $parameters['favourite_fundraiser_term'] = $term;
        }

        $sql .= ' ORDER BY f.created_at DESC';

        // Executes favourite list search query.
        $statement = $this->db->prepare($sql);
        $statement->execute($parameters);

        return $statement->fetchAll();
    }

    // Retrieves details for one campaign from the donor side.
    public function getCampaignDetails(int $campaignId, int $donorUserId): ?array
    {
        $statement = $this->db->prepare(
            "SELECT c.id, c.title, c.service_type, c.story, c.funding_goal, c.current_amount,
                    c.status, c.start_date, c.end_date, c.created_at,
                    cat.name AS category_name, u.full_name AS fundraiser_name,
                    COALESCE(v.view_count, 0) AS view_count,
                    COALESCE(f.shortlist_count, 0) AS shortlist_count,
                    CASE WHEN fav.id IS NULL THEN 0 ELSE 1 END AS is_favourite
             FROM campaigns c
             INNER JOIN categories cat ON cat.id = c.category_id
             INNER JOIN users u ON u.id = c.fundraiser_user_id
             LEFT JOIN favourites fav
                ON fav.campaign_id = c.id AND fav.donor_user_id = :donor_user_id
             LEFT JOIN (
                SELECT campaign_id, COUNT(*) AS view_count
                FROM campaign_views
                GROUP BY campaign_id
             ) v ON v.campaign_id = c.id
             LEFT JOIN (
                SELECT campaign_id, COUNT(*) AS shortlist_count
                FROM favourites
                GROUP BY campaign_id
             ) f ON f.campaign_id = c.id
             WHERE c.id = :campaign_id
             LIMIT 1"
        );
        $statement->execute([
            'campaign_id' => $campaignId,
            'donor_user_id' => $donorUserId,
        ]);

        return $statement->fetch() ?: null;
    }

    // Records that a campaign has been viewed.
    public function recordView(int $campaignId, ?int $viewerUserId): void
    {
        $statement = $this->db->prepare(
            'INSERT INTO campaign_views (campaign_id, viewer_user_id)
             VALUES (:campaign_id, :viewer_user_id)'
        );
        $statement->execute([
            'campaign_id' => $campaignId,
            'viewer_user_id' => $viewerUserId,
        ]);
    }

    // Adds a campaign into a donor favourite list.
    public function addFavourite(int $donorUserId, int $campaignId): bool
    {
        $statement = $this->db->prepare(
            'INSERT IGNORE INTO favourites (donor_user_id, campaign_id)
             VALUES (:donor_user_id, :campaign_id)'
        );

        return $statement->execute([
            'donor_user_id' => $donorUserId,
            'campaign_id' => $campaignId,
        ]);
    }

    // Removes a campaign from a donor favourite list.
    public function removeFavourite(int $donorUserId, int $campaignId): bool
    {
        $statement = $this->db->prepare(
            'DELETE FROM favourites
             WHERE donor_user_id = :donor_user_id
               AND campaign_id = :campaign_id'
        );

        return $statement->execute([
            'donor_user_id' => $donorUserId,
            'campaign_id' => $campaignId,
        ]);
    }
}