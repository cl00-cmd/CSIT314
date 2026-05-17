<?php
declare(strict_types=1);

namespace App\Controller;

// Load the Entity class used by this Controller.
use App\Entity\Profile;

// BCE route:
// Boundary/UAUpdateProfile.php calls this Controller.
// This Controller then calls Entity/Profile.php.
final class UAUpdateProfileC
{
    private Profile $profile;

    // Creates the Profile Entity object.
    public function __construct()
    {
        // Controller -> Entity.
        $this->profile = new Profile();
    }

    // Retrieves one selected profile role.
    public function searchProfile(string $roleCode): ?array
    {
        // Controller -> Entity to retrieve profile role details.
        return $this->profile->getProfileRole($roleCode);
    }

    // Validates and updates profile role details.
    public function updateProfile(array $input): array
    {
        $roleCode = trim((string) ($input['role_code'] ?? ''));
        $roleLabel = trim((string) ($input['role_label'] ?? ''));
        $status = (string) ($input['status'] ?? 'active');

        if ($roleCode === '') {
            return ['success' => false, 'message' => 'Invalid profile role selected.'];
        }

        if ($roleLabel === '') {
            return ['success' => false, 'message' => 'Please enter a role name.'];
        }

        if (!in_array($status, ['active', 'suspended'], true)) {
            return ['success' => false, 'message' => 'Please choose a valid role status.'];
        }

        try {
            // Controller -> Entity to update profile role.
            $updated = $this->profile->editProfileRole($roleCode, $roleLabel, $status);
        } catch (\Throwable) {
            return ['success' => false, 'message' => 'Unable to update the profile role.'];
        }

        return [
            'success' => true,
            'message' => $updated
                ? 'Profile Role Updated'
                : 'No profile role changes were needed.',
        ];
    }
}