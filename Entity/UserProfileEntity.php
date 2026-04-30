<?php
declare(strict_types=1);

namespace App\Entity;

use App\Config\Database;
use PDO;

// Entity layer for user profile records in the user_profiles table.
// Called by profile Controllers such as SearchProfileController.php,
// ViewProfileDetailsController.php, UpdateProfileController.php,
// and SuspendProfileController.php.
final class UserProfileEntity
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function searchProfiles(string $keyword = ''): array
    {
        $sql = 'SELECT u.id, u.username, u.full_name, u.role, u.email,
                       p.phone, p.organisation, p.city, p.biography, p.status, p.updated_at
                FROM user_profiles p
                INNER JOIN users u ON u.id = p.user_id';

        $parameters = [];
        if ($keyword !== '') {
            $sql .= ' WHERE u.username LIKE :username_term
                      OR u.full_name LIKE :full_name_term
                      OR u.role LIKE :role_term
                      OR p.organisation LIKE :organisation_term
                      OR p.city LIKE :city_term';
            $term = '%' . $keyword . '%';
            $parameters = [
                'username_term' => $term,
                'full_name_term' => $term,
                'role_term' => $term,
                'organisation_term' => $term,
                'city_term' => $term,
            ];
        }

        $sql .= ' ORDER BY p.updated_at DESC, u.id DESC';
        $statement = $this->db->prepare($sql);
        $statement->execute($parameters);
        return $statement->fetchAll();
    }

    public function getProfileByUserId(int $userId): ?array
    {
        $statement = $this->db->prepare(
            'SELECT u.id, u.username, u.full_name, u.role, u.email,
                    p.phone, p.organisation, p.city, p.biography, p.status, p.updated_at
             FROM user_profiles p
             INNER JOIN users u ON u.id = p.user_id
             WHERE p.user_id = :user_id
             LIMIT 1'
        );
        $statement->execute(['user_id' => $userId]);
        return $statement->fetch() ?: null;
    }

    public function createProfile(int $userId, array $data): bool
    {
        $statement = $this->db->prepare(
            'INSERT INTO user_profiles (user_id, phone, organisation, city, biography, status)
             VALUES (:user_id, :phone, :organisation, :city, :biography, :status)'
        );
        return $statement->execute([
            'user_id' => $userId,
            'phone' => $data['phone'],
            'organisation' => $data['organisation'],
            'city' => $data['city'],
            'biography' => $data['biography'],
            'status' => $data['status'],
        ]);
    }

    public function updateProfile(int $userId, array $data): bool
    {
        $statement = $this->db->prepare(
            'UPDATE user_profiles
             SET phone = :phone,
                 organisation = :organisation,
                 city = :city,
                 biography = :biography,
                 status = :status
             WHERE user_id = :user_id'
        );
        return $statement->execute([
            'user_id' => $userId,
            'phone' => $data['phone'],
            'organisation' => $data['organisation'],
            'city' => $data['city'],
            'biography' => $data['biography'],
            'status' => $data['status'],
        ]);
    }

    public function suspendProfile(int $userId, string $status): bool
    {
        $statement = $this->db->prepare(
            'UPDATE user_profiles
             SET status = :status
             WHERE user_id = :user_id'
        );
        return $statement->execute([
            'user_id' => $userId,
            'status' => $status,
        ]);
    }
}
