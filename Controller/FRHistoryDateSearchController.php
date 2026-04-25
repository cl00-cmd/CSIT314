<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\FundraisingActivity;

// BCE route:
// Boundary/FRHistoryDateSearchUI.php -> Controller/FRHistoryDateSearchController.php -> Entity/FundraisingActivity.php.
// This Controller validates the date range before reading Fund Raiser history data from the Entity.
final class FRHistoryDateSearchController
{
    private FundraisingActivity $fundraisingActivity;

    public function __construct()
    {
        $this->fundraisingActivity = new FundraisingActivity();
    }

    public function validateDateRange(string $fromDate, string $toDate): array
    {
        $fromDate = trim($fromDate);
        $toDate = trim($toDate);

        if ($fromDate !== '' && strtotime($fromDate) === false) {
            return ['success' => false, 'message' => 'Please enter a valid start date.'];
        }
        if ($toDate !== '' && strtotime($toDate) === false) {
            return ['success' => false, 'message' => 'Please enter a valid end date.'];
        }
        if ($fromDate !== '' && $toDate !== '' && strtotime($toDate) < strtotime($fromDate)) {
            return ['success' => false, 'message' => 'End date cannot be earlier than start date.'];
        }

        return ['success' => true, 'fromDate' => $fromDate, 'toDate' => $toDate];
    }

    public function searchByDate(int $fundraiserUserId, string $fromDate = '', string $toDate = ''): array
    {
        $validated = $this->validateDateRange($fromDate, $toDate);
        if (!$validated['success']) {
            return [];
        }

        return $this->fundraisingActivity->getCompletedDetailsByDate(
            $fundraiserUserId,
            $validated['fromDate'],
            $validated['toDate']
        );
    }
}
