<?php
declare(strict_types=1);

namespace App\Controller;

// Load the FundraisingActivity Entity class.
use App\Entity\FundraisingActivity;

// BCE route:
// Boundary/FundraisingUI.php -> Controller/FundraisingSearchC.php -> Entity/FundraisingActivity.php.
// Boundary/fundraiser_dashboard.php -> Controller/FundraisingSearchC.php -> Entity/FundraisingActivity.php.
// This Controller filters Fund Raiser activities for search and dashboard listing.
final class FundraisingSearchC
{
    private FundraisingActivity $fundraisingActivity;

    // Creates the FundraisingActivity Entity object.
    public function __construct()
    {
        $this->fundraisingActivity = new FundraisingActivity();
    }

    // Validates and trims the search query.
    public function validateQuery(string $query): string
    {
        return trim($query);
    }

    // Searches fundraising activities using the keyword filter.
    public function searchActivity(int $fundraiserUserId, string $query = ''): array
    {
        // Controller -> Entity to retrieve matching fundraising activities.
        return $this->fundraisingActivity->listDetails($fundraiserUserId, [
            'keyword' => $this->validateQuery($query),
        ]);
    }
}