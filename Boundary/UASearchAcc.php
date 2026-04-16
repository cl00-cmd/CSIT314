<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/admin_shared.php';

use App\Controller\UASearchAccController;

// BCE route:
// Boundary/UASearchAcc.php -> Controller/UASearchAccController.php -> Entity/Account.php.
// This Boundary collects the search keyword and asks the Controller for results.
require_login(['user_admin']);

$search = trim((string) ($_GET['search'] ?? ''));
// Boundary -> Controller.
$controller = new UASearchAccController();
$users = $controller->searchUserAccount($search);
$hasSearched = $search !== '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UASearchAcc</title>
    <link rel="stylesheet" href="../assets/css/app.css">
</head>
<body>
    <?php render_admin_topbar('UASearchAcc', 'UASearchAcc.php'); ?>
    <main class="page-shell">
        <?php render_flash_if_any(); ?>
        <section class="panel">
            <div class="panel__header panel__header--stack">
                <div>
                    <h2>Search and list user accounts</h2>
                </div>
                <form method="get" class="inline-filters">
                    <input type="text" name="search" value="<?= e($search) ?>" placeholder="Search username, full name, email, role">
                    <button type="submit" class="button button--ghost">Search</button>
                </form>
            </div>

            <?php if ($hasSearched && $users === []): ?>
                <div class="flash flash--error">
                    No user account found for "<?= e($search) ?>". Please try another username, name, email, or role.
                </div>
            <?php else: ?>
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
                                        <a class="button button--ghost button--small" href="UAViewAcc.php?id=<?= e((string) $user['id']) ?>">View Account</a>
                                        <a class="button button--ghost button--small" href="UAUpdateAcc.php?id=<?= e((string) $user['id']) ?>">Update Account</a>
                                        <a class="button button--ghost button--small" href="UserAdminPg.php?id=<?= e((string) $user['id']) ?>">Suspend Account</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>
