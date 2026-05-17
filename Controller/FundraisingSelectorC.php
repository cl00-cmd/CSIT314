<?php
declare(strict_types=1);

namespace App\Controller;

// Load the FundraisingActivity Entity class.
use App\Entity\FundraisingActivity;

// BCE route:
// Boundary/FundraisingUI.php -> Controller/FundraisingSelectorC.php -> Entity/FundraisingActivity.php.
// This Controller validates and deletes a Fund Raiser activity.
final class FundraisingSelectorC
{
    private FundraisingActivity $fundraisingActivity;

    // Creates the FundraisingActivity Entity object.
    public function __construct()
    {
        $this->fundraisingActivity = new FundraisingActivity();
    }

    // Validates the selected fundraising activity before deletion.
    public function validateDeletion(int $activityId): array
    {
        if ($activityId <= 0) {
            return [
                'success' => false,
                'message' => 'Please select a fundraising activity to delete.'
            ];
        }

        return ['success' => true];
    }

    // Deletes the selected fundraising activity.
    public function deleteActivity(int $fundraiserUserId, int $activityId): array
    {
        $validated = $this->validateDeletion($activityId);

        // Stops the delete process when validation fails.
        if (!$validated['success']) {
            return $validated;
        }

        try {

            // Controller -> Entity to delete the fundraising activity.
            $deleted = $this->fundraisingActivity->delete(
                $fundraiserUserId,
                $activityId
            );

        } catch (\Throwable) {
            return [
                'success' => false,
                'message' => 'Unable to delete the fundraising activity.'
            ];
        }

        return [
            'success' => $deleted,
            'message' => $deleted
                ? 'Fundraising activity deleted successfully.'
                : 'Unable to delete the fundraising activity.',
        ];
    }
}