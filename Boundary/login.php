<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

use App\Controller\LoginController;

// Shared login Boundary for every role.
// BCE route: Boundary/login.php -> Controller/LoginController.php -> Entity/UserEntity.php.
// User Admin, Fund Raiser, Donor, and Platform Manager all enter through this one login page.
// The old role-specific login idea is removed so the system has one login use case only.

if (current_user() !== null) {
    redirect_to_dashboard_for_role((string) current_user()['role']);
}

$flash = pull_flash();

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    // Boundary -> Controller: pass only the submitted credentials to the shared login Controller.
    // The Controller handles authentication for every user role, so this Boundary does not
    // choose a role-specific login controller.
    $controller = new LoginController();
    $result = $controller->authenticate(
        (string) ($_POST['username'] ?? ''),
        (string) ($_POST['password'] ?? '')
    );

    if ($result['success']) {
        // Store the authenticated user once, then redirect based on the role saved in the user record.
        $_SESSION['auth_user'] = $result['user'];
        set_flash('success', 'Welcome back, ' . $result['user']['full_name'] . '.');
        redirect_to_dashboard_for_role((string) $result['user']['role']);
    }

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
            <div class="hero-copy">
                <h1>FundSphere</h1>
                <div class="tag-row">
                    <span>User Admin</span>
                    <span>Fund Raiser</span>
                    <span>Donor</span>
                    <span>Platform Manager</span>
                </div>
            </div>

            <form method="post" class="card form-stack">
                <div>
                    <p class="section-label">Sign In</p>
                    <h2>Log in to your role dashboard</h2>
                </div>

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

                <button type="submit" class="button button--primary">Login</button>
            </form>
        </section>
    </main>
</body>
</html>
