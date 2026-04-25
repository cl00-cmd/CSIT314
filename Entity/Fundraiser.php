<?php
declare(strict_types=1);

namespace App\Entity;

// BCE route:
// Boundary/FRLoginAccount.php -> Boundary/login.php -> Controller/LoginController.php -> Entity/UserEntity.php.
// This older Fundraiser Entity wrapper is legacy-only because the shared login flow now uses Entity/UserEntity.php directly.
final class Fundraiser
{
    private UserEntity $userEntity;

    public function __construct()
    {
        $this->userEntity = new UserEntity();
    }

    public function getByUsername(string $username): ?array
    {
        return $this->userEntity->getByUsername($username);
    }

    public function login(string $username, string $password): string
    {
        $fundraiser = $this->userEntity->getByUsername($username);
        if ($fundraiser === null) {
            return 'Invalid username or password.';
        }
        if (($fundraiser['status'] ?? '') !== 'active') {
            return 'Account is suspended.';
        }
        if (!password_verify($password, $fundraiser['password_hash'] ?? '')) {
            return 'Invalid username or password.';
        }

        return 'Login successful.';
    }
}
