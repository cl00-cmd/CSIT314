<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\UserProfileEntity;

// BCE route:
// Boundary/view_profilePg.php and profile update pages call this Controller.
// This Controller calls Entity/UserProfileEntity.php.
final class ViewProfileDetailsController
{
    private UserProfileEntity $profileEntity;

    public function __construct()
    {
        // Controller -> Entity.
        $this->profileEntity = new UserProfileEntity();
    }

    public function viewProfile(int $userId): ?array
    {
        return $this->profileEntity->getProfileByUserId($userId);
    }
}
