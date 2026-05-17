<?php
declare(strict_types=1);

namespace App\Controller;

// Load the FundraisingActivity Entity class.
use App\Entity\FundraisingActivity;

// BCE route:
// Boundary/DonorProgressUI.php -> Controller/DonorProgressC.php -> Entity/FundraisingActivity.php.
// This Controller calculates and returns Donor-facing fundraising activity progress.
final class DonorProgressC
{
    private FundraisingActivity $fundraisingActivity;

    // Creates the FundraisingActivity Entity object.
    public function __construct()
    {
        $this->fundraisingActivity = new FundraisingActivity();
    }

    // Retrieves fundraising activity progress details.
    public function getProgress(int $userId, int $activityId): ?array
    {
        // Controller -> Entity to retrieve fundraising progress data.
        $activity = $this->fundraisingActivity->getProgress($activityId, $userId);

        // Returns null when activity is not found.
        if ($activity === null) {
            return null;
        }

        // Calculates the fundraising progress percentage.
        $activity['progress_percent'] = $this->calculateProgress($activity);

        return $activity;
    }

    // Calculates the fundraising progress percentage.
    public function calculateProgress(array $activity): float
    {
        $goalAmount = (float) ($activity['funding_goal'] ?? 0);
        $currentAmount = (float) ($activity['current_amount'] ?? 0);

        // Prevents division by zero when goal amount is invalid.
        if ($goalAmount <= 0) {
            return 0.0;
        }

        // Calculates the progress percentage and limits it to 100%.
        return min(100.0, round(($currentAmount / $goalAmount) * 100, 1));
    }
}