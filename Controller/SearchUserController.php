<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\UserAccountEntity;

final class SearchUserController
{
    private UserAccountEntity $accountEntity;

    public function __construct()
    {
        $this->accountEntity = new UserAccountEntity();
    }

    public function searchUser(string $keyword = ''): array
    {
        return $this->accountEntity->searchAccounts(trim($keyword));
    }
}
