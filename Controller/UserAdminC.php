<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\Account;

// BCE route:
// Boundary/UserAdminPg.php calls this Controller.
// This Controller then calls Entity/Account.php.
final class UserAdminC
{
    private Account $account;

    public function __construct()
    {
        // Controller -> Entity.
        $this->account = new Account();
    }

    public function suspendUser(int $userId): array
    {
        if ($userId <= 0) {
            return ['success' => false, 'message' => 'Invalid account selected.'];
        }

        $user = $this->account->getuser($userId);
        if ($user === null) {
            return ['success' => false, 'message' => 'User account not found.'];
        }

        $targetStatus = ($user['status'] ?? '') === 'suspended' ? 'active' : 'suspended';

        try {
            $updated = $this->account->setAccountStatus($userId, $targetStatus);
        } catch (\Throwable) {
            return ['success' => false, 'message' => 'Unable to update the user account status.'];
        }

        return [
            'success' => $updated,
            'message' => $targetStatus === 'suspended' ? 'Account Suspended' : 'Account Reactivated',
        ];
    }
}
