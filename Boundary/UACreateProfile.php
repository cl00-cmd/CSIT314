<?php
declare(strict_types=1);

// Load system setup, shared admin layout, and helper functions
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/admin_shared.php';

use App\Controller\UACreateProfileC;

// BCE route:
// Boundary/UACreateProfile.php -> Controller/UACreateProfileC.php -> Entity/Profile.php.
// This Boundary only talks to the Controller; it does not call Entity classes directly.
require_login(['user_admin']);

// Boundary -> Controller to manage user profile roles.
$controller = new UACreateProfileC();

// Handles create profile role form submission.
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {

    // Boundary -> Control: ask the controller to add a new role type.
    $result = $controller->addProfile((string) ($_POST['role_label'] ?? ''));

    set_flash($result['success'] ? 'success' : 'error', $result['message']);
    app_redirect('UACreateProfile.php');
}

// Gets all existing profile roles.
$profileTypes = $controller->listProfiles();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UACreateProfile</title>
    <link rel="stylesheet" href="../assets/css/app.css">
</head>
<body>

    <!-- User Admin top navigation bar -->
    <?php render_admin_topbar('UACreateProfile', 'UACreateProfile.php'); ?>

    <main class="page-shell">

        <!-- Display success or error message if available -->
        <?php render_flash_if_any(); ?>

        <!-- Create user profile role section -->
        <section class="panel">
            <div class="panel__header">
                <div>
                    <h2>Create a user profile role so there is a new user role</h2>
                </div>
            </div>

            <!-- Create user profile role form -->
            <form method="post" class="form-grid">
                <label class="field field--full">
                    <span>New Profile Role</span>
                    <input type="text" name="role_label" placeholder="e.g. Volunteer Coordinator" required>
                </label>

                <button type="submit" class="button button--primary">
                    Create Profile Role
                </button>
            </form>
        </section>

        <!-- Existing user profile roles table -->
        <section class="panel">
            <div class="panel__header">
                <div>
                    <h2>Existing user profile roles</h2>
                </div>
            </div>

            <div class="table-shell">
                <table>
                    <thead>
                        <tr>
                            <th>Role Code</th>
                            <th>Role Label</th>
                            <th>Status</th>
                        </tr>
                    </thead>

                    <tbody>

                        <!-- Display each existing profile role -->
                        <?php foreach ($profileTypes as $profileType): ?>
                            <tr>
                                <td><?= e($profileType['role_code']) ?></td>
                                <td><?= e($profileType['role_label']) ?></td>
                                <td><?= e($profileType['status']) ?></td>
                            </tr>
                        <?php endforeach; ?>

                        <!-- Show message when no profile roles are found -->
                        <?php if ($profileTypes === []): ?>
                            <tr>
                                <td colspan="3">
                                    No user profile roles found.
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