<?php
declare(strict_types=1);

namespace App\Controller;

// Load the Entity class used by this Controller.
use App\Entity\Account;

// BCE route:
// Boundary/UASearchAcc.php and Boundary/UserAdminPg.php call this Controller.
// This Controller then calls Entity/Account.php.
final class UASearchAccController
{
    private Account $account;

    // Creates the Account Entity object.
    public function __construct()
    {
        // Controller -> Entity.
        $this->account = new Account();
    }

    // Searches for user accounts using the given keyword.
    public function searchUserAccount(string $keyword = ''): array
    {
        // Removes extra spaces from the search keyword.
        $keyword = trim($keyword);

        // Controller -> Entity to retrieve matching user accounts.
        return $this->account->findAccount($keyword);
    }
}