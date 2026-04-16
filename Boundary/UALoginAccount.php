<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

use App\Controller\UALoginAccountC;

// BCE route:
// Boundary/UALoginAccount.php -> Controller/UALoginAccountC.php -> Entity/Account.php.
// This Boundary only sends login input to the Controller.
if (current_user() !== null && (current_user()['role'] ?? '') === 'user_admin') {
    app_redirect('admin_dashboard.php');
}

$flash = pull_flash();

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    // The boundary collects form input and hands validation/authentication to the controller.
    $controller = new UALoginAccountC();
    $result = $controller->loginDetails(
        (string) ($_POST['username'] ?? ''),
        (string) ($_POST['password'] ?? '')
    );

    if ($result['success']) {
        $_SESSION['auth_user'] = $result['user'];
        set_flash('success', 'User admin login successful.');
        app_redirect('admin_dashboard.php');
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
    <title>User Admin Login</title>
    <link rel="stylesheet" href="../assets/css/app.css">
</head>
<body class="auth-body">
    <main class="auth-shell">
        <section class="auth-panel">
            <div class="hero-copy">
                <h1>UALoginAccount</h1>
            </div>

            <form method="post" class="card form-stack">
                <div>
                    <p class="section-label">Login</p>
                    <h2>Enter user admin login details</h2>
                </div>

                <?php if ($flash !== null): ?>
                    <div class="<?= e(flash_class($flash['type'] ?? null)) ?>">
                        <?= e($flash['message'] ?? '') ?>
                    </div>
                <?php endif; ?>

                <label class="field">
                    <span>User ID</span>
                    <input type="text" name="username" placeholder="e.g. admin01" required>
                </label>

                <label class="field">
                    <span>Password</span>
                    <input type="password" name="password" placeholder="Enter your password" required>
                </label>

                <button type="submit" class="button button--primary">Login</button>
                <a class="button button--ghost" href="login.php">Back to shared login</a>
            </form>
        </section>
    </main>
</body>
</html>
