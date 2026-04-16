<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\UserAccountEntity;

final class ViewUserDetailsController
{
    private UserAccountEntity $accountEntity;

    public function __construct()
    {
        $this->accountEntity = new UserAccountEntity();
    }

    public function viewUser(int $userId): ?array
    {
        return $this->accountEntity->getAccountById($userId);
    }
}
