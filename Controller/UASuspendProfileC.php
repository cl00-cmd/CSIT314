<?php
declare(strict_types=1);

namespace App\Controller;

// Load the Entity class used by this Controller.
use App\Entity\Profile;

// BCE route:
// Boundary/UASuspendProfile.php calls this Controller.
// This Controller then calls Entity/Profile.php.
final class UASuspendProfileC
{
    private Profile $profile;

    // Creates the Profile Entity object.
    public function __construct()
    {
        // Controller -> Entity.
        $this->profile = new Profile();
    }

    // Searches for profile roles using the given keyword.
    public function searchProfileRoles(string $keyword = ''): array
    {
        // Removes extra spaces from the search keyword.
        $keyword = trim($keyword);

        // Controller -> Entity:
        // ask Profile.php to search role rows in profile_types.
        return $this->profile->findProfileRoles($keyword);
    }

    // Suspends or reactivates a profile role.
    public function suspendProfile(string $roleCode, bool $shouldSuspend): array
    {
        // Removes extra spaces from the role code.
        $roleCode = trim($roleCode);

        // Validates whether a role was selected.
        if ($roleCode === '') {
            return [
                'success' => false,
                'message' => 'Invalid profile role selected.'
            ];
        }

        try {

            // Controller -> Entity:
            // only the profile role status is changed,
            // not user account status.
            $updated = $this->profile->suspendProfileRole(
                $roleCode,
                $shouldSuspend ? 'suspended' : 'active'
            );

        } catch (\Throwable) {
            return [
                'success' => false,
                'message' => 'Unable to update the profile role status.'
            ];
        }

        return [
            'success' => $updated,
            'message' => $shouldSuspend
                ? 'Profile Role Suspended'
                : 'Profile Role Reactivated',
        ];
    }
}