<?php
declare(strict_types=1);

namespace App\Controller;

// Load the Entity class used by this Controller.
use App\Entity\Account;

// BCE route:
// Boundary/UAUpdateAcc.php calls this Controller.
// This Controller then calls Entity/Account.php.
final class UAUpdateAccController
{
    private Account $account;

    // Creates the Account Entity object.
    public function __construct()
    {
        // Controller -> Entity.
        $this->account = new Account();
    }

    // Retrieves a single user account using the user ID.
    public function findAccount(int $userId): ?array
    {
        // Controller -> Entity to retrieve account details.
        return $this->account->getuser($userId);
    }

    // Validates and updates user account details.
    public function updateUserAccount(array $input): array
    {
        $userId = (int) ($input['user_id'] ?? 0);
        $username = trim((string) ($input['username'] ?? ''));
        $fullName = trim((string) ($input['full_name'] ?? ''));
        $email = trim((string) ($input['email'] ?? ''));

        // Validation for required account fields.
        if ($userId <= 0 || $username === '' || $fullName === '' || $email === '') {
            return [
                'success' => false,
                'message' => 'Please provide valid user account details.'
            ];
        }

        // Validation for email format.
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return [
                'success' => false,
                'message' => 'Please enter a valid email address.'
            ];
        }

        try {

            // Controller -> Entity to update account details.
            $updated = $this->account->updateUserAccount([
                'user_id' => $userId,
                'username' => $username,
                'full_name' => $fullName,
                'email' => $email,
                'role' => (string) ($input['role'] ?? 'donor'),
                'status' => (string) ($input['status'] ?? 'active'),
                'password' => (string) ($input['password'] ?? ''),
            ]);

        } catch (\Throwable) {
            return [
                'success' => false,
                'message' => 'Unable to update the user account.'
            ];
        }

        return [
            'success' => $updated,
            'message' => $updated
                ? 'User Account Updated'
                : 'Unable to update the user account.',
        ];
    }
}