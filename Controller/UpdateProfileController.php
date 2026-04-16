<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\UserProfileEntity;

final class UpdateProfileController
{
    private UserProfileEntity $profileEntity;

    public function __construct()
    {
        $this->profileEntity = new UserProfileEntity();
    }

    public function updateProfile(array $input): array
    {
        $userId = (int) ($input['user_id'] ?? 0);
        if ($userId <= 0) {
            return ['success' => false, 'message' => 'Invalid profile selected.'];
        }

        try {
            $this->profileEntity->updateProfile($userId, [
                'phone' => trim((string) ($input['phone'] ?? '')),
                'organisation' => trim((string) ($input['organisation'] ?? '')),
                'city' => trim((string) ($input['city'] ?? '')),
                'biography' => trim((string) ($input['biography'] ?? '')),
                'status' => (string) ($input['status'] ?? 'active'),
            ]);
        } catch (\Throwable) {
            return ['success' => false, 'message' => 'Unable to update the user profile.'];
        }

        return ['success' => true, 'message' => 'User profile updated successfully.'];
    }
}
