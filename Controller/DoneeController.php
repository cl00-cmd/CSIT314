<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\CampaignEntity;
use App\Entity\CategoryEntity;
use App\Entity\DonationEntity;

final class DoneeController
{
    private CampaignEntity $campaignEntity;
    private CategoryEntity $categoryEntity;
    private DonationEntity $donationEntity;

    public function __construct()
    {
        $this->campaignEntity = new CampaignEntity();
        $this->categoryEntity = new CategoryEntity();
        $this->donationEntity = new DonationEntity();
    }

    public function getDashboardData(int $doneeUserId, array $filters = []): array
    {
        $favourites = $this->campaignEntity->getFavouriteCampaigns($doneeUserId);
        $summary = $this->donationEntity->getDoneeSummary($doneeUserId);

        return [
            'campaigns' => $this->campaignEntity->getDiscoverableCampaigns($doneeUserId, $filters),
            'favourites' => $favourites,
            'history' => $this->donationEntity->getDoneeHistory($doneeUserId, $filters),
            'categories' => $this->categoryEntity->getAll('', true),
            'summary' => [
                'favourite_count' => count($favourites),
                'donation_count' => $summary['donation_count'] ?? 0,
                'donation_amount' => $summary['total_amount'] ?? 0,
            ],
        ];
    }

    public function viewCampaign(int $doneeUserId, int $campaignId): ?array
    {
        $this->campaignEntity->recordView($campaignId, $doneeUserId);
        return $this->campaignEntity->getCampaignDetails($campaignId, $doneeUserId);
    }

    public function saveFavourite(int $doneeUserId, int $campaignId): array
    {
        try {
            $this->campaignEntity->addFavourite($doneeUserId, $campaignId);
        } catch (\Throwable) {
            return ['success' => false, 'message' => 'Unable to save this campaign to favourites.'];
        }

        return ['success' => true, 'message' => 'Campaign added to your favourite list.'];
    }

    public function removeFavourite(int $doneeUserId, int $campaignId): array
    {
        try {
            $this->campaignEntity->removeFavourite($doneeUserId, $campaignId);
        } catch (\Throwable) {
            return ['success' => false, 'message' => 'Unable to remove this campaign from favourites.'];
        }

        return ['success' => true, 'message' => 'Campaign removed from your favourite list.'];
    }

    public function donate(int $doneeUserId, array $input): array
    {
        $campaignId = (int) ($input['campaign_id'] ?? 0);
        $amount = (float) ($input['amount'] ?? 0);

        if ($campaignId <= 0 || $amount <= 0) {
            return ['success' => false, 'message' => 'Please choose a campaign and donation amount.'];
        }

        try {
            $this->donationEntity->createDonation(
                $doneeUserId,
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
