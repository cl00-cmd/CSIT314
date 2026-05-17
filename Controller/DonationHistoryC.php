<?php
declare(strict_types=1);

namespace App\Controller;

// Load the Donation Entity class.
use App\Entity\Donation;

// BCE route:
// Boundary/DonationHistoryUI.php -> Controller/DonationHistoryC.php -> Entity/Donation.php.
// This Controller validates Donor donation history filters and returns matching donation records.
final class DonationHistoryC
{
    private Donation $donation;

    // Creates the Donation Entity object.
    public function __construct()
    {
        $this->donation = new Donation();
    }

    // Validates the donation history search criteria.
    public function validateCriteria(array $filters): array
    {
        $from = (string) ($filters['history_from'] ?? '');
        $to = (string) ($filters['history_to'] ?? '');

        // Checks whether the start date is later than the end date.
        if ($from !== '' && $to !== '' && $from > $to) {
            return [
                'success' => false,
                'message' => 'Start date cannot be later than end date.'
            ];
        }

        return [
            'success' => true,
            'message' => 'Criteria is valid.'
        ];
    }

    // Searches donation history after validating the filters.
    public function searchHistory(int $userId, array $filters = []): array
    {
        $validated = $this->validateCriteria($filters);

        // Stops search when validation fails.
        if (!$validated['success']) {
            return [];
        }

        // Controller -> Entity to retrieve donation history records.
        return $this->donation->displayResults($userId, $filters);
    }

    // Returns the donation history search results.
    public function displayResults(int $userId, array $filters = []): array
    {
        return $this->searchHistory($userId, $filters);
    }

    // Retrieves all donation categories.
    public function listCategories(): array
    {
        return $this->donation->listCategories();
    }

    // Retrieves donation summary details for the donor.
    public function getSummary(int $userId): array
    {
        return $this->donation->getSummary($userId);
    }
}