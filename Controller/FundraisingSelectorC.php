<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\FundraisingActivity;

// BCE route:
// Boundary/FundraisingUI.php -> Controller/FundraisingSelectorC.php -> Entity/FundraisingActivity.php.
final class FundraisingSelectorC
{
    private FundraisingActivity $fundraisingActivity;

    public function __construct()
    {
        $this->fundraisingActivity = new FundraisingActivity();
    }

    public function validateDeletion(int $activityId): array
    {
        if ($activityId <= 0) {
            return ['success' => false, 'message' => 'Please select a fundraising activity to delete.'];
        }

        return ['success' => true];
    }

    public function deleteActivity(int $fundraiserUserId, int $activityId): array
    {
        $validated = $this->validateDeletion($activityId);
        if (!$validated['success']) {
            return $validated;
        }

        try {
            $deleted = $this->fundraisingActivity->delete($fundraiserUserId, $activityId);
        } catch (\Throwable) {
            return ['success' => false, 'message' => 'Unable to delete the fundraising activity.'];
        }

        return [
            'success' => $deleted,
            'message' => $deleted ? 'Fundraising activity deleted successfully.' : 'Unable to delete the fundraising activity.',
        ];
    }
}
