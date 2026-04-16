<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\UserAccountEntity;

// BCE route:
// Boundary/view_users.php and Boundary/suspendAccPage.php call this Controller.
// This Controller calls Entity/UserAccountEntity.php.
final class SearchUserController
{
    private UserAccountEntity $accountEntity;

    public function __construct()
    {
        // Controller -> Entity.
        $this->accountEntity = new UserAccountEntity();
    }

    public function searchUser(string $keyword = ''): array
    {
        return $this->accountEntity->searchAccounts(trim($keyword));
    }
}
