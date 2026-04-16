<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\UserEntity;

// BCE route:
// Older generic user-admin Boundary pages call this Controller.
// This Controller calls Entity/UserEntity.php for account/profile database work.
final class AdminController
{
    public const ROLES = [
        'user_admin',
        'fund_raiser',
        'donor',
        'platform_manager',
    ];

    private UserEntity $userEntity;

    public function __construct()
    {
        // Controller -> Entity.
        $this->userEntity = new UserEntity();
    }

    public function getDashboardData(string $search = ''): array
    {
        return [
            'summary' => $this->userEntity->getSummary(),
            'users' => $this->userEntity->searchUsers(trim($search)),
            'roles' => self::ROLES,
        ];
    }

    public function getUser(int $userId): ?array
    {
        return $this->userEntity->getById($userId);
    }

    public function createUser(array $input): array
    {
        $username = trim((string) ($input['username'] ?? ''));
        $fullName = trim((string) ($input['full_name'] ?? ''));
        $email = trim((string) ($input['email'] ?? ''));
        $password = (string) ($input['password'] ?? '');
        $role = (string) ($input['role'] ?? 'donor');

        if ($username === '' || $fullName === '' || $email === '' || $password === '') {
            return ['success' => false, 'message' => 'Username, full name, email, and password are required.'];
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Please enter a valid email address.'];
        }
        if (!in_array($role, self::ROLES, true)) {
            return ['success' => false, 'message' => 'Invalid role selected.'];
        }

        try {
            $this->userEntity->createUser([
                'username' => $username,
                'full_name' => $fullName,
                'email' => $email,
                'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                'role' => $role,
                'status' => (string) ($input['status'] ?? 'active'),
                'phone' => trim((string) ($input['phone'] ?? '')),
                'organisation' => trim((string) ($input['organisation'] ?? '')),
                'city' => trim((string) ($input['city'] ?? '')),
                'biography' => trim((string) ($input['biography'] ?? '')),
            ]);
        } catch (\Throwable) {
            return ['success' => false, 'message' => 'Unable to create the account. Username or email may already exist.'];
        }

        return ['success' => true, 'message' => 'User account and profile created successfully.'];
    }

    public function updateProfile(array $input): array
    {
        $userId = (int) ($input['user_id'] ?? 0);
        $fullName = trim((string) ($input['full_name'] ?? ''));
        $email = trim((string) ($input['email'] ?? ''));
        $role = (string) ($input['role'] ?? 'donor');
        $status = (string) ($input['status'] ?? 'active');
        $password = (string) ($input['password'] ?? '');

        if ($userId <= 0 || $fullName === '' || $email === '') {
            return ['success' => false, 'message' => 'A valid user, full name, and email are required.'];
        }
        if (!in_array($role, self::ROLES, true)) {
            return ['success' => false, 'message' => 'Invalid role selected.'];
        }
        if (!in_array($status, ['active', 'suspended'], true)) {
            return ['success' => false, 'message' => 'Invalid account status.'];
        }

        try {
            $this->userEntity->updateUserProfile($userId, [
                'full_name' => $fullName,
                'email' => $email,
                'role' => $role,
                'status' => $status,
                'password_hash' => $password !== '' ? password_hash($password, PASSWORD_DEFAULT) : null,
                'phone' => trim((string) ($input['phone'] ?? '')),
                'organisation' => trim((string) ($input['organisation'] ?? '')),
                'city' => trim((string) ($input['city'] ?? '')),
                'biography' => trim((string) ($input['biography'] ?? '')),
            ]);
        } catch (\Throwable) {
            return ['success' => false, 'message' => 'Unable to update the user profile.'];
        }

        return ['success' => true, 'message' => 'User profile updated successfully.'];
    }
}
