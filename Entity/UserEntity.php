<?php
declare(strict_types=1);

namespace App\Entity;

use App\Config\Database;
use PDO;

// Entity used only by the shared login BCE flow.
// It reads the users table for every role and returns the authenticated session user.
final class UserEntity
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function findActiveLoginUser(string $username, string $password): ?array
    {
        // Look up the account once from the shared users table, regardless of role.
        $statement = $this->db->prepare(
            'SELECT id, username, full_name, email, password_hash, role, status, created_at
             FROM users
             WHERE username = :username
             LIMIT 1'
        );
        $statement->execute(['username' => $username]);
        $user = $statement->fetch();

        if (!$user || !password_verify($password, $user['password_hash'] ?? '')) {
            return null;
        }

        if (($user['status'] ?? '') !== 'active') {
            return null;
        }

        // Remove password_hash before the user record is stored in the session.
        unset($user['password_hash']);
        return $user;
    }
}
