<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/admin_shared.php';

use App\Controller\UASuspendProfileC;

// BCE route for the suspend action:
// Boundary/UASuspendProfile.php -> Controller/UASuspendProfileC.php -> Entity/Profile.php.
// This Boundary only calls Controllers, never Entity classes directly.
require_login(['user_admin']);

$controller = new UASuspendProfileC();

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    // Boundary -> Control: toggle whether the selected profile role is suspended.
    // The Boundary sends only role_code and target_status; the Controller decides the outcome.
    $result = $controller->suspendProfile(
        (string) ($_POST['role_code'] ?? ''),
        (string) ($_POST['target_status'] ?? 'suspended') === 'suspended'
    );
    set_flash($result['success'] ? 'success' : 'error', $result['message']);
    app_redirect('UASuspendProfile.php');
}

$search = trim((string) ($_GET['search'] ?? ''));
// Boundary -> Controller -> Entity for the list/filter table:
// Boundary/UASuspendProfile.php -> Controller/UASuspendProfileC.php -> Entity/Profile.php.
// This table lists profile roles from profile_types, not individual user profile records.
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
    <?php render_admin_topbar('UASuspendProfile', 'UASuspendProfile.php'); ?>
    <main class="page-shell">
        <?php render_flash_if_any(); ?>
        <section class="panel">
            <div class="panel__header panel__header--stack">
                <div>
                    <h2>Suspend the profile role so that role access can be controlled</h2>
                </div>
                <form method="get" class="inline-filters">
                    <input type="text" name="search" value="<?= e($search) ?>" placeholder="Search role name or status">
                    <button type="submit" class="button button--ghost">Search</button>
                </form>
            </div>

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
                        <?php foreach ($profileRoles as $profileRole): ?>
                            <tr>
                                <td><?= e($profileRole['role_label']) ?> <span class="muted">(<?= e($profileRole['role_code']) ?>)</span></td>
                                <td><?= e($profileRole['status']) ?></td>
                                <td>
                                    <!-- role_code identifies the profile role to suspend/reactivate in Entity/Profile.php. -->
                                    <form method="post">
                                        <input type="hidden" name="role_code" value="<?= e($profileRole['role_code']) ?>">
                                        <input type="hidden" name="target_status" value="<?= e(($profileRole['status'] ?? '') === 'suspended' ? 'active' : 'suspended') ?>">
                                        <button type="submit" class="button <?= ($profileRole['status'] ?? '') === 'suspended' ? 'button--ghost' : 'button--primary' ?> button--small">
                                            <?= ($profileRole['status'] ?? '') === 'suspended' ? 'Reactivate' : 'Suspend' ?>
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
