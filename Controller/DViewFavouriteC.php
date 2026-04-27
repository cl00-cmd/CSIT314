<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\FundraisingActivity;

// BCE route:
// Boundary/DViewFavouriteUI.php -> Controller/DViewFavouriteC.php -> Entity/FundraisingActivity.php.
// This Controller validates and displays fundraising activities opened from the Donor favourite list.
final class DViewFavouriteC
{
    private FundraisingActivity $fundraisingActivity;

    public function __construct()
    {
        $this->fundraisingActivity = new FundraisingActivity();
    }

    public function validateRequest(int $activityId): array
    {
        if ($activityId <= 0) {
            return ['success' => false, 'message' => 'Please choose a saved fundraising activity.'];
        }

        return ['success' => true, 'message' => 'Request is valid.'];
    }

    public function displayFavourites(int $userId, int $activityId): ?array
    {
        $validated = $this->validateRequest($activityId);
        if (!$validated['success']) {
            return null;
        }

        return $this->fundraisingActivity->getActivity($activityId, $userId);
    }
}
