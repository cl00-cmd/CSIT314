<?php
declare(strict_types=1);

namespace App\Controller;

use App\Config\Database;
use App\Entity\Account;
use App\Entity\UserProfileEntity;

// BCE route:
// Boundary/UACreateAccount.php calls this Controller.
// This Controller then calls Entity/Account.php and Entity/UserProfileEntity.php.
final class UACreateAccountC
{
    private Account $account;
    private UserProfileEntity $profileEntity;

    public function __construct()
    {
        // Controller -> Entity: all account saving is delegated to Account.
        $this->account = new Account();
        // Controller -> Entity: this creates the default profile for the new account.
        $this->profileEntity = new UserProfileEntity();
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

        $db = Database::getConnection();
        try {
            $db->beginTransaction();
            // Create the account first so the generated user id can be reused for the profile.
            $saved = $this->account->saveAccount([
                'username' => $username,
                'full_name' => $fullName,
                'email' => $email,
                'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                'role' => (string) ($input['role'] ?? 'donor'),
                'status' => (string) ($input['account_status'] ?? 'active'),
            ]);

            if (!$saved) {
                throw new \RuntimeException('Unable to save account.');
            }

            $createdAccount = $this->account->getAccountByUsername($username);
            if ($createdAccount === null) {
                throw new \RuntimeException('Created account could not be loaded.');
            }

            // The create-account sequence also prepares the default profile for that new user.
            $this->profileEntity->createProfile((int) $createdAccount['id'], [
                'phone' => trim((string) ($input['phone'] ?? '')),
                'organisation' => trim((string) ($input['organisation'] ?? '')),
                'city' => trim((string) ($input['city'] ?? '')),
                'biography' => trim((string) ($input['biography'] ?? '')),
                'status' => (string) ($input['profile_status'] ?? 'active'),
            ]);

            $db->commit();
        } catch (\Throwable) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            return ['success' => false, 'message' => 'Unable to create the user account.'];
        }

        return ['success' => true, 'message' => 'Account Created'];
    }
}
