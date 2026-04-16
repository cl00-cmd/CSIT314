<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/admin_shared.php';

use App\Controller\UASearchProfileC;
use App\Controller\UASuspendProfileC;

// Boundary page for the suspend-user-profile sequence.
require_login(['user_admin']);

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    // Boundary -> Control: toggle whether the selected profile is suspended.
    $controller = new UASuspendProfileC();
    $result = $controller->suspendProfile(
        (int) ($_POST['user_id'] ?? 0),
        (string) ($_POST['target_status'] ?? 'suspended') === 'suspended'
    );
    set_flash($result['success'] ? 'success' : 'error', $result['message']);
    app_redirect('UASuspendProfile.php');
}

$search = trim((string) ($_GET['search'] ?? ''));
// Reuse the search-profile controller so admins can filter before acting on a profile.
$searchController = new UASearchProfileC();
$profiles = $searchController->searchProfile($search);
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
                    <p class="section-label">UASuspendProfile -> UASuspendProfileC -> Profile</p>
                    <h2>Suspend the user profile so that the access can be controlled</h2>
                </div>
                <form method="get" class="inline-filters">
                    <input type="text" name="search" value="<?= e($search) ?>" placeholder="Search full name, role, organisation, city">
                    <button type="submit" class="button button--ghost">Search</button>
                </form>
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
