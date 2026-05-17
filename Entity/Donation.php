<?php
declare(strict_types=1);

namespace App\Entity;

// BCE route:
// Boundary/DonationHistoryUI.php -> Controller/DonationHistoryC.php -> Entity/Donation.php.
// This Entity returns Donor donation history records by FSA category and date period.
final class Donation
{
    private DonationEntity $donationEntity;
    private CategoryEntity $categoryEntity;

    // Creates Entity objects for donation and category data.
    public function __construct()
    {
        $this->donationEntity = new DonationEntity();
        $this->categoryEntity = new CategoryEntity();
    }

    // Saves a donor donation record.
    public function submitDonation(int $userId, int $activityId, float $amount, string $message): bool
    {
        return $this->donationEntity->createDonation(
            $userId,
            $activityId,
            $amount,
            $message
        );
    }

    // Retrieves donation summary for the donor.
    public function getSummary(int $userId): array
    {
        return $this->donationEntity->getDonorSummary($userId);
    }

    // Retrieves donation history based on selected filters.
    public function displayResults(int $userId, array $filters = []): array
    {
        return $this->donationEntity->getDonorHistory($userId, [
            'category_id' => $filters['history_category_id'] ?? '',
            'from' => $filters['history_from'] ?? '',
            'to' => $filters['history_to'] ?? '',
        ]);
    }

    // Retrieves active categories for the donation history filter.
    public function listCategories(): array
    {
        return $this->categoryEntity->getAll('', true);
    }
}