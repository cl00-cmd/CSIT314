<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\UserProfileEntity;

// BCE route:
// Boundary/view_profiles.php and Boundary/suspendProfilePg.php call this Controller.
// This Controller calls Entity/UserProfileEntity.php.
final class SearchProfileController
{
    private UserProfileEntity $profileEntity;

    public function __construct()
    {
        // Controller -> Entity.
        $this->profileEntity = new UserProfileEntity();
    }

    public function searchProfile(string $keyword = ''): array
    {
        return $this->profileEntity->searchProfiles(trim($keyword));
    }
}
