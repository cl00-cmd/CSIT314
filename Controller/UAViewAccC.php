<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\Account;

// Control class for viewing one user account in detail.
final class UAViewAccC
{
    private Account $account;

    public function __construct()
    {
        $this->account = new Account();
    }

    public function findAccount(int $userId): ?array
    {
        return $this->account->getuser($userId);
    }
}
