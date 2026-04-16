<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\Account;

// BCE route:
// Boundary/UASearchAcc.php and Boundary/UserAdminPg.php call this Controller.
// This Controller then calls Entity/Account.php.
final class UASearchAccController
{
    private Account $account;

    public function __construct()
    {
        // Controller -> Entity.
        $this->account = new Account();
    }

    public function searchUserAccount(string $keyword = ''): array
    {
        return $this->account->findAccount(trim($keyword));
    }
}
