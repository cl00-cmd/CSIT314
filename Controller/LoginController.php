<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\UserEntity;

// BCE route:
// Boundary/login.php calls this Controller.
// This Controller calls Entity/UserEntity.php for authentication.
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
        $username = trim($username);
        if ($username === '' || $password === '') {
            return [
                'success' => false,
                'message' => 'Please enter both username and password.',
            ];
        }

        $user = $this->userEntity->authenticate($username, $password);
        if ($user === null) {
            return [
                'success' => false,
                'message' => 'Invalid username or password.',
            ];
        }

        if ($user['status'] !== 'active') {
            return [
                'success' => false,
                'message' => 'This account is currently suspended.',
            ];
        }
        return [
            'success' => true,
            'user' => $user,
            'message' => 'Login successful.',
        ];
    }
}
