<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

use App\Controller\FRLoginAccountC;

// BCE route:
// Boundary/FRLoginAccount.php -> Controller/FRLoginAccountC.php -> Entity/Fundraiser.php.
// This follows the same dedicated login pattern used by Boundary/UALoginAccount.php.
if (current_user() !== null && (current_user()['role'] ?? '') === 'fund_raiser') {
    app_redirect('fundraiser_dashboard.php');
}

$flash = pull_flash();

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $controller = new FRLoginAccountC();
    $result = $controller->loginDetails(
        (string) ($_POST['username'] ?? ''),
        (string) ($_POST['password'] ?? '')
    );

    if ($result['success']) {
        $_SESSION['auth_user'] = $result['user'];
        set_flash('success', 'Fund raiser login successful.');
        app_redirect('fundraiser_dashboard.php');
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
    <title>Fund Raiser Login</title>
    <link rel="stylesheet" href="../assets/css/app.css">
</head>
<body class="auth-body">
    <main class="auth-shell">
        <section class="auth-panel">
            <div class="hero-copy">
                <h1>FRLoginAccount</h1>
            </div>

            <form method="post" class="card form-stack">
                <div>
                    <p class="section-label">Login</p>
                    <h2>Enter fund raiser login details</h2>
                </div>

                <?php if ($flash !== null): ?>
                    <div class="<?= e(flash_class($flash['type'] ?? null)) ?>">
                        <?= e($flash['message'] ?? '') ?>
                    </div>
                <?php endif; ?>

                <label class="field">
                    <span>User ID</span>
                    <input type="text" name="username" placeholder="e.g. fr01" required>
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
