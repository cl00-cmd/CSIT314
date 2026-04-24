<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\FundraisingActivity;

// BCE route:
// Boundary/FundraisingUI.php -> Controller/FundraisingSearchC.php -> Entity/FundraisingActivity.php.
final class FundraisingSearchC
{
    private FundraisingActivity $fundraisingActivity;

    public function __construct()
    {
        $this->fundraisingActivity = new FundraisingActivity();
    }

    public function validateQuery(string $query): string
    {
        return trim($query);
    }

    public function searchActivity(int $fundraiserUserId, string $query = ''): array
    {
        return $this->fundraisingActivity->listDetails($fundraiserUserId, [
            'keyword' => $this->validateQuery($query),
        ]);
    }
}
