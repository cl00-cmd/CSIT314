<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

use App\Controller\LoginController;

// BCE route:
// Boundary/login.php -> Controller/LoginController.php -> Entity/UserEntity.php.
// This Boundary collects the username/password and sends them to the Controller.
if (current_user() !== null) {
    redirect_to_dashboard_for_role((string) current_user()['role']);
}

$flash = pull_flash();

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    // Boundary -> Controller.
    $controller = new LoginController();
    $result = $controller->authenticate(
        (string) ($_POST['username'] ?? ''),
        (string) ($_POST['password'] ?? '')
    );

    if ($result['success']) {
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

                <div class="card card--soft">
                    <strong>Demo setup note</strong>
                    <p class="muted">
                        Run <code>database/setup.php</code> first to create the database schema and large demo dataset.
                    </p>
                </div>

                <div class="card card--soft">
                    <strong>Role-specific BCE login</strong>
                    <div class="action-row">
                        <a class="button button--ghost button--small" href="UALoginAccount.php">User Admin Login</a>
                        <a class="button button--ghost button--small" href="FRLoginAccount.php">Fund Raiser Login</a>
                    </div>
                </div>
            </form>
        </section>
    </main>
</body>
</html>
