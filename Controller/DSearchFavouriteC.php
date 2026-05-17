<?php
declare(strict_types=1);

namespace App\Controller;

// Load the FavouriteList Entity class.
use App\Entity\FavouriteList;

// BCE route:
// Boundary/DFavouriteUI.php -> Controller/DSearchFavouriteC.php -> Entity/FavouriteList.php.
// This Controller validates Donor favourite list search requests and returns filtered saved activities.
final class DSearchFavouriteC
{
    private FavouriteList $favouriteList;

    // Creates the FavouriteList Entity object.
    public function __construct()
    {
        $this->favouriteList = new FavouriteList();
    }

    // Validates and trims the favourite search keyword.
    public function validateSearch(string $keyword): array
    {
        return [
            'success' => true,
            'keyword' => trim($keyword)
        ];
    }

    // Searches the donor favourite list using the provided filters.
    public function searchFavourite(int $userId, array $filters = []): array
    {
        // Validates the favourite search keyword.
        $validated = $this->validateSearch(
            (string) ($filters['favourite_search'] ?? '')
        );

        // Stores the cleaned search keyword back into the filters array.
        $filters['favourite_search'] = $validated['keyword'];

        // Controller -> Entity to retrieve filtered favourite activities.
        return $this->favouriteList->filterFavourites($userId, $filters);
    }
}