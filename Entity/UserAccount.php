<?php
declare(strict_types=1);

namespace App\Entity;

// BCE entity used by the suspend-account sequence where the admin updates account access.
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
