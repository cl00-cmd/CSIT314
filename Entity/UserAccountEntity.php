<?php
declare(strict_types=1);

namespace App\Entity;

use App\Config\Database;
use PDO;

// Entity layer for user account records in the users table.
// Called by generic user-admin Controllers such as SearchUserController.php,
// ViewUserDetailsController.php, UpdateUserController.php, SuspendAccController.php,
// and CreateAccountController.php.
final class UserAccountEntity
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function searchAccounts(string $keyword = ''): array
    {
        $sql = 'SELECT u.id, u.username, u.full_name, u.email, u.role, u.status, u.created_at,
                       p.organisation, p.city, p.status AS profile_status
                FROM users u
                LEFT JOIN user_profiles p ON p.user_id = u.id';

        $parameters = [];
        if ($keyword !== '') {
            $sql .= ' WHERE u.username LIKE :username_term
                      OR u.full_name LIKE :full_name_term
                      OR u.email LIKE :email_term
                      OR u.role LIKE :role_term';
            $term = '%' . $keyword . '%';
            $parameters = [
                'username_term' => $term,
                'full_name_term' => $term,
                'email_term' => $term,
                'role_term' => $term,
            ];
        }

        $sql .= ' ORDER BY u.created_at DESC, u.id DESC';
        $statement = $this->db->prepare($sql);
        $statement->execute($parameters);
        return $statement->fetchAll();
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

    public function getAccountById(int $userId): ?array
    {
        $statement = $this->db->prepare(
            'SELECT id, username, full_name, email, role, status, created_at
             FROM users
             WHERE id = :user_id
             LIMIT 1'
        );
        $statement->execute(['user_id' => $userId]);
        return $statement->fetch() ?: null;
    }

    public function createAccount(array $data): int
    {
        $statement = $this->db->prepare(
            'INSERT INTO users (username, full_name, email, password_hash, role, status)
             VALUES (:username, :full_name, :email, :password_hash, :role, :status)'
        );
        $statement->execute([
            'username' => $data['username'],
            'full_name' => $data['full_name'],
            'email' => $data['email'],
            'password_hash' => $data['password_hash'],
            'role' => $data['role'],
            'status' => $data['status'],
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function updateAccount(int $userId, array $data): bool
    {
        $fields = [
            'username = :username',
            'full_name = :full_name',
            'email = :email',
            'role = :role',
            'status = :status',
        ];
        $parameters = [
            'user_id' => $userId,
            'username' => $data['username'],
            'full_name' => $data['full_name'],
            'email' => $data['email'],
            'role' => $data['role'],
            'status' => $data['status'],
        ];

        if (!empty($data['password_hash'])) {
            $fields[] = 'password_hash = :password_hash';
            $parameters['password_hash'] = $data['password_hash'];
        }

        $statement = $this->db->prepare(
            'UPDATE users
             SET ' . implode(', ', $fields) . '
             WHERE id = :user_id'
        );
        return $statement->execute($parameters);
    }

    public function suspendAccount(int $userId, string $status): bool
    {
        $statement = $this->db->prepare(
            'UPDATE users
             SET status = :status
             WHERE id = :user_id'
        );
        return $statement->execute([
            'user_id' => $userId,
            'status' => $status,
        ]);
    }
}
