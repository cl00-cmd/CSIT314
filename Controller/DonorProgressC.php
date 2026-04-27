<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\FundraisingActivity;

// BCE route:
// Boundary/DonorProgressUI.php -> Controller/DonorProgressC.php -> Entity/FundraisingActivity.php.
// This Controller calculates and returns Donor-facing fundraising activity progress.
final class DonorProgressC
{
    private FundraisingActivity $fundraisingActivity;

    public function __construct()
    {
        $this->fundraisingActivity = new FundraisingActivity();
    }

    public function getProgress(int $userId, int $activityId): ?array
    {
        $activity = $this->fundraisingActivity->getProgress($activityId, $userId);
        if ($activity === null) {
            return null;
        }

        $activity['progress_percent'] = $this->calculateProgress($activity);
        return $activity;
    }

    public function calculateProgress(array $activity): float
    {
        $goalAmount = (float) ($activity['funding_goal'] ?? 0);
        $currentAmount = (float) ($activity['current_amount'] ?? 0);
        if ($goalAmount <= 0) {
            return 0.0;
        }

        return min(100.0, round(($currentAmount / $goalAmount) * 100, 1));
    }
}
