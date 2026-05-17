<?php
declare(strict_types=1);

namespace App\Controller;

// Load the FundraisingActivity Entity class.
use App\Entity\FundraisingActivity;

// BCE route:
// Boundary/FRViewsUI.php -> Controller/FRViewsController.php -> Entity/FundraisingActivity.php.
// This Controller reads view totals for the Fund Raiser pages.
final class FRViewsController
{
    private FundraisingActivity $fundraisingActivity;

    // Creates the FundraisingActivity Entity object.
    public function __construct()
    {
        $this->fundraisingActivity = new FundraisingActivity();
    }

    // Retrieves the view count for fundraising activities.
    public function getViewCount(int $fundraiserUserId, int $activityId = 0): array
    {
        // Controller -> Entity to retrieve fundraising activity view totals.
        return $this->fundraisingActivity->getViewCount(
            $fundraiserUserId,
            $activityId
        );
    }
}