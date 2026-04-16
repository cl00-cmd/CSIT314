<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/admin_shared.php';

use App\Controller\SearchProfileController;
use App\Controller\SuspendProfileController;

// BCE routes:
// Boundary/suspendProfilePg.php -> Controller/SearchProfileController.php -> Entity/UserProfileEntity.php.
// Boundary/suspendProfilePg.php -> Controller/SuspendProfileController.php -> Entity/UserProfileEntity.php.
// This older generic suspend page searches profiles and updates status through Controllers.
require_login(['user_admin']);

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    // Boundary -> Controller for profile status changes.
    $controller = new SuspendProfileController();
    $result = $controller->suspendProfile(
        (int) ($_POST['user_id'] ?? 0),
        (string) ($_POST['target_status'] ?? 'suspended') === 'suspended'
    );
    set_flash($result['success'] ? 'success' : 'error', $result['message']);
    app_redirect('suspendProfilePg.php');
}

// Boundary -> Controller for the profile list.
$searchController = new SearchProfileController();
$profiles = $searchController->searchProfile(trim((string) ($_GET['search'] ?? '')));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Suspend User Profile</title>
    <link rel="stylesheet" href="../assets/css/app.css">
</head>
<body>
    <?php render_admin_topbar('Suspend User Profile', 'suspendProfilePg.php'); ?>
    <main class="page-shell">
        <?php render_flash_if_any(); ?>
        <section class="panel">
            <div class="panel__header">
                <div>
                    <p class="section-label">Suspend User Profile</p>
                    <h2>Control whether a user profile remains active</h2>
                </div>
            </div>

            <div class="table-shell">
                <table>
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Organisation</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($profiles as $profile): ?>
                            <tr>
                                <td><?= e($profile['full_name']) ?> <span class="muted">(<?= e($profile['username']) ?>)</span></td>
                                <td><?= e($profile['organisation']) ?> <span class="muted"><?= e($profile['city']) ?></span></td>
                                <td><?= e($profile['status']) ?></td>
                                <td>
                                    <form method="post">
                                        <input type="hidden" name="user_id" value="<?= e((string) $profile['id']) ?>">
                                        <input type="hidden" name="target_status" value="<?= e(($profile['status'] ?? '') === 'suspended' ? 'active' : 'suspended') ?>">
                                        <button type="submit" class="button <?= ($profile['status'] ?? '') === 'suspended' ? 'button--ghost' : 'button--primary' ?> button--small">
                                            <?= ($profile['status'] ?? '') === 'suspended' ? 'Reactivate Profile' : 'Suspend Profile' ?>
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
