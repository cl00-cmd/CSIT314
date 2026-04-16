<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/admin_shared.php';

use App\Controller\UAUpdateAccController;
use App\Controller\UACreateProfileC;

// BCE route:
// Boundary/UAUpdateAcc.php -> Controller/UAUpdateAccController.php -> Entity/Account.php.
// This page is opened from search/view results, so it stays hidden from the dashboard shortcut list.
require_login(['user_admin']);

// Boundary -> Controller.
$controller = new UAUpdateAccController();
$userId = (int) ($_GET['id'] ?? $_POST['user_id'] ?? 0);
$account = $userId > 0 ? $controller->findAccount($userId) : null;

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    // Boundary -> Control: submit the edited account fields for update.
    $result = $controller->updateUserAccount($_POST);
    set_flash($result['success'] ? 'success' : 'error', $result['message']);
    app_redirect('UAUpdateAcc.php', ['id' => (int) ($_POST['user_id'] ?? 0)]);
}

$profileController = new UACreateProfileC();
$profileTypes = $profileController->listProfiles();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UAUpdateAcc</title>
    <link rel="stylesheet" href="../assets/css/app.css">
</head>
<body>
    <?php render_admin_topbar('UAUpdateAcc', 'UAUpdateAcc.php'); ?>
    <main class="page-shell">
        <?php render_flash_if_any(); ?>
        <section class="panel">
            <div class="panel__header">
                <div>
                    <p class="section-label">UAUpdateAcc -> UAUpdateAccController -> Account</p>
                    <h2><?= $account !== null ? e($account['username']) : 'Choose a user account from UASearchAcc' ?></h2>
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
                            <?php foreach ($profileTypes as $profileType): ?>
                                <option value="<?= e($profileType['role_code']) ?>" <?= selected_if($account['role'], $profileType['role_code']) ?>>
                                    <?= e($profileType['role_label']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label class="field">
                        <span>Status</span>
                        <select name="status">
                            <option value="active" <?= selected_if($account['status'], 'active') ?>>active</option>
                            <option value="suspended" <?= selected_if($account['status'], 'suspended') ?>>suspended</option>
                        </select>
                    </label>
                    <button type="submit" class="button button--primary">Update Account</button>
                </form>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>
