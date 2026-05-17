<?php
declare(strict_types=1);

namespace App\Controller;

// Load the FundraisingActivity Entity class.
use App\Entity\FundraisingActivity;

// BCE route:
// Boundary/FundraisingUI.php -> Controller/FundraisingViewC.php -> Entity/FundraisingActivity.php.
// This Controller loads Fund Raiser activity lists and single activity details.
final class FundraisingViewC
{
    private FundraisingActivity $fundraisingActivity;

    // Creates the FundraisingActivity Entity object.
    public function __construct()
    {
        $this->fundraisingActivity = new FundraisingActivity();
    }

    // Retrieves details for one selected fundraising activity.
    public function getActivityDetails(int $fundraiserUserId, int $activityId): ?array
    {
        // Stops the request when the activity ID is invalid.
        if ($activityId <= 0) {
            return null;
        }

        // Controller -> Entity to retrieve activity details.
        return $this->fundraisingActivity->getDetails(
            $fundraiserUserId,
            $activityId
        );
    }

    // Retrieves all fundraising activities for the Fund Raiser.
    public function retrieveActivity(int $fundraiserUserId): array
    {
        // Controller -> Entity to retrieve activity list.
        return $this->fundraisingActivity->listDetails($fundraiserUserId);
    }
}