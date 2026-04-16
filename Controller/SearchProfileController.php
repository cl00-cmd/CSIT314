<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\UserProfileEntity;

final class SearchProfileController
{
    private UserProfileEntity $profileEntity;

    public function __construct()
    {
        $this->profileEntity = new UserProfileEntity();
    }

    public function searchProfile(string $keyword = ''): array
    {
        return $this->profileEntity->searchProfiles(trim($keyword));
    }
}
