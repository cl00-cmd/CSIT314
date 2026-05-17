<?php
declare(strict_types=1);

namespace App\Controller;

// Load the Entity class used by this Controller.
use App\Entity\Account;

// BCE route:
// Boundary/UAViewAcc.php calls this Controller.
// This Controller then calls Entity/Account.php.
final class UAViewAccC
{
    private Account $account;

    // Creates the Account Entity object.
    public function __construct()
    {
        // Controller -> Entity.
        $this->account = new Account();
    }

    // Retrieves a single user account using the user ID.
    public function findAccount(int $userId): ?array
    {
        // Controller -> Entity to retrieve account details.
        return $this->account->getuser($userId);
    }
}