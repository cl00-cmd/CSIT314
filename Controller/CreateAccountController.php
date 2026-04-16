<?php
declare(strict_types=1);

namespace App\Controller;

use App\Config\Database;
use App\Entity\UserAccountEntity;
use App\Entity\UserProfileEntity;

final class CreateAccountController
{
    private UserAccountEntity $accountEntity;
    private UserProfileEntity $profileEntity;

    public function __construct()
    {
        $this->accountEntity = new UserAccountEntity();
        $this->profileEntity = new UserProfileEntity();
    }

    public function createAccount(array $input): array
    {
        $username = trim((string) ($input['username'] ?? ''));
        $fullName = trim((string) ($input['full_name'] ?? ''));
        $email = trim((string) ($input['email'] ?? ''));
        $password = (string) ($input['password'] ?? '');

        if ($username === '' || $fullName === '' || $email === '' || $password === '') {
            return ['success' => false, 'message' => 'Username, full name, email, and password are required.'];
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Please enter a valid email address.'];
        }

        $db = Database::getConnection();
        try {
            $db->beginTransaction();
            $userId = $this->accountEntity->createAccount([
                'username' => $username,
                'full_name' => $fullName,
                'email' => $email,
                'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                'role' => (string) ($input['role'] ?? 'donee'),
                'status' => (string) ($input['account_status'] ?? 'active'),
            ]);

            $this->profileEntity->createProfile($userId, [
                'phone' => trim((string) ($input['phone'] ?? '')),
                'organisation' => trim((string) ($input['organisation'] ?? '')),
                'city' => trim((string) ($input['city'] ?? '')),
                'biography' => trim((string) ($input['biography'] ?? '')),
                'status' => (string) ($input['profile_status'] ?? 'active'),
            ]);
            $db->commit();
        } catch (\Throwable) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            return ['success' => false, 'message' => 'Unable to create the user account and profile.'];
        }

        return ['success' => true, 'message' => 'User account and profile created successfully.'];
    }
}
