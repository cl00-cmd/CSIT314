<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\Profile;

// Control class for maintaining the list of profile role types.
final class UACreateProfileC
{
    private Profile $profile;

    public function __construct()
    {
        $this->profile = new Profile();
    }

    public function addProfile(string $role): array
    {
        $role = trim($role);
        if ($role === '') {
            return ['success' => false, 'message' => 'Please enter a role name.'];
        }

        try {
            $createdRole = $this->profile->addProfile($role);
        } catch (\Throwable) {
            return ['success' => false, 'message' => 'Unable to create the user profile role.'];
        }

        return [
            'success' => true,
            'message' => 'Role Created: ' . $createdRole,
        ];
    }

    public function listProfiles(): array
    {
        return $this->profile->getRoleTypes(false);
    }
}
