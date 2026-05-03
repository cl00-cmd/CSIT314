<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\UserEntity;

// Shared login Controller for every role.
// BCE route: Boundary/login.php -> Controller/LoginController.php -> Entity/UserEntity.php.
// This Controller is intentionally shared by User Admin, Fund Raiser, Donor, and Platform Manager.
final class LoginController
{
    private UserEntity $userEntity;

    public function __construct()
    {
        // Controller -> Entity.
        $this->userEntity = new UserEntity();
    }

    public function authenticate(string $username, string $password): array
    {
        // Same validation applies to every role because all users submit the same login form.
        $username = trim($username);
        if ($username === '' || $password === '') {
            return [
                'success' => false,
                'message' => 'Please enter both username and password.',
            ];
        }

        // Controller -> Entity: ask UserEntity to verify credentials against the users table.
        $user = $this->userEntity->findActiveLoginUser($username, $password);
        if ($user === null) {
            return [
                'success' => false,
                'message' => 'Invalid username or password, or this account is suspended.',
            ];
        }

        return [
            'success' => true,
            'user' => $user,
            'message' => 'Login successful.',
        ];
    }
}
