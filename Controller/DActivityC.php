<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\FundraisingActivity;

// BCE route:
// Boundary/DSearchUI.php -> Controller/DActivityC.php -> Entity/FundraisingActivity.php.
// Boundary/DActivityUI.php -> Controller/DActivityC.php -> Entity/FundraisingActivity.php.
// This Controller validates Donor activity search/view requests and asks the Entity for fundraising activity data.
final class DActivityC
{
    private FundraisingActivity $fundraisingActivity;

    public function __construct()
    {
        $this->fundraisingActivity = new FundraisingActivity();
    }

    public function validateRequest(int $activityId = 0): array
    {
        if ($activityId < 0) {
            return ['success' => false, 'message' => 'Please select a valid fundraising activity.'];
        }

        return ['success' => true, 'message' => 'Request is valid.'];
    }

    public function searchActivity(int $donorUserId, array $filters = []): array
    {
        return $this->fundraisingActivity->searchActivity($donorUserId, $filters);
    }

    public function viewActivityDetails(int $donorUserId, int $activityId): ?array
    {
        $validated = $this->validateRequest($activityId);
        if (!$validated['success'] || $activityId <= 0) {
            return null;
        }

        return $this->fundraisingActivity->getActivity($activityId, $donorUserId);
    }
}
