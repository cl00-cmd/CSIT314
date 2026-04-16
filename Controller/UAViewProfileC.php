<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\Profile;

// Control class for viewing one user profile in detail.
final class UAViewProfileC
{
    private Profile $profile;

    public function __construct()
    {
        $this->profile = new Profile();
    }

    public function findProfile(int $userId): ?array
    {
        return $this->profile->getProfile($userId);
    }
}
