<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\UserAccountEntity;

// BCE route:
// Boundary/view_userPg.php and account update pages call this Controller.
// This Controller calls Entity/UserAccountEntity.php.
final class ViewUserDetailsController
{
    private UserAccountEntity $accountEntity;

    public function __construct()
    {
        // Controller -> Entity.
        $this->accountEntity = new UserAccountEntity();
    }

    public function viewUser(int $userId): ?array
    {
        return $this->accountEntity->getAccountById($userId);
    }
}
