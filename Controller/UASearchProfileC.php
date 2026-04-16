<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\Profile;

// BCE route:
// Boundary/UASearchProfile.php and Boundary/UASuspendProfile.php call this Controller.
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
        return $this->profile->findProfile(trim($keyword));
    }
}
