<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\FundraisingActivity;

// BCE route:
// Boundary/FundraisingUI.php -> Controller/FundraisingViewC.php -> Entity/FundraisingActivity.php.
final class FundraisingViewC
{
    private FundraisingActivity $fundraisingActivity;

    public function __construct()
    {
        $this->fundraisingActivity = new FundraisingActivity();
    }

    public function getActivityDetails(int $fundraiserUserId, int $activityId): ?array
    {
        if ($activityId <= 0) {
            return null;
        }

        return $this->fundraisingActivity->getDetails($fundraiserUserId, $activityId);
    }

    public function retrieveActivity(int $fundraiserUserId): array
    {
        return $this->fundraisingActivity->listDetails($fundraiserUserId);
    }
}
