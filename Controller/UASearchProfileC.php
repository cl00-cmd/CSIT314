<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\Profile;

// Control class for the "search user profile" User Admin sequence.
final class UASearchProfileC
{
    private Profile $profile;

    public function __construct()
    {
        $this->profile = new Profile();
    }

    public function searchProfile(string $keyword = ''): array
    {
        return $this->profile->findProfile(trim($keyword));
    }
}
