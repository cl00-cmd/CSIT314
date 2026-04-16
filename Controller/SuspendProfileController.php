<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\UserProfileEntity;

// BCE route:
// Boundary/suspendProfilePg.php calls this Controller.
// This Controller calls Entity/UserProfileEntity.php.
final class SuspendProfileController
{
    private UserProfileEntity $profileEntity;

    public function __construct()
    {
        // Controller -> Entity.
        $this->profileEntity = new UserProfileEntity();
    }

    public function suspendProfile(int $userId, bool $shouldSuspend): array
    {
        if ($userId <= 0) {
            return ['success' => false, 'message' => 'Invalid profile selected.'];
        }

        try {
            $this->profileEntity->suspendProfile($userId, $shouldSuspend ? 'suspended' : 'active');
        } catch (\Throwable) {
            return ['success' => false, 'message' => 'Unable to update the profile status.'];
        }

        return ['success' => true, 'message' => $shouldSuspend ? 'User profile suspended.' : 'User profile reactivated.'];
    }
}
