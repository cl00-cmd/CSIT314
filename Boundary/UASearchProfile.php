<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/admin_shared.php';

use App\Controller\UASearchProfileC;

// BCE route:
// Boundary/UASearchProfile.php -> Controller/UASearchProfileC.php -> Entity/Profile.php.
// This Boundary searches profile roles in profile_types through the Controller.
require_login(['user_admin']);

$search = trim((string) ($_GET['search'] ?? ''));
// Boundary -> Controller.
$controller = new UASearchProfileC();
$profiles = $controller->searchProfile($search);
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
    <?php render_admin_topbar('UASearchProfile', 'UASearchProfile.php'); ?>
    <main class="page-shell">
        <?php render_flash_if_any(); ?>
        <section class="panel">
            <div class="panel__header panel__header--stack">
                <div>
                    <h2>Search profile roles</h2>
                </div>
                <form method="get" class="inline-filters">
                    <input type="text" name="search" value="<?= e($search) ?>" placeholder="Search role name, role code, or status">
                    <button type="submit" class="button button--ghost">Search</button>
                </form>
            </div>

            <?php if ($hasSearched && $profiles === []): ?>
                <div class="flash flash--error">
                    No profile role found for "<?= e($search) ?>". Please try another role name, role code, or status.
                </div>
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
                            <?php foreach ($profiles as $profile): ?>
                                <tr>
                                    <td><?= e($profile['role_code']) ?></td>
                                    <td><strong><?= e($profile['role_label']) ?></strong></td>
                                    <td><?= e($profile['status']) ?></td>
                                    <td><?= e(format_date($profile['created_at'] ?? null)) ?></td>
                                    <td class="action-row">
                                        <a class="button button--ghost button--small" href="UAViewProfile.php?role_code=<?= e(rawurlencode((string) $profile['role_code'])) ?>">View Profile</a>
                                        <a class="button button--ghost button--small" href="UAUpdateProfile.php?role_code=<?= e(rawurlencode((string) $profile['role_code'])) ?>">Update Profile</a>
                                        <a class="button button--ghost button--small" href="UASuspendProfile.php?search=<?= e(rawurlencode((string) $profile['role_code'])) ?>">Suspend Profile</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>
