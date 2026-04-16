<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\UserAccountEntity;

final class UpdateUserController
{
    private UserAccountEntity $accountEntity;

    public function __construct()
    {
        $this->accountEntity = new UserAccountEntity();
    }

    public function updateUser(array $input): array
    {
        $userId = (int) ($input['user_id'] ?? 0);
        $username = trim((string) ($input['username'] ?? ''));
        $fullName = trim((string) ($input['full_name'] ?? ''));
        $email = trim((string) ($input['email'] ?? ''));
        if ($userId <= 0 || $username === '' || $fullName === '' || $email === '') {
            return ['success' => false, 'message' => 'Please provide valid user account details.'];
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Please enter a valid email address.'];
        }

        try {
            $this->accountEntity->updateAccount($userId, [
                'username' => $username,
                'full_name' => $fullName,
                'email' => $email,
                'role' => (string) ($input['role'] ?? 'donee'),
                'status' => (string) ($input['status'] ?? 'active'),
                'password_hash' => trim((string) ($input['password'] ?? '')) !== ''
                    ? password_hash((string) $input['password'], PASSWORD_DEFAULT)
                    : null,
            ]);
        } catch (\Throwable) {
            return ['success' => false, 'message' => 'Unable to update the user account.'];
        }

        return ['success' => true, 'message' => 'User account updated successfully.'];
    }
}
