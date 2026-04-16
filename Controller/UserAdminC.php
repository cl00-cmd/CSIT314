<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\UserAccount;

// Control class for suspending or reactivating user account access.
final class UserAdminC
{
    private UserAccount $userAccount;

    public function __construct()
    {
        $this->userAccount = new UserAccount();
    }

    public function suspendUser(int $userId): array
    {
        if ($userId <= 0) {
            return ['success' => false, 'message' => 'Invalid account selected.'];
        }

        $user = $this->userAccount->findById($userId);
        if ($user === null) {
            return ['success' => false, 'message' => 'User account not found.'];
        }

        $targetStatus = ($user['status'] ?? '') === 'suspended' ? 'active' : 'suspended';

        try {
            $updated = $this->userAccount->setStatus($userId, $targetStatus);
        } catch (\Throwable) {
            return ['success' => false, 'message' => 'Unable to update the user account status.'];
        }

        return [
            'success' => $updated,
            'message' => $targetStatus === 'suspended' ? 'Account Suspended' : 'Account Reactivated',
        ];
    }
}
