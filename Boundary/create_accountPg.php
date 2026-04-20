<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/admin_shared.php';

use App\Controller\CreateAccountController;

// BCE route:
// Boundary/create_accountPg.php -> Controller/CreateAccountController.php
// -> Entity/UserAccountEntity.php and Entity/UserProfileEntity.php.
// This is the older generic create-account page; the diagram-named page is UACreateAccount.php.
require_login(['user_admin']);

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    // Boundary -> Controller.
    $controller = new CreateAccountController();
    $result = $controller->createAccount($_POST);
    set_flash($result['success'] ? 'success' : 'error', $result['message']);
    app_redirect('create_accountPg.php');
}

$roles = ['user_admin', 'fund_raiser', 'donor', 'platform_manager'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create User Account</title>
    <link rel="stylesheet" href="../assets/css/app.css">
</head>
<body>
    <?php render_admin_topbar('Create User Account', 'create_accountPg.php'); ?>
    <main class="page-shell">
        <?php render_flash_if_any(); ?>
        <section class="panel">
            <div class="panel__header">
                <div>
                    <p class="section-label">Create Account</p>
                    <h2>Create a user account and user profile</h2>
                </div>
            </div>

            <form method="post" class="form-grid">
                <label class="field">
                    <span>Username</span>
                    <input type="text" name="username" required>
                </label>
                <label class="field">
                    <span>Full Name</span>
                    <input type="text" name="full_name" required>
                </label>
                <label class="field">
                    <span>Email</span>
                    <input type="email" name="email" required>
                </label>
                <label class="field">
                    <span>Password</span>
                    <input type="password" name="password" required>
                </label>
                <label class="field">
                    <span>Role</span>
                    <select name="role">
                        <?php foreach ($roles as $role): ?>
                            <option value="<?= e($role) ?>"><?= e($role) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label class="field">
                    <span>Phone</span>
                    <input type="text" name="phone">
                </label>
                <label class="field">
                    <span>Organisation</span>
                    <input type="text" name="organisation">
                </label>
                <label class="field">
                    <span>City</span>
                    <input type="text" name="city">
                </label>
                <label class="field field--full">
                    <span>Biography</span>
                    <textarea name="biography" rows="5"></textarea>
                </label>

                <button type="submit" class="button button--primary">Create Account</button>
            </form>
        </section>
    </main>
</body>
</html>
