<?php
declare(strict_types=1);

namespace App\Controller;

// Load entity classes used by this Controller.
use App\Entity\Donation;
use App\Entity\FundraisingActivity;

// BCE route:
// Boundary/DSearchUI.php -> Controller/DActivityC.php -> Entity/FundraisingActivity.php.
// Boundary/DActivityUI.php -> Controller/DActivityC.php -> Entity/FundraisingActivity.php.
// Boundary/DActivityUI.php -> Controller/DActivityC.php -> Entity/Donation.php.
// This Controller validates Donor activity search/view requests and asks the Entity for fundraising activity data.
final class DActivityC
{
    private FundraisingActivity $fundraisingActivity;
    private Donation $donation;

    // Creates Entity objects needed for activity and donation functions.
    public function __construct()
    {
        $this->fundraisingActivity = new FundraisingActivity();
        $this->donation = new Donation();
    }

    // Validates whether the selected activity ID is valid.
    public function validateRequest(int $activityId = 0): array
    {
        if ($activityId < 0) {
            return ['success' => false, 'message' => 'Please select a valid fundraising activity.'];
        }

        return ['success' => true, 'message' => 'Request is valid.'];
    }

    // Sends donor search filters to the FundraisingActivity Entity.
    public function searchActivity(int $donorUserId, array $filters = []): array
    {
        return $this->fundraisingActivity->searchActivity($donorUserId, $filters);
    }

    // Retrieves all fundraising activity categories.
    public function listCategories(): array
    {
        return $this->fundraisingActivity->listCategories();
    }

    // Retrieves the selected fundraising activity details.
    public function viewActivityDetails(int $donorUserId, int $activityId): ?array
    {
        $validated = $this->validateRequest($activityId);

        if (!$validated['success'] || $activityId <= 0) {
            return null;
        }

        return $this->fundraisingActivity->getActivity($activityId, $donorUserId);
    }

    // Validates and submits a donor donation.
    public function submitDonation(int $donorUserId, int $activityId, float $amount, string $message): array
    {
        if ($donorUserId <= 0 || $activityId <= 0 || $amount <= 0) {
            return ['success' => false, 'message' => 'Please choose a fundraising activity and donation amount.'];
        }

        try {
            $this->donation->submitDonation($donorUserId, $activityId, $amount, trim($message));
        } catch (\Throwable $exception) {
            return ['success' => false, 'message' => $exception->getMessage()];
        }

        return ['success' => true, 'message' => 'Donation recorded successfully.'];
    }
}