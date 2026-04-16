<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/admin_shared.php';

use App\Controller\SearchUserController;
use App\Controller\SuspendAccController;

// BCE routes:
// Boundary/suspendAccPage.php -> Controller/SearchUserController.php -> Entity/UserAccountEntity.php.
// Boundary/suspendAccPage.php -> Controller/SuspendAccController.php -> Entity/UserAccountEntity.php.
// This older generic suspend page searches accounts and updates status through Controllers.
require_login(['user_admin']);

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    // Boundary -> Controller for account status changes.
    $controller = new SuspendAccController();
    $result = $controller->suspendAccount(
        (int) ($_POST['user_id'] ?? 0),
        (string) ($_POST['target_status'] ?? 'suspended') === 'suspended'
    );
    set_flash($result['success'] ? 'success' : 'error', $result['message']);
    app_redirect('suspendAccPage.php');
}

// Boundary -> Controller for the account list.
$searchController = new SearchUserController();
$users = $searchController->searchUser(trim((string) ($_GET['search'] ?? '')));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Suspend User Account</title>
    <link rel="stylesheet" href="../assets/css/app.css">
</head>
<body>
    <?php render_admin_topbar('Suspend User Account', 'suspendAccPage.php'); ?>
    <main class="page-shell">
        <?php render_flash_if_any(); ?>
        <section class="panel">
            <div class="panel__header">
                <div>
                    <p class="section-label">Suspend User Account</p>
                    <h2>Control whether a user account can access the system</h2>
                </div>
            </div>

            <div class="table-shell">
                <table>
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?= e($user['full_name']) ?> <span class="muted">(<?= e($user['username']) ?>)</span></td>
                                <td><?= e($user['role']) ?></td>
                                <td><?= e($user['status']) ?></td>
                                <td>
                                    <form method="post">
                                        <input type="hidden" name="user_id" value="<?= e((string) $user['id']) ?>">
                                        <input type="hidden" name="target_status" value="<?= e(($user['status'] ?? '') === 'suspended' ? 'active' : 'suspended') ?>">
                                        <button type="submit" class="button <?= ($user['status'] ?? '') === 'suspended' ? 'button--ghost' : 'button--primary' ?> button--small">
                                            <?= ($user['status'] ?? '') === 'suspended' ? 'Reactivate Account' : 'Suspend Account' ?>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</body>
</html>
