<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/admin_shared.php';

use App\Controller\UAViewAccC;

// BCE route:
// Boundary/UAViewAcc.php -> Controller/UAViewAccC.php -> Entity/Account.php.
// This Boundary reads the selected id and asks the Controller for one account.
require_login(['user_admin']);

// Boundary -> Controller.
$controller = new UAViewAccC();
$userId = (int) ($_GET['id'] ?? 0);
$account = $userId > 0 ? $controller->findAccount($userId) : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UAViewAcc</title>
    <link rel="stylesheet" href="../assets/css/app.css">
</head>
<body>
    <?php render_admin_topbar('UAViewAcc', 'UASearchAcc.php'); ?>
    <main class="page-shell">
        <?php render_flash_if_any(); ?>
        <section class="panel">
            <div class="panel__header">
                <div>
                    <h2><?= $account !== null ? e($account['full_name']) : 'Account not found' ?></h2>
                </div>
            </div>

            <?php if ($account !== null): ?>
                <div class="layout-grid">
                    <article class="card card--soft">
                        <h3>Account Details</h3>
                        <p><strong>Username:</strong> <?= e($account['username']) ?></p>
                        <p><strong>Email:</strong> <?= e($account['email']) ?></p>
                        <p><strong>Role:</strong> <?= e($account['role']) ?></p>
                        <p><strong>Status:</strong> <?= e($account['status']) ?></p>
                    </article>
                    <article class="card card--soft">
                        <h3>Record Information</h3>
                        <p><strong>User ID:</strong> <?= e((string) $account['id']) ?></p>
                        <p><strong>Created:</strong> <?= e(format_date($account['created_at'])) ?></p>
                    </article>
                </div>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>
