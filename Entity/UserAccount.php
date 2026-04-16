<?php
declare(strict_types=1);

namespace App\Entity;

// Entity layer for the suspend-account BCE flow:
// Controller/UserAdminC.php -> Entity/UserAccount.php.
final class UserAccount
{
    private UserAccountEntity $accountEntity;

    public function __construct()
    {
        $this->accountEntity = new UserAccountEntity();
    }

    public function findById(int $userId): ?array
    {
        return $this->accountEntity->getAccountById($userId);
    }

    public function setStatus(int $userId, string $status): bool
    {
        return $this->accountEntity->suspendAccount($userId, $status);
    }
}
