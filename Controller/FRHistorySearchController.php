<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\FundraisingActivity;

// BCE route:
// Boundary/FRHistorySearchUI.php -> Controller/FRHistorySearchController.php -> Entity/FundraisingActivity.php.
final class FRHistorySearchController
{
    private FundraisingActivity $fundraisingActivity;

    public function __construct()
    {
        $this->fundraisingActivity = new FundraisingActivity();
    }

    public function validateCriteria(string $serviceType): array
    {
        $serviceType = trim($serviceType);
        if ($serviceType !== '' && !in_array($serviceType, $this->fundraisingActivity->listServiceTypes(), true)) {
            return ['success' => false, 'message' => 'Please select a valid service type.'];
        }

        return ['success' => true, 'serviceType' => $serviceType];
    }

    public function searchHistory(int $fundraiserUserId, string $serviceType = ''): array
    {
        $validated = $this->validateCriteria($serviceType);
        if (!$validated['success']) {
            return [];
        }

        return $this->fundraisingActivity->getCompletedDetailsByService($fundraiserUserId, $validated['serviceType']);
    }

    public function getServiceTypes(): array
    {
        return $this->fundraisingActivity->listServiceTypes();
    }
}
