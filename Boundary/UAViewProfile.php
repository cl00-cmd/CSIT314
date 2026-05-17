<?php
declare(strict_types=1);

// Load system setup, shared admin layout, and helper functions
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/admin_shared.php';

use App\Controller\UAViewProfileC;

// BCE route:
// Boundary/UAViewProfile.php -> Controller/UAViewProfileC.php -> Entity/Profile.php.
// This Boundary views profile roles from profile_types through the Controller.
require_login(['user_admin']);

// Boundary -> Controller to retrieve profile role details.
$controller = new UAViewProfileC();

// Gets the selected profile role code.
$roleCode = trim((string) ($_GET['role_code'] ?? ''));

// Gets selected profile role details if a role code is provided.
$profile = $roleCode !== '' ? $controller->findProfile($roleCode) : null;

// Gets all profile roles for the table.
$profileRoles = $controller->listProfiles();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UAViewProfile</title>
    <link rel="stylesheet" href="../assets/css/app.css">
</head>
<body>

    <!-- User Admin top navigation bar -->
    <?php render_admin_topbar('UAViewProfile', 'UASearchProfile.php'); ?>

    <main class="page-shell">

        <!-- Display success or error message if available -->
        <?php render_flash_if_any(); ?>

        <!-- View selected profile role section -->
        <section class="panel">
            <div class="panel__header">
                <div>
                    <h2>
                        <?= $profile !== null ? e($profile['role_label']) : 'View all profile roles' ?>
                    </h2>
                </div>
            </div>

            <!-- Display selected profile role details -->
            <?php if ($profile !== null): ?>
                <div class="layout-grid">
                    <article class="card card--soft">
                        <h3>Profile Role Details</h3>
                        <p><strong>Role Code:</strong> <?= e($profile['role_code']) ?></p>
                        <p><strong>Role Name:</strong> <?= e($profile['role_label']) ?></p>
                        <p><strong>Status:</strong> <?= e($profile['status']) ?></p>
                        <p><strong>Created:</strong> <?= e(format_date($profile['created_at'] ?? null)) ?></p>
                    </article>
                </div>

            <!-- Show message when selected profile role cannot be found -->
            <?php elseif ($roleCode !== ''): ?>
                <div class="flash flash--error">
                    Profile role not found.
                </div>
            <?php endif; ?>
        </section>

        <!-- All profile roles table -->
        <section class="panel">
            <div class="panel__header">
                <div>
                    <h2>All profile roles</h2>
                </div>
            </div>

            <div class="table-shell">
                <table>
                    <thead>
                        <tr>
                            <th>Role Code</th>
                            <th>Role Name</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>

                        <!-- Display each profile role -->
                        <?php foreach ($profileRoles as $profileRole): ?>
                            <tr>
                                <td><?= e($profileRole['role_code']) ?></td>
                                <td><strong><?= e($profileRole['role_label']) ?></strong></td>
                                <td><?= e($profileRole['status']) ?></td>
                                <td><?= e(format_date($profileRole['created_at'] ?? null)) ?></td>

                                <!-- Profile role action buttons -->
                                <td class="action-row">
                                    <a class="button button--ghost button--small"
                                       href="UAViewProfile.php?role_code=<?= e(rawurlencode((string) $profileRole['role_code'])) ?>">
                                        View Profile
                                    </a>

                                    <a class="button button--ghost button--small"
                                       href="UAUpdateProfile.php?role_code=<?= e(rawurlencode((string) $profileRole['role_code'])) ?>">
                                        Update Profile
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>

                        <!-- Show message when no profile roles are found -->
                        <?php if ($profileRoles === []): ?>
                            <tr>
                                <td colspan="5">
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