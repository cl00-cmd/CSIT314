<?php
declare(strict_types=1);

namespace App\Controller;

// Load the FundraisingActivity Entity class.
use App\Entity\FundraisingActivity;

// BCE route:
// Boundary/FRHistoryDateSearchUI.php -> Controller/FRHistoryDateSearchController.php -> Entity/FundraisingActivity.php.
// This Controller validates the date range before reading Fund Raiser history data from the Entity.
final class FRHistoryDateSearchController
{
    private FundraisingActivity $fundraisingActivity;

    // Creates the FundraisingActivity Entity object.
    public function __construct()
    {
        $this->fundraisingActivity = new FundraisingActivity();
    }

    // Validates the selected date range.
    public function validateDateRange(string $fromDate, string $toDate): array
    {
        $fromDate = trim($fromDate);
        $toDate = trim($toDate);

        // Checks whether the start date is valid.
        if ($fromDate !== '' && strtotime($fromDate) === false) {
            return [
                'success' => false,
                'message' => 'Please enter a valid start date.'
            ];
        }

        // Checks whether the end date is valid.
        if ($toDate !== '' && strtotime($toDate) === false) {
            return [
                'success' => false,
                'message' => 'Please enter a valid end date.'
            ];
        }

        // Checks whether the end date is earlier than the start date.
        if ($fromDate !== '' && $toDate !== '' && strtotime($toDate) < strtotime($fromDate)) {
            return [
                'success' => false,
                'message' => 'End date cannot be earlier than start date.'
            ];
        }

        return [
            'success' => true,
            'fromDate' => $fromDate,
            'toDate' => $toDate
        ];
    }

    // Searches completed fundraising history using the selected date range.
    public function searchByDate(
        int $fundraiserUserId,
        string $fromDate = '',
        string $toDate = ''
    ): array {

        // Validates the date range before searching.
        $validated = $this->validateDateRange($fromDate, $toDate);

        // Stops the search when validation fails.
        if (!$validated['success']) {
            return [];
        }

        // Controller -> Entity to retrieve completed fundraising history.
        return $this->fundraisingActivity->getCompletedDetailsByDate(
            $fundraiserUserId,
            $validated['fromDate'],
            $validated['toDate']
        );
    }
}