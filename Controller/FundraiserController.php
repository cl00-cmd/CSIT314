<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\CampaignEntity;
use App\Entity\CategoryEntity;

// BCE route:
// Boundary/fundraiser_dashboard.php calls this Controller.
// This Controller calls Entity/CampaignEntity.php and Entity/CategoryEntity.php.
final class FundraiserController
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
        // Controller -> Entity for fundraiser FSA/campaign data.
        $this->campaignEntity = new CampaignEntity();
        // Controller -> Entity for category dropdown/filter data.
        $this->categoryEntity = new CategoryEntity();
    }

    public function getDashboardData(int $fundraiserUserId, array $filters = []): array
    {
        return [
            'summary' => $this->campaignEntity->getFundraiserStats($fundraiserUserId),
            'campaigns' => $this->campaignEntity->getCampaignsByFundraiser($fundraiserUserId, ['status' => 'active']),
            'history' => $this->campaignEntity->getCampaignsByFundraiser($fundraiserUserId, [
                'status' => 'completed',
                'service_type' => $filters['service_type'] ?? '',
                'from' => $filters['from'] ?? '',
                'to' => $filters['to'] ?? '',
            ]),
            'categories' => $this->categoryEntity->getAll('', true),
            'service_types' => self::SERVICE_TYPES,
        ];
    }

    public function getCampaignForEdit(int $fundraiserUserId, int $campaignId): ?array
    {
        return $this->campaignEntity->getCampaignForFundraiser($fundraiserUserId, $campaignId);
    }

    public function createCampaign(int $fundraiserUserId, array $input): array
    {
        $title = trim((string) ($input['title'] ?? ''));
        $story = trim((string) ($input['story'] ?? ''));
        $serviceType = trim((string) ($input['service_type'] ?? ''));
        $startDate = (string) ($input['start_date'] ?? '');
        $goal = (float) ($input['funding_goal'] ?? 0);

        if ($title === '' || $story === '' || $serviceType === '' || $startDate === '' || $goal <= 0) {
            return ['success' => false, 'message' => 'Please complete all required campaign fields.'];
        }

        try {
            $this->campaignEntity->createCampaign($fundraiserUserId, [
                'category_id' => (int) ($input['category_id'] ?? 0),
                'title' => $title,
                'service_type' => $serviceType,
                'story' => $story,
                'funding_goal' => $goal,
                'status' => (string) ($input['status'] ?? 'active'),
                'start_date' => $startDate,
                'end_date' => (string) ($input['end_date'] ?? ''),
            ]);
        } catch (\Throwable) {
            return ['success' => false, 'message' => 'Unable to create the fundraising activity.'];
        }

        return ['success' => true, 'message' => 'Fundraising activity created successfully.'];
    }

    public function updateCampaign(int $fundraiserUserId, array $input): array
    {
        $campaignId = (int) ($input['campaign_id'] ?? 0);
        $title = trim((string) ($input['title'] ?? ''));
        $story = trim((string) ($input['story'] ?? ''));
        $serviceType = trim((string) ($input['service_type'] ?? ''));
        $startDate = (string) ($input['start_date'] ?? '');
        $goal = (float) ($input['funding_goal'] ?? 0);

        if ($campaignId <= 0 || $title === '' || $story === '' || $serviceType === '' || $startDate === '' || $goal <= 0) {
            return ['success' => false, 'message' => 'Please complete all required campaign fields before updating.'];
        }

        try {
            $this->campaignEntity->updateCampaign($fundraiserUserId, $campaignId, [
                'category_id' => (int) ($input['category_id'] ?? 0),
                'title' => $title,
                'service_type' => $serviceType,
                'story' => $story,
                'funding_goal' => $goal,
                'status' => (string) ($input['status'] ?? 'active'),
                'start_date' => $startDate,
                'end_date' => (string) ($input['end_date'] ?? ''),
            ]);
        } catch (\Throwable) {
            return ['success' => false, 'message' => 'Unable to update the fundraising activity.'];
        }

        return ['success' => true, 'message' => 'Fundraising activity updated successfully.'];
    }
}
