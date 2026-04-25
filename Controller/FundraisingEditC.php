<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\FundraisingActivity;

// BCE route:
// Boundary/FundraisingUI.php -> Controller/FundraisingEditC.php -> Entity/FundraisingActivity.php.
// This Controller validates and updates an existing Fund Raiser activity.
final class FundraisingEditC
{
    private FundraisingActivity $fundraisingActivity;

    public function __construct()
    {
        $this->fundraisingActivity = new FundraisingActivity();
    }

    public function validateUpdate(array $input): array
    {
        $activityId = (int) ($input['activity_id'] ?? 0);
        if ($activityId <= 0) {
            return ['success' => false, 'message' => 'Please select a fundraising activity to update.'];
        }

        $createController = new FundraisingActivityC();
        $validated = $createController->validateDetails($input);
        if (!$validated['success']) {
            return $validated;
        }

        return [
            'success' => true,
            'activityId' => $activityId,
            'data' => $validated['data'],
        ];
    }

    public function updateActivity(int $fundraiserUserId, int $activityId, array $details): bool
    {
        return $this->fundraisingActivity->updateDetails($fundraiserUserId, $activityId, $details);
    }

    public function saveChanges(int $fundraiserUserId, array $input): array
    {
        $validated = $this->validateUpdate($input);
        if (!$validated['success']) {
            return $validated;
        }

        try {
            $saved = $this->updateActivity($fundraiserUserId, $validated['activityId'], $validated['data']);
        } catch (\Throwable) {
            return ['success' => false, 'message' => 'Unable to update the fundraising activity.'];
        }

        return [
            'success' => $saved,
            'message' => $saved ? 'Fundraising activity updated successfully.' : 'Unable to update the fundraising activity.',
        ];
    }

    public function getFormOptions(): array
    {
        return [
            'categories' => $this->fundraisingActivity->listCategories(),
            'serviceTypes' => $this->fundraisingActivity->listServiceTypes(),
        ];
    }
}
