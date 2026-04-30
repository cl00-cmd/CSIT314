<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/admin_shared.php';

use App\Controller\UASearchAccController;
use App\Controller\UserAdminC;

// BCE route for the suspend action:
// Boundary/UserAdminPg.php -> Controller/UserAdminC.php -> Entity/Account.php.
// This Boundary only calls Controllers, never Entity classes directly.
require_login(['user_admin']);

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    // Boundary -> Control: toggle the selected user's access status.
    $controller = new UserAdminC();
    $result = $controller->suspendUser((int) ($_POST['user_id'] ?? 0));
    set_flash($result['success'] ? 'success' : 'error', $result['message']);
    app_redirect('UserAdminPg.php');
}

$search = trim((string) ($_GET['search'] ?? ''));
// Boundary -> Controller -> Entity for the list/filter table:
// Boundary/UserAdminPg.php -> Controller/UASearchAccController.php -> Entity/Account.php.
$searchController = new UASearchAccController();
$users = $searchController->searchUserAccount($search);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UserAdminPg</title>
    <link rel="stylesheet" href="../assets/css/app.css">
</head>
<body>
    <?php render_admin_topbar('UserAdminPg', 'UserAdminPg.php'); ?>
    <main class="page-shell">
        <?php render_flash_if_any(); ?>
        <section class="panel">
            <div class="panel__header panel__header--stack">
                <div>
                    <h2>Suspend the user account so that the account has no access</h2>
                </div>
                <form method="get" class="inline-filters">
                    <input type="text" name="search" value="<?= e($search) ?>" placeholder="Search username, full name, email, role">
                    <button type="submit" class="button button--ghost">Search</button>
                </form>
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
