<?php
declare(strict_types=1);

namespace App\Entity;

use App\Config\Database;
use PDO;

// Entity layer in these BCE flows:
// Controller/UACreateAccountC.php -> Entity/Account.php,
// Controller/UASearchAccController.php -> Entity/Account.php,
// Controller/UAViewAccC.php -> Entity/Account.php,
// Controller/UAUpdateAccController.php -> Entity/Account.php,
// Controller/UserAdminC.php -> Entity/Account.php.
// This Entity is the only user-admin account class that calls the database.
final class Account
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function createAccount(array $accountData): bool
    {
        $data = [
            'username' => $accountData['username'],
            'full_name' => $accountData['full_name'],
            'email' => $accountData['email'],
            'password_hash' => password_hash((string) $accountData['password'], PASSWORD_DEFAULT),
            'role' => $accountData['role'],
            'status' => $accountData['account_status'] ?? $accountData['status'] ?? 'active',
            'phone' => $accountData['phone'] ?? '',
            'organisation' => $accountData['organisation'] ?? '',
            'city' => $accountData['city'] ?? '',
            'biography' => $accountData['biography'] ?? '',
            'profile_status' => $accountData['profile_status'] ?? 'active',
        ];

        try {
            $this->db->beginTransaction();

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

            $userId = (int) $this->db->lastInsertId();
            $profileStatement = $this->db->prepare(
                'INSERT INTO user_profiles (user_id, phone, organisation, city, biography, status)
                 VALUES (:user_id, :phone, :organisation, :city, :biography, :status)'
            );
            $profileStatement->execute([
                'user_id' => $userId,
                'phone' => $data['phone'],
                'organisation' => $data['organisation'],
                'city' => $data['city'],
                'biography' => $data['biography'],
                'status' => $data['profile_status'],
            ]);

            $this->db->commit();
            return true;
        } catch (\Throwable $exception) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            throw $exception;
        }
    }

    public function findAccount(string $keyword = ''): array
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

    public function getuser(int $userId): ?array
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

    public function updateUserAccount(array $accountData): bool
    {
        $fields = [
            'username = :username',
            'full_name = :full_name',
            'email = :email',
            'role = :role',
            'status = :status',
        ];
        $parameters = [
            'user_id' => (int) $accountData['user_id'],
            'username' => $accountData['username'],
            'full_name' => $accountData['full_name'],
            'email' => $accountData['email'],
            'role' => $accountData['role'],
            'status' => $accountData['status'],
        ];

        if (!empty($accountData['password'])) {
            $fields[] = 'password_hash = :password_hash';
            $parameters['password_hash'] = password_hash((string) $accountData['password'], PASSWORD_DEFAULT);
        }

        $statement = $this->db->prepare(
            'UPDATE users
             SET ' . implode(', ', $fields) . '
             WHERE id = :user_id'
        );

        return $statement->execute($parameters);
    }

    public function setAccountStatus(int $userId, string $status): bool
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
