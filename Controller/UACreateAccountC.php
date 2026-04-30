<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\Account;

// BCE route:
// Boundary/UACreateAccount.php calls this Controller.
// This Controller then calls Entity/Account.php.
final class UACreateAccountC
{
    private Account $account;

    public function __construct()
    {
        $this->account = new Account();
    }

    public function createAccount(array $input): array
    {
        $username = trim((string) ($input['username'] ?? ''));
        $fullName = trim((string) ($input['full_name'] ?? ''));
        $email = trim((string) ($input['email'] ?? ''));
        $password = (string) ($input['password'] ?? '');

        if ($username === '' || $fullName === '' || $email === '' || $password === '') {
            return ['success' => false, 'message' => 'Username, full name, email, and password are required.'];
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Please enter a valid email address.'];
        }

        try {
            $saved = $this->account->createAccount([
                'username' => $username,
                'full_name' => $fullName,
                'email' => $email,
                'password' => $password,
                'role' => (string) ($input['role'] ?? 'donor'),
                'phone' => trim((string) ($input['phone'] ?? '')),
                'organisation' => trim((string) ($input['organisation'] ?? '')),
                'city' => trim((string) ($input['city'] ?? '')),
                'biography' => trim((string) ($input['biography'] ?? '')),
                'account_status' => (string) ($input['account_status'] ?? 'active'),
                'profile_status' => (string) ($input['profile_status'] ?? 'active'),
            ]);
        } catch (\Throwable) {
            return ['success' => false, 'message' => 'Unable to create the user account.'];
        }

        return [
            'success' => $saved,
            'message' => $saved ? 'Account Created' : 'Unable to create the user account.',
        ];
    }
}
