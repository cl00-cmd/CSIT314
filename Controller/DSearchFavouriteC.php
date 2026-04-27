<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\FavouriteList;

// BCE route:
// Boundary/DFavouriteUI.php -> Controller/DSearchFavouriteC.php -> Entity/FavouriteList.php.
// This Controller validates Donor favourite list search requests and returns filtered saved activities.
final class DSearchFavouriteC
{
    private FavouriteList $favouriteList;

    public function __construct()
    {
        $this->favouriteList = new FavouriteList();
    }

    public function validateSearch(string $keyword): array
    {
        return ['success' => true, 'keyword' => trim($keyword)];
    }

    public function searchFavourite(int $userId, array $filters = []): array
    {
        $validated = $this->validateSearch((string) ($filters['favourite_search'] ?? ''));
        $filters['favourite_search'] = $validated['keyword'];

        return $this->favouriteList->filterFavourites($userId, $filters);
    }
}
