<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\CampaignEntity;
use App\Entity\CategoryEntity;
use App\Entity\DonationEntity;

// BCE route:
// Boundary/donor_dashboard.php calls this Controller.
// This Controller calls Entity/CampaignEntity.php, Entity/CategoryEntity.php,
// and Entity/DonationEntity.php.
final class DonorController
{
    private CampaignEntity $campaignEntity;
    private CategoryEntity $categoryEntity;
    private DonationEntity $donationEntity;

    public function __construct()
    {
        // Controller -> Entity for campaign search, details, views, and favourites.
        $this->campaignEntity = new CampaignEntity();
        // Controller -> Entity for category filter data.
        $this->categoryEntity = new CategoryEntity();
        // Controller -> Entity for donation creation and donation history.
        $this->donationEntity = new DonationEntity();
    }

    public function getDashboardData(int $donorUserId, array $filters = []): array
    {
        $favourites = $this->campaignEntity->getFavouriteCampaigns($donorUserId);
        $summary = $this->donationEntity->getDonorSummary($donorUserId);

        return [
            'campaigns' => $this->campaignEntity->getDiscoverableCampaigns($donorUserId, $filters),
            'favourites' => $favourites,
            'history' => $this->donationEntity->getDonorHistory($donorUserId, $filters),
            'categories' => $this->categoryEntity->getAll('', true),
            'summary' => [
                'favourite_count' => count($favourites),
                'donation_count' => $summary['donation_count'] ?? 0,
                'donation_amount' => $summary['total_amount'] ?? 0,
            ],
        ];
    }

    public function viewCampaign(int $donorUserId, int $campaignId): ?array
    {
        $this->campaignEntity->recordView($campaignId, $donorUserId);
        return $this->campaignEntity->getCampaignDetails($campaignId, $donorUserId);
    }

    public function saveFavourite(int $donorUserId, int $campaignId): array
    {
        try {
            $this->campaignEntity->addFavourite($donorUserId, $campaignId);
        } catch (\Throwable) {
            return ['success' => false, 'message' => 'Unable to save this campaign to favourites.'];
        }

        return ['success' => true, 'message' => 'Campaign added to your favourite list.'];
    }

    public function removeFavourite(int $donorUserId, int $campaignId): array
    {
        try {
            $this->campaignEntity->removeFavourite($donorUserId, $campaignId);
        } catch (\Throwable) {
            return ['success' => false, 'message' => 'Unable to remove this campaign from favourites.'];
        }

        return ['success' => true, 'message' => 'Campaign removed from your favourite list.'];
    }

    public function donate(int $donorUserId, array $input): array
    {
        $campaignId = (int) ($input['campaign_id'] ?? 0);
        $amount = (float) ($input['amount'] ?? 0);

        if ($campaignId <= 0 || $amount <= 0) {
            return ['success' => false, 'message' => 'Please choose a campaign and donation amount.'];
        }

        try {
            $this->donationEntity->createDonation(
                $donorUserId,
                $campaignId,
                $amount,
                trim((string) ($input['message'] ?? ''))
            );
        } catch (\Throwable $exception) {
            return ['success' => false, 'message' => $exception->getMessage()];
        }

        return ['success' => true, 'message' => 'Donation recorded successfully.'];
    }
}
