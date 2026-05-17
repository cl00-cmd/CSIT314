<?php
declare(strict_types=1);

namespace App\Controller;

// Load the Entity class used by this Controller.
use App\Entity\Profile;

// BCE route:
// Boundary/UACreateProfile.php calls this Controller.
// This Controller then calls Entity/Profile.php.
final class UACreateProfileC
{
    private Profile $profile;

    // Creates the Profile Entity object.
    public function __construct()
    {
        // Controller -> Entity.
        $this->profile = new Profile();
    }

    // Validates and creates a new user profile role.
    public function addProfile(string $role): array
    {
        $role = trim($role);

        // Checks whether the role name is empty.
        if ($role === '') {
            return [
                'success' => false,
                'message' => 'Please enter a role name.'
            ];
        }

        try {

            // Controller -> Entity to create profile role.
            $createdRole = $this->profile->addProfile($role);

        } catch (\Throwable) {
            return [
                'success' => false,
                'message' => 'Unable to create the user profile role.'
            ];
        }

        return [
            'success' => true,
            'message' => 'Role Created: ' . $createdRole,
        ];
    }

    // Retrieves available user profile roles.
    public function listProfiles(bool $activeOnly = false): array
    {
        // Controller -> Entity: activeOnly lets create-account pages hide suspended roles.
        return $this->profile->getRoleTypes($activeOnly);
    }
}