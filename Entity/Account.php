<?php
declare(strict_types=1);

namespace App\Entity;

// Entity layer in these BCE flows:
// Controller/UACreateAccountC.php -> Entity/Account.php,
// Controller/UASearchAccController.php -> Entity/Account.php,
// Controller/UAViewAccC.php -> Entity/Account.php,
// Controller/UAUpdateAccController.php -> Entity/Account.php,
// Controller/UALoginAccountC.php -> Entity/Account.php.
final class Account
{
    private UserAccountEntity $accountEntity;

    public function __construct()
    {
        $this->accountEntity = new UserAccountEntity();
    }

    public function saveAccount(array $accountData): bool
    {
        $this->accountEntity->createAccount($accountData);
        return true;
    }

    public function findAccount(string $keyword = ''): array
    {
        return $this->accountEntity->searchAccounts($keyword);
    }

    public function getAccountByUsername(string $username): ?array
    {
        return $this->accountEntity->getByUsername($username);
    }

    public function getuser(int $userId): ?array
    {
        return $this->accountEntity->getAccountById($userId);
    }

    public function updateUserAccount(array $accountData): bool
    {
        return $this->accountEntity->updateAccount((int) $accountData['user_id'], $accountData);
    }

    public function verifyLogin(string $userId, string $password): string
    {
        $user = $this->accountEntity->getByUsername($userId);
        if ($user === null) {
            return 'Invalid username or password.';
        }
        if (($user['status'] ?? '') !== 'active') {
            return 'Account is suspended.';
        }
        if (!password_verify($password, $user['password_hash'] ?? '')) {
            return 'Invalid username or password.';
        }

        return 'Login successful.';
    }
}
