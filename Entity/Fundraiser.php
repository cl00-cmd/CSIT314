<?php
declare(strict_types=1);

namespace App\Entity;

// BCE entity for the Fund Raiser login sequence:
// Controller/FRLoginAccountC.php -> Entity/Fundraiser.php.
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
