<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/admin_shared.php';

use App\Controller\ViewProfileDetailsController;

// BCE route:
// Boundary/view_profilePg.php -> Controller/ViewProfileDetailsController.php -> Entity/UserProfileEntity.php.
// This Boundary reads the selected user id and asks the Controller for profile details.
require_login(['user_admin']);

// Boundary -> Controller.
$controller = new ViewProfileDetailsController();
$userId = (int) ($_GET['id'] ?? 0);
$profile = $userId > 0 ? $controller->viewProfile($userId) : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View User Profile</title>
    <link rel="stylesheet" href="../assets/css/app.css">
</head>
<body>
    <?php render_admin_topbar('View User Profile', 'view_profiles.php'); ?>
    <main class="page-shell">
        <?php render_flash_if_any(); ?>
        <section class="panel">
            <div class="panel__header">
                <div>
                    <p class="section-label">View User Profile</p>
                    <h2><?= $profile !== null ? e($profile['full_name']) : 'Profile not found' ?></h2>
                </div>
            </div>

            <?php if ($profile !== null): ?>
                <div class="layout-grid">
                    <article class="card card--soft">
                        <h3>Account Summary</h3>
                        <p><strong>Username:</strong> <?= e($profile['username']) ?></p>
                        <p><strong>Email:</strong> <?= e($profile['email']) ?></p>
                        <p><strong>Role:</strong> <?= e($profile['role']) ?></p>
                        <p><strong>Profile Status:</strong> <?= e($profile['status']) ?></p>
                    </article>
                    <article class="card card--soft">
                        <h3>Profile Details</h3>
                        <p><strong>Phone:</strong> <?= e($profile['phone']) ?></p>
                        <p><strong>Organisation:</strong> <?= e($profile['organisation']) ?></p>
                        <p><strong>City:</strong> <?= e($profile['city']) ?></p>
                        <p><strong>Biography:</strong> <?= e($profile['biography']) ?></p>
                    </article>
                </div>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>
