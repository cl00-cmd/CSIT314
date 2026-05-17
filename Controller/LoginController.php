<?php
declare(strict_types=1);

namespace App\Controller;

// Load the UserEntity class.
use App\Entity\UserEntity;

// Shared login Controller for every role.
// BCE route: Boundary/login.php -> Controller/LoginController.php -> Entity/UserEntity.php.
// This Controller is intentionally shared by User Admin, Fund Raiser, Donor, and Platform Manager.
final class LoginController
{
    private UserEntity $userEntity;

    // Creates the UserEntity object.
    public function __construct()
    {
        // Controller -> Entity.
        $this->userEntity = new UserEntity();
    }

    // Validates login credentials and authenticates the user.
    public function authenticate(string $username, string $password): array
    {
        // Removes extra spaces from the username.
        $username = trim($username);

        // Checks whether username or password is empty.
        if ($username === '' || $password === '') {
            return [
                'success' => false,
                'message' => 'Please enter both username and password.',
            ];
        }

        // Controller -> Entity to verify login credentials.
        $user = $this->userEntity->findActiveLoginUser(
            $username,
            $password
        );

        // Stops login when credentials are invalid or account is suspended.
        if ($user === null) {
            return [
                'success' => false,
                'message' => 'Invalid username or password, or this account is suspended.',
            ];
        }

        // Returns authenticated user details.
        return [
            'success' => true,
            'user' => $user,
            'message' => 'Login successful.',
        ];
    }
}