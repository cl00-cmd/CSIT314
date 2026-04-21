<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\Profile;

// BCE route:
// Boundary/UASuspendProfile.php calls this Controller.
// This Controller then calls Entity/Profile.php.
final class UASuspendProfileC
{
    private Profile $profile;

    public function __construct()
    {
        // Controller -> Entity.
        $this->profile = new Profile();
    }

    public function searchProfile(int $userId): ?array
    {
        return $this->profile->getProfile($userId);
    }

    public function searchProfileRoles(string $keyword = ''): array
    {
        // Controller -> Entity: ask Profile.php to search role rows in profile_types.
        return $this->profile->findProfileRoles(trim($keyword));
    }

    public function suspendProfile(string $roleCode, bool $shouldSuspend): array
    {
        // Controller validates Boundary input before passing it to the Entity layer.
        $roleCode = trim($roleCode);
        if ($roleCode === '') {
            return ['success' => false, 'message' => 'Invalid profile role selected.'];
        }

        try {
            // Controller -> Entity: only the profile role status is changed, not user account status.
            $updated = $this->profile->suspendProfileRole($roleCode, $shouldSuspend ? 'suspended' : 'active');
        } catch (\Throwable) {
            return ['success' => false, 'message' => 'Unable to update the profile role status.'];
        }

        return [
            'success' => $updated,
            'message' => $shouldSuspend ? 'Profile Role Suspended' : 'Profile Role Reactivated',
        ];
    }
}
