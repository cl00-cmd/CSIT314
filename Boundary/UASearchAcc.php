<?php
declare(strict_types=1);

// Load system setup, shared admin layout, and helper functions
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/admin_shared.php';

use App\Controller\UASearchAccController;

// BCE route:
// Boundary/UASearchAcc.php -> Controller/UASearchAccController.php -> Entity/Account.php.
// This Boundary collects the search keyword and asks the Controller for results.
require_login(['user_admin']);

// Gets the search keyword entered by the User Admin.
$search = trim((string) ($_GET['search'] ?? ''));

// Boundary -> Controller to search user accounts.
$controller = new UASearchAccController();
$users = $controller->searchUserAccount($search);

// Checks whether the User Admin has searched.
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

    <!-- User Admin top navigation bar -->
    <?php render_admin_topbar('UASearchAcc', 'UASearchAcc.php'); ?>

    <main class="page-shell">

        <!-- Display success or error message if available -->
        <?php render_flash_if_any(); ?>

        <!-- Search user account section -->
        <section class="panel">
            <div class="panel__header panel__header--stack">
                <div>
                    <h2>Search and list user accounts</h2>
                </div>

                <!-- Search user account form -->
                <form method="get" class="inline-filters">
                    <input type="text" name="search" value="<?= e($search) ?>" placeholder="Search username, full name, email, role">
                    <button type="submit" class="button button--ghost">
                        Search
                    </button>
                </form>
            </div>

            <!-- Show message when search has no matching result -->
            <?php if ($hasSearched && $users === []): ?>
                <div class="flash flash--error">
                    No user account found for "<?= e($search) ?>". Please try another username, name, email, or role.
                </div>

            <!-- Display user account search results -->
            <?php else: ?>
                <div class="table-shell">
                    <table>
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Role</th>
                                <th>Account Status</th>
                                <th>Role Status</th>
                                <th>User Profile Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>

                        <tbody>

                            <!-- Display each user account record -->
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td>
                                        <strong><?= e($user['full_name']) ?></strong><br>
                                        <span class="muted">
                                            <?= e($user['username']) ?> | <?= e($user['email']) ?>
                                        </span>
                                    </td>

                                    <td><?= e($user['role']) ?></td>
                                    <td><?= e($user['status']) ?></td>
                                    <td><?= e($user['role_status'] ?? 'active') ?></td>
                                    <td><?= e($user['profile_status'] ?? 'active') ?></td>

                                    <!-- User account action buttons -->
                                    <td class="action-row">
                                        <a class="button button--ghost button--small"
                                           href="UAViewAcc.php?id=<?= e((string) $user['id']) ?>">
                                            View Account
                                        </a>

                                        <a class="button button--ghost button--small"
                                           href="UAUpdateAcc.php?id=<?= e((string) $user['id']) ?>">
                                            Update Account
                                        </a>

                                        <a class="button button--ghost button--small"
                                           href="UserAdminPg.php?id=<?= e((string) $user['id']) ?>">
                                            Suspend Account
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>

                            <!-- Show message when no user accounts are available -->
                            <?php if ($users === []): ?>
                                <tr>
                                    <td colspan="6">
                                        No user accounts found.
                                    </td>
                                </tr>
                            <?php endif; ?>

                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>