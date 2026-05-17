<?php
declare(strict_types=1);

// Load system setup, shared admin layout, and helper functions
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/admin_shared.php';

use App\Controller\UASearchProfileC;

// BCE route:
// Boundary/UASearchProfile.php -> Controller/UASearchProfileC.php -> Entity/Profile.php.
// This Boundary searches profile roles in profile_types through the Controller.
require_login(['user_admin']);

// Gets the search keyword entered by the User Admin.
$search = trim((string) ($_GET['search'] ?? ''));

// Boundary -> Controller to search profile roles.
$controller = new UASearchProfileC();
$profiles = $controller->searchProfile($search);

// Checks whether the User Admin has searched.
$hasSearched = $search !== '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UASearchProfile</title>
    <link rel="stylesheet" href="../assets/css/app.css">
</head>
<body>

    <!-- User Admin top navigation bar -->
    <?php render_admin_topbar('UASearchProfile', 'UASearchProfile.php'); ?>

    <main class="page-shell">

        <!-- Display success or error message if available -->
        <?php render_flash_if_any(); ?>

        <!-- Search profile role section -->
        <section class="panel">
            <div class="panel__header panel__header--stack">
                <div>
                    <h2>Search profile roles</h2>
                </div>

                <!-- Search profile role form -->
                <form method="get" class="inline-filters">
                    <input type="text" name="search" value="<?= e($search) ?>" placeholder="Search role name, role code, or status">

                    <button type="submit" class="button button--ghost">
                        Search
                    </button>
                </form>
            </div>

            <!-- Show message when search has no matching result -->
            <?php if ($hasSearched && $profiles === []): ?>
                <div class="flash flash--error">
                    No profile role found for "<?= e($search) ?>". Please try another role name, role code, or status.
                </div>

            <!-- Display profile role search results -->
            <?php else: ?>
                <div class="table-shell">
                    <table>
                        <thead>
                            <tr>
                                <th>Role Code</th>
                                <th>Role Name</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>

                        <tbody>

                            <!-- Display each profile role record -->
                            <?php foreach ($profiles as $profile): ?>
                                <tr>
                                    <td><?= e($profile['role_code']) ?></td>
                                    <td><strong><?= e($profile['role_label']) ?></strong></td>
                                    <td><?= e($profile['status']) ?></td>
                                    <td><?= e(format_date($profile['created_at'] ?? null)) ?></td>

                                    <!-- Profile role action buttons -->
                                    <td class="action-row">
                                        <a class="button button--ghost button--small"
                                           href="UAViewProfile.php?role_code=<?= e(rawurlencode((string) $profile['role_code'])) ?>">
                                            View Profile
                                        </a>

                                        <a class="button button--ghost button--small"
                                           href="UAUpdateProfile.php?role_code=<?= e(rawurlencode((string) $profile['role_code'])) ?>">
                                            Update Profile
                                        </a>

                                        <a class="button button--ghost button--small"
                                           href="UASuspendProfile.php?search=<?= e(rawurlencode((string) $profile['role_code'])) ?>">
                                            Suspend Profile
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>

                            <!-- Show message when no profile roles are available -->
                            <?php if ($profiles === []): ?>
                                <tr>
                                    <td colspan="5">
                                        No profile roles found.
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