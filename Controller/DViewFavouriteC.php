<?php
declare(strict_types=1);

namespace App\Controller;

// Load the FundraisingActivity Entity class.
use App\Entity\FundraisingActivity;

// BCE route:
// Boundary/DViewFavouriteUI.php -> Controller/DViewFavouriteC.php -> Entity/FundraisingActivity.php.
// This Controller validates and displays fundraising activities opened from the Donor favourite list.
final class DViewFavouriteC
{
    private FundraisingActivity $fundraisingActivity;

    // Creates the FundraisingActivity Entity object.
    public function __construct()
    {
        $this->fundraisingActivity = new FundraisingActivity();
    }

    // Validates the selected fundraising activity ID.
    public function validateRequest(int $activityId): array
    {
        if ($activityId <= 0) {
            return [
                'success' => false,
                'message' => 'Please choose a saved fundraising activity.'
            ];
        }

        return [
            'success' => true,
            'message' => 'Request is valid.'
        ];
    }

    // Retrieves fundraising activity details from the donor favourite list.
    public function displayFavourites(int $userId, int $activityId): ?array
    {
        // Validates the selected activity.
        $validated = $this->validateRequest($activityId);

        // Stops retrieval when validation fails.
        if (!$validated['success']) {
            return null;
        }

        // Controller -> Entity to retrieve fundraising activity details.
        return $this->fundraisingActivity->getActivity($activityId, $userId);
    }
}