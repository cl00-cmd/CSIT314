<?php
declare(strict_types=1);

namespace App\Controller;

// Load the FundraisingActivity Entity class.
use App\Entity\FundraisingActivity;

// BCE route:
// Boundary/FundraisingUI.php -> Controller/FundraisingEditC.php -> Entity/FundraisingActivity.php.
// This Controller validates and updates an existing Fund Raiser activity.
final class FundraisingEditC
{
    private FundraisingActivity $fundraisingActivity;

    // Creates the FundraisingActivity Entity object.
    public function __construct()
    {
        $this->fundraisingActivity = new FundraisingActivity();
    }

    // Validates the selected activity and updated details.
    public function validateUpdate(array $input): array
    {
        $activityId = (int) ($input['activity_id'] ?? 0);

        if ($activityId <= 0) {
            return ['success' => false, 'message' => 'Please select a fundraising activity to update.'];
        }

        // Reuses the create controller validation for activity details.
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

    // Sends updated fundraising activity details to the Entity.
    public function updateActivity(int $fundraiserUserId, int $activityId, array $details): bool
    {
        return $this->fundraisingActivity->updateDetails($fundraiserUserId, $activityId, $details);
    }

    // Validates and saves the updated fundraising activity details.
    public function saveChanges(int $fundraiserUserId, array $input): array
    {
        $validated = $this->validateUpdate($input);

        if (!$validated['success']) {
            return $validated;
        }

        try {
            // Controller -> Entity to update the fundraising activity.
            $saved = $this->updateActivity($fundraiserUserId, $validated['activityId'], $validated['data']);
        } catch (\Throwable) {
            return ['success' => false, 'message' => 'Unable to update the fundraising activity.'];
        }

        return [
            'success' => $saved,
            'message' => $saved
                ? 'Fundraising activity updated successfully.'
                : 'Unable to update the fundraising activity.',
        ];
    }

    // Retrieves category and service type options for the update form.
    public function getFormOptions(): array
    {
        return [
            'categories' => $this->fundraisingActivity->listCategories(),
            'serviceTypes' => $this->fundraisingActivity->listServiceTypes(),
        ];
    }
}