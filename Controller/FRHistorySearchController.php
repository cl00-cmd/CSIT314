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

    public function validateCriteria(string $serviceType, string $fromDate = '', string $toDate = ''): array
    {
        $serviceType = trim($serviceType);
        $fromDate = trim($fromDate);
        $toDate = trim($toDate);

        if ($serviceType !== '' && !in_array($serviceType, $this->fundraisingActivity->listServiceTypes(), true)) {
            return ['success' => false, 'message' => 'Please select a valid service type.'];
        }
        if ($fromDate !== '' && strtotime($fromDate) === false) {
            return ['success' => false, 'message' => 'Please enter a valid start date.'];
        }
        if ($toDate !== '' && strtotime($toDate) === false) {
            return ['success' => false, 'message' => 'Please enter a valid end date.'];
        }
        if ($fromDate !== '' && $toDate !== '' && strtotime($toDate) < strtotime($fromDate)) {
            return ['success' => false, 'message' => 'End date cannot be earlier than start date.'];
        }

        return [
            'success' => true,
            'serviceType' => $serviceType,
            'fromDate' => $fromDate,
            'toDate' => $toDate,
        ];
    }

    public function searchHistory(
        int $fundraiserUserId,
        string $serviceType = '',
        string $fromDate = '',
        string $toDate = ''
    ): array
    {
        $validated = $this->validateCriteria($serviceType, $fromDate, $toDate);
        if (!$validated['success']) {
            return [];
        }

        return $this->fundraisingActivity->getCompletedHistory(
            $fundraiserUserId,
            $validated['serviceType'],
            $validated['fromDate'],
            $validated['toDate']
        );
    }

    public function getServiceTypes(): array
    {
        return $this->fundraisingActivity->listServiceTypes();
    }
}
