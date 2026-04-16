<?php
declare(strict_types=1);

namespace App\Entity;

use App\Config\Database;
use PDO;

// Entity layer for shared user account/profile data.
// Called by Controller/LoginController.php and Controller/AdminController.php.
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

    public function getById(int $userId): ?array
    {
        $statement = $this->db->prepare(
            'SELECT u.id, u.username, u.full_name, u.email, u.role, u.status, u.created_at,
                    p.phone, p.organisation, p.city, p.biography, p.status AS profile_status
             FROM users u
             LEFT JOIN user_profiles p ON p.user_id = u.id
             WHERE u.id = :user_id
             LIMIT 1'
        );
        $statement->execute(['user_id' => $userId]);

        return $statement->fetch() ?: null;
    }

    public function getSummary(): array
    {
        $statement = $this->db->query(
            "SELECT
                COUNT(*) AS total_users,
                SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) AS active_users,
                SUM(CASE WHEN status = 'suspended' THEN 1 ELSE 0 END) AS suspended_users,
                SUM(CASE WHEN role = 'user_admin' THEN 1 ELSE 0 END) AS admin_users,
                SUM(CASE WHEN role = 'fund_raiser' THEN 1 ELSE 0 END) AS fundraiser_users,
                SUM(CASE WHEN role = 'donor' THEN 1 ELSE 0 END) AS donor_users,
                SUM(CASE WHEN role = 'platform_manager' THEN 1 ELSE 0 END) AS platform_users
             FROM users"
        );

        return $statement->fetch() ?: [
            'total_users' => 0,
            'active_users' => 0,
            'suspended_users' => 0,
            'admin_users' => 0,
            'fundraiser_users' => 0,
            'donor_users' => 0,
            'platform_users' => 0,
        ];
    }

    public function searchUsers(string $keyword = ''): array
    {
        $sql = 'SELECT u.id, u.username, u.full_name, u.email, u.role, u.status, u.created_at,
                       p.phone, p.organisation, p.city, p.biography, p.status AS profile_status
                FROM users u
                LEFT JOIN user_profiles p ON p.user_id = u.id';

        $parameters = [];
        if ($keyword !== '') {
            $sql .= ' WHERE u.username LIKE :username_term
                      OR u.full_name LIKE :full_name_term
                      OR u.email LIKE :email_term
                      OR u.role LIKE :role_term
                      OR p.organisation LIKE :organisation_term
                      OR p.city LIKE :city_term';
            $term = '%' . $keyword . '%';
            $parameters = [
                'username_term' => $term,
                'full_name_term' => $term,
                'email_term' => $term,
                'role_term' => $term,
                'organisation_term' => $term,
                'city_term' => $term,
            ];
        }

        $sql .= ' ORDER BY u.created_at DESC, u.id DESC';
        $statement = $this->db->prepare($sql);
        $statement->execute($parameters);

        return $statement->fetchAll();
    }

    public function createUser(array $data): bool
    {
        $this->db->beginTransaction();

        $userStatement = $this->db->prepare(
            'INSERT INTO users (username, full_name, email, password_hash, role, status)
             VALUES (:username, :full_name, :email, :password_hash, :role, :status)'
        );
        $userStatement->execute([
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
            'status' => $data['profile_status'] ?? 'active',
        ]);

        $this->db->commit();
        return true;
    }

    public function updateUserProfile(int $userId, array $data): bool
    {
        $this->db->beginTransaction();

        $fields = [
            'full_name = :full_name',
            'email = :email',
            'role = :role',
            'status = :status',
        ];
        $parameters = [
            'id' => $userId,
            'full_name' => $data['full_name'],
            'email' => $data['email'],
            'role' => $data['role'],
            'status' => $data['status'],
        ];

        if (!empty($data['password_hash'])) {
            $fields[] = 'password_hash = :password_hash';
            $parameters['password_hash'] = $data['password_hash'];
        }

        $userStatement = $this->db->prepare(
            'UPDATE users SET ' . implode(', ', $fields) . ' WHERE id = :id'
        );
        $userStatement->execute($parameters);

        $profileStatement = $this->db->prepare(
            'UPDATE user_profiles
             SET phone = :phone,
                 organisation = :organisation,
                 city = :city,
                 biography = :biography,
                 status = :profile_status
             WHERE user_id = :user_id'
        );
        $profileStatement->execute([
            'user_id' => $userId,
            'phone' => $data['phone'],
            'organisation' => $data['organisation'],
            'city' => $data['city'],
            'biography' => $data['biography'],
            'profile_status' => $data['profile_status'] ?? 'active',
        ]);

        $this->db->commit();
        return true;
    }
}
