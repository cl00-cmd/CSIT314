<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\Profile;

// Control class for toggling whether a user profile remains active.
final class UASuspendProfileC
{
    private Profile $profile;

    public function __construct()
    {
        $this->profile = new Profile();
    }

    public function searchProfile(int $userId): ?array
    {
        return $this->profile->getProfile($userId);
    }

    public function suspendProfile(int $userId, bool $shouldSuspend): array
    {
        if ($userId <= 0) {
            return ['success' => false, 'message' => 'Invalid profile selected.'];
        }

        try {
            $updated = $this->profile->suspendProfile($userId, $shouldSuspend ? 'suspended' : 'active');
        } catch (\Throwable) {
            return ['success' => false, 'message' => 'Unable to update the profile status.'];
        }

        return [
            'success' => $updated,
            'message' => $shouldSuspend ? 'Profile Suspended' : 'Profile Reactivated',
        ];
    }
}
