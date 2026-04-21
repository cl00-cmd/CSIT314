<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\Profile;

// BCE route:
// Boundary/UAViewProfile.php calls this Controller.
// This Controller then calls Entity/Profile.php.
final class UAViewProfileC
{
    private Profile $profile;

    public function __construct()
    {
        // Controller -> Entity.
        $this->profile = new Profile();
    }

    public function findProfile(string $roleCode): ?array
    {
        return $this->profile->getProfileRole($roleCode);
    }

    public function listProfiles(string $keyword = ''): array
    {
        return $this->profile->findProfileRoles(trim($keyword));
    }
}
