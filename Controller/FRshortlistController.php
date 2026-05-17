<?php
declare(strict_types=1);

namespace App\Controller;

// Load the FundraisingActivity Entity class.
use App\Entity\FundraisingActivity;

// BCE route:
// Boundary/FRshortlistUI.php -> Controller/FRshortlistController.php -> Entity/FundraisingActivity.php.
// This Controller reads shortlist totals for the Fund Raiser pages.
final class FRshortlistController
{
    private FundraisingActivity $fundraisingActivity;

    // Creates the FundraisingActivity Entity object.
    public function __construct()
    {
        $this->fundraisingActivity = new FundraisingActivity();
    }

    // Retrieves shortlist count for a selected fundraising activity.
    public function getShortlistedCount(int $fundraiserUserId, int $activityId = 0): array
    {
        // Controller -> Entity to retrieve shortlisted activity count.
        return $this->fundraisingActivity->getShortlisted(
            $fundraiserUserId,
            $activityId
        );
    }

    // Retrieves shortlist counts for all fundraising activities.
    public function retrieveShortlistedCount(int $fundraiserUserId): array
    {
        // Controller -> Entity to retrieve all shortlist totals.
        return $this->fundraisingActivity->getShortlisted($fundraiserUserId);
    }
}