<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\Account;

// Legacy Controller kept for reference from the earlier dedicated user admin login flow.
// The active BCE login route is Boundary/login.php -> Controller/LoginController.php -> Entity/UserEntity.php.
final class UALoginAccountC
{
    private Account $account;

    public function __construct()
    {
        // Controller -> Entity.
        $this->account = new Account();
    }

    public function loginDetails(string $userId, string $password): array
    {
        $userId = trim($userId);
        if ($userId === '' || $password === '') {
            return ['success' => false, 'message' => 'Please enter both username and password.'];
        }

        $message = $this->account->verifyLogin($userId, $password);
        if ($message !== 'Login successful.') {
            return ['success' => false, 'message' => $message];
        }

        $user = $this->account->getAccountByUsername($userId);
        if ($user === null) {
            return ['success' => false, 'message' => 'Unable to load the user account.'];
        }

        if (($user['role'] ?? '') !== 'user_admin') {
            return ['success' => false, 'message' => 'This login flow is for user admin accounts only.'];
        }

        unset($user['password_hash']);

        return ['success' => true, 'message' => $message, 'user' => $user];
    }
}
