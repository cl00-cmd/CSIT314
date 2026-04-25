<?php
declare(strict_types=1);

namespace App\Entity;

// BCE entity for the Fund Raiser sequences:
// Controller/FundraisingActivityC.php, Controller/FundraisingViewC.php,
// Controller/FundraisingEditC.php, Controller/FundraisingSelectorC.php,
// Controller/FundraisingSearchC.php, Controller/FRViewsController.php,
// Controller/FRshortlistController.php, Controller/FRHistorySearchController.php,
// and Controller/FRHistoryDateSearchController.php all call this Entity.
final class FundraisingActivity
{
    public const SERVICE_TYPES = [
        'Community Support',
        'Education',
        'Emergency Relief',
        'Environment',
        'Health Care',
        'Shelter and Housing',
    ];

    private CampaignEntity $campaignEntity;
    private CategoryEntity $categoryEntity;

    public function __construct()
    {
        $this->campaignEntity = new CampaignEntity();
        $this->categoryEntity = new CategoryEntity();
    }

    public function setDetails(int $fundraiserUserId, array $details): bool
    {
        return $this->campaignEntity->createCampaign($fundraiserUserId, $details);
    }

    public function getDetails(int $fundraiserUserId, int $activityId): ?array
    {
        return $this->campaignEntity->getCampaignForFundraiser($fundraiserUserId, $activityId);
    }

    public function listDetails(int $fundraiserUserId, array $filters = []): array
    {
        return $this->campaignEntity->getCampaignsByFundraiser($fundraiserUserId, $filters);
    }

    public function updateDetails(int $fundraiserUserId, int $activityId, array $details): bool
    {
        return $this->campaignEntity->updateCampaign($fundraiserUserId, $activityId, $details);
    }

    public function delete(int $fundraiserUserId, int $activityId): bool
    {
        return $this->campaignEntity->deleteCampaign($fundraiserUserId, $activityId);
    }

    public function getViews(int $fundraiserUserId, int $activityId = 0): array
    {
        $activities = $activityId > 0
            ? array_filter(
                [$this->getDetails($fundraiserUserId, $activityId)],
                static fn (?array $activity): bool => $activity !== null
            )
            : $this->listDetails($fundraiserUserId);

        return array_values(array_map(
            static fn (array $activity): array => [
                'activityID' => (int) $activity['id'],
                'title' => $activity['title'],
                'viewsCount' => (int) ($activity['view_count'] ?? 0),
                'status' => $activity['status'] ?? '',
            ],
            $activities
        ));
    }

    public function getShortlisted(int $fundraiserUserId, int $activityId = 0): array
    {
        $activities = $activityId > 0
            ? array_filter(
                [$this->getDetails($fundraiserUserId, $activityId)],
                static fn (?array $activity): bool => $activity !== null
            )
            : $this->listDetails($fundraiserUserId);

        return array_values(array_map(
            static fn (array $activity): array => [
                'activityID' => (int) $activity['id'],
                'title' => $activity['title'],
                'shortlistedCount' => (int) ($activity['shortlist_count'] ?? 0),
                'status' => $activity['status'] ?? '',
            ],
            $activities
        ));
    }

    public function getCompletedDetailsByService(int $fundraiserUserId, string $serviceType = ''): array
    {
        return $this->getCompletedHistory($fundraiserUserId, $serviceType);
    }

    public function getCompletedDetailsByDate(int $fundraiserUserId, string $fromDate = '', string $toDate = ''): array
    {
        return $this->getCompletedHistory($fundraiserUserId, '', $fromDate, $toDate);
    }

    public function getCompletedHistory(
        int $fundraiserUserId,
        string $serviceType = '',
        string $fromDate = '',
        string $toDate = ''
    ): array
    {
        return $this->listDetails($fundraiserUserId, [
            'status' => 'completed',
            'service_type' => $serviceType,
            'from' => $fromDate,
            'to' => $toDate,
        ]);
    }

    public function listCategories(): array
    {
        return $this->categoryEntity->getAll('', true);
    }

    public function listServiceTypes(): array
    {
        return self::SERVICE_TYPES;
    }
}
