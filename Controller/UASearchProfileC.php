<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\Profile;

// BCE route:
// Boundary/UASearchProfile.php calls this Controller.
// This Controller then calls Entity/Profile.php.
final class UASearchProfileC
{
    private Profile $profile;

    public function __construct()
    {
        // Controller -> Entity.
        $this->profile = new Profile();
    }

    public function searchProfile(string $keyword = ''): array
    {
        // Profile management is role management: search profile_types, not user_profiles.
        return $this->profile->findProfileRoles(trim($keyword));
    }
}
