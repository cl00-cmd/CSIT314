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

    // Creates the CampaignEntity object used for favourite records.
    public function __construct()
    {
        $this->campaignEntity = new CampaignEntity();
    }

    // Saves a fundraising activity into the donor favourite list.
    public function saveFavourite(int $userId, int $activityId): bool
    {
        return $this->campaignEntity->addFavourite($userId, $activityId);
    }

    // Removes a fundraising activity from the donor favourite list.
    public function removeFavourite(int $userId, int $activityId): bool
    {
        return $this->campaignEntity->removeFavourite($userId, $activityId);
    }

    // Retrieves the donor favourite list based on selected filters.
    public function filterFavourites(int $userId, array $filters = []): array
    {
        return $this->campaignEntity->getFavouriteCampaigns($userId, $filters);
    }
}