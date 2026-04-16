<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/admin_shared.php';

use App\Controller\UASearchProfileC;

// BCE route:
// Boundary/UASearchProfile.php -> Controller/UASearchProfileC.php -> Entity/Profile.php.
// This Boundary collects the search keyword and asks the Controller for results.
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
                    <p class="section-label">UASearchProfile -> UASearchProfileC -> Profile</p>
                    <h2>Search for a user profile</h2>
                </div>
                <form method="get" class="inline-filters">
                    <input type="text" name="search" value="<?= e($search) ?>" placeholder="Search full name, role, organisation, city">
                    <button type="submit" class="button button--ghost">Search</button>
                </form>
            </div>

            <?php if ($hasSearched && $profiles === []): ?>
                <div class="flash flash--error">
                    No user profile found for "<?= e($search) ?>". Please try another name, role, organisation, or city.
                </div>
            <?php else: ?>
                <div class="table-shell">
                    <table>
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Contact</th>
                                <th>Organisation</th>
                                <th>Profile Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($profiles as $profile): ?>
                                <tr>
                                    <td>
                                        <strong><?= e($profile['full_name']) ?></strong><br>
                                        <span class="muted"><?= e($profile['username']) ?> | <?= e($profile['role']) ?></span>
                                    </td>
                                    <td><?= e($profile['phone']) ?><br><span class="muted"><?= e($profile['email']) ?></span></td>
                                    <td><?= e($profile['organisation']) ?><br><span class="muted"><?= e($profile['city']) ?></span></td>
                                    <td><?= e($profile['status']) ?></td>
                                    <td class="action-row">
                                        <a class="button button--ghost button--small" href="UAViewProfile.php?id=<?= e((string) $profile['id']) ?>">View Profile</a>
                                        <a class="button button--ghost button--small" href="UAUpdateProfile.php?id=<?= e((string) $profile['id']) ?>">Update Profile</a>
                                        <a class="button button--ghost button--small" href="UASuspendProfile.php?id=<?= e((string) $profile['id']) ?>">Suspend Profile</a>
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
