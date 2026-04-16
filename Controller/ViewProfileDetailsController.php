<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\UserProfileEntity;

final class ViewProfileDetailsController
{
    private UserProfileEntity $profileEntity;

    public function __construct()
    {
        $this->profileEntity = new UserProfileEntity();
    }

    public function viewProfile(int $userId): ?array
    {
        return $this->profileEntity->getProfileByUserId($userId);
    }
}
