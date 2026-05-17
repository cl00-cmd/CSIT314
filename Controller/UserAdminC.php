<?php
declare(strict_types=1);

namespace App\Controller;

// Load the Entity class used by this Controller.
use App\Entity\Account;

// BCE route:
// Boundary/UserAdminPg.php calls this Controller.
// This Controller then calls Entity/Account.php.
final class UserAdminC
{
    private Account $account;

    // Creates the Account Entity object.
    public function __construct()
    {
        // Controller -> Entity.
        $this->account = new Account();
    }

    // Suspends or reactivates a user account.
    public function suspendUser(int $userId): array
    {
        // Validates the selected user ID.
        if ($userId <= 0) {
            return [
                'success' => false,
                'message' => 'Invalid account selected.'
            ];
        }

        // Controller -> Entity to retrieve user account details.
        $user = $this->account->getuser($userId);

        // Checks whether the user account exists.
        if ($user === null) {
            return [
                'success' => false,
                'message' => 'User account not found.'
            ];
        }

        // Determines whether the account should be suspended or reactivated.
        $targetStatus = ($user['status'] ?? '') === 'suspended'
            ? 'active'
            : 'suspended';

        try {

            // Controller -> Entity to update account status.
            $updated = $this->account->setAccountStatus($userId, $targetStatus);

        } catch (\Throwable) {
            return [
                'success' => false,
                'message' => 'Unable to update the user account status.'
            ];
        }

        return [
            'success' => $updated,
            'message' => $targetStatus === 'suspended'
                ? 'Account Suspended'
                : 'Account Reactivated',
        ];
    }
}