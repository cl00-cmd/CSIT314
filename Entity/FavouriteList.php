<?php
declare(strict_types=1);

namespace App\Entity;

// BCE route:
// Boundary/DFavouriteUI.php -> Controller/DSaveFavouriteC.php -> Entity/FavouriteList.php.
// Boundary/DFavouriteUI.php -> Controller/DSearchFavouriteC.php -> Entity/FavouriteList.php.
// This Entity stores and filters the Donor favourite list using campaign favourite records.
final class FavouriteList
{
    private CampaignEntity $campaignEntity;

    public function __construct()
    {
        $this->campaignEntity = new CampaignEntity();
    }

    public function saveFavourite(int $userId, int $activityId): bool
    {
        return $this->campaignEntity->addFavourite($userId, $activityId);
    }

    public function removeFavourite(int $userId, int $activityId): bool
    {
        return $this->campaignEntity->removeFavourite($userId, $activityId);
    }

    public function filterFavourites(int $userId, array $filters = []): array
    {
        return $this->campaignEntity->getFavouriteCampaigns($userId, $filters);
    }
}
