<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\FundraisingActivity;

// BCE route:
// Boundary/FRViewsUI.php -> Controller/FRViewsController.php -> Entity/FundraisingActivity.php.
// This Controller reads view totals for the Fund Raiser pages.
final class FRViewsController
{
    private FundraisingActivity $fundraisingActivity;

    public function __construct()
    {
        $this->fundraisingActivity = new FundraisingActivity();
    }

    public function getViewCount(int $fundraiserUserId, int $activityId = 0): array
    {
        return $this->fundraisingActivity->getViewCount($fundraiserUserId, $activityId);
    }
}
