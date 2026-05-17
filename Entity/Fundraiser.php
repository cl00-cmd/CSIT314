<?php
declare(strict_types=1);

namespace App\Entity;

// BCE route:
// Boundary/FRLoginAccount.php -> Boundary/login.php -> Controller/LoginController.php -> Entity/UserEntity.php.
// This older Fundraiser Entity wrapper is legacy-only because the shared login flow now uses Entity/UserEntity.php directly.
final class Fundraiser
{
    private UserEntity $userEntity;

    // Creates the shared UserEntity object.
    public function __construct()
    {
        $this->userEntity = new UserEntity();
    }

    // Retrieves fundraiser account data using the username.
    public function getByUsername(string $username): ?array
    {
        return $this->userEntity->getByUsername($username);
    }

    // Validates fundraiser login credentials.
    public function login(string $username, string $password): string
    {
        // Retrieves the fundraiser account record.
        $fundraiser = $this->userEntity->getByUsername($username);

        // Returns an error if the account does not exist.
        if ($fundraiser === null) {
            return 'Invalid username or password.';
        }

        // Returns an error if the account is suspended.
        if (($fundraiser['status'] ?? '') !== 'active') {
            return 'Account is suspended.';
        }

        // Verifies the password against the stored password hash.
        if (!password_verify($password, $fundraiser['password_hash'] ?? '')) {
            return 'Invalid username or password.';
        }

        return 'Login successful.';
    }
}