<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\Fundraiser;

// BCE route:
// Boundary/FRLoginAccount.php -> Controller/FRLoginAccountC.php -> Entity/Fundraiser.php.
final class FRLoginAccountC
{
    private Fundraiser $fundraiser;

    public function __construct()
    {
        $this->fundraiser = new Fundraiser();
    }

    public function validateLogin(string $userId, string $password): array
    {
        $userId = trim($userId);
        if ($userId === '' || $password === '') {
            return ['success' => false, 'message' => 'Please enter both username and password.'];
        }

        return ['success' => true, 'userId' => $userId, 'password' => $password];
    }

    public function loginDetails(string $userId, string $password): array
    {
        $validated = $this->validateLogin($userId, $password);
        if (!$validated['success']) {
            return $validated;
        }

        $message = $this->fundraiser->login($validated['userId'], $validated['password']);
        if ($message !== 'Login successful.') {
            return ['success' => false, 'message' => $message];
        }

        $fundraiser = $this->fundraiser->getByUsername($validated['userId']);
        if ($fundraiser === null) {
            return ['success' => false, 'message' => 'Unable to load the fund raiser account.'];
        }

        if (($fundraiser['role'] ?? '') !== 'fund_raiser') {
            return ['success' => false, 'message' => 'This login flow is for fund raiser accounts only.'];
        }

        unset($fundraiser['password_hash']);

        return ['success' => true, 'message' => $message, 'user' => $fundraiser];
    }
}
