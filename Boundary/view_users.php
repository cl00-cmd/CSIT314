<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/admin_shared.php';

use App\Controller\SearchUserController;

require_login(['user_admin']);

$search = trim((string) ($_GET['search'] ?? ''));
$controller = new SearchUserController();
$users = $controller->searchUser($search);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View User Accounts</title>
    <link rel="stylesheet" href="../assets/css/app.css">
</head>
<body>
    <?php render_admin_topbar('View User Accounts', 'view_users.php'); ?>
    <main class="page-shell">
        <?php render_flash_if_any(); ?>
        <section class="panel">
            <div class="panel__header panel__header--stack">
                <div>
                    <p class="section-label">Search User Account</p>
                    <h2>Search and view user account records</h2>
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
                            <th>Account Status</th>
                            <th>Profile Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td>
                                    <strong><?= e($user['full_name']) ?></strong><br>
                                    <span class="muted"><?= e($user['username']) ?> | <?= e($user['email']) ?></span>
                                </td>
                                <td><?= e($user['role']) ?></td>
                                <td><?= e($user['status']) ?></td>
                                <td><?= e($user['profile_status'] ?? 'active') ?></td>
                                <td class="action-row">
                                    <a class="button button--ghost button--small" href="view_userPg.php?id=<?= e((string) $user['id']) ?>">View Account</a>
                                    <a class="button button--ghost button--small" href="update_usersPg.php?id=<?= e((string) $user['id']) ?>">Update Account</a>
                                    <a class="button button--ghost button--small" href="update_profilePg.php?id=<?= e((string) $user['id']) ?>">Update Profile</a>
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
