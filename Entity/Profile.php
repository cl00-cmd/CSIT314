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
