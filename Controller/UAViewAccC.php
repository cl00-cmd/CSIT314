<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\Account;

// BCE route:
// Boundary/UAViewAcc.php calls this Controller.
// This Controller then calls Entity/Account.php.
final class UAViewAccC
{
    private Account $account;

    public function __construct()
    {
        // Controller -> Entity.
        $this->account = new Account();
    }

    public function findAccount(int $userId): ?array
    {
        return $this->account->getuser($userId);
    }
}
