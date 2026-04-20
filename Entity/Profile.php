<?php
declare(strict_types=1);

namespace App\Entity;

use App\Config\Database;
use PDO;

// Entity layer in these BCE flows:
// Controller/UASearchProfileC.php -> Entity/Profile.php,
// Controller/UAViewProfileC.php -> Entity/Profile.php,
// Controller/UAUpdateProfileC.php -> Entity/Profile.php,
// Controller/UASuspendProfileC.php -> Entity/Profile.php,
// Controller/UACreateProfileC.php -> Entity/Profile.php.
final class Profile
{
    private UserProfileEntity $profileEntity;
    private PDO $db;

    public function __construct()
    {
        $this->profileEntity = new UserProfileEntity();
        $this->db = Database::getConnection();
    }

    public function findProfile(string $keyword = ''): array
    {
        return $this->profileEntity->searchProfiles($keyword);
    }

    public function findProfileRoles(string $keyword = ''): array
    {
        $sql = 'SELECT role_code, role_label, status FROM profile_types';
        $parameters = [];

        if ($keyword !== '') {
            $sql .= ' WHERE role_code LIKE :role_code_term
                      OR role_label LIKE :role_label_term
                      OR status LIKE :status_term';
            $term = '%' . $keyword . '%';
            $parameters = [
                'role_code_term' => $term,
                'role_label_term' => $term,
                'status_term' => $term,
            ];
        }

        $sql .= ' ORDER BY role_label ASC';
        $statement = $this->db->prepare($sql);
        $statement->execute($parameters);
        return $statement->fetchAll();
    }

    public function getProfile(int $userId): ?array
    {
        return $this->profileEntity->getProfileByUserId($userId);
    }

    public function editProfile(array $profileData): bool
    {
        return $this->profileEntity->updateProfile((int) $profileData['user_id'], $profileData);
    }

    public function suspendProfile(int $userId, string $status = 'suspended'): bool
    {
        return $this->profileEntity->suspendProfile($userId, $status);
    }

    public function suspendProfileRole(string $roleCode, string $status = 'suspended'): bool
    {
        $statement = $this->db->prepare(
            'UPDATE profile_types
             SET status = :status
             WHERE role_code = :role_code'
        );
        $statement->execute([
            'role_code' => $roleCode,
            'status' => $status,
        ]);

        return $statement->rowCount() > 0;
    }

    public function addProfile(string $role): string
    {
        // Profile roles are stored in a separate lookup table so admins can add new role types.
        $roleCode = strtolower(trim(preg_replace('/\s+/', '_', $role)));
        $statement = $this->db->prepare(
            'INSERT INTO profile_types (role_code, role_label, status)
             VALUES (:role_code, :role_label, :status)'
        );
        $statement->execute([
            'role_code' => $roleCode,
            'role_label' => trim($role),
            'status' => 'active',
        ]);

        return trim($role);
    }

    public function getRoleTypes(bool $activeOnly = true): array
    {
        $sql = 'SELECT role_code, role_label, status FROM profile_types';
        if ($activeOnly) {
            $sql .= " WHERE status = 'active'";
        }
        $sql .= ' ORDER BY role_label ASC';
        return $this->db->query($sql)->fetchAll();
    }
}
