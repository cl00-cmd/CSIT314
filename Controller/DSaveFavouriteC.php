<?php
declare(strict_types=1);

namespace App\Controller;

// Load the FavouriteList Entity class.
use App\Entity\FavouriteList;

// BCE route:
// Boundary/DFavouriteUI.php -> Controller/DSaveFavouriteC.php -> Entity/FavouriteList.php.
// This Controller validates Donor save favourite requests before saving an activity into the favourite list.
final class DSaveFavouriteC
{
    private FavouriteList $favouriteList;

    // Creates the FavouriteList Entity object.
    public function __construct()
    {
        $this->favouriteList = new FavouriteList();
    }

    // Validates the donor and fundraising activity selection.
    public function validateRequest(int $userId, int $activityId): array
    {
        if ($userId <= 0 || $activityId <= 0) {
            return [
                'success' => false,
                'message' => 'Please choose a fundraising activity to save.'
            ];
        }

        return [
            'success' => true,
            'message' => 'Request is valid.'
        ];
    }

    // Saves the selected fundraising activity into the donor favourite list.
    public function addFavourite(int $userId, int $activityId): array
    {
        $validated = $this->validateRequest($userId, $activityId);

        // Stops the save action when validation fails.
        if (!$validated['success']) {
            return $validated;
        }

        try {

            // Controller -> Entity to save favourite activity.
            $this->favouriteList->saveFavourite($userId, $activityId);

        } catch (\Throwable) {
            return [
                'success' => false,
                'message' => 'Unable to save this fundraising activity.'
            ];
        }

        return [
            'success' => true,
            'message' => 'Fundraising activity saved into your favourite list.'
        ];
    }

    // Removes the selected fundraising activity from the donor favourite list.
    public function removeFavourite(int $userId, int $activityId): array
    {
        $validated = $this->validateRequest($userId, $activityId);

        // Stops the remove action when validation fails.
        if (!$validated['success']) {
            return $validated;
        }

        try {

            // Controller -> Entity to remove favourite activity.
            $this->favouriteList->removeFavourite($userId, $activityId);

        } catch (\Throwable) {
            return [
                'success' => false,
                'message' => 'Unable to remove this fundraising activity.'
            ];
        }

        return [
            'success' => true,
            'message' => 'Fundraising activity removed from your favourite list.'
        ];
    }
}