<?php
declare(strict_types=1);

namespace App\Controller;

// Load the Entity class used by this Controller.
use App\Entity\Profile;

// BCE route:
// Boundary/UASearchProfile.php calls this Controller.
// This Controller then calls Entity/Profile.php.
final class UASearchProfileC
{
    private Profile $profile;

    // Creates the Profile Entity object.
    public function __construct()
    {
        // Controller -> Entity.
        $this->profile = new Profile();
    }

    // Searches for profile roles using the given keyword.
    public function searchProfile(string $keyword = ''): array
    {
        // Removes extra spaces from the search keyword.
        $keyword = trim($keyword);

        // Profile management is role management:
        // search profile_types, not user_profiles.
        // Controller -> Entity to retrieve matching profile roles.
        return $this->profile->findProfileRoles($keyword);
    }
}