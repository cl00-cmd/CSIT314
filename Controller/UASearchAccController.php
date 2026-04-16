<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\Account;

// Control class for listing or searching user accounts in the admin workflow.
final class UASearchAccController
{
    private Account $account;

    public function __construct()
    {
        $this->account = new Account();
    }

    public function searchUserAccount(string $keyword = ''): array
    {
        return $this->account->findAccount(trim($keyword));
    }
}
