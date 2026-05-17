<?php
declare(strict_types=1);

// Load system setup, shared admin layout, and helper functions
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/admin_shared.php';

use App\Controller\UASuspendProfileC;

// BCE route for the suspend action:
// Boundary/UASuspendProfile.php -> Controller/UASuspendProfileC.php -> Entity/Profile.php.
// This Boundary only calls Controllers, never Entity classes directly.
require_login(['user_admin']);

// Boundary -> Controller to manage profile role suspension.
$controller = new UASuspendProfileC();

// Handles suspend or reactivate profile role action.
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {

    // Boundary -> Control: toggle whether the selected profile role is suspended.
    $result = $controller->suspendProfile(
        (string) ($_POST['role_code'] ?? ''),
        (string) ($_POST['target_status'] ?? 'suspended') === 'suspended'
    );

    set_flash($result['success'] ? 'success' : 'error', $result['message']);
    app_redirect('UASuspendProfile.php');
}

// Gets the search keyword entered by the User Admin.
$search = trim((string) ($_GET['search'] ?? ''));

// Gets profile roles based on the search keyword.
$profileRoles = $controller->searchProfileRoles($search);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UASuspendProfile</title>
    <link rel="stylesheet" href="../assets/css/app.css">
</head>
<body>

    <!-- User Admin top navigation bar -->
    <?php render_admin_topbar('UASuspendProfile', 'UASuspendProfile.php'); ?>

    <main class="page-shell">

        <!-- Display success or error message if available -->
        <?php render_flash_if_any(); ?>

        <!-- Suspend profile role section -->
        <section class="panel">
            <div class="panel__header panel__header--stack">
                <div>
                    <h2>Suspend the profile role so that role access can be controlled</h2>
                </div>

                <!-- Search profile role form -->
                <form method="get" class="inline-filters">
                    <input type="text" name="search" value="<?= e($search) ?>" placeholder="Search role name or status">

                    <button type="submit" class="button button--ghost">
                        Search
                    </button>
                </form>
            </div>

            <!-- Profile role suspension table -->
            <div class="table-shell">
                <table>
                    <thead>
                        <tr>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>

                        <!-- Display each profile role -->
                        <?php foreach ($profileRoles as $profileRole): ?>
                            <tr>
                                <td>
                                    <?= e($profileRole['role_label']) ?>
                                    <span class="muted">
                                        (<?= e($profileRole['role_code']) ?>)
                                    </span>
                                </td>

                                <td><?= e($profileRole['status']) ?></td>

                                <!-- Suspend or reactivate profile role -->
                                <td>
                                    <form method="post">
                                        <input type="hidden" name="role_code" value="<?= e($profileRole['role_code']) ?>">
                                        <input type="hidden" name="target_status" value="<?= e(($profileRole['status'] ?? '') === 'suspended' ? 'active' : 'suspended') ?>">

                                        <button type="submit"
                                                class="button <?= ($profileRole['status'] ?? '') === 'suspended' ? 'button--ghost' : 'button--primary' ?> button--small">
                                            <?= ($profileRole['status'] ?? '') === 'suspended' ? 'Reactivate' : 'Suspend' ?>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>

                        <!-- Show message when no profile roles are found -->
                        <?php if ($profileRoles === []): ?>
                            <tr>
                                <td colspan="3">
                                    No profile roles found.
                                </td>
                            </tr>
                        <?php endif; ?>

                    </tbody>
                </table>
            </div>
        </section>
    </main>
</body>
</html>