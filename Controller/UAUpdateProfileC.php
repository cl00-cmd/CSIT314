<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\Profile;

// Control class for loading and updating a single user profile.
final class UAUpdateProfileC
{
    private Profile $profile;

    public function __construct()
    {
        $this->profile = new Profile();
    }

    public function searchProfile(int $userId): ?array
    {
        return $this->profile->getProfile($userId);
    }

    public function updateProfile(array $input): array
    {
        $userId = (int) ($input['user_id'] ?? 0);
        if ($userId <= 0) {
            return ['success' => false, 'message' => 'Invalid profile selected.'];
        }

        try {
            $updated = $this->profile->editProfile([
                'user_id' => $userId,
                'phone' => trim((string) ($input['phone'] ?? '')),
                'organisation' => trim((string) ($input['organisation'] ?? '')),
                'city' => trim((string) ($input['city'] ?? '')),
                'biography' => trim((string) ($input['biography'] ?? '')),
                'status' => (string) ($input['status'] ?? 'active'),
            ]);
        } catch (\Throwable) {
            return ['success' => false, 'message' => 'Unable to update the user profile.'];
        }

        return [
            'success' => $updated,
            'message' => $updated ? 'Profile Updated' : 'Unable to update the user profile.',
        ];
    }
}
