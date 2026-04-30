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
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function findProfileRoles(string $keyword = ''): array
    {
        // Entity reads the profile_types table because "Suspend Profile" manages roles.
        $sql = 'SELECT id, role_code, role_label, status, created_at FROM profile_types';
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

    public function getProfileRole(string $roleCode): ?array
    {
        $statement = $this->db->prepare(
            'SELECT id, role_code, role_label, status, created_at
             FROM profile_types
             WHERE role_code = :role_code'
        );
        $statement->execute(['role_code' => trim($roleCode)]);
        $profileRole = $statement->fetch();

        return $profileRole === false ? null : $profileRole;
    }

    public function suspendProfileRole(string $roleCode, string $status = 'suspended'): bool
    {
        // Entity updates only profile_types.status, so account status and user_profiles remain unchanged.
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

    public function editProfileRole(string $roleCode, string $roleLabel, string $status): bool
    {
        $statement = $this->db->prepare(
            'UPDATE profile_types
             SET role_label = :role_label,
                 status = :status
             WHERE role_code = :role_code'
        );
        $statement->execute([
            'role_code' => trim($roleCode),
            'role_label' => trim($roleLabel),
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
        // Entity supports both use cases: all roles for admin management, active roles for account creation.
        $sql = 'SELECT role_code, role_label, status FROM profile_types';
        if ($activeOnly) {
            $sql .= " WHERE status = 'active'";
        }
        $sql .= ' ORDER BY role_label ASC';
        return $this->db->query($sql)->fetchAll();
    }
}
