<?php
declare(strict_types=1);

namespace App\Controller;

// Load the Entity class used by this Controller.
use App\Entity\Profile;

// BCE route:
// Boundary/UAViewProfile.php calls this Controller.
// This Controller then calls Entity/Profile.php.
final class UAViewProfileC
{
    private Profile $profile;

    // Creates the Profile Entity object.
    public function __construct()
    {
        // Controller -> Entity.
        $this->profile = new Profile();
    }

    // Retrieves a single profile role using the role code.
    public function findProfile(string $roleCode): ?array
    {
        // Controller -> Entity to retrieve profile role details.
        return $this->profile->getProfileRole($roleCode);
    }

    // Retrieves all profile roles or searches using a keyword.
    public function listProfiles(string $keyword = ''): array
    {
        // Removes extra spaces from the keyword.
        $keyword = trim($keyword);

        // Controller -> Entity to retrieve matching profile roles.
        return $this->profile->findProfileRoles($keyword);
    }
}