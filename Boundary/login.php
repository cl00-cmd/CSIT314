<?php
declare(strict_types=1);

// Load system setup and helper functions
require_once __DIR__ . '/../bootstrap.php';

use App\Controller\LoginController;

// Shared login Boundary for every role.
// BCE route: Boundary/login.php -> Controller/LoginController.php -> Entity/UserEntity.php.
// User Admin, Fund Raiser, Donor, and Platform Manager all enter through this one login page.

// Redirect logged-in users to their own role dashboard.
if (current_user() !== null) {
    redirect_to_dashboard_for_role((string) current_user()['role']);
}

// Retrieves flash message if available.
$flash = pull_flash();

// Handles login form submission.
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {

    // Boundary -> Controller: pass submitted login details to the shared login Controller.
    $controller = new LoginController();

    $result = $controller->authenticate(
        (string) ($_POST['username'] ?? ''),
        (string) ($_POST['password'] ?? '')
    );

    // Store authenticated user and redirect based on role.
    if ($result['success']) {
        $_SESSION['auth_user'] = $result['user'];
        set_flash('success', 'Welcome back, ' . $result['user']['full_name'] . '.');
        redirect_to_dashboard_for_role((string) $result['user']['role']);
    }

    // Show error message when login fails.
    $flash = [
        'type' => 'error',
        'message' => $result['message'],
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FundSphere Login</title>
    <link rel="stylesheet" href="../assets/css/app.css">
</head>
<body class="auth-body">
    <main class="auth-shell">
        <section class="auth-panel">

            <!-- Display system name and available user roles -->
            <div class="hero-copy">
                <h1>FundSphere</h1>

                <div class="tag-row">
                    <span>User Admin</span>
                    <span>Fund Raiser</span>
                    <span>Donor</span>
                    <span>Platform Manager</span>
                </div>
            </div>

            <!-- Login form -->
            <form method="post" class="card form-stack">
                <div>
                    <p class="section-label">Sign In</p>
                    <h2>Log in to your role dashboard</h2>
                </div>

                <!-- Display login success or error message -->
                <?php if ($flash !== null): ?>
                    <div class="<?= e(flash_class($flash['type'] ?? null)) ?>">
                        <?= e($flash['message'] ?? '') ?>
                    </div>
                <?php endif; ?>

                <label class="field">
                    <span>Username</span>
                    <input type="text" name="username" placeholder="e.g. admin01" required>
                </label>

                <label class="field">
                    <span>Password</span>
                    <input type="password" name="password" placeholder="Enter your password" required>
                </label>

                <button type="submit" class="button button--primary">
                    Login
                </button>
            </form>
        </section>
    </main>
</body>
</html>