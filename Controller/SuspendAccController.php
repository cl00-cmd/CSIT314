<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\UserAccountEntity;

// BCE route:
// Boundary/suspendAccPage.php calls this Controller.
// This Controller calls Entity/UserAccountEntity.php.
final class SuspendAccController
{
    private UserAccountEntity $accountEntity;

    public function __construct()
    {
        // Controller -> Entity.
        $this->accountEntity = new UserAccountEntity();
    }

    public function suspendAccount(int $userId, bool $shouldSuspend): array
    {
        if ($userId <= 0) {
            return ['success' => false, 'message' => 'Invalid account selected.'];
        }

        try {
            $this->accountEntity->suspendAccount($userId, $shouldSuspend ? 'suspended' : 'active');
        } catch (\Throwable) {
            return ['success' => false, 'message' => 'Unable to update the account status.'];
        }

        return ['success' => true, 'message' => $shouldSuspend ? 'User account suspended.' : 'User account reactivated.'];
    }
}
