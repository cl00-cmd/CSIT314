<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/admin_shared.php';

use App\Controller\UpdateUserController;
use App\Controller\ViewUserDetailsController;

// BCE routes:
// Boundary/update_usersPg.php -> Controller/ViewUserDetailsController.php -> Entity/UserAccountEntity.php.
// Boundary/update_usersPg.php -> Controller/UpdateUserController.php -> Entity/UserAccountEntity.php.
// This older generic update page loads account data and submits edits through Controllers.
require_login(['user_admin']);

// Boundary -> Controller for loading the current account values.
$viewController = new ViewUserDetailsController();
$userId = (int) ($_GET['id'] ?? $_POST['user_id'] ?? 0);
$account = $userId > 0 ? $viewController->viewUser($userId) : null;

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    // Boundary -> Controller for saving edited account values.
    $controller = new UpdateUserController();
    $result = $controller->updateUser($_POST);
    set_flash($result['success'] ? 'success' : 'error', $result['message']);
    app_redirect('update_usersPg.php', ['id' => (int) ($_POST['user_id'] ?? 0)]);
}

$roles = ['user_admin', 'fund_raiser', 'donor', 'platform_manager'];
// This older Boundary page mirrors UAUpdateAcc.php.
// Status is preserved here and changed only through the suspend-account flow.
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update User Account</title>
    <link rel="stylesheet" href="../assets/css/app.css">
</head>
<body>
    <?php render_admin_topbar('Update User Account', 'update_usersPg.php'); ?>
    <main class="page-shell">
        <?php render_flash_if_any(); ?>
        <section class="panel">
            <div class="panel__header">
                <div>
                    <p class="section-label">Update User Account</p>
                    <h2><?= $account !== null ? e($account['username']) : 'Choose a user account from View User Accounts' ?></h2>
                </div>
            </div>

            <?php if ($account !== null): ?>
                <form method="post" class="form-grid">
                    <input type="hidden" name="user_id" value="<?= e((string) $account['id']) ?>">
                    <label class="field">
                        <span>Username</span>
                        <input type="text" name="username" value="<?= e($account['username']) ?>" required>
                    </label>
                    <label class="field">
                        <span>Full Name</span>
                        <input type="text" name="full_name" value="<?= e($account['full_name']) ?>" required>
                    </label>
                    <label class="field">
                        <span>Email</span>
                        <input type="email" name="email" value="<?= e($account['email']) ?>" required>
                    </label>
                    <label class="field">
                        <span>New Password</span>
                        <input type="password" name="password" placeholder="Leave blank to keep existing password">
                    </label>
                    <label class="field">
                        <span>Role</span>
                        <select name="role">
                            <?php foreach ($roles as $role): ?>
                                <option value="<?= e($role) ?>" <?= selected_if($account['role'], $role) ?>><?= e($role) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <!-- Hidden status prevents a normal profile/account edit from changing suspension state. -->
                    <input type="hidden" name="status" value="<?= e($account['status']) ?>">
                    <button type="submit" class="button button--primary">Update Account</button>
                </form>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>
