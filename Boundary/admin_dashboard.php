<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/admin_shared.php';

use App\Controller\UASearchAccController;
use App\Controller\UASearchProfileC;
use App\Controller\UASuspendProfileC;

// BCE route:
// Boundary/admin_dashboard.php -> Controller/UASearchAccController.php -> Entity/Account.php.
// Boundary/admin_dashboard.php -> Controller/UASearchProfileC.php -> Entity/Profile.php.
// This Boundary only reads dashboard summary data and links to the specific User Admin sequence pages.
require_login(['user_admin']);

$searchUserController = new UASearchAccController();
$searchProfileController = new UASearchProfileC();
$suspendProfileController = new UASuspendProfileC();

$accounts = $searchUserController->searchUserAccount();
$profiles = $searchProfileController->searchProfile();
$profileRoles = $suspendProfileController->searchProfileRoles();
$suspendedAccounts = count(array_filter($accounts, static fn (array $row): bool => ($row['status'] ?? '') === 'suspended'));
$suspendedProfileRoles = count(array_filter($profileRoles, static fn (array $row): bool => ($row['status'] ?? '') === 'suspended'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/css/app.css">
</head>
<body>
    <?php render_admin_topbar('User Admin Dashboard', 'admin_dashboard.php'); ?>

    <main class="page-shell">
        <?php render_flash_if_any(); ?>

        <section class="stats-grid">
            <article class="stat-card">
                <span>User Accounts</span>
                <strong><?= e((string) count($accounts)) ?></strong>
            </article>
            <article class="stat-card">
                <span>User Profiles</span>
                <strong><?= e((string) count($profiles)) ?></strong>
            </article>
            <article class="stat-card">
                <span>Suspended Accounts</span>
                <strong><?= e((string) $suspendedAccounts) ?></strong>
            </article>
            <article class="stat-card">
                <span>Suspended Roles</span>
                <strong><?= e((string) $suspendedProfileRoles) ?></strong>
            </article>
        </section>

        <section class="panel">
            <div class="panel__header">
                <div>
                    <h2>View Overall Accounts</h2>
                </div>
            </div>

            <div class="table-shell">
                <table>
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Account Status</th>
                            <th>Profile Status</th>
                            <th>Organisation</th>
                            <th>City</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($accounts as $account): ?>
                            <tr>
                                <td>
                                    <strong><?= e($account['full_name']) ?></strong><br>
                                    <span class="muted"><?= e($account['username']) ?></span>
                                </td>
                                <td><?= e($account['email']) ?></td>
                                <td><?= e($account['role']) ?></td>
                                <td><?= e($account['status']) ?></td>
                                <td><?= e($account['profile_status'] ?? 'active') ?></td>
                                <td><?= e($account['organisation'] ?? '-') ?></td>
                                <td><?= e($account['city'] ?? '-') ?></td>
                                <td class="action-row">
                                    <a class="button button--ghost button--small" href="UAViewAcc.php?id=<?= e((string) $account['id']) ?>">View</a>
                                    <a class="button button--ghost button--small" href="UAUpdateAcc.php?id=<?= e((string) $account['id']) ?>">Update</a>
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
