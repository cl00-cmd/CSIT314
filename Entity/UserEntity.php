<?php
declare(strict_types=1);

namespace App\Entity;

// Load database connection and PDO.
use App\Config\Database;
use PDO;

// Entity used only by the shared login BCE flow.
// It reads the users table for every role and returns the authenticated session user.
final class UserEntity
{
    private PDO $db;

    // Creates the database connection.
    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    // Finds and verifies an active user login record.
    public function findActiveLoginUser(string $username, string $password): ?array
    {
        // Look up the account once from the shared users table, regardless of role.
        $statement = $this->db->prepare(
            'SELECT id, username, full_name, email, password_hash, role, status, created_at
             FROM users
             WHERE username = :username
             LIMIT 1'
        );

        $statement->execute([
            'username' => $username,
        ]);

        $user = $statement->fetch();

        // Rejects login if user does not exist or password is incorrect.
        if (!$user || !password_verify($password, $user['password_hash'] ?? '')) {
            return null;
        }

        // Rejects login if the account is not active.
        if (($user['status'] ?? '') !== 'active') {
            return null;
        }

        // Remove password_hash before the user record is stored in the session.
        unset($user['password_hash']);

        return $user;
    }
}