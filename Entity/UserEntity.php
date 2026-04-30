<?php
declare(strict_types=1);

namespace App\Entity;

use App\Config\Database;
use PDO;

// Entity layer for shared login account data.
// Called by Controller/LoginController.php for the shared login flow.
final class UserEntity
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function getByUsername(string $username): ?array
    {
        $statement = $this->db->prepare(
            'SELECT id, username, full_name, email, password_hash, role, status, created_at
             FROM users
             WHERE username = :username
             LIMIT 1'
        );
        $statement->execute(['username' => $username]);

        return $statement->fetch() ?: null;
    }

    public function authenticate(string $username, string $password): ?array
    {
        $user = $this->getByUsername($username);
        if ($user === null || !password_verify($password, $user['password_hash'] ?? '')) {
            return null;
        }

        unset($user['password_hash']);
        return $user;
    }
}
