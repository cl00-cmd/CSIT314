<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/admin_shared.php';

use App\Controller\UACreateProfileC;

// BCE route:
// Boundary/UACreateProfile.php -> Controller/UACreateProfileC.php -> Entity/Profile.php.
// This Boundary only talks to the Controller; it does not call Entity classes directly.
require_login(['user_admin']);

$controller = new UACreateProfileC();

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    // Boundary -> Control: ask the controller to add a new role type.
    $result = $controller->addProfile((string) ($_POST['role_label'] ?? ''));
    set_flash($result['success'] ? 'success' : 'error', $result['message']);
    app_redirect('UACreateProfile.php');
}

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
    <?php render_admin_topbar('UACreateProfile', 'UACreateProfile.php'); ?>
    <main class="page-shell">
        <?php render_flash_if_any(); ?>
        <section class="panel">
            <div class="panel__header">
                <div>
                    <h2>Create a user profile role so there is a new user role</h2>
                </div>
            </div>

            <form method="post" class="form-grid">
                <label class="field field--full">
                    <span>New Profile Role</span>
                    <input type="text" name="role_label" placeholder="e.g. Volunteer Coordinator" required>
                </label>

                <button type="submit" class="button button--primary">Create Profile Role</button>
            </form>
        </section>

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
                        <?php foreach ($profileTypes as $profileType): ?>
                            <tr>
                                <td><?= e($profileType['role_code']) ?></td>
                                <td><?= e($profileType['role_label']) ?></td>
                                <td><?= e($profileType['status']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</body>
</html>
