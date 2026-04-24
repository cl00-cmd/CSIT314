<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\FundraisingActivity;

// BCE route:
// Boundary/FRshortlistUI.php -> Controller/FRshortlistController.php -> Entity/FundraisingActivity.php.
final class FRshortlistController
{
    private FundraisingActivity $fundraisingActivity;

    public function __construct()
    {
        $this->fundraisingActivity = new FundraisingActivity();
    }

    public function getShortlistedCount(int $fundraiserUserId, int $activityId = 0): array
    {
        return $this->fundraisingActivity->getShortlisted($fundraiserUserId, $activityId);
    }

    public function retrieveShortlistedCount(int $fundraiserUserId): array
    {
        return $this->fundraisingActivity->getShortlisted($fundraiserUserId);
    }
}
