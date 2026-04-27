<?php
declare(strict_types=1);

namespace App\Entity;

// BCE route:
// Boundary/DonationHistoryUI.php -> Controller/DonationHistoryC.php -> Entity/Donation.php.
// This Entity returns Donor donation history records by FSA category and date period.
final class Donation
{
    private DonationEntity $donationEntity;

    public function __construct()
    {
        $this->donationEntity = new DonationEntity();
    }

    public function displayResults(int $userId, array $filters = []): array
    {
        return $this->donationEntity->getDonorHistory($userId, [
            'category_id' => $filters['history_category_id'] ?? '',
            'from' => $filters['history_from'] ?? '',
            'to' => $filters['history_to'] ?? '',
        ]);
    }
}
