<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\Donation;

// BCE route:
// Boundary/DonationHistoryUI.php -> Controller/DonationHistoryC.php -> Entity/Donation.php.
// This Controller validates Donor donation history filters and returns matching donation records.
final class DonationHistoryC
{
    private Donation $donation;

    public function __construct()
    {
        $this->donation = new Donation();
    }

    public function validateCriteria(array $filters): array
    {
        $from = (string) ($filters['history_from'] ?? '');
        $to = (string) ($filters['history_to'] ?? '');
        if ($from !== '' && $to !== '' && $from > $to) {
            return ['success' => false, 'message' => 'Start date cannot be later than end date.'];
        }

        return ['success' => true, 'message' => 'Criteria is valid.'];
    }

    public function searchHistory(int $userId, array $filters = []): array
    {
        $validated = $this->validateCriteria($filters);
        if (!$validated['success']) {
            return [];
        }

        return $this->donation->displayResults($userId, $filters);
    }

    public function displayResults(int $userId, array $filters = []): array
    {
        return $this->searchHistory($userId, $filters);
    }
}
